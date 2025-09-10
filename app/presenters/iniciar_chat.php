<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/socialnetwork-lib.php';

if(!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}

$de   = $_SESSION['id'];
$para = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;

if(!$para || $para == $de){
  header("Location: chat.php");
  exit();
}

// Buscar conversación existente (con placeholders únicos)
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

if(!$com) {
  // Crear conversación si no existe
  $stmtI = $conexion->prepare("INSERT INTO c_chats (de,para) VALUES (:de,:para)");
  $stmtI->execute([':de' => $de, ':para' => $para]);
}

// Redirigir al chat
header("Location: chat.php?usuario=".$para);
exit();
