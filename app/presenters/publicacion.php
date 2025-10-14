<?php
$notificacionesTriggers->notificarNuevaPublicacion($conexion, $autor_id, $nombre_autor, $publicacion_id, $contenido);
session_start();
require_once __DIR__.'/../models/config.php'; // Aquí debe estar la conexión PDO en $conexion
require_once __DIR__.'/../models/socialnetwork-lib.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si el usuario está bloqueado
if (isUserBlocked($_SESSION['id'], $conexion)) {
    session_destroy();
    header("Location: login.php?error=blocked");
    exit();
}

// Obtener el ID de la publicación desde GET
$publicacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($publicacion_id <= 0) {
    header('Location: index.php');
    exit();
}

try {
    // Obtener información de la publicación y su autor
    $stmt = $conexion->prepare("
        SELECT p.id_pub, p.usuario, p.fecha, p.contenido, p.imagen, u.usuario AS nombre_usuario, u.avatar
        FROM publicaciones p JOIN usuarios u ON p.usuario = u.id_use
        WHERE p.id_pub = ?
    ");
    $stmt->execute([$publicacion_id]);
    $publicacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$publicacion) {
        // Si no existe la publicación, redirigir al índice
        header('Location: index.php');
        exit();
    }

    // Obtener comentarios de la publicación
    $stmt = $conexion->prepare("
        SELECT c.id_com, c.comentario, c.fecha, u.usuario AS nombre_usuario, u.avatar
        FROM comentarios c JOIN usuarios u ON c.usuario = u.id_use
        WHERE c.publicacion = ?
        ORDER BY c.fecha ASC
    ");
    $stmt->execute([$publicacion_id]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html class="no-js">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>REDSOCIAL</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" type="text/css" href="css/component.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script src="js/jquery.jscroll.js"></script>
</head>
<body>
<div class="wrapper">
    <!-- Aquí agregamos el Headerb() y el Side() -->
    <?php echo Headerb(); ?>
    <?php echo Side(); ?>

    <!-- Contenido principal -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <!-- Publicación -->

                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center">
                                <img src="public/avatars/<?php echo $publicacion['avatar'] ?? 'defect.jpg'; ?>"
                                     alt="Avatar" class="rounded-circle me-3" width="50" height="50" />
                                <div>
                                    <strong><?php echo htmlspecialchars($publicacion['nombre_usuario']); ?></strong><br />
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($publicacion['fecha'])); ?></small>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br(htmlspecialchars($publicacion['contenido'])); ?></p>
                                <?php if (!empty($publicacion['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($publicacion['imagen']); ?>" class="img-fluid rounded" alt="Imagen de la publicación" />
                                <?php else:  ?>
                                    <img src="../public/images/invisible.png" alt="">
                            </div>
                        </div>

                        <!-- Formulario para comentar -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form action="/converza/app/presenters/agregarcomentario.php" method="POST" class="d-flex">
                                <input type="hidden" name="publicacion_id" value="<?php echo $publicacion_id; ?>" />
                                <textarea name="comentario" class="form-control me-2" rows="1" placeholder="Escribe un comentario..." required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> <!-- icono de bootstrap (opcional) -->
                                </button>
                                </form>
                            </div>
                        </div>
                        <!-- Comentarios -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Comentarios (<?php echo count($comentarios); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($comentarios)): ?>
                                    <p class="text-muted">No hay comentarios aún.</p>
                                <?php else: ?>
                                    <?php foreach ($comentarios as $comentario): ?>
                                        <div class="d-flex mb-3">
                                            <a href="/Converza/app/presenters/perfil.php?id=<?php echo (int)$comentario['usuario']; ?>" style="text-decoration:none;">
                                                <img src="public/avatars/<?php echo $comentario['avatar'] ?? 'defect.jpg'; ?>"
                                                     alt="Avatar" class="rounded-circle me-3" width="40" height="40" />
                                            </a>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <a href="/Converza/app/presenters/perfil.php?id=<?php echo (int)$comentario['usuario']; ?>" class="fw-bold" style="text-decoration:none;color:inherit;">
                                                            <?php echo htmlspecialchars($comentario['nombre_usuario']); ?>
                                                        </a>
                                                        <p><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?></small>
                                                    </div>
                                                    <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $comentario['usuario']): ?>
                                                        <button class="btn btn-link btn-sm text-danger p-0 ms-2 eliminar-comentario" 
                                                                data-comentario-id="<?php echo $comentario['id_com']; ?>" 
                                                                title="Eliminar comentario">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="index.php" class="btn btn-secondary mt-3">Volver al inicio</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/fastclick/fastclick.js"></script>
<script src="dist/js/app.min.js"></script>
<script src="plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="js/custom-file-input.js"></script>
</body>
</html>

