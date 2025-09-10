<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/socialnetwork-lib.php';

if(!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}

$user = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
$sess = $_SESSION['id'];

// ✅ Obtener solo amigos confirmados (estado = 1)
$stmtAmigos = $conexion->prepare("
    SELECT u.* 
    FROM usuarios u
    INNER JOIN amigos a 
        ON (
            (a.de = :sess1 AND a.para = u.id_use) 
            OR (a.para = :sess2 AND a.de = u.id_use)
        )
    WHERE a.estado = 1
    ORDER BY u.usuario ASC
");
$stmtAmigos->execute([
    ':sess1' => $sess,
    ':sess2' => $sess
]);
$amigos = $stmtAmigos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>REDSOCIAL - Chat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <!-- 📌 Listado de amigos -->
      <div class="card shadow-lg mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between">
          <a href="/TrabajoRedSocial/app/view/index.php" class="btn btn-light btn-sm">
            <i class="fa fa-arrow-left"></i> Volver
          </a>
          <span><i class="bi bi-people-fill"></i> Tus amigos</span>
        </div>
        <div class="card-body">
          <?php if($amigos): ?>
            <ul class="list-group">
              <?php foreach($amigos as $am): ?>
                <li class="list-group-item d-flex align-items-center justify-content-between">
                  <div>
                    <img src="/TrabajoRedSocial/public/avatars/<?php echo $am['avatar']; ?>" 
                         width="32" height="32" class="rounded-circle me-2">
                    <?php echo htmlspecialchars($am['usuario']); ?>
                  </div>
                  <!-- ✅ Botón que inicia conversación si no existe -->
                  <a href="iniciar_chat.php?usuario=<?php echo $am['id_use']; ?>" 
                     class="btn btn-sm btn-primary">
                    <i class="bi bi-chat-dots"></i> Chatear
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No tienes amigos registrados todavía.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- 📌 Ventana de chat -->
      <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
          <i class="bi bi-chat-dots"></i> Chat
        </div>
        <div class="card-body" style="min-height:400px;max-height:500px;overflow-y:auto;">
          <?php if($user == 0): ?>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Selecciona un amigo para iniciar un chat.
            </div>
          <?php else: ?>
            <?php
            $stmt = $conexion->prepare(
              "SELECT * FROM chats 
               WHERE (de = :user1 AND para = :sess1) 
                  OR (de = :sess2 AND para = :user2) 
               ORDER BY id_cha DESC"
            );
            $stmt->execute([
              ':user1'  => $user,
              ':user2' => $user,
              ':sess1' => $sess,
              ':sess2' => $sess
            ]);
            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($chats as $ch) {
              $var = ($ch['de'] == $user) ? $user : $sess;
              $stmtU = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
              $stmtU->execute([':id' => $var]);
              $us = $stmtU->fetch(PDO::FETCH_ASSOC);

              if ($ch['de'] == $user) {
                echo '
                <div class="d-flex mb-3">
                  <img src="/TrabajoRedSocial/public/avatars/'.$us['avatar'].'" 
                       class="rounded-circle me-2" width="48" height="48">
                  <div>
                    <div class="fw-bold text-primary">'.htmlspecialchars($us['usuario']).'</div>
                    <div class="bg-light border rounded p-2 mb-1">'.htmlspecialchars($ch['mensaje']).'</div>
                    <small class="text-muted">'.$ch['fecha'].'</small>
                  </div>
                </div>';
              } else {
                echo '
                <div class="d-flex flex-row-reverse mb-3">
                  <img src="/TrabajoRedSocial/public/avatars/'.$us['avatar'].'" 
                       class="rounded-circle ms-2" width="48" height="48">
                  <div>
                    <div class="fw-bold text-success text-end">'.htmlspecialchars($us['usuario']).'</div>
                    <div class="bg-primary text-white border rounded p-2 mb-1">'.htmlspecialchars($ch['mensaje']).'</div>
                    <small class="text-muted">'.$ch['fecha'].'</small>
                  </div>
                </div>';
              }
            }
            ?>
          <?php endif; ?>
        </div>

        <!-- 📌 Formulario de envío -->
        <?php if($user != 0): ?>
        <div class="card-footer bg-white">
          <form action="" method="post" class="d-flex align-items-center gap-2">
            <input type="text" name="mensaje" placeholder="Escribe un mensaje" 
                   class="form-control" required>
            <button type="submit" name="enviar" class="btn btn-primary">
              <i class="bi bi-send"></i> Enviar
            </button>
          </form>
          <?php
            if(isset($_POST['enviar'])) {
                $mensaje = trim($_POST['mensaje']);
                $de = $_SESSION['id'];
                $para = $user;

                if(empty($mensaje) || !$de || !$para){
                  echo "<div class='alert alert-danger mt-2'>⚠️ Error: faltan datos para enviar el mensaje.</div>";
                } else {
                  // Buscar conversación (placeholders únicos)
                  $stmtC = $conexion->prepare(
                    "SELECT id_cch FROM c_chats 
                    WHERE (de = :de1 AND para = :para1) 
                        OR (de = :de2 AND para = :para2)"
                  );
                  $stmtC->execute([
                    ':de1'   => $de,
                    ':para1' => $para,
                    ':de2'   => $para,
                    ':para2' => $de
                  ]);
                  $com = $stmtC->fetch(PDO::FETCH_ASSOC);

                  if($com && isset($com['id_cch'])) {
                    $id_cch = $com['id_cch'];

                    $stmtMsg = $conexion->prepare(
                      "INSERT INTO chats (id_cch,de,para,mensaje,fecha,leido) 
                      VALUES (:id_cch,:de,:para,:mensaje,NOW(),0)"
                    );
                    $stmtMsg->execute([
                      ':id_cch' => $id_cch,
                      ':de'     => $de,
                      ':para'   => $para,
                      ':mensaje'=> $mensaje
                    ]);

                    echo '<script>window.location="chat.php?usuario='.$para.'"</script>';
                  } else {
                    echo "<div class='alert alert-danger mt-2'>⚠️ Error: no se encontró la conversación.</div>";
                  }
                }
            }
          ?>  
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../plugins/fastclick/fastclick.js"></script>
<script src="../dist/js/app.min.js"></script>
<script src="../plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="../plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="../js/custom-file-input.js"></script>
</body>
</html>
