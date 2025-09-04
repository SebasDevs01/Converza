<?php
session_start();
include 'lib/config.php'; // Aquí debes tener la conexión PDO en $conexion

// Redirigir si ya está logueado
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Registro</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="plugins/iCheck/square/blue.css">
</head>
<body class="hold-transition register-page">

<div class="register-box">
  <div class="register-logo">
    <a href="#"><b>RED</b>SOCIAL</a>
  </div>

  <div class="register-box-body">
    <p class="login-box-msg">Regístrate en REDSOCIAL</p>

    <form action="" method="post">
      <div class="form-group has-feedback">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre completo" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required>
        <span class="glyphicon glyphicon-star form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="text" name="usuario" class="form-control" placeholder="Usuario" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>" required>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="contrasena" class="form-control" placeholder="Contraseña" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" name="repcontrasena" class="form-control" placeholder="Repita la contraseña" required>
        <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-10">
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" name="check" required> Acepto los <a href="#">términos y condiciones</a>
            </label>
          </div>
        </div>
        <div class="col-xs-12">
          <button type="submit" name="registrar" class="btn btn-primary btn-block btn-flat">Registrarme</button>
        </div>
      </div>
    </form>

<?php
if (isset($_POST['registrar'])) {
    $nombre        = trim($_POST['nombre']);
    $email         = trim($_POST['email']);
    $usuario       = trim($_POST['usuario']);
    $contrasena    = $_POST['contrasena'];
    $repcontrasena = $_POST['repcontrasena'];
    $avatar = "defect.jpg";

    try {
        // Verificar si usuario existe
        $stmt = $conexion->prepare("SELECT id_use FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $checkUser = $stmt->fetch();

        // Verificar si email existe
        $stmt = $conexion->prepare("SELECT id_use FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $checkEmail = $stmt->fetch();

        if ($checkUser) {
            echo '<br><div class="alert alert-danger">El nombre de usuario está en uso, por favor escoja otro</div>';
        } elseif ($checkEmail) {
            echo '<br><div class="alert alert-danger">El email ya está en uso, por favor verifique si ya tiene cuenta</div>';
        } elseif ($contrasena !== $repcontrasena) {
            echo '<br><div class="alert alert-danger">Las contraseñas no coinciden</div>';
        } else {
            // Cifrar contraseña
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);

            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre,email,usuario,contrasena,fecha_reg,avatar) VALUES (?,?,?,?,NOW(),?)");
            $success = $stmt->execute([$nombre, $email, $usuario, $hash, $avatar]);

            if ($success) {
                echo '<br><div class="alert alert-success">Felicidades, se ha registrado correctamente. Redirigiendo al login...</div>';
                echo '<script>setTimeout(function(){ window.location.href="login.php"; }, 2000);</script>';
                exit();
            } else {
                echo '<br><div class="alert alert-danger">Error al registrar, intente nuevamente</div>';
            }
        }
    } catch (PDOException $e) {
        echo '<br><div class="alert alert-danger">Error en la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

    <br>
    <a href="login.php" class="text-center">Tengo actualmente una cuenta</a>
  </div>
</div>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' 
    });
  });
</script>
</body>
</html>
