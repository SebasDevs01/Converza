<?php
session_start();
require_once __DIR__.'/../models/config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

$usuario_id = $_SESSION['id'];
$usuario_contactado_id = isset($_POST['usuario_contactado_id']) ? (int)$_POST['usuario_contactado_id'] : 0;
$fecha_hoy = date('Y-m-d');

if(!$usuario_contactado_id) {
    echo json_encode(['error' => 'ID de usuario requerido']);
    exit();
}

try {
    // Marcar como contactado en el Daily Shuffle
    $stmtUpdate = $conexion->prepare("
        UPDATE daily_shuffle 
        SET ya_contactado = TRUE, fecha_contacto = CURRENT_TIMESTAMP
        WHERE usuario_id = ? 
        AND usuario_mostrado_id = ? 
        AND fecha_shuffle = ?
    ");
    
    $resultado = $stmtUpdate->execute([$usuario_id, $usuario_contactado_id, $fecha_hoy]);
    
    if($resultado) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Usuario marcado como contactado en Daily Shuffle'
        ]);
    } else {
        echo json_encode([
            'error' => 'No se pudo actualizar el estado'
        ]);
    }

} catch(Exception $e) {
    echo json_encode([
        'error' => 'Error al marcar contacto: ' . $e->getMessage()
    ]);
}
?>