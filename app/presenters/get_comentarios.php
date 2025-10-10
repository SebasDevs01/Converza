<?php
session_start();
require_once(__DIR__.'/../models/config.php');
require_once(__DIR__.'/../models/bloqueos-helper.php');

header('Content-Type: application/json');

$postId = $_GET['postId'] ?? null;

if (!$postId) {
    echo json_encode(['error' => 'ID de publicación no proporcionado']);
    exit;
}

try {
    // Obtener comentarios con información de usuarios, excluyendo usuarios bloqueados
    $sessionUserId = $_SESSION['id'] ?? 0;
    $filtroBloqueos = $sessionUserId ? generarFiltroBloqueos($conexion, $sessionUserId, 'c.usuario') : '1=1';
    
    $stmt = $conexion->prepare("
        SELECT c.*, u.usuario, u.avatar 
        FROM comentarios c 
        JOIN usuarios u ON c.usuario = u.id_use 
        WHERE c.publicacion = :postId AND ($filtroBloqueos)
        ORDER BY c.fecha DESC
    ");
    $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar total de comentarios
    $total = count($comentarios);

    echo json_encode([
        'success' => true,
        'comentarios' => $comentarios,
        'total' => $total
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>