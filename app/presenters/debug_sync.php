<?php
session_start();
require_once __DIR__.'/../models/config.php';

header('Content-Type: application/json');

// Debug completo
$debug = [
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'get_params' => $_GET,
    'timestamp' => date('Y-m-d H:i:s')
];

error_log("🔍 DEBUG ENDPOINT: " . json_encode($debug));

// Verificar parámetros básicos
if (!isset($_SESSION['usuario']) || !isset($_GET['user'])) {
    echo json_encode([
        'cambios' => false,
        'error' => 'Faltan parámetros',
        'debug' => $debug
    ]);
    exit;
}

$sess = $_SESSION['id'];
$user = intval($_GET['user']);
$ultimoId = intval($_GET['ultimo_id'] ?? 0);

try {
    // Consulta simple para obtener mensajes
    $stmt = $conexion->prepare(
        "SELECT c.*, u.usuario as de_usuario, u.avatar as de_avatar 
         FROM chats c
         LEFT JOIN usuarios u ON c.de = u.id_use
         WHERE ((c.de = ? AND c.para = ?) OR (c.de = ? AND c.para = ?))
         AND c.id_cha > ?
         ORDER BY c.id_cha ASC
         LIMIT 5"
    );
    
    $stmt->execute([$user, $sess, $sess, $user, $ultimoId]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $respuesta = [
        'cambios' => count($mensajes) > 0,
        'nuevos_mensajes' => $mensajes,
        'debug' => $debug,
        'sql_params' => [$user, $sess, $sess, $user, $ultimoId],
        'mensajes_count' => count($mensajes)
    ];
    
    error_log("📤 RESPUESTA: " . json_encode($respuesta));
    echo json_encode($respuesta);
    
} catch (Exception $e) {
    error_log("❌ ERROR: " . $e->getMessage());
    echo json_encode([
        'cambios' => false,
        'error' => $e->getMessage(),
        'debug' => $debug
    ]);
}
?>