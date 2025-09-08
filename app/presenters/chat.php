<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/socialnetwork-lib.php';




if(!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}

if(isset($_GET['leido']) && isset($_GET['usuario'])) {
  $leido = (int)$_GET['leido'];
  $usuariod = (int)$_GET['usuario'];
  $stmt = $conexion->prepare("SELECT * FROM chats WHERE de = :usuariod OR para = :usuariod");
  $stmt->execute([':usuariod' => $usuariod]);
  $tc = $stmt->fetch(PDO::FETCH_ASSOC);
  if($tc && $tc['de'] != $_SESSION['id']) {
    $stmt2 = $conexion->prepare("UPDATE chats SET leido = 1 WHERE de = :usuariod OR para = :usuariod");
    $stmt2->execute([':usuariod' => $usuariod]);
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>REDSOCIAL</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg mb-4">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Chat</h5>
        </div>
        <div class="card-body" style="min-height:400px;max-height:500px;overflow-y:auto;">
          <?php
          $user = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
          $sess = $_SESSION['id'];
          $stmt = $conexion->prepare("SELECT * FROM chats WHERE (de = :user AND para = :sess) OR (de = :sess AND para = :user) ORDER BY id_cha DESC");
          $stmt->execute([':user' => $user, ':sess' => $sess]);
          $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
          foreach($chats as $ch) {
            $var = ($ch['de'] == $user) ? $user : $sess;
            $stmtU = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
            $stmtU->execute([':id' => $var]);
            $us = $stmtU->fetch(PDO::FETCH_ASSOC);
            if ($ch['de'] == $user) {
          ?>
          <div class="d-flex mb-3">
            <img src="avatars/<?php echo $us['avatar']; ?>" class="rounded-circle me-2" width="48" height="48">
            <div>
              <div class="fw-bold text-primary"><?php echo htmlspecialchars($us['usuario']); ?></div>
              <div class="bg-light border rounded p-2 mb-1"> <?php echo htmlspecialchars($ch['mensaje']); ?> </div>
              <small class="text-muted"><?php echo $ch['fecha']; ?></small>
            </div>
          </div>
          <?php } else { ?>
          <div class="d-flex flex-row-reverse mb-3">
            <img src="avatars/<?php echo $us['avatar']; ?>" class="rounded-circle ms-2" width="48" height="48">
            <div>
              <div class="fw-bold text-success text-end"><?php echo htmlspecialchars($us['usuario']); ?></div>
              <div class="bg-primary text-white border rounded p-2 mb-1"> <?php echo htmlspecialchars($ch['mensaje']); ?> </div>
              <small class="text-muted"><?php echo $ch['fecha']; ?></small>
            </div>
          </div>
          <?php } } ?>
        </div>
        <div class="card-footer bg-white">
          <form action="" method="post" class="d-flex align-items-center gap-2">
            <input type="text" name="mensaje" placeholder="Escribe un mensaje" class="form-control" required>
            <button type="submit" name="enviar" class="btn btn-primary"><i class="bi bi-send"></i> Enviar</button>
          </form>
          <?php
          if(isset($_POST['enviar'])) {
            $mensaje = trim($_POST['mensaje']);
            $de = $_SESSION['id'];
            $para = $user;
            // Buscar/construir conversación
            $stmtC = $conexion->prepare("SELECT * FROM c_chats WHERE (de = :de AND para = :para) OR (de = :para AND para = :de)");
            $stmtC->execute([':de' => $de, ':para' => $para]);
            $com = $stmtC->fetch(PDO::FETCH_ASSOC);
            if(!$com) {
              $stmtI = $conexion->prepare("INSERT INTO c_chats (de,para) VALUES (:de,:para)");
              $stmtI->execute([':de' => $de, ':para' => $para]);
              $stmtS = $conexion->prepare("SELECT id_cch FROM c_chats WHERE (de = :de AND para = :para) OR (de = :para AND para = :de)");
              $stmtS->execute([':de' => $de, ':para' => $para]);
              $si = $stmtS->fetch(PDO::FETCH_ASSOC);
              $stmtI2 = $conexion->prepare("INSERT INTO chats (id_cch,de,para,mensaje,fecha,leido) VALUES (:id_cch,:de,:para,:mensaje,NOW(),0)");
              $stmtI2->execute([':id_cch' => $si['id_cch'], ':de' => $de, ':para' => $para, ':mensaje' => $mensaje]);
              echo '<script>window.location="chat.php?usuario='.$para.'"</script>';
            } else {
              $stmtI3 = $conexion->prepare("INSERT INTO chats (id_cch,de,para,mensaje,fecha,leido) VALUES (:id_cch,:de,:para,:mensaje,NOW(),0)");
              $stmtI3->execute([':id_cch' => $com['id_cch'], ':de' => $de, ':para' => $para, ':mensaje' => $mensaje]);
              echo '<script>window.location="chat.php?usuario='.$para.'"</script>';
            }
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
