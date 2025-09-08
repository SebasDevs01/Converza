<?php
session_start();
include __DIR__.'/../models/config.php';
echo $_SESSION['id'];
echo $_POST['id'];


if (!isset($_POST['id']) || !isset($_SESSION['id'])) {
	echo json_encode(['error' => 'Invalid request']);
	exit;
}

$post = mysqli_real_escape_string($conexion, $_POST['id']);
$usuario = mysqli_real_escape_string($conexion, $_SESSION['id']);

$comprobar = mysqli_query($conexion, "SELECT * FROM likes WHERE post = '$post' AND usuario = '$usuario'");
if (!$comprobar) {
	echo json_encode(['error' => 'Database error']);
	exit;
}
$count = mysqli_num_rows($comprobar);

if ($count == 0) {
	$insert = mysqli_query($conexion, "INSERT INTO likes (usuario,post,fecha) values ('$usuario','$post',now())");
	$update = mysqli_query($conexion, "UPDATE publicaciones SET likes = likes+1 WHERE id_pub = '$post'");
} else {
	$delete = mysqli_query($conexion, "DELETE FROM likes WHERE post = '$post' AND usuario = '$usuario'");
	$update = mysqli_query($conexion, "UPDATE publicaciones SET likes = likes-1 WHERE id_pub = '$post'");
}

$contar = mysqli_query($conexion, "SELECT likes FROM publicaciones WHERE id_pub = '$post'");
if (!$contar) {
	echo json_encode(['error' => 'Database error']);
	exit;
}
$cont = mysqli_fetch_array($contar);
$likes = $cont['likes'];

if ($count >= 1) {
	$megusta = "<i class='fa fa-thumbs-o-up'></i> Me gusta";
	$likes_display = " (" . ($likes - 1) . ")";
} else {
	$megusta = "<i class='fa fa-thumbs-o-up'></i> No me gusta";
	$likes_display = " (" . ($likes + 1) . ")";
}

$datos = array('likes' => $likes_display, 'text' => $megusta);

echo json_encode($datos);

?>