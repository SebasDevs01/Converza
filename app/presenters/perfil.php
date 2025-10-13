<?php
require_once __DIR__.'/../models/config.php'; // Conexión PDO en $conexion
require_once __DIR__.'/../models/socialnetwork-lib.php';
require_once __DIR__.'/../models/bloqueos-helper.php';

// ✅ Verificamos sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Obtenemos el id del perfil
$id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['id'];

// ✅ Verificar si hay bloqueo mutuo
$mostrarModalBloqueo = false;
if ($id != $_SESSION['id']) {
    $bloqueoInfo = verificarBloqueoMutuo($conexion, $_SESSION['id'], $id);
    
    if ($bloqueoInfo['bloqueado']) {
        if ($bloqueoInfo['direccion'] === 'me_bloquearon') {
            // El usuario actual fue bloqueado - mostrar modal
            $mostrarModalBloqueo = true;
        }
        // Si yo bloquee al usuario, continúo pero con funcionalidad limitada
    }
}

// ✅ Consultamos la info del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    echo "No se encontró ningún usuario con ese ID.";
    exit();
}

// ✅ Consultamos las publicaciones del usuario
$stmt_posts = $conexion->prepare("SELECT * FROM publicaciones WHERE usuario = :id ORDER BY fecha DESC");
$stmt_posts->bindParam(':id', $id, PDO::PARAM_INT);
$stmt_posts->execute();
$posts = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

// ✅ Obtener datos de seguimiento
$seguidores_count = 0;
$siguiendo_count = 0;
$ya_siguiendo = false;

try {
    // Contar seguidores del perfil
    $stmt_seguidores = $conexion->prepare("SELECT COUNT(*) as total FROM seguidores WHERE seguido_id = ?");
    $stmt_seguidores->execute([$id]);
    $seguidores_count = $stmt_seguidores->fetch()['total'];
    
    // Contar usuarios que sigue el perfil
    $stmt_siguiendo = $conexion->prepare("SELECT COUNT(*) as total FROM seguidores WHERE seguidor_id = ?");
    $stmt_siguiendo->execute([$id]);
    $siguiendo_count = $stmt_siguiendo->fetch()['total'];
    
    // Verificar si el usuario actual ya sigue este perfil
    if ($id !== $_SESSION['id']) {
        $stmt_relacion = $conexion->prepare("SELECT COUNT(*) as siguiendo FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $stmt_relacion->execute([$_SESSION['id'], $id]);
        $ya_siguiendo = $stmt_relacion->fetch()['siguiendo'] > 0;
    }
} catch (Exception $e) {
    // En caso de error, usar valores por defecto
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Perfil | Converza</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/Converza/public/css/navbar-animations.css" />
  <style>
    /* Estilo para contador de notificaciones estilo Facebook */
    .notification-badge {
        min-width: 16px;
        height: 16px;
        font-size: 0.55rem;
        line-height: 1.2;
        padding: 2px 4px;
        border: 2px solid white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        top: -5px !important;
        right: 5px !important;
        transform: none !important;
        z-index: 10;
    }
    
    /* Asegurar que los enlaces tengan posición relativa */
    .nav-link.position-relative {
        position: relative !important;
        display: inline-block !important;
    }
  </style>
</head>

<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4 sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/Converza/app/view/index.php" style="letter-spacing:2px;">Converza</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="/Converza/app/view/index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
        <li class="nav-item"><a class="nav-link active" href="/Converza/app/presenters/perfil.php?id=<?php echo $_SESSION['id']; ?>" aria-current="page"><i class="bi bi-person-circle"></i> Perfil</a></li>
        <li class="nav-item">
            <a class="nav-link position-relative" href="/Converza/app/presenters/chat.php">
                <i class="bi bi-chat-dots"></i> Mensajes
                <?php
                // Contar mensajes no leídos
                $countMensajes = 0;
                try {
                    $stmtCheckTable = $conexion->query("SHOW TABLES LIKE 'chats'");
                    if ($stmtCheckTable->rowCount() > 0) {
                        // Contar solo mensajes recibidos no leídos
                        $stmtMensajes = $conexion->prepare("
                            SELECT COUNT(DISTINCT c.id_cha) as total 
                            FROM chats c
                            WHERE c.para = :usuario_id 
                            AND c.leido = 0
                            AND c.de != :usuario_id2
                        ");
                        $stmtMensajes->execute([
                            ':usuario_id' => $_SESSION['id'],
                            ':usuario_id2' => $_SESSION['id']
                        ]);
                        $result = $stmtMensajes->fetch(PDO::FETCH_ASSOC);
                        $countMensajes = $result['total'] ?? 0;
                    }
                } catch (Exception $e) {
                    // Si hay error, simplemente no mostrar contador
                    $countMensajes = 0;
                }
                if ($countMensajes > 0):
                ?>
                <span class="position-absolute badge rounded-pill bg-danger notification-badge">
                    <?php echo $countMensajes > 9 ? '9+' : $countMensajes; ?>
                </span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="/Converza/app/presenters/albumes.php?id=<?php echo $_SESSION['id']; ?>"><i class="bi bi-images"></i> Álbumes</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDailyShuffle" title="Daily Shuffle"><i class="bi bi-shuffle"></i> Shuffle</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" title="Buscar usuarios"><i class="bi bi-search"></i></a></li>
        <li class="nav-item">
            <a class="nav-link position-relative" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSolicitudes" title="Solicitudes de amistad">
                <i class="bi bi-person-plus"></i>
                <?php
                // Contar solicitudes pendientes
                $stmtCount = $conexion->prepare("SELECT COUNT(*) as total FROM amigos WHERE para = :usuario_id AND estado = 0");
                $stmtCount->bindParam(':usuario_id', $_SESSION['id'], PDO::PARAM_INT);
                $stmtCount->execute();
                $countSolicitudes = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
                if ($countSolicitudes > 0):
                ?>
                <span class="position-absolute badge rounded-pill bg-danger notification-badge">
                    <?php echo $countSolicitudes > 9 ? '9+' : $countSolicitudes; ?>
                </span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNuevos" title="Nuevos usuarios"><i class="bi bi-people"></i></a></li>
        <li class="nav-item"><a class="nav-link" href="/Converza/app/presenters/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
        <li class="nav-item"><a class="nav-link text-warning fw-bold" href="/Converza/app/view/admin.php"><i class="bi bi-shield-lock"></i> Panel Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<?php include '../view/_navbar_panels.php'; ?>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg mb-4">
        <div class="card-body text-center">
          <?php
            $avatar = htmlspecialchars($usuario['avatar']);
            $avatarPath = __DIR__ . '/../../public/avatars/' . $avatar; 
            $avatarWebPath = '/converza/public/avatars/' . $avatar;

            if ($avatar && $avatar !== 'default_avatar.svg' && $avatar !== 'defect.jpg' && file_exists($avatarPath)) {
                echo '<img src="' . $avatarWebPath . '" class="rounded-circle mb-3" width="120" height="120" alt="Avatar">';
            } else {
                echo '<img src="/converza/public/avatars/defect.jpg" class="rounded-circle mb-3" width="120" height="120" alt="Avatar por defecto">';
            }
          ?>

          <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($usuario['nombre']); ?> <?php if ($usuario['verificado'] != 0) { ?><span class="text-primary" title="Verificado"><i class="bi bi-patch-check-fill"></i></span><?php } ?></h3>
          <div class="text-muted mb-2">@<?php echo htmlspecialchars($usuario['usuario']); ?></div>
          <div class="mb-3">
            <span class="badge bg-secondary">Miembro desde <?php echo date('d/m/Y', strtotime($usuario['fecha_reg'])); ?></span>
            <?php if ($usuario['tipo'] === 'admin') { ?><span class="badge bg-warning text-dark ms-2">Administrador</span><?php } ?>
          </div>
          
          <!-- Contadores de seguidores -->
          <div class="mb-3">
            <div class="row text-center">
              <div class="col-4">
                <div class="fw-bold fs-6" id="seguidores-count"><?php echo $seguidores_count; ?></div>
                <div class="text-muted small">Seguidores</div>
              </div>
              <div class="col-4">
                <div class="fw-bold fs-6" id="siguiendo-count"><?php echo $siguiendo_count; ?></div>
                <div class="text-muted small">Siguiendo</div>
              </div>
              <div class="col-4">
                <div class="fw-bold fs-6"><?php echo count($posts); ?></div>
                <div class="text-muted small">Publicaciones</div>
              </div>
            </div>
          </div>

          <!-- Botones de acción en fila -->
          <div class="d-flex gap-2 justify-content-center mb-3">
              <?php if ($_SESSION['id'] === $usuario['id_use']): ?>
                  <!-- Botón editar perfil para el dueño -->
                  <a href="editarperfil.php?id=<?php echo $usuario['id_use']; ?>" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-pencil"></i> Editar perfil
                  </a>
                  <!-- Botón Daily Shuffle -->
                  <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDailyShuffle">
                      <i class="bi bi-shuffle"></i> Daily Shuffle
                  </button>
              <?php else: ?>
                  <!-- Botón Seguir/Siguiendo -->
                  <button id="btn-seguir" class="btn btn-sm" data-usuario-id="<?php echo $usuario['id_use']; ?>">
                      <i class="bi" id="icono-seguir"></i>
                      <span id="texto-seguir"></span>
                  </button>
                  
                  <!-- Botón/Badge de Amistad -->
                  <div id="btn-amistad-container"></div>
                  
                  <!-- Botón Enviar Mensaje -->
                  <button id="btn-mensaje" class="btn btn-primary btn-sm" data-usuario-id="<?php echo $usuario['id_use']; ?>">
                      <i class="bi bi-chat-dots"></i> Mensaje
                  </button>
              <?php endif; ?>
          </div>

          <!-- JavaScript para manejar los botones de amistad -->
          <script>
              // Datos de amistad y bloqueo desde PHP
              const amistadData = <?php
              if ($_SESSION['id'] != $usuario['id_use']) {
                  // Verificar si yo he bloqueado a este usuario
                  $stmtBloqueo = $conexion->prepare('
                      SELECT id FROM bloqueos 
                      WHERE bloqueador_id = :yo AND bloqueado_id = :otro
                  ');
                  $stmtBloqueo->bindParam(':yo', $_SESSION['id'], PDO::PARAM_INT);
                  $stmtBloqueo->bindParam(':otro', $usuario['id_use'], PDO::PARAM_INT);
                  $stmtBloqueo->execute();
                  $yoBloqueado = $stmtBloqueo->fetch();
                  
                  if ($yoBloqueado) {
                      // Si yo bloqueé a este usuario
                      echo json_encode([
                          'yo_bloquee' => true,
                          'tiene_relacion' => false
                      ]);
                  } else {
                      // Verificar amistad normal
                      $stmtAmistad = $conexion->prepare('
                          SELECT *, 
                                 CASE WHEN de = :yo_check THEN "enviada" ELSE "recibida" END as direccion
                          FROM amigos
                          WHERE ((de = :yo1 AND para = :otro1) OR (de = :otro2 AND para = :yo2))
                            AND estado IN (0,1)
                      ');
                      $stmtAmistad->bindParam(':yo_check', $_SESSION['id'], PDO::PARAM_INT);
                      $stmtAmistad->bindParam(':yo1', $_SESSION['id'], PDO::PARAM_INT);
                      $stmtAmistad->bindParam(':otro1', $usuario['id_use'], PDO::PARAM_INT);
                      $stmtAmistad->bindParam(':otro2', $usuario['id_use'], PDO::PARAM_INT);
                      $stmtAmistad->bindParam(':yo2', $_SESSION['id'], PDO::PARAM_INT);
                      $stmtAmistad->execute();
                      $amistad = $stmtAmistad->fetch();
                      
                      if ($amistad) {
                          echo json_encode([
                              'yo_bloquee' => false,
                              'tiene_relacion' => true,
                              'estado' => $amistad['estado'],
                              'direccion' => $amistad['direccion']
                          ]);
                      } else {
                          echo json_encode([
                              'yo_bloquee' => false,
                              'tiene_relacion' => false
                          ]);
                      }
                  }
              } else {
                  echo json_encode(['es_propio' => true]);
              }
              ?>;

              // Actualizar botón de amistad al cargar la página
              document.addEventListener('DOMContentLoaded', function() {
                  actualizarBotonAmistad();
              });

              function actualizarBotonAmistad() {
                  const container = document.getElementById('btn-amistad-container');
                  
                  if (amistadData.es_propio) {
                      return; // No mostrar nada para el propio perfil
                  }

                  // Si yo he bloqueado a este usuario
                  if (amistadData.yo_bloquee) {
                      container.innerHTML = `
                          <button class="btn btn-outline-danger btn-sm" onclick="desbloquearUsuario()">
                              <i class="bi bi-unlock"></i> Desbloquear
                          </button>
                      `;
                      return;
                  }

                  if (!amistadData.tiene_relacion) {
                      // No hay relación - Mostrar botón de seguir y botón añadir amigo
                      const btnSeguir = document.getElementById('btn-seguir');
                      if (btnSeguir) {
                          btnSeguir.style.display = 'block';
                      }
                      
                      container.innerHTML = `
                          <a href="solicitud.php?action=agregar&id=<?php echo $usuario['id_use']; ?>" class="btn btn-outline-success btn-sm">
                              <i class="bi bi-person-plus"></i> Añadir Amigo
                          </a>
                      `;
                  } else if (amistadData.estado == 1) {
                      // Son amigos - Ocultar botón de seguir
                      const btnSeguir = document.getElementById('btn-seguir');
                      if (btnSeguir) {
                          btnSeguir.style.display = 'none';
                      }
                      
                      // Son amigos - Dropdown con opciones
                      container.innerHTML = `
                          <div class="dropdown">
                              <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                  <i class="bi bi-people-fill"></i> Amigos
                              </button>
                              <ul class="dropdown-menu">
                                  <li><a class="dropdown-item text-danger" href="#" onclick="eliminarAmigo()">
                                      <i class="bi bi-person-x"></i> Eliminar de amigos
                                  </a></li>
                                  <li><hr class="dropdown-divider"></li>
                                  <li><a class="dropdown-item text-warning" href="#" onclick="bloquearUsuario()">
                                      <i class="bi bi-shield-x"></i> Bloquear usuario
                                  </a></li>
                              </ul>
                          </div>
                      `;
                  } else if (amistadData.estado == 0) {
                      // Solicitud pendiente - Mostrar botón de seguir (aún no son amigos)
                      const btnSeguir = document.getElementById('btn-seguir');
                      if (btnSeguir) {
                          btnSeguir.style.display = 'block';
                      }
                      
                      if (amistadData.direccion === 'enviada') {
                          container.innerHTML = `
                              <div class="btn-group">
                                  <span class="btn btn-outline-warning btn-sm disabled">
                                      <i class="bi bi-clock"></i> Solicitud enviada
                                  </span>
                                  <button class="btn btn-outline-danger btn-sm" onclick="cancelarSolicitud()" title="Cancelar solicitud">
                                      <i class="bi bi-x"></i>
                                  </button>
                              </div>
                          `;
                      } else {
                          container.innerHTML = `
                              <span class="btn btn-outline-info btn-sm disabled">
                                  <i class="bi bi-person-plus"></i> Solicitud recibida
                              </span>
                          `;
                      }
                  }
              }

              function eliminarAmigo() {
                  if (confirm('¿Estás seguro de que quieres eliminar esta amistad?')) {
                      const xhr = new XMLHttpRequest();
                      xhr.open('POST', 'solicitud.php', true);
                      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                      
                      xhr.onreadystatechange = function() {
                          if (xhr.readyState === 4 && xhr.status === 200) {
                              // Actualizar los datos de amistad
                              amistadData.tiene_relacion = false;
                              amistadData.estado = null;
                              amistadData.direccion = null;
                              
                              // Mostrar el botón de seguir nuevamente y forzar estado "Seguir"
                              const btnSeguir = document.getElementById('btn-seguir');
                              if (btnSeguir) {
                                  btnSeguir.style.display = 'block';
                                  // Forzar a estado "Seguir" (no "Siguiendo")
                                  actualizarBotonSeguir(false);
                              }
                              
                              // Actualizar la interfaz
                              actualizarBotonAmistad();
                              
                              // Mostrar notificación
                              mostrarNotificacion('Amistad eliminada correctamente. Ya no sigues a este usuario.', 'success');
                          }
                      };
                      
                      xhr.send('action=eliminar&id=<?php echo $usuario['id_use']; ?>');
                  }
              }

              function bloquearUsuario() {
                  if (confirm('¿Estás seguro de que quieres bloquear este usuario? No podrá ver tu perfil ni interactuar contigo.')) {
                      bloquearUsuarioAjax(<?php echo $usuario['id_use']; ?>);
                  }
              }

              let bloqueoEnProceso = false;
              
              function bloquearUsuarioAjax(usuarioId) {
                  // Prevenir múltiples llamadas simultáneas
                  if (bloqueoEnProceso) {
                      console.log('🔒 Bloqueo ya en proceso, ignorando solicitud duplicada');
                      return;
                  }
                  
                  bloqueoEnProceso = true;
                  console.log('🚀 Iniciando bloqueo de usuario:', usuarioId);
                  
                  fetch('bloquear_usuario.php', {
                      method: 'POST',
                      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                      body: `usuario_id=${usuarioId}`
                  })
                  .then(r => {
                      console.log('📡 Respuesta recibida, status:', r.status);
                      return r.json();
                  })
                  .then(response => {
                      console.log('📦 Datos de respuesta:', response);
                      
                      if (response.success) {
                          console.log('✅ Bloqueo exitoso!');
                          alert('✓ Usuario bloqueado correctamente');
                          
                          // Recargar la página después de 500ms
                          console.log('🔄 Recargando página en 500ms...');
                          setTimeout(() => {
                              window.location.reload();
                          }, 500);
                      } else {
                          console.error('❌ Error en respuesta:', response.message);
                          alert('❌ Error al bloquear usuario: ' + (response.message || 'Error desconocido'));
                          bloqueoEnProceso = false;
                      }
                  })
                  .catch(err => {
                      console.error('💥 Error en fetch:', err);
                      alert('❌ Error al procesar la solicitud');
                      bloqueoEnProceso = false;
                  });
              }

              function desbloquearUsuario() {
                  if (confirm('¿Estás seguro de que quieres desbloquear este usuario? Podrá volver a interactuar contigo.')) {
                      desbloquearUsuarioAjax(<?php echo $usuario['id_use']; ?>);
                  }
              }

              let desbloqueoEnProceso = false;
              
              function desbloquearUsuarioAjax(usuarioId) {
                  // Prevenir múltiples llamadas simultáneas
                  if (desbloqueoEnProceso) {
                      console.log('🔒 Desbloqueo ya en proceso, ignorando solicitud duplicada');
                      return;
                  }
                  
                  desbloqueoEnProceso = true;
                  console.log('🚀 Iniciando desbloqueo de usuario:', usuarioId);
                  
                  fetch('desbloquear_usuario.php', {
                      method: 'POST',
                      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                      body: `usuario_id=${usuarioId}`
                  })
                  .then(r => {
                      console.log('📡 Respuesta recibida, status:', r.status);
                      return r.json();
                  })
                  .then(response => {
                      console.log('📦 Datos de respuesta:', response);
                      
                      if (response.success) {
                          console.log('✅ Desbloqueo exitoso!');
                          
                          // Mostrar mensaje de éxito
                          if (response.data && response.data.tiene_conversacion) {
                              alert(`✓ Usuario desbloqueado. Conversación restaurada (${response.data.mensajes_previos} mensajes).`);
                          } else {
                              alert('✓ Usuario desbloqueado correctamente');
                          }
                          
                          // Recargar la página después de 500ms (reducido de 800ms)
                          console.log('🔄 Recargando página en 500ms...');
                          setTimeout(() => {
                              window.location.reload();
                          }, 500);
                      } else {
                          console.error('❌ Error en respuesta:', response.message);
                          alert('❌ Error al desbloquear usuario: ' + (response.message || 'Error desconocido'));
                          desbloqueoEnProceso = false;
                      }
                  })
                  .catch(err => {
                      console.error('💥 Error en fetch:', err);
                      alert('❌ Error al procesar la solicitud');
                      desbloqueoEnProceso = false;
                  });
              }
              
              function cancelarSolicitud() {
                  if (confirm('¿Estás seguro de que quieres cancelar la solicitud de amistad?')) {
                      const xhr = new XMLHttpRequest();
                      xhr.open('POST', '/Converza/app/presenters/cancelar_solicitud.php', true);
                      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                      
                      xhr.onreadystatechange = function() {
                          if (xhr.readyState === 4) {
                              if (xhr.status === 200) {
                                  try {
                                      const response = JSON.parse(xhr.responseText);
                                      if (response.success) {
                                          // Actualizar los datos de amistad
                                          amistadData.tiene_relacion = false;
                                          amistadData.estado = null;
                                          amistadData.direccion = null;
                                          
                                          // Mostrar el botón de seguir nuevamente
                                          const btnSeguir = document.getElementById('btn-seguir');
                                          if (btnSeguir) {
                                              btnSeguir.style.display = 'block';
                                          }
                                          
                                          // Actualizar la interfaz - volver al botón de enviar solicitud
                                          actualizarBotonAmistad();
                                          
                                          // Mostrar notificación de éxito
                                          mostrarNotificacion('Solicitud cancelada exitosamente', 'success');
                                      } else {
                                          mostrarNotificacion('Error: ' + response.message, 'error');
                                      }
                                  } catch (e) {
                                      mostrarNotificacion('Error al procesar la respuesta', 'error');
                                  }
                              } else {
                                  mostrarNotificacion('Error al cancelar la solicitud', 'error');
                              }
                          }
                      };
                      
                      xhr.send('usuario_id=<?php echo $id; ?>');
                  }
              }

              // Función para mostrar notificaciones
              function mostrarNotificacion(mensaje, tipo) {
                  const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
                  const alerta = document.createElement('div');
                  alerta.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
                  alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                  alerta.setAttribute('role', 'alert');
                  alerta.innerHTML = `
                      ${mensaje}
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  `;
                  document.body.appendChild(alerta);
                  
                  // Auto-ocultar después de 3 segundos
                  setTimeout(function() {
                      alerta.style.opacity = '0';
                      setTimeout(function() {
                          if (alerta.parentNode) {
                              alerta.parentNode.removeChild(alerta);
                          }
                      }, 300);
                  }, 3000);
              }
          </script>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-card-text"></i> Publicaciones de <?php echo htmlspecialchars($usuario['nombre']); ?></h5>
        </div>
        <div class="card-body">
          <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
              <div class="mb-4 border-bottom pb-3">
                <div class="d-flex align-items-center mb-2">
                  <img src="/Converza/public/avatars/<?php echo htmlspecialchars($usuario['avatar']); ?>" class="rounded-circle me-2" width="40" height="40">
                  <div>
                    <span class="fw-bold"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    <span class="text-muted small ms-2"><?php echo date('d/m/Y H:i', strtotime($post['fecha'])); ?></span>
                  </div>
                </div>
                <div class="mb-2"><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></div>
                
                <?php 
                // Obtener imágenes de la publicación
                $stmt_imagenes = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = :pub_id");
                $stmt_imagenes->bindParam(':pub_id', $post['id_pub'], PDO::PARAM_INT);
                $stmt_imagenes->execute();
                $imagenes = $stmt_imagenes->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($imagenes)): ?>
                  <div class="mb-3">
                    <?php foreach ($imagenes as $imagen): ?>
                      <img src="/Converza/public/publicaciones/<?php echo htmlspecialchars($imagen); ?>" class="img-fluid rounded mb-2 me-2" style="max-width: 300px;" alt="Imagen de publicación">
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($post['video'])): ?>
                  <div class="mb-3">
                    <video controls class="img-fluid rounded" style="max-width: 400px;">
                      <source src="/Converza/public/publicaciones/<?php echo htmlspecialchars($post['video']); ?>" type="video/mp4">
                      Tu navegador no soporta el elemento de video.
                    </video>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-center text-muted">Este usuario no ha publicado nada aún.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div class="col-lg-4">
      <div class="card mb-4">
        <div class="card-header bg-success text-white">
          <h6 class="mb-0"><i class="bi bi-people"></i> Amigos</h6>
        </div>
        <div class="card-body">
          <?php
          $stmtAmigos = $conexion->prepare("
              SELECT u.id_use, u.usuario, u.avatar 
              FROM amigos a 
              JOIN usuarios u 
                ON (a.de = u.id_use OR a.para = u.id_use) 
              AND u.id_use != :yo_exclude
              WHERE (a.de = :yo1 OR a.para = :yo2) 
                AND a.estado = 1 
              LIMIT 8
          ");
          $stmtAmigos->bindValue(':yo_exclude', $usuario['id_use'], PDO::PARAM_INT);
          $stmtAmigos->bindValue(':yo1', $usuario['id_use'], PDO::PARAM_INT);
          $stmtAmigos->bindValue(':yo2', $usuario['id_use'], PDO::PARAM_INT);
          $stmtAmigos->execute();
          $amigos = $stmtAmigos->fetchAll(PDO::FETCH_ASSOC);

          if ($amigos):
              foreach ($amigos as $am): ?>
                  <a href="perfil.php?id=<?php echo (int)$am['id_use']; ?>" class="d-inline-block text-center me-2 mb-2">
                    <img src="/converza/public/avatars/<?php echo htmlspecialchars($am['avatar']); ?>" class="rounded-circle" width="48" height="48" alt="Avatar">
                    <div class="small fw-bold"><?php echo htmlspecialchars($am['usuario']); ?></div>
                  </a>
              <?php endforeach;
          else:
              echo '<div class="text-muted">Sin amigos aún.</div>';
          endif;
          ?>
        </div>
      </div>
      <div class="card mb-4">
        <div class="card-header bg-info text-white">
          <h6 class="mb-0"><i class="bi bi-chat-dots"></i> Mensajes</h6>
        </div>
        <div class="card-body">
          <a href="chat.php" class="btn btn-outline-info w-100"><i class="bi bi-chat-dots"></i> Ir a mensajes</a>
        </div>
      </div>
      <div class="card">
        <div class="card-header bg-warning text-dark">
          <h6 class="mb-0"><i class="bi bi-person-plus"></i> Solicitudes</h6>
        </div>
        <div class="card-body">
          <button class="btn btn-outline-warning w-100 position-relative" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSolicitudes">
            <i class="bi bi-person-plus"></i> Ver solicitudes
            <?php
            // Usar la misma variable de contador que ya se calculó arriba
            if ($countSolicitudes > 0):
            ?>
            <span class="position-absolute badge rounded-pill bg-danger notification-badge">
                <?php echo $countSolicitudes > 9 ? '9+' : $countSolicitudes; ?>
            </span>
            <?php endif; ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Enviar Mensaje -->
<div class="modal fade" id="modalEnviarMensaje" tabindex="-1" aria-labelledby="modalEnviarMensajeLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalEnviarMensajeLabel">
            <i class="bi bi-chat-dots"></i> Enviar mensaje a @<?php echo htmlspecialchars($usuario['usuario']); ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- Alerta de permisos -->
        <div id="alerta-permisos" class="alert" style="display: none;"></div>
        
        <!-- Formulario de mensaje -->
        <form id="form-enviar-mensaje">
          <div class="mb-3">
            <label for="mensaje-texto" class="form-label">Mensaje:</label>
            <textarea 
              class="form-control" 
              id="mensaje-texto" 
              rows="4" 
              placeholder="Escribe tu mensaje aquí..."
              maxlength="500"
              required
            ></textarea>
            <div class="form-text">
              <span id="contador-caracteres">0</span>/500 caracteres
            </div>
          </div>
          
          <!-- Info sobre restricciones -->
          <div id="info-restriccion" class="alert alert-info" style="display: none;">
            <i class="bi bi-info-circle"></i> 
            <span id="texto-restriccion"></span>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btn-enviar-mensaje-submit">
          <i class="bi bi-send"></i> Enviar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Converza/public/js/buscador.js"></script>

<script>
$(document).ready(function() {
    // Configurar estado inicial del botón
    const yaSiguiendo = <?php echo $ya_siguiendo ? 'true' : 'false'; ?>;
    const usuarioId = <?php echo $id; ?>;
    const esPropio = <?php echo $_SESSION['id'] === $usuario['id_use'] ? 'true' : 'false'; ?>;
    
    if (!esPropio) {
        actualizarBotonSeguir(yaSiguiendo);
    }
    
    // Manejar click en botón seguir
    $('#btn-seguir').click(function() {
        const boton = $(this);
        const accion = boton.hasClass('btn-success') ? 'dejar_seguir' : 'seguir';
        
        // Deshabilitar botón durante la petición
        boton.prop('disabled', true);
        
        $.ajax({
            url: 'seguir_usuario.php',
            method: 'POST',
            dataType: 'json',
            data: {
                usuario_id: usuarioId,
                accion: accion
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar botón
                    actualizarBotonSeguir(accion === 'seguir');
                    
                    // Actualizar contadores
                    $('#seguidores-count').text(response.seguidores);
                    
                    // Mostrar mensaje de éxito
                    mostrarNotificacion(response.mensaje, 'success');
                } else {
                    mostrarNotificacion(response.error || 'Error al procesar solicitud', 'error');
                }
            },
            error: function(xhr) {
                let mensaje = 'Error de conexión';
                try {
                    const response = JSON.parse(xhr.responseText);
                    mensaje = response.error || mensaje;
                } catch(e) {}
                mostrarNotificacion(mensaje, 'error');
            },
            complete: function() {
                boton.prop('disabled', false);
            }
        });
    });
    
    function actualizarBotonSeguir(siguiendo) {
        const boton = $('#btn-seguir');
        const icono = $('#icono-seguir');
        const texto = $('#texto-seguir');
        
        if (siguiendo) {
            boton.removeClass('btn-primary').addClass('btn-success');
            icono.removeClass('bi-person-plus').addClass('bi-person-check');
            texto.text('Siguiendo');
        } else {
            boton.removeClass('btn-success').addClass('btn-primary');
            icono.removeClass('bi-person-check').addClass('bi-person-plus');
            texto.text('Seguir');
        }
    }
    
    function mostrarNotificacion(mensaje, tipo) {
        const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
        const alerta = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; max-width: 300px;" role="alert">
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(alerta);
        
        // Auto-ocultar después de 3 segundos
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 3000);
    }
});
</script>

<!-- Modal para perfil bloqueado -->
<?php if ($mostrarModalBloqueo): ?>
<div class="modal fade" id="modalPerfilBloqueado" tabindex="-1" aria-labelledby="modalPerfilBloqueadoLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalPerfilBloqueadoLabel">
          <i class="bi bi-shield-x"></i> Acceso Restringido
        </h5>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <i class="bi bi-person-x-fill text-danger" style="font-size: 4rem;"></i>
        </div>
        <h5 class="text-danger">No puedes ver este perfil</h5>
        <p class="text-muted">Este usuario ha restringido el acceso a su perfil. No puedes ver su información ni interactuar con él hasta que te desbloquee.</p>
        
        <!-- Mostrar opción para desbloquear si yo lo tengo bloqueado -->
        <div id="opcion-desbloqueo" style="display: none;">
          <hr>
          <p class="small text-info">
            <i class="bi bi-info-circle"></i> 
            También tienes bloqueado a este usuario. Puedes desbloquearlo si deseas.
          </p>
          <button class="btn btn-outline-warning btn-sm" onclick="desbloquearDesdeModal()">
            <i class="bi bi-unlock"></i> Desbloquear usuario
          </button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="volverAlInicio()">
          <i class="bi bi-house"></i> Volver al inicio
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Mostrar modal automáticamente si el perfil está bloqueado
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('modalPerfilBloqueado'));
    modal.show();
    
    // Verificar si yo también tengo bloqueado a este usuario
    verificarBloqueoMutuo();
});

function volverAlInicio() {
    window.location.href = '/Converza/app/view/';
}

function verificarBloqueoMutuo() {
    // Esta función verifica si yo también he bloqueado a este usuario
    if (amistadData && amistadData.yo_bloquee) {
        document.getElementById('opcion-desbloqueo').style.display = 'block';
    }
}

function desbloquearDesdeModal() {
    if (confirm('¿Deseas desbloquear a este usuario? Podrán volver a interactuar contigo.')) {
        desbloquearUsuarioAjax(<?php echo $usuario['id_use']; ?>);
        // Cerrar modal después de desbloquear
        setTimeout(function() {
            window.location.reload();
        }, 1500);
    }
}
</script>
<?php endif; ?>

<!-- JavaScript para sistema de mensajes con permisos -->
<script>
<?php if ($_SESSION['id'] !== $usuario['id_use']): ?>
// Sistema de envío de mensajes con permisos (solo si no es el propio perfil)
const usuarioDestinoId = <?php echo $usuario['id_use']; ?>;
const usuarioDestinoNombre = "<?php echo htmlspecialchars($usuario['usuario']); ?>";

// Contador de caracteres
$('#mensaje-texto').on('input', function() {
    const length = $(this).val().length;
    $('#contador-caracteres').text(length);
});

// Manejar click en botón de mensaje
$('#btn-mensaje').on('click', function(e) {
    e.preventDefault();
    
    console.log('🔵 Click en botón mensaje, verificando si puede chatear...');
    console.log('Usuario destino ID:', usuarioDestinoId);
    
    // Verificar si ya existe una conversación aceptada
    $.ajax({
        url: 'verificar_conversacion_existente.php',
        method: 'POST',
        data: { usuario_id: usuarioDestinoId },
        dataType: 'json',
        success: function(response) {
            console.log('✅ Respuesta del servidor:', response);
            
            if (response.existe_conversacion) {
                // Ya existe una conversación aceptada, redirigir al chat
                console.log('✅ Puede chatear - Tipo:', response.tipo, '- Redirigiendo...');
                window.location.href = 'iniciar_chat.php?usuario=' + usuarioDestinoId;
            } else {
                // No existe conversación, abrir el modal
                console.log('⚠️ No puede chatear libremente - Mostrando modal de solicitud');
                $('#modalEnviarMensaje').modal('show');
            }
        },
        error: function(xhr, status, error) {
            // En caso de error, abrir el modal por defecto
            console.error('❌ Error al verificar conversación:', status, error);
            console.error('Respuesta del servidor:', xhr.responseText);
            $('#modalEnviarMensaje').modal('show');
        }
    });
});

