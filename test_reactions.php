<?php
require_once(__DIR__.'/app/models/config.php');
session_start();

header('Content-Type: application/json');

$postId = $_GET['postId'] ?? 154;
$userId = $_SESSION['id'] ?? null;

echo "Testing reactions for post $postId...\n\n";

// Consulta simple para ver todas las reacciones
try {
    $stmt = $conexion->prepare("SELECT * FROM reacciones WHERE id_publicacion = :postId");
    $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $allReactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "1. All reactions for post $postId:\n";
    print_r($allReactions);
    
    // Consulta agrupada
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
    $groupedReactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n2. Grouped reactions:\n";
    print_r($groupedReactions);
    
    // User reaction
    if ($userId) {
        $stmt = $conexion->prepare("SELECT tipo_reaccion FROM reacciones WHERE id_publicacion = :postId AND id_usuario = :userId");
        $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $userReaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\n3. User $userId reaction:\n";
        print_r($userReaction);
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>