<?php
/**
 * API para contar mensajes no leídos
 * Endpoint: /Converza/app/presenters/mensajes_api.php
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

if ($action === 'contar_no_leidos') {
    try {
        // Verificar que la tabla existe
        $stmtCheckTable = $conexion->query("SHOW TABLES LIKE 'chats'");
        
        if ($stmtCheckTable->rowCount() === 0) {
            echo json_encode([
                'success' => true,
                'total' => 0
            ]);
            exit;
        }

        // Contar solo mensajes recibidos no leídos
        $stmtMensajes = $conexion->prepare("
            SELECT COUNT(DISTINCT c.id_cha) as total 
            FROM chats c
            WHERE c.para = :usuario_id 
            AND c.leido = 0
            AND c.de != :usuario_id2
        ");
        
        $stmtMensajes->execute([
            ':usuario_id' => $_SESSION['id'],
            ':usuario_id2' => $_SESSION['id']
        ]);
        
        $result = $stmtMensajes->fetch(PDO::FETCH_ASSOC);
        $countMensajes = (int)($result['total'] ?? 0);

        echo json_encode([
            'success' => true,
            'total' => $countMensajes
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al contar mensajes: ' . $e->getMessage(),
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