// Al abrir el modal, verificar permisos de chat
$('#modalEnviarMensaje').on('show.bs.modal', function() {
    verificarPermisosChat();
});

function verificarPermisosChat() {
    $('#alerta-permisos').hide();
    $('#info-restriccion').hide();
    $('#btn-enviar-mensaje-submit').prop('disabled', true);
    
    // Verificar permisos mediante AJAX
    $.ajax({
        url: '/Converza/app/presenters/verificar_permisos_chat.php',
        method: 'POST',
        data: { para: usuarioDestinoId },
        dataType: 'json',
        success: function(response) {
            if (response.puede_chatear) {
                // Puede chatear libremente
                mostrarInfoPermiso(response.tipo_relacion);
                $('#btn-enviar-mensaje-submit').prop('disabled', false);
            } else if (response.necesita_solicitud) {
                // Necesita enviar solicitud (solo 1 mensaje)
                if (response.tiene_solicitud_pendiente) {
                    // Ya tiene solicitud pendiente
                    mostrarAlerta('warning', '⏳ Ya enviaste un mensaje a este usuario. Espera a que lo acepte para poder chatear.', true);
                    $('#mensaje-texto').val(response.primer_mensaje).prop('disabled', true);
                } else if (response.solicitud_rechazada) {
                    // Solicitud fue rechazada
                    mostrarAlerta('danger', '❌ Este usuario rechazó tu solicitud de mensaje anterior. No puedes enviar más mensajes.', true);
                    $('#mensaje-texto').prop('disabled', true);
                } else {
                    // Puede enviar 1 mensaje
                    mostrarInfoRestriccion('⚠️ Solo puedes enviar <strong>1 mensaje</strong> hasta que este usuario lo acepte. Escribe con cuidado.');
                    $('#btn-enviar-mensaje-submit').prop('disabled', false);
                }
            } else {
                mostrarAlerta('danger', 'No es posible enviar mensajes a este usuario.', true);
            }
        },
        error: function() {
            mostrarAlerta('danger', 'Error al verificar permisos. Intenta de nuevo.', false);
        }
    });
}

