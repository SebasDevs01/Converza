<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? 'user') !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once __DIR__.'/../models/config.php';

// Función para determinar el tipo de publicación
function determinarTipoPublicacion($contenido, $imagenes, $totalImagenes) {
    $tieneTexto = !empty(trim($contenido));
    $tieneImagenes = !empty($imagenes) && $totalImagenes > 0;
    $tieneVideo = false;
    
    // Detectar videos en contenido
    if ($tieneTexto) {
        $patronesVideo = [
            '/youtube\.com\/watch\?v=/',
            '/youtu\.be\//',
            '/vimeo\.com\//',
            '/\.mp4(\?|$|&)/',
            '/\.webm(\?|$|&)/',
            '/\.avi(\?|$|&)/',
            '/\.mov(\?|$|&)/',
            '/\.mkv(\?|$|&)/'
        ];
        
        foreach ($patronesVideo as $patron) {
            if (preg_match($patron, $contenido)) {
                $tieneVideo = true;
                break;
            }
        }
    }
    
    // Detectar videos en nombres de archivo
    if ($tieneImagenes) {
        $archivos = explode(',', $imagenes);
        foreach ($archivos as $archivo) {
            $extension = strtolower(pathinfo(trim($archivo), PATHINFO_EXTENSION));
            if (in_array($extension, ['mp4', 'webm', 'avi', 'mov', 'mkv', 'flv', '3gp'])) {
                $tieneVideo = true;
                break;
            }
        }
    }
    
    // Retornar tipo con información detallada
    return [
        'tieneTexto' => $tieneTexto,
        'tieneImagenes' => $tieneImagenes,
        'tieneVideo' => $tieneVideo,
        'totalImagenes' => $totalImagenes
    ];
}

$mensaje = '';
$error = '';

