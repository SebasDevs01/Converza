<?php
require_once(__DIR__.'/../models/config.php');

// Obtener datos enviados por AJAX
$postId = $_GET['postId'] ?? null;
$userId = $_SESSION['id'] ?? null;

if (!$postId || !$userId) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Consultar la reacción del usuario para la publicación
    $stmt = $conexion->prepare("SELECT tipo_reaccion FROM reacciones WHERE id_publicacion = :postId AND id_usuario = :userId");
    $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $reaction = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'userReaction' => $reaction['tipo_reaccion'] ?? null]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