function mostrarInfoPermiso(tipoRelacion) {
    let texto = '';
    if (tipoRelacion === 'amigos') {
        texto = '✅ Son amigos. Pueden chatear libremente.';
    } else if (tipoRelacion === 'seguidores_mutuos') {
        texto = '✅ Se siguen mutuamente. Pueden chatear libremente.';
    } else if (tipoRelacion === 'solicitud_aceptada') {
        texto = '✅ Solicitud de mensaje aceptada. Pueden chatear libremente.';
    }
    
    if (texto) {
        $('#info-restriccion')
            .removeClass('alert-warning alert-info')
            .addClass('alert-success')
            .html('<i class="bi bi-check-circle"></i> ' + texto)
            .show();
    }
}

function mostrarInfoRestriccion(texto) {
    $('#info-restriccion')
        .removeClass('alert-success alert-danger')
        .addClass('alert-warning')
        .html(texto)
        .show();
}

function mostrarAlerta(tipo, mensaje, deshabilitarBoton) {
    const clases = {
        'success': 'alert-success',
        'warning': 'alert-warning',
        'danger': 'alert-danger',
        'info': 'alert-info'
    };
    
    $('#alerta-permisos')
        .removeClass('alert-success alert-warning alert-danger alert-info')
        .addClass(clases[tipo])
        .html(mensaje)
        .show();
        
    if (deshabilitarBoton) {
        $('#btn-enviar-mensaje-submit').prop('disabled', true);
    }
}

