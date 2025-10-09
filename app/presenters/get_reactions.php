<?php
require_once(__DIR__.'/../models/config.php');
session_start();

// Obtener datos enviados por AJAX
$postId = $_GET['postId'] ?? null;
$userId = $_SESSION['id'] ?? null;

// Debug
error_log("GET_REACTIONS - Post ID: $postId, User ID: $userId");

if (!$postId) {
    echo json_encode(['error' => 'ID de publicación no proporcionado']);
    exit;
}

try {
    // Obtener las reacciones agrupadas por tipo con usuarios
    $stmt = $conexion->prepare("
        SELECT r.tipo_reaccion, COUNT(*) as total, GROUP_CONCAT(u.usuario SEPARATOR ', ') as usuarios 
        FROM reacciones r 
        JOIN usuarios u ON r.id_usuario = u.id_use 
        WHERE r.id_publicacion = :postId 
        GROUP BY r.tipo_reaccion 
        ORDER BY total DESC
    ");
    $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $reactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener la reacción del usuario actual si está logueado
    $userReaction = null;
    if ($userId) {
        error_log("Buscando reacción del usuario: Post=$postId, User=$userId");
        $stmtUser = $conexion->prepare("SELECT tipo_reaccion FROM reacciones WHERE id_publicacion = :postId AND id_usuario = :userId");
        $stmtUser->bindParam(':postId', $postId, PDO::PARAM_INT);
        $stmtUser->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmtUser->execute();
        $userReactionData = $stmtUser->fetch(PDO::FETCH_ASSOC);
        error_log("Resultado consulta usuario: " . print_r($userReactionData, true));
        if ($userReactionData) {
            $userReaction = $userReactionData['tipo_reaccion'];
            error_log("User reaction encontrada: '$userReaction'");
        } else {
            error_log("No se encontró reacción para user $userId en post $postId");
        }
    } else {
        error_log("No hay usuario logueado (userId es null)");
    }

    // Debug detallado
    error_log("GET_REACTIONS - Reactions query result:");
    foreach ($reactions as $i => $r) {
        error_log("  [$i] tipo: '{$r['tipo_reaccion']}', total: {$r['total']}, usuarios: '{$r['usuarios']}'");
    }
    error_log("GET_REACTIONS - User reaction: " . ($userReaction ? "'{$userReaction}'" : "null"));
    
    $response = [
        'success' => true, 
        'reactions' => $reactions,
        'userReaction' => $userReaction
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
