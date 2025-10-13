<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/chat-permisos-helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$para = isset($_POST['para']) ? (int)$_POST['para'] : 0;
$de = $_SESSION['id'];

if ($para <= 0 || $para == $de) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Usuario inválido']);
    exit;
}

try {
    // Verificar permisos de chat
    $permisos = verificarPermisoChat($conexion, $de, $para);
    
    // Verificar si tiene solicitud pendiente o rechazada
    $solicitud = tieneSolicitudMensajePendiente($conexion, $de, $para);
    
    $response = [
        'puede_chatear' => $permisos['puede_chatear'],
        'necesita_solicitud' => $permisos['necesita_solicitud'],
        'tipo_relacion' => $permisos['tipo_relacion'],
        'motivo' => $permisos['motivo'],
        'tiene_solicitud_pendiente' => false,
        'solicitud_rechazada' => false,
        'primer_mensaje' => null
    ];
    
    if ($solicitud) {
        if ($solicitud['estado'] === 'pendiente') {
            $response['tiene_solicitud_pendiente'] = true;
            $response['primer_mensaje'] = $solicitud['primer_mensaje'];
        } else if ($solicitud['estado'] === 'rechazada') {
            $response['solicitud_rechazada'] = true;
        }
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
