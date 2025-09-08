<?php
session_start();
include __DIR__.'/../models/config.php'; // Aquí debes tener la conexión PDO en $conexion

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
  <title>Registro | Converza</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
  <div class="card shadow-lg p-4" style="max-width: 420px; width: 100%;">
    <div class="text-center mb-3">
  <h2 class="fw-bold mb-0" style="letter-spacing:2px;"><span class="text-primary">Con</span>verza</h2>
  <p class="text-muted">Crea tu cuenta en Converza</p>
    </div>
    <form action="" method="post" autocomplete="off">
      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre completo</label>
        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre completo" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      </div>
      <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Usuario" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>" required>
      </div>
      <div class="mb-3">
        <label for="contrasena" class="form-label">Contraseña</label>
        <div class="input-group">
          <input type="password" name="contrasena" id="contrasena" class="form-control" placeholder="Contraseña" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePassword"><span id="eyeIcon" class="bi bi-eye"></span></button>
        </div>
      </div>
      <div class="mb-3">
        <label for="repcontrasena" class="form-label">Repite la contraseña</label>
        <div class="input-group">
          <input type="password" name="repcontrasena" id="repcontrasena" class="form-control" placeholder="Repite la contraseña" required>
          <button class="btn btn-outline-secondary" type="button" id="toggleRepPassword"><span id="eyeRepIcon" class="bi bi-eye"></span></button>
        </div>
      </div>
      <div class="mb-3">
        <label for="tipo" class="form-label">Tipo de cuenta</label>
        <select name="tipo" id="tipo" class="form-select" required>
          <option value="user" selected>Usuario</option>
          <option value="admin">Administrador</option>
        </select>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="check" id="check" required>
        <label class="form-check-label" for="check">
          Acepto los <a href="#">términos y condiciones</a>
        </label>
      </div>
      <button type="submit" name="registrar" class="btn btn-primary w-100">Registrarme</button>
    </form>

<?php
if (isset($_POST['registrar'])) {
  $nombre        = trim($_POST['nombre']);
  $email         = trim($_POST['email']);
  $usuario       = trim($_POST['usuario']);
  $contrasena    = $_POST['contrasena'];
  $repcontrasena = $_POST['repcontrasena'];
  $tipo          = $_POST['tipo'] ?? 'user';
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

      $stmt = $conexion->prepare("INSERT INTO usuarios (nombre,email,usuario,contrasena,fecha_reg,avatar,tipo) VALUES (?,?,?,?,NOW(),?,?)");
      $success = $stmt->execute([$nombre, $email, $usuario, $hash, $avatar, $tipo]);

      if ($success) {
        echo '<br><div class="alert alert-success">Felicidades, se ha registrado correctamente. Redirigiendo al login...</div>';
        echo '<script>setTimeout(function(){ window.location.href=\'login.php\'; }, 2000);</script>';
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

    <div class="text-center mt-3">
      <a href="login.php" class="text-decoration-none">¿Ya tienes una cuenta? Inicia sesión</a>
    </div>
  </div>
</div>
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script>
  // Mostrar/ocultar contraseña
  document.addEventListener("DOMContentLoaded", function() {
    // Contraseña principal
    const passwordField = document.getElementById('contrasena');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');
    togglePassword.addEventListener('click', function() {
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('bi-eye');
        eyeIcon.classList.add('bi-eye-slash');
      } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('bi-eye-slash');
        eyeIcon.classList.add('bi-eye');
      }
    });
    // Repetir contraseña
    const repPasswordField = document.getElementById('repcontrasena');
    const toggleRepPassword = document.getElementById('toggleRepPassword');
    const eyeRepIcon = document.getElementById('eyeRepIcon');
    toggleRepPassword.addEventListener('click', function() {
      if (repPasswordField.type === 'password') {
        repPasswordField.type = 'text';
        eyeRepIcon.classList.remove('bi-eye');
        eyeRepIcon.classList.add('bi-eye-slash');
      } else {
        repPasswordField.type = 'password';
        eyeRepIcon.classList.remove('bi-eye-slash');
        eyeRepIcon.classList.add('bi-eye');
      }
    });
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
