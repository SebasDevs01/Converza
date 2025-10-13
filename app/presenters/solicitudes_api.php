<?php
/**
 * API para contar solicitudes de amistad pendientes
 * Endpoint: /Converza/app/presenters/solicitudes_api.php
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../models/config.php';

// Verificar sesión
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'No autenticado',
        'total' => 0
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'contar_pendientes') {
    try {
        // Contar solicitudes pendientes (estado = 0)
        $stmtCount = $conexion->prepare("
            SELECT COUNT(*) as total 
            FROM amigos 
            WHERE para = :usuario_id 
            AND estado = 0
        ");
        
        $stmtCount->execute([':usuario_id' => $_SESSION['id']]);
        
        $result = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $countSolicitudes = (int)($result['total'] ?? 0);

        echo json_encode([
            'success' => true,
            'total' => $countSolicitudes
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al contar solicitudes: ' . $e->getMessage(),
            'total' => 0
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Acción no válida',
        'total' => 0
    ]);
}
