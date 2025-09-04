<?php
session_start();
include 'lib/config.php'; // Debe crear $conexion (mysqli)

// Redirección si ya está logueado
if (isset($_SESSION['id']) && isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bienvenido a REDSOCIAL</title>
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/jquery.jscroll.js"></script>
  <script src="js/custom-file-input.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="hold-transition login-page">

<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>RED</b>SOCIAL</a>
  </div>

  <div class="login-box-body">
    <p class="login-box-msg">Bienvenido a REDSOCIAL</p>

    <!-- Formulario de login -->
    <form action="" method="post">
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="Usuario" name="usuario" required>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Contraseña" name="contrasena" required>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <button type="submit" name="login" class="btn btn-primary btn-block btn-flat">Iniciar Sesión</button>
        </div>
      </div>
    </form>

<?php
if (isset($_POST['login'])) {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    // Consultar solo por usuario
    $stmt = $conexion->prepare("SELECT id_use, usuario, contrasena,avatar FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]); // ✅ Ejecuta con el parámetro
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC); // ✅ Obtiene un solo resultado

    if ($resultado) {
        // Verificar contraseña
        if (password_verify($contrasena, $resultado['contrasena'])) {
            $_SESSION['id'] = $resultado['id_use'];
            $_SESSION['usuario'] = $resultado['usuario'];
            $_SESSION['avatar'] = !empty($resultado['avatar']) ? $resultado['avatar'] : 'defect.jpg';
            header('Location: index.php');
            exit();
        } else {
            echo "<p style='color:red;'>Los datos ingresados no son correctos</p>";
        }
    } else {
        echo "<p style='color:red;'>Los datos ingresados no son correctos</p>";
    }
}
?>

  </div>

  <!-- Enlaces fuera del formulario -->
  <div style="text-align:center; margin-top:10px;">
    <a href="#">Olvidé mi contraseña</a><br>
    <a href="#" id="registro">Registrarme en REDSOCIAL</a>
  </div>
</div>

<script>
// Redirección segura a registro.php
document.getElementById("registro").addEventListener("click", function(e){
    e.preventDefault();
    window.location.href = 'registro.php';
});
</script>

</body>
</html>