// Enviar mensaje
$('#btn-enviar-mensaje-submit').click(function() {
    const mensaje = $('#mensaje-texto').val().trim();
    
    if (!mensaje) {
        alert('Por favor escribe un mensaje');
        return;
    }
    
    // Deshabilitar botón mientras se envía
    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Enviando...');
    
    $.ajax({
        url: '/Converza/app/presenters/enviar_mensaje_con_permisos.php',
        method: 'POST',
        data: {
            para: usuarioDestinoId,
            mensaje: mensaje
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.tipo === 'mensaje_enviado') {
                    // Mensaje enviado correctamente - redirigir al chat
                    mostrarAlerta('success', '✅ ' + response.mensaje, false);
                    setTimeout(function() {
                        window.location.href = '/Converza/app/presenters/chat.php?usuario=' + usuarioDestinoId;
                    }, 1500);
                } else if (response.tipo === 'solicitud_creada') {
                    // Solicitud de mensaje creada
                    mostrarAlerta('info', '📬 ' + response.mensaje, true);
                    $('#mensaje-texto').prop('disabled', true);
                    setTimeout(function() {
                        $('#modalEnviarMensaje').modal('hide');
                    }, 3000);
                }
            } else {
                mostrarAlerta('danger', '❌ ' + response.error, false);
                $('#btn-enviar-mensaje-submit').prop('disabled', false).html('<i class="bi bi-send"></i> Enviar');
            }
        },
        error: function() {
            mostrarAlerta('danger', 'Error al enviar mensaje. Intenta de nuevo.', false);
            $('#btn-enviar-mensaje-submit').prop('disabled', false).html('<i class="bi bi-send"></i> Enviar');
        }
    });
});

// Limpiar modal al cerrar
$('#modalEnviarMensaje').on('hidden.bs.modal', function() {
    $('#mensaje-texto').val('').prop('disabled', false);
    $('#contador-caracteres').text('0');
    $('#alerta-permisos').hide();
    $('#info-restriccion').hide();
    $('#btn-enviar-mensaje-submit').prop('disabled', false).html('<i class="bi bi-send"></i> Enviar');
});
<?php endif; ?>
</script>

</body>
</html>
