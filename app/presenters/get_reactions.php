<?php
require_once(__DIR__.'/../models/config.php');

// Obtener datos enviados por AJAX
$postId = $_GET['postId'] ?? null;

if (!$postId) {
    echo json_encode(['error' => 'ID de publicación no proporcionado']);
    exit;
}

try {
    // Obtener el número total de reacciones y los usuarios que reaccionaron
    $stmt = $conexion->prepare("SELECT tipo_reaccion, COUNT(*) as total, GROUP_CONCAT(u.usuario SEPARATOR ', ') as usuarios FROM reacciones r JOIN usuarios u ON r.id_usuario = u.id_use WHERE id_publicacion = :postId GROUP BY tipo_reaccion");
    $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reactions' => $reactions]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
