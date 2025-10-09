<?php
require_once __DIR__.'/../models/config.php'; // Conexión PDO en $conexion
require_once __DIR__.'/../models/socialnetwork-lib.php';

// ✅ Verificamos sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Obtenemos el id del perfil
$id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['id'];

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
</head>

<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="../view/index.php" style="letter-spacing:2px;">Converza</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="../view/index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="perfil.php?id=<?php echo $_SESSION['id']; ?>"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
        <li class="nav-item"><a class="nav-link" href="chat.php"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
        <li class="nav-item"><a class="nav-link" href="albumes.php?id=<?php echo $_SESSION['id']; ?>"><i class="bi bi-images"></i> Álbumes</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
        <li class="nav-item"><a class="nav-link text-warning fw-bold" href="../view/admin.php"><i class="bi bi-shield-lock"></i> Panel Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

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
          <div class="mb-2">
            <span class="badge bg-secondary">Miembro desde <?php echo date('d/m/Y', strtotime($usuario['fecha_reg'])); ?></span>
            <?php if ($usuario['tipo'] === 'admin') { ?><span class="badge bg-warning text-dark ms-2">Administrador</span><?php } ?>
          </div>

          <!-- Botón Editar perfil solo para el dueño -->
          <?php if ($_SESSION['id'] === $usuario['id_use']): ?>
              <a href="editarperfil.php?id=<?php echo $usuario['id_use']; ?>" class="btn btn-outline-primary btn-sm mb-2"><i class="bi bi-pencil"></i> Editar perfil</a>
          <?php endif; ?>

          <!-- Botón Añadir amigo solo si no son amigos y no hay solicitud pendiente -->
          <?php
          if ($_SESSION['id'] != $usuario['id_use']) {
              $stmtAmistad = $conexion->prepare('
                  SELECT * FROM amigos
                  WHERE ((de = :yo1 AND para = :otro1) OR (de = :otro2 AND para = :yo2))
                    AND estado IN (0,1)
              ');
              $stmtAmistad->bindParam(':yo1', $_SESSION['id'], PDO::PARAM_INT);
              $stmtAmistad->bindParam(':otro1', $usuario['id_use'], PDO::PARAM_INT);
              $stmtAmistad->bindParam(':otro2', $usuario['id_use'], PDO::PARAM_INT);
              $stmtAmistad->bindParam(':yo2', $_SESSION['id'], PDO::PARAM_INT);
              $stmtAmistad->execute();
              $amistad = $stmtAmistad->fetch();

              if (!$amistad) {
                  echo '<a href="solicitud.php?action=agregar&id=' . $usuario['id_use'] . '" class="btn btn-outline-primary btn-sm mb-2">Añadir Amigo</a>';
              }
          }

          ?>
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
                  <img src="/converza/public/avatars/<?php echo htmlspecialchars($usuario['avatar']); ?>" class="rounded-circle me-2" width="40" height="40">
                  <div>
                    <span class="fw-bold"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                    <span class="text-muted small ms-2"><?php echo date('d/m/Y H:i', strtotime($post['fecha'])); ?></span>
                  </div>
                </div>
                <div class="mb-2"><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></div>
                <?php if (!empty($post['imagen'])) { ?>
                  <img src="/converza/public/avatars/<?php echo htmlspecialchars($post['imagen']); ?>" class="img-fluid rounded mb-2" alt="Imagen publicación">
                <?php } ?>
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
          <a href="solicitud.php" class="btn btn-outline-warning w-100"><i class="bi bi-person-plus"></i> Ver solicitudes</a>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