// Crear tabla seguidores si no existe
try {
    $conexion->exec("CREATE TABLE IF NOT EXISTS seguidores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seguidor_id INT NOT NULL,
        seguido_id INT NOT NULL,
        fecha_seguimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seguidor_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        FOREIGN KEY (seguido_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        INDEX idx_seguidor (seguidor_id),
        INDEX idx_seguido (seguido_id),
        INDEX idx_seguimiento (seguidor_id, seguido_id),
        UNIQUE KEY unique_seguimiento (seguidor_id, seguido_id)
    )");
} catch (Exception $e) {
    // Tabla ya existe o error menor, continuar
}

// Verificar mensajes temporales
if (isset($_SESSION['mensaje_temp'])) {
    $mensaje = $_SESSION['mensaje_temp'];
    unset($_SESSION['mensaje_temp']);
}

// Procesar acciones de administración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_publicacion']) && is_numeric($_POST['eliminar_publicacion'])) {
        $pubId = (int)$_POST['eliminar_publicacion'];
        try {
            // Verificar/crear tabla imagenes_publicacion si no existe
            $conexion->exec("CREATE TABLE IF NOT EXISTS imagenes_publicacion (
                id INT AUTO_INCREMENT PRIMARY KEY,
                publicacion_id INT NOT NULL,
                nombre_imagen VARCHAR(255) NOT NULL,
                INDEX idx_publicacion (publicacion_id)
            )");
            
            // Eliminar imágenes asociadas
            $stmtImg = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = :pub");
            $stmtImg->bindParam(':pub', $pubId, PDO::PARAM_INT);
            $stmtImg->execute();
            $imagenes = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
            
            // Eliminar archivos físicos
            foreach ($imagenes as $img) {
                $rutaImg = __DIR__.'/../../public/publicaciones/'.$img;
                if (file_exists($rutaImg)) unlink($rutaImg);
            }
            
            // Eliminar registros de base de datos
            try {
                $conexion->prepare("DELETE FROM imagenes_publicacion WHERE publicacion_id = :pub")->execute([':pub' => $pubId]);
            } catch (PDOException $e) {
                // Si la tabla no existe, continuar
            }
            
            $conexion->prepare("DELETE FROM comentarios WHERE publicacion = :pub")->execute([':pub' => $pubId]);
            
            // Eliminar reacciones (usar id_publicacion que es el campo correcto)
            try {
                $conexion->prepare("DELETE FROM reacciones WHERE id_publicacion = :pub")->execute([':pub' => $pubId]);
            } catch (PDOException $e) {
                // Si la tabla no existe, no pasa nada
                if (strpos($e->getMessage(), "doesn't exist") === false) {
                    throw $e; // Re-lanzar si es otro error
                }
            }
            $conexion->prepare("DELETE FROM publicaciones WHERE id_pub = :pub")->execute([':pub' => $pubId]);
            
            $mensaje = "Publicación eliminada correctamente.";
        } catch (Exception $e) {
            $error = "Error al eliminar publicación: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['eliminar_comentario']) && is_numeric($_POST['eliminar_comentario'])) {
        $comId = (int)$_POST['eliminar_comentario'];
        try {
            $conexion->prepare("DELETE FROM comentarios WHERE id_com = :com")->execute([':com' => $comId]);
            $mensaje = "Comentario eliminado correctamente.";
        } catch (Exception $e) {
            $error = "Error al eliminar comentario: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['bloquear_usuario']) && is_numeric($_POST['bloquear_usuario'])) {
        $userId = (int)$_POST['bloquear_usuario'];
        try {
            // Verificar que no es admin ni el mismo usuario
            $stmtTipo = $conexion->prepare("SELECT tipo FROM usuarios WHERE id_use = :id");
            $stmtTipo->execute([':id' => $userId]);
            $tipo = $stmtTipo->fetchColumn();
            
            if ($tipo !== 'admin' && $userId !== (int)$_SESSION['id']) {
                // Debug: Verificar usuario antes de la actualización
                $stmtDebug = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
                $stmtDebug->execute([':id' => $userId]);
                $usuarioAntes = $stmtDebug->fetch();
                
                $stmt = $conexion->prepare("UPDATE usuarios SET tipo = 'blocked' WHERE id_use = :id");
                $result = $stmt->execute([':id' => $userId]);
                
                if ($result && $stmt->rowCount() > 0) {
                    $_SESSION['mensaje_temp'] = "Usuario bloqueado correctamente.";
                } else {
                    // Debug mejorado
                    $errorInfo = $stmt->errorInfo();
                    $_SESSION['mensaje_temp'] = "Error: No se pudo bloquear el usuario. " .
                        "ID: $userId, " .
                        "Filas afectadas: " . $stmt->rowCount() . ", " .
                        "Usuario existe: " . ($usuarioAntes ? 'Sí' : 'No') . ", " .
                        "Tipo actual: " . ($usuarioAntes['tipo'] ?? 'NULL') . ", " .
                        "Error SQL: " . $errorInfo[2];
                }
                header("Location: admin.php");
                exit();
            } else {
                $error = "No puedes bloquear administradores o a ti mismo.";
            }
        } catch (Exception $e) {
            $error = "Error al bloquear usuario: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['desbloquear_usuario']) && is_numeric($_POST['desbloquear_usuario'])) {
        $userId = (int)$_POST['desbloquear_usuario'];
        try {
            // Debug: Verificar usuario antes de la actualización
            $stmtDebug = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
            $stmtDebug->execute([':id' => $userId]);
            $usuarioAntes = $stmtDebug->fetch();
            
            $stmt = $conexion->prepare("UPDATE usuarios SET tipo = 'user' WHERE id_use = :id");
            $result = $stmt->execute([':id' => $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['mensaje_temp'] = "Usuario desbloqueado correctamente.";
            } else {
                // Debug mejorado
                $errorInfo = $stmt->errorInfo();
                $_SESSION['mensaje_temp'] = "Error: No se pudo desbloquear el usuario. " .
                    "ID: $userId, " .
                    "Filas afectadas: " . $stmt->rowCount() . ", " .
                    "Usuario existe: " . ($usuarioAntes ? 'Sí' : 'No') . ", " .
                    "Tipo actual: " . ($usuarioAntes['tipo'] ?? 'NULL') . ", " .
                    "Error SQL: " . $errorInfo[2];
            }
            header("Location: admin.php");
            exit();
        } catch (Exception $e) {
            $error = "Error al desbloquear usuario: " . $e->getMessage();
        }
    }
}

// Eliminar usuario si se solicita (GET para compatibilidad)
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $idEliminar = (int)$_GET['eliminar'];
    $stmtTipo = $conexion->prepare("SELECT tipo FROM usuarios WHERE id_use = :id");
    $stmtTipo->bindParam(':id', $idEliminar, PDO::PARAM_INT);
    $stmtTipo->execute();
    $tipoEliminar = $stmtTipo->fetchColumn();
    if ($tipoEliminar !== 'admin' && $idEliminar !== (int)$_SESSION['id']) {
        $stmtDel = $conexion->prepare("DELETE FROM usuarios WHERE id_use = :id");
        $stmtDel->bindParam(':id', $idEliminar, PDO::PARAM_INT);
        $stmtDel->execute();
        $mensaje = "Usuario eliminado correctamente.";
    }
}

// Obtener datos para el panel
$stmt = $conexion->prepare("SELECT id_use, usuario, avatar, tipo, fecha_reg FROM usuarios ORDER BY id_use DESC");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener publicaciones recientes (usando solo la tabla publicaciones por ahora)
$stmtPubs = $conexion->prepare("SELECT p.*, u.usuario FROM publicaciones p JOIN usuarios u ON p.usuario = u.id_use ORDER BY p.id_pub DESC LIMIT 10");
$stmtPubs->execute();
$publicaciones = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);

// Agregar información de imágenes manualmente de forma robusta
foreach ($publicaciones as &$pub) {
    $pub['imagenes'] = '';
    $pub['total_imagenes'] = 0;
    
    // Método 1: Campo imagen en publicaciones
    if (isset($pub['imagen']) && !empty(trim($pub['imagen']))) {
        $pub['imagenes'] = trim($pub['imagen']);
        $pub['total_imagenes'] = 1;
    }
    
    // Método 2: Tabla imagenes_publicacion
    try {
        $stmtImg = $conexion->prepare("SELECT COUNT(*) FROM imagenes_publicacion WHERE publicacion_id = ?");
        $stmtImg->execute([$pub['id_pub']]);
        $imgCount = $stmtImg->fetchColumn();
        
        if ($imgCount > 0) {
            $pub['total_imagenes'] = $imgCount;
            
            // Obtener nombres de archivos
            try {
                $stmtNames = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = ? LIMIT 3");
                $stmtNames->execute([$pub['id_pub']]);
                $nombres = $stmtNames->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($nombres)) {
                    $pub['imagenes'] = implode(',', array_filter($nombres));
                }
            } catch (Exception $e) {
                // Ignorar errores al obtener nombres
            }
        }
    } catch (Exception $e) {
        // Tabla imagenes_publicacion no existe, usar solo campo imagen
    }
}

