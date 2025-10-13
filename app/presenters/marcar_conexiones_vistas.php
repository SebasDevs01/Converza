<?php
session_start();
require_once(__DIR__ . '/../models/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

try {
    $usuarioId = $_SESSION['id'];
    
    // Marcar todas las conexiones del usuario como vistas
    $sql = "
        UPDATE conexiones_misticas 
        SET 
            visto_usuario1 = CASE WHEN usuario1_id = ? THEN 1 ELSE visto_usuario1 END,
            visto_usuario2 = CASE WHEN usuario2_id = ? THEN 1 ELSE visto_usuario2 END
        WHERE usuario1_id = ? OR usuario2_id = ?
    ";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuarioId, $usuarioId, $usuarioId, $usuarioId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Conexiones marcadas como vistas'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al marcar vistas',
        'message' => $e->getMessage()
    ]);
}
?>
