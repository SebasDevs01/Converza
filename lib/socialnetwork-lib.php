<?php
// ✅ Iniciar sesión SIEMPRE al principio
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 🔹 Conexión global a la BD con PDO
try {
    $conexion = new PDO("mysql:host=localhost;dbname=redsocial;charset=utf8mb4", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// ✅ Avatar por defecto si no existe
if (!isset($_SESSION['avatar']) || empty($_SESSION['avatar'])) {
    $_SESSION['avatar'] = "default.png"; // asegúrate que este archivo exista en /avatars/
}

// ----------------------------------
// FUNCION HEADER
// ----------------------------------
function Headerb() {
  global $conexion;

  // ✅ Verificar que el usuario haya iniciado sesión
  if (!isset($_SESSION['id'])) {
      header("Location: login.php");
      exit();
  }

  // 🔹 Consultar datos del usuario logueado usando PDO
  $stmtUser = $conexion->prepare("SELECT usuario, avatar, fecha_reg FROM usuarios WHERE id_use = :id");
  $stmtUser->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
  $stmtUser->execute();
  $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

  // Guardar en variables
  $username = ucwords($userData['usuario']);
  $avatar   = !empty($userData['avatar']) ? $userData['avatar'] : "default.png";
  $fechaReg = date("F d, Y", strtotime($userData['fecha_reg'])); // Ej: May 31, 2024
  $fechaRegEsp = date("d \d\e F \d\e Y", strtotime($userData['fecha_reg']));

  // 🔹 Consulta de notificaciones usando PDO
  $query = "SELECT * FROM notificaciones WHERE user2 = :user2 AND leido = '0' ORDER BY id_not DESC";
  $stmt = $conexion->prepare($query);
  $stmt->bindParam(':user2', $_SESSION['id'], PDO::PARAM_INT);
  $stmt->execute();
  $noti = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $cuantas = count($noti);
  ?>
  <!-- START HEADER -->
  <header class="main-header">
      <!-- Logo -->
      <a href="index.php" class="logo">
        <span class="logo-lg"><b>RED</b>SOCIAL</span>
      </a>

      <nav class="navbar navbar-static-top">
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">

            <!-- Notifications -->
            <li class="dropdown notifications-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-bell-o"></i>
                <span class="label label-warning"><?php echo $cuantas; ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="header">Tienes <?php echo $cuantas; ?> notificaciones</li>
                <li>
                  <ul class="menu">
                  <?php
                  foreach($noti as $no) {
                      $stmt2 = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
                      $stmt2->bindParam(':id', $no['user1'], PDO::PARAM_INT);
                      $stmt2->execute();
                      $usa = $stmt2->fetch(PDO::FETCH_ASSOC);
                  ?>
                    <li>
                      <a href="publicacion.php?id=<?php echo $no['id_pub']; ?>">
                        <i class="fa fa-users text-aqua"></i> 
                        El usuario <?php echo $usa['usuario']; ?> <?php echo $no['tipo']; ?> tu publicación
                      </a>
                    </li>
                  <?php } ?>
                  </ul>
                </li>
              </ul>
            </li>

            <!-- User Account -->
            <li class="dropdown user user-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="public/avatars/<?php echo htmlspecialchars($avatar); ?>" class="user-image" alt="User Image">
                <span class="hidden-xs"><?php echo $username; ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header">
                  <img src="public/avatars/<?php echo htmlspecialchars($avatar); ?>" class="img-circle" alt="User Image">
                  <p>
                    <?php echo $username; ?>
                    <small>Miembro desde <?php echo $fechaRegEsp; ?></small>
                  </p>
                </li>
                <li class="user-body">
                  <div class="row">
                    <div class="col-xs-6 text-center"><a href="#">Seguidores</a></div>
                    <div class="col-xs-6 text-center"><a href="#">Seguidos</a></div>
                  </div>
                </li>
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="editarperfil.php?id=<?php echo $_SESSION['id'];?>" class="btn btn-default btn-flat">Editar perfil</a>
                  </div>
                  <div class="pull-right">
                    <!-- ✅ Botón Cerrar Sesión -->
                    <a href="logout.php" class="btn btn-default btn-flat">Cerrar sesión</a>
                  </div>
                </li>
              </ul>
            </li>

            <!-- Config -->
            <li>
              <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
            </li>
          </ul>
        </div>
      </nav>
  </header>
  <!-- END HEADER -->
  <?php
}


// ----------------------------------
// FUNCION SIDE
// ----------------------------------
function Side() {
?>
<!-- START LEFT SIDE -->
<aside class="main-sidebar">
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
      <div class="pull-left">
        <img src="public/avatars/<?php echo htmlspecialchars($_SESSION['avatar']); ?>" width="60" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p><?php echo ucwords($_SESSION['usuario']); ?></p>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <!-- search form -->
    <form action="#" method="get" class="sidebar-form">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Encuentra a tus amigos">
        <span class="input-group-btn">
          <button type="submit" name="search" id="search-btn" class="btn btn-flat">
            <i class="fa fa-search"></i>
          </button>
        </span>
      </div>
    </form>

    <!-- sidebar menu -->
    <ul class="sidebar-menu">
      <li class="header">MENÚ DE NAVEGACIÓN</li>
      <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Noticias</span></a></li>
      <li><a href="mensajes.php"><i class="fa fa-comment"></i> <span>Chat</span></a></li>
      <li><a href="index.php"><i class="fa fa-user"></i> <span>Mis seguidores</span></a></li>
      <li><a href="index.php"><i class="fa fa-arrow-right"></i> <span>Seguidos</span></a></li>
      <li><a href="index.php"><i class="fa fa-heart"></i> <span>Me gusta</span></a></li>
    </ul>
  </section>
</aside>
<!-- END LEFT SIDE -->
<?php
}
?>
