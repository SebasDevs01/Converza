<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? 'user') !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once __DIR__.'/../models/config.php';

// Obtener usuarios válidos (solo los que existen en la base y tienen avatar válido)
$stmt = $conexion->prepare("SELECT id_use, usuario, avatar, tipo, fecha_reg FROM usuarios WHERE avatar IS NOT NULL AND avatar != '' ORDER BY id_use DESC");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eliminar usuario si se solicita
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
  $idEliminar = (int)$_GET['eliminar'];
  // No permitir eliminar admins ni a uno mismo
  $stmtTipo = $conexion->prepare("SELECT tipo FROM usuarios WHERE id_use = :id");
  $stmtTipo->bindParam(':id', $idEliminar, PDO::PARAM_INT);
  $stmtTipo->execute();
  $tipoEliminar = $stmtTipo->fetchColumn();
  if ($tipoEliminar !== 'admin' && $idEliminar !== (int)$_SESSION['id']) {
    $stmtDel = $conexion->prepare("DELETE FROM usuarios WHERE id_use = :id");
    $stmtDel->bindParam(':id', $idEliminar, PDO::PARAM_INT);
    $stmtDel->execute();
    header("Location: admin.php");
    exit();
  }
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
  <div class="row g-4">
    <div class="col-12 col-lg-8 mx-auto">
      <div class="card shadow-lg border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Panel de Administración</h5>
        </div>
        <div class="card-body bg-light">
          <h6 class="fw-bold mb-3">Usuarios registrados</h6>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Usuario</th>
                  <th>Avatar</th>
                  <th>Tipo</th>
                  <th>Fecha registro</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                  <td><?php echo (int)$u['id_use']; ?></td>
                  <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                    <td>
                      <?php
                        $avatar = htmlspecialchars($u['avatar']);
                        $avatarPath = __DIR__.'/../../public/avatars/'.$avatar;
                        if ($avatar && file_exists($avatarPath)) {
                          $src = '/converza/public/avatars/'.$avatar;
                        } else {
                          $src = '/converza/public/avatars/defect.jpg';
                        }
                      ?>
                      <img src="<?php echo $src; ?>" alt="avatar" width="40" height="40" class="rounded-circle">
                    </td>
                  <td><span class="badge <?php echo $u['tipo']==='admin'?'bg-warning text-dark':'bg-secondary'; ?>"><?php echo htmlspecialchars($u['tipo']); ?></span></td>
                  <td><?php echo htmlspecialchars($u['fecha_reg']); ?></td>
                  <td>
                    <?php if ($u['tipo'] !== 'admin'): ?>
                      <a href="admin.php?eliminar=<?php echo (int)$u['id_use']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');"><i class="bi bi-person-x"></i> Eliminar</a>
                    <?php else: ?>
                      <span class="text-muted">-</span>
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
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
