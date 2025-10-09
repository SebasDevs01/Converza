<?php
session_start();
require __DIR__ . '/../models/config.php'; // $conexion es PDO

header('Content-Type: application/json');

$postId = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$postId) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$postId  = (int) $postId;

// Manejar diferentes tipos de reacciones
$reactionType = $_POST['reaction'] ?? 'like';

// Verificar si el usuario está logueado o generar un identificador único para usuarios anónimos
if (!isset($_SESSION['id'])) {
    if (!isset($_COOKIE['anon_id'])) {
        $anonId = bin2hex(random_bytes(16)); // Generar un identificador único
        setcookie('anon_id', $anonId, time() + (86400 * 30), "/"); // Cookie válida por 30 días
    } else {
        $anonId = $_COOKIE['anon_id'];
    }
    $usuario = $anonId; // Usar el identificador anónimo como usuario
} else {
    $usuario = (int) $_SESSION['id'];
}

// Verificar si ya existe una reacción del mismo tipo para el usuario y la publicación
$stmt = $conexion->prepare("SELECT id FROM reacciones WHERE id_publicacion = :post AND id_usuario = :usuario AND tipo_reaccion = :tipo");
$stmt->execute([':post' => $postId, ':usuario' => $usuario, ':tipo' => $reactionType]);
$yaExiste = $stmt->fetchColumn();

// Actualizar el contador de likes en la tabla publicaciones
if (!$yaExiste) {
    // Insertar nueva reacción
    $stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (:usuario, :post, :tipo, NOW())");
    $stmt->execute([':usuario' => $usuario, ':post' => $postId, ':tipo' => $reactionType]);

    $stmtUpdate = $conexion->prepare("UPDATE publicaciones SET likes = likes + 1 WHERE id_pub = :post");
    $stmtUpdate->execute([':post' => $postId]);

    $megusta = "<i class='fa fa-thumbs-o-up'></i> No me gusta";
} else {
    // Eliminar reacción existente
    $conexion->prepare("DELETE FROM reacciones WHERE id_publicacion = :post AND id_usuario = :usuario AND tipo_reaccion = :tipo")
             ->execute([':post' => $postId, ':usuario' => $usuario, ':tipo' => $reactionType]);

    $stmtUpdate = $conexion->prepare("UPDATE publicaciones SET likes = likes - 1 WHERE id_pub = :post");
    $stmtUpdate->execute([':post' => $postId]);

    $megusta = "<i class='fa fa-thumbs-o-up'></i> Me gusta";
}

// Recuperar el estado actual de las reacciones al cargar la página
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conexion->prepare("SELECT tipo_reaccion FROM reacciones WHERE id_publicacion = :post AND id_usuario = :usuario");
    $stmt->execute([':post' => $postId, ':usuario' => $usuario]);
    $reactionType = $stmt->fetchColumn();

    $stmt = $conexion->prepare("SELECT COUNT(*) FROM reacciones WHERE id_publicacion = :post");
    $stmt->execute([':post' => $postId]);
    $likes = (int) $stmt->fetchColumn();

    echo json_encode([
        'likes' => " ($likes)",
        'reaction' => $reactionType
    ]);
    exit;
}

echo json_encode([
    'likes' => " ($likes)",
    'text'  => $megusta
]);
