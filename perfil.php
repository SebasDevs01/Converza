<?php
require_once 'lib/config.php'; // Conexión PDO en $conexion
require_once 'lib/socialnetwork-lib.php';

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

// Usamos rowCount() para obtener el número de filas
if ($stmt->rowCount() > 0) {
    // Si la consulta ha devuelto filas, proceder con la lógica
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    // Continuar con el procesamiento de la consulta
} else {
    // Si no se encuentran resultados
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
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php echo Headerb(); ?>
<?php echo Side(); ?>

<div class="content-wrapper">
  <section class="content">

  <div class="box box-widget widget-user">
    <!-- Imagen de portada -->
    <div class="widget-user-header bg-aqua-active">
      <h3 class="widget-user-username"><?php echo htmlspecialchars($usuario['usuario']); ?></h3>
      <h5 class="widget-user-desc"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
    </div>

    <!-- Avatar -->
    <div class="widget-user-image">
      <img class="img-circle" src="public/avatars/<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="User Image">
    </div>

    <div class="box-footer">
      <div class="row">
        <div class="col-sm-12 border-right text-center">
          <h3 class="profile-username text-center">
            <?php echo htmlspecialchars($usuario['nombre']); ?>
          </h3>

          <?php if ($usuario['verificado'] != 0) { ?>
            <center><span class="glyphicon glyphicon-ok"></span></center>
          <?php } ?>

          <!-- 🔹 Fecha de registro -->
          <small class="text-muted text-center">
            <?php  
            setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'Spanish'); 
            echo "Miembro desde " . strftime("%d de %B de %Y", strtotime($usuario['fecha_reg']));  
            ?>
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ Sección de publicaciones -->
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">Publicaciones de <?php echo htmlspecialchars($usuario['nombre']); ?></h3>
    </div>
    <div class="box-body">
      <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
        <div class="post">
          <div class="user-block">
            <img class="img-circle img-bordered-sm" src="public/avatars/<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="user image">
            <span class="username">
              <a href="#"><?php echo htmlspecialchars($usuario['nombre']); ?></a>
            </span>
            <span class="description">
              <?php echo date('d/m/Y H:i', strtotime($post['fecha'])); ?>
            </span>
          </div>
          <p><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></p>

          <?php if (!empty($post['imagen'])) { ?>
          <div class="attachment-block clearfix">
            <img class="attachment-img" src="public/uploads/<?php echo htmlspecialchars($post['imagen']); ?>" alt="Post image">
          </div>
          <?php } ?>

          <hr>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-muted">Este usuario no ha publicado nada aún.</p>
      <?php endif; ?>
    </div>
  </div>

  </section>
</div>
<div class="control-sidebar-bg"></div>
</div>

<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/fastclick/fastclick.js"></script>
<script src="dist/js/app.min.js"></script>
<script src="plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="js/custom-file-input.js"></script>
</body>
</html>

