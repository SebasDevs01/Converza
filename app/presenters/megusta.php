<?php
session_start();
require __DIR__ . '/../models/config.php'; // $conexion es PDO

header('Content-Type: application/json');

$postId = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$postId || !isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$postId  = (int) $postId;
$usuario = (int) $_SESSION['id'];

// 🔹 Verificar si ya le dio like
$stmt = $conexion->prepare("SELECT 1 FROM likes WHERE post = :post AND usuario = :usuario");
$stmt->execute([':post' => $postId, ':usuario' => $usuario]);
$yaExiste = $stmt->fetchColumn();

if (!$yaExiste) {
    // Insertar like
    $stmt = $conexion->prepare("INSERT INTO likes (usuario, post, fecha) VALUES (:usuario, :post, NOW())");
    $stmt->execute([':usuario' => $usuario, ':post' => $postId]);

    $conexion->prepare("UPDATE publicaciones SET likes = likes + 1 WHERE id_pub = :post")
             ->execute([':post' => $postId]);

    $megusta = "<i class='fa fa-thumbs-o-up'></i> No me gusta";
} else {
    // Eliminar like
    $conexion->prepare("DELETE FROM likes WHERE post = :post AND usuario = :usuario")
             ->execute([':post' => $postId, ':usuario' => $usuario]);

    $conexion->prepare("UPDATE publicaciones SET likes = likes - 1 WHERE id_pub = :post")
             ->execute([':post' => $postId]);

    $megusta = "<i class='fa fa-thumbs-o-up'></i> Me gusta";
}

// 🔹 Obtener total actualizado
$stmt = $conexion->prepare("SELECT likes FROM publicaciones WHERE id_pub = :post");
$stmt->execute([':post' => $postId]);
$likes = (int) $stmt->fetchColumn();

echo json_encode([
    'likes' => " ($likes)",
    'text'  => $megusta
]);