// Obtener comentarios recientes con información completa de la publicación
$stmtComs = $conexion->prepare("
    SELECT c.*, 
           u.usuario, 
           p.contenido as pub_contenido, 
           p.imagen as pub_imagen,
           p.id_pub,
           up.usuario as pub_autor,
           GROUP_CONCAT(DISTINCT ip.nombre_imagen ORDER BY ip.id SEPARATOR ',') as imagenes,
           COUNT(DISTINCT ip.id) as total_imagenes
    FROM comentarios c 
    JOIN usuarios u ON c.usuario = u.id_use 
    JOIN publicaciones p ON c.publicacion = p.id_pub 
    JOIN usuarios up ON p.usuario = up.id_use
    LEFT JOIN imagenes_publicacion ip ON p.id_pub = ip.publicacion_id
    GROUP BY c.id_com, c.comentario, c.fecha, c.publicacion, c.usuario, 
             u.usuario, p.contenido, p.imagen, p.id_pub, up.usuario
    ORDER BY c.id_com DESC LIMIT 10
");
$stmtComs->execute();
$comentarios = $stmtComs->fetchAll(PDO::FETCH_ASSOC);

// Obtener estadísticas de seguimiento
try {
    $stmtSeguidores = $conexion->prepare("SELECT COUNT(*) as total FROM seguidores");
    $stmtSeguidores->execute();
    $totalSeguimientos = $stmtSeguidores->fetch()['total'];
    
    $stmtUsuariosActivos = $conexion->prepare("
        SELECT COUNT(DISTINCT seguidor_id) as total 
        FROM seguidores 
        WHERE DATE(fecha_seguimiento) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmtUsuariosActivos->execute();
    $usuariosActivosSeguimiento = $stmtUsuariosActivos->fetch()['total'];
} catch (Exception $e) {
    $totalSeguimientos = 0;
    $usuariosActivosSeguimiento = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Panel Admin | Converza</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/public/css/component.css" />
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">Converza Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="../view/index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="../presenters/perfil.php?id=<?php echo (int)$_SESSION['id']; ?>"><i class="bi bi-person-circle"></i> Perfil</a></li>
        <li class="nav-item"><a class="nav-link" href="../presenters/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4">
  <?php if ($mensaje): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($mensaje); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>
  
  <?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Panel de estadísticas -->
    <div class="col-12">
      <div class="row g-3">
        <div class="col-md-3">
          <div class="card bg-primary text-white">
            <div class="card-body text-center">
              <i class="bi bi-people fs-1"></i>
              <h3><?php echo count($usuarios); ?></h3>
              <small>Usuarios totales</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-success text-white">
            <div class="card-body text-center">
              <i class="bi bi-chat-square-text fs-1"></i>
              <h3><?php echo count($publicaciones); ?></h3>
              <small>Publicaciones recientes</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-info text-white">
            <div class="card-body text-center">
              <i class="bi bi-chat-dots fs-1"></i>
              <h3><?php echo count($comentarios); ?></h3>
              <small>Comentarios recientes</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-warning text-dark">
            <div class="card-body text-center">
              <i class="bi bi-shield-lock fs-1"></i>
              <h3><?php echo count(array_filter($usuarios, fn($u) => $u['tipo'] === 'blocked')); ?></h3>
              <small>Usuarios bloqueados</small>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Segunda fila de estadísticas: Seguimiento -->
      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <div class="card bg-purple text-white" style="background-color: #6f42c1;">
            <div class="card-body text-center">
              <i class="bi bi-person-hearts fs-1"></i>
              <h3><?php echo $totalSeguimientos; ?></h3>
              <small>Conexiones de seguimiento</small>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-dark text-white">
            <div class="card-body text-center">
              <i class="bi bi-activity fs-1"></i>
              <h3><?php echo $usuariosActivosSeguimiento; ?></h3>
              <small>Usuarios activos (7 días)</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gestión de Usuarios -->
    <div class="col-12 col-lg-6">
      <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-people"></i> Gestión de Usuarios</h5>
        </div>
        <div class="card-body bg-light">
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-middle">
              <thead class="table-dark sticky-top">
                <tr>
                  <th>Usuario</th>
                  <th>Tipo</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <?php
                        $avatar = htmlspecialchars($u['avatar']);
                        $avatarPath = __DIR__.'/../../public/avatars/'.$avatar;
                        if ($avatar && file_exists($avatarPath)) {
                          $src = '/converza/public/avatars/'.$avatar;
                        } else {
                          $src = '/converza/public/avatars/defect.jpg';
                        }
                      ?>
                      <img src="<?php echo $src; ?>" alt="avatar" width="32" height="32" class="rounded-circle me-2">
                      <div>
                        <strong><?php echo htmlspecialchars($u['usuario']); ?></strong><br>
                        <small class="text-muted">ID: <?php echo (int)$u['id_use']; ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge <?php 
                      echo $u['tipo'] === 'admin' ? 'bg-warning text-dark' : 'bg-secondary text-white'; 
                    ?>">
                      <?php echo $u['tipo'] === 'admin' ? 'admin' : 'user'; ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge <?php 
                      echo $u['tipo'] === 'blocked' ? 'bg-danger text-white' : 'bg-success text-white'; 
                    ?>">
                      <?php echo $u['tipo'] === 'blocked' ? 'Bloqueado' : 'Activo'; ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($u['tipo'] === 'admin'): ?>
                      <span class="text-muted"><i class="bi bi-shield-lock"></i> Protegido</span>
                    <?php elseif ($u['id_use'] === (int)$_SESSION['id']): ?>
                      <span class="text-muted"><i class="bi bi-person-check"></i> Tú</span>
                    <?php else: ?>
                      <div class="d-flex gap-1 flex-wrap">
                        <!-- Botón Toggle Bloquear/Desbloquear -->
                        <?php if ($u['tipo'] === 'blocked'): ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="desbloquear_usuario" value="<?php echo (int)$u['id_use']; ?>">
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Desbloquear usuario <?php echo htmlspecialchars($u['usuario']); ?>?');" title="Usuario bloqueado - Click para desbloquear">
                              <i class="bi bi-unlock-fill"></i> Activo
                            </button>
                          </form>
                        <?php else: ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="bloquear_usuario" value="<?php echo (int)$u['id_use']; ?>">
                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Bloquear usuario <?php echo htmlspecialchars($u['usuario']); ?>?');" title="Usuario activo - Click para bloquear">
                              <i class="bi bi-lock-fill"></i> Bloquear
                            </button>
                          </form>
                        <?php endif; ?>
                        
                        <!-- Botón Eliminar -->
                        <a href="admin.php?eliminar=<?php echo (int)$u['id_use']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar permanentemente a <?php echo htmlspecialchars($u['usuario']); ?>?');" title="Eliminar usuario permanentemente">
                          <i class="bi bi-person-x-fill"></i>
                        </a>
                      </div>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Gestión de Publicaciones -->
    <div class="col-12 col-lg-6">
      <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="bi bi-chat-square-text"></i> Publicaciones Recientes</h5>
        </div>
        <div class="card-body bg-light">
          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover align-middle">
              <thead class="table-dark sticky-top">
                <tr>
                  <th>Usuario</th>
                  <th>Tipo</th>
                  <th>Contenido</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($publicaciones as $pub): ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($pub['usuario']); ?></strong><br>
                    <small class="text-muted"><?php echo htmlspecialchars($pub['fecha']); ?></small>
                  </td>
                  <td>
                    <?php 
                      $tipo = determinarTipoPublicacion($pub['contenido'], $pub['imagenes'], $pub['total_imagenes']);
                      
                      // Mostrar tipo con iconos y colores
                      if ($tipo['tieneTexto'] && $tipo['tieneImagenes'] && $tipo['tieneVideo']) {
                          echo '<span class="badge bg-info text-white"><i class="bi bi-collection"></i> Completo</span>';
                      } elseif ($tipo['tieneTexto'] && $tipo['tieneImagenes']) {
                          echo '<span class="badge bg-primary text-white"><i class="bi bi-image-fill"></i> Texto + Imagen</span>';
                      } elseif ($tipo['tieneTexto'] && $tipo['tieneVideo']) {
                          echo '<span class="badge bg-warning text-dark"><i class="bi bi-play-circle"></i> Texto + Video</span>';
                      } elseif ($tipo['tieneImagenes'] && !$tipo['tieneTexto']) {
                          $cantidadTexto = $tipo['totalImagenes'] > 1 ? "Imágenes ({$tipo['totalImagenes']})" : "Imagen";
                          echo '<span class="badge bg-success text-white"><i class="bi bi-image"></i> Solo ' . $cantidadTexto . '</span>';
                      } elseif ($tipo['tieneVideo'] && !$tipo['tieneTexto']) {
                          echo '<span class="badge bg-danger text-white"><i class="bi bi-play-circle-fill"></i> Solo Video</span>';
                      } elseif ($tipo['tieneTexto'] && !$tipo['tieneImagenes'] && !$tipo['tieneVideo']) {
                          echo '<span class="badge bg-secondary text-white"><i class="bi bi-text-paragraph"></i> Solo Texto</span>';
                      } else {
                          echo '<span class="badge bg-light text-dark"><i class="bi bi-question-circle"></i> Vacío</span>';
                      }
                    ?>
                  </td>
                  <td>
                    <?php 
                      $tipo = determinarTipoPublicacion($pub['contenido'], $pub['imagenes'], $pub['total_imagenes']);
                      $contenido = htmlspecialchars($pub['contenido']);
                      
                      // Mostrar contenido según el tipo
                      if ($tipo['tieneTexto'] && $tipo['tieneImagenes']) {
                          // Texto + imágenes
                          $textoCorto = strlen($contenido) > 30 ? substr($contenido, 0, 30) . '...' : $contenido;
                          echo "<div>$textoCorto</div>";
                          echo "<small class='text-muted'><i class='bi bi-images'></i> {$tipo['totalImagenes']} archivo(s)</small>";
                      } elseif ($tipo['tieneTexto'] && !$tipo['tieneImagenes']) {
                          // Solo texto
                          echo strlen($contenido) > 50 ? substr($contenido, 0, 50) . '...' : $contenido;
                      } elseif (!$tipo['tieneTexto'] && $tipo['tieneImagenes']) {
                          // Solo imágenes
                          $imagenes = explode(',', $pub['imagenes']);
                          $primerArchivo = trim($imagenes[0]);
                          $extension = strtolower(pathinfo($primerArchivo, PATHINFO_EXTENSION));
                          
                          if (in_array($extension, ['mp4', 'webm', 'avi', 'mov'])) {
                              echo "<i class='bi bi-play-circle text-danger'></i> " . basename($primerArchivo);
                          } else {
                              echo "<i class='bi bi-image text-primary'></i> " . basename($primerArchivo);
                          }
                          
                          if (count($imagenes) > 1) {
                              echo "<br><small class='text-muted'>+" . (count($imagenes)-1) . " archivo(s) más</small>";
                          }
                      } else {
                          echo "<span class='text-muted'><i class='bi bi-dash-circle'></i> Sin contenido</span>";
                      }
                    ?>
                  </td>
                  <td>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="eliminar_publicacion" value="<?php echo (int)$pub['id_pub']; ?>">
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar publicación?');">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Gestión de Comentarios -->
    <div class="col-12">
      <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Comentarios Recientes</h5>
        </div>
        <div class="card-body bg-light">
          <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-hover align-middle">
              <thead class="table-dark sticky-top">
                <tr>
                  <th>Usuario</th>
                  <th>Comentario</th>
                  <th>Tipo</th>
                  <th>Publicación</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($comentarios as $com): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($com['usuario']); ?></strong></td>
                  <td>
                    <?php 
                      $comentario = htmlspecialchars($com['comentario']);
                      echo strlen($comentario) > 40 ? substr($comentario, 0, 40) . '...' : $comentario;
                    ?>
                  </td>
                  <td>
                    <?php 
                      // Usar la misma función que en publicaciones con todos los datos
                      $imagenes_pub = !empty($com['imagenes']) ? $com['imagenes'] : $com['pub_imagen'];
                      $total_imgs = !empty($com['total_imagenes']) ? (int)$com['total_imagenes'] : (!empty($com['pub_imagen']) ? 1 : 0);
                      
                      $tipo = determinarTipoPublicacion($com['pub_contenido'], $imagenes_pub, $total_imgs);
                      
                      // Mostrar tipo con iconos y colores igual que en publicaciones
                      if ($tipo['tieneTexto'] && $tipo['tieneImagenes'] && $tipo['tieneVideo']) {
                          echo '<span class="badge bg-info text-white"><i class="bi bi-collection"></i> Completo</span>';
                      } elseif ($tipo['tieneTexto'] && $tipo['tieneImagenes']) {
                          echo '<span class="badge bg-primary text-white"><i class="bi bi-image-fill"></i> Texto + Imagen</span>';
                      } elseif ($tipo['tieneTexto'] && $tipo['tieneVideo']) {
                          echo '<span class="badge bg-warning text-dark"><i class="bi bi-play-circle"></i> Texto + Video</span>';
                      } elseif ($tipo['tieneImagenes'] && !$tipo['tieneTexto']) {
                          $cantidadTexto = $tipo['totalImagenes'] > 1 ? "Imágenes ({$tipo['totalImagenes']})" : "Imagen";
                          echo '<span class="badge bg-success text-white"><i class="bi bi-image"></i> Solo ' . $cantidadTexto . '</span>';
                      } elseif ($tipo['tieneVideo'] && !$tipo['tieneTexto']) {
                          echo '<span class="badge bg-danger text-white"><i class="bi bi-play-circle-fill"></i> Solo Video</span>';
                      } elseif ($tipo['tieneTexto'] && !$tipo['tieneImagenes'] && !$tipo['tieneVideo']) {
                          echo '<span class="badge bg-secondary text-white"><i class="bi bi-text-paragraph"></i> Solo Texto</span>';
                      } else {
                          echo '<span class="badge bg-light text-dark"><i class="bi bi-question-circle"></i> Vacío</span>';
                      }
                    ?>
                  </td>
                  <td>
                    <?php 
                      // Mostrar autor de la publicación
                      echo "<strong>" . htmlspecialchars($com['pub_autor']) . "</strong><br>";
                      
                      // Usar la misma lógica de contenido que en publicaciones
                      $imagenes_pub = !empty($com['imagenes']) ? $com['imagenes'] : $com['pub_imagen'];
                      $total_imgs = !empty($com['total_imagenes']) ? (int)$com['total_imagenes'] : (!empty($com['pub_imagen']) ? 1 : 0);
                      
                      $tipo = determinarTipoPublicacion($com['pub_contenido'], $imagenes_pub, $total_imgs);
                      $contenido = htmlspecialchars($com['pub_contenido']);
                      
                      // Mostrar contenido según el tipo
                      if ($tipo['tieneTexto'] && $tipo['tieneImagenes']) {
                          // Texto + imágenes
                          $textoCorto = strlen($contenido) > 30 ? substr($contenido, 0, 30) . '...' : $contenido;
                          echo "<div>$textoCorto</div>";
                          echo "<small class='text-muted'><i class='bi bi-images'></i> {$tipo['totalImagenes']} archivo(s)</small>";
                      } elseif ($tipo['tieneTexto'] && !$tipo['tieneImagenes'] && !$tipo['tieneVideo']) {
                          // Solo texto
                          echo strlen($contenido) > 50 ? substr($contenido, 0, 50) . '...' : $contenido;
                      } elseif (!$tipo['tieneTexto'] && $tipo['tieneImagenes']) {
                          // Solo imágenes
                          $imagenes = explode(',', $imagenes_pub);
                          $primerArchivo = trim($imagenes[0]);
                          $extension = strtolower(pathinfo($primerArchivo, PATHINFO_EXTENSION));
                          
                          if (in_array($extension, ['mp4', 'webm', 'avi', 'mov'])) {
                              echo "<i class='bi bi-play-circle text-danger'></i> " . basename($primerArchivo);
                          } else {
                              echo "<i class='bi bi-image text-primary'></i> " . basename($primerArchivo);
                          }
                          
                          if ($tipo['totalImagenes'] > 1) {
                              echo "<br><small class='text-muted'>+ " . ($tipo['totalImagenes'] - 1) . " más</small>";
                          }
                      } elseif ($tipo['tieneVideo'] && !$tipo['tieneTexto']) {
                          // Solo video
                          echo "<i class='bi bi-play-circle-fill text-danger'></i> Video detectado en contenido";
                      } else {
                          echo "<small class='text-muted'>Sin contenido</small>";
                      }
                    ?>
                  </td>
                  <td><small><?php echo htmlspecialchars($com['fecha']); ?></small></td>
                  <td>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="eliminar_comentario" value="<?php echo (int)$com['id_com']; ?>">
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar comentario?');">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
