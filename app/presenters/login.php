<?php
session_start();
include __DIR__.'/../models/config.php'; // Debe crear $conexion (mysqli)

// Redirección si ya está logueado
if (isset($_SESSION['id']) && isset($_SESSION['usuario'])) {
  header("Location: ../view/index.php");
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bienvenido a Converza</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4 mt-5">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h2 class="fw-bold mb-0" style="letter-spacing:2px;"><span class="text-primary">Con</span>verza</h2>
              <p class="text-muted mb-0">Bienvenido a Converza</p>
            </div>
            <!-- Formulario de login -->
            <form action="" method="post" autocomplete="off">
              <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required autofocus>
              </div>
              <div class="mb-3">
                <label for="password-field" class="form-label">Contraseña</label>
                <div class="input-group">
                  <input type="password" class="form-control" placeholder="Contraseña" name="contrasena" id="password-field" required autocomplete="current-password">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                    <span id="eyeIcon" class="bi bi-eye"></span>
                  </button>
                </div>
              </div>
              <div class="d-grid mb-2">
                <button type="submit" name="login" class="btn btn-primary">Iniciar Sesión</button>
              </div>
            </form>
            <?php
            if (isset($_POST['login'])) {
                $usuario = trim($_POST['usuario']);
                $contrasena = trim($_POST['contrasena']);

                // Consultar usuario y tipo
                $stmt = $conexion->prepare("SELECT id_use, usuario, contrasena, avatar, tipo FROM usuarios WHERE usuario = ?");
                $stmt->execute([$usuario]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($resultado) {
                    // Verificar si el usuario está bloqueado
                    if ($resultado['tipo'] === 'blocked') {
                        echo "<div class='alert alert-warning mt-3 text-center' role='alert'>
                                <i class='bi bi-shield-lock'></i> Tu cuenta ha sido suspendida. Contacta al administrador.
                              </div>";
                    } elseif (password_verify($contrasena, $resultado['contrasena'])) {
                        $_SESSION['id'] = $resultado['id_use'];
                        $_SESSION['usuario'] = $resultado['usuario'];
                        $_SESSION['avatar'] = !empty($resultado['avatar']) ? $resultado['avatar'] : 'defect.jpg';
                        $_SESSION['tipo'] = $resultado['tipo'] ?? 'user';
                        // Redirigir según tipo
                        if ($_SESSION['tipo'] === 'admin') {
                          header('Location: ../view/admin.php');
                        } else {
                          header('Location: ../view/index.php');
                        }
                        exit();
                    } else {
                        echo "<div class='alert alert-danger mt-3 text-center' role='alert'>Usuario o contraseña incorrectos.</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger mt-3 text-center' role='alert'>Usuario o contraseña incorrectos.</div>";
                }
            }
            
            // Mostrar mensaje si viene de un redirect por bloqueo
            if (isset($_GET['error']) && $_GET['error'] === 'blocked') {
                echo "<div class='alert alert-warning mt-3 text-center' role='alert'>
                        <i class='bi bi-shield-lock'></i> Tu sesión ha sido suspendida. Tu cuenta fue bloqueada.
                      </div>";
            }
            ?>
            <div class="text-center mt-3">
              <a href="#" class="small text-decoration-none">¿Olvidaste tu contraseña?</a><br>
              <a href="#" id="registro" class="small text-decoration-none">Registrarme en Converza</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Redirección segura a registro.php
    document.addEventListener("DOMContentLoaded", function() {
      document.getElementById("registro").addEventListener("click", function(e){
        e.preventDefault();
        window.location.href = 'registro.php';
      });
      // Mostrar/ocultar contraseña (solo mostrar/ocultar real)
      const passwordField = document.getElementById('password-field');
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
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
