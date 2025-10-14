<?php
/**
 * API ENDPOINT: Verificar Coincidence Alerts en tiempo real
 * Se llama vía AJAX cada 30 segundos cuando el usuario está activo
 */

session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../models/config.php');
require_once(__DIR__ . '/../models/coincidence-alerts-helper.php');

// Verificar autenticación
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No autenticado'
    ]);
    exit;
}

$usuario_id = $_SESSION['id'];
$action = $_POST['action'] ?? 'check';

try {
    $coincidenceAlerts = new CoincidenceAlertsHelper($conexion);
    
    switch ($action) {
        case 'check':
            // Detectar coincidencias en tiempo real
            $resultado = $coincidenceAlerts->detectarCoincidenciasEnTiempoReal($usuario_id);
            
            // Obtener alertas no leídas
            $alertas = $coincidenceAlerts->obtenerAlertasNoLeidas($usuario_id);
            
            echo json_encode([
                'success' => true,
                'hay_coincidencias' => $resultado['hay_coincidencias'],
                'total' => $resultado['total'] ?? 0,
                'coincidencias' => $resultado['coincidencias'] ?? [],
                'alertas_no_leidas' => $alertas,
                'contador' => count($alertas),
                'mensaje' => $resultado['mensaje'] ?? ''
            ]);
            break;
            
        case 'marcar_leida':
            $alerta_id = $_POST['alerta_id'] ?? 0;
            $resultado = $coincidenceAlerts->marcarComoLeida($alerta_id);
            
            echo json_encode([
                'success' => $resultado,
                'mensaje' => $resultado ? 'Alerta marcada como leída' : 'Error al marcar'
            ]);
            break;
            
        case 'contador':
            // Solo obtener contador (más ligero)
            $total = $coincidenceAlerts->contarAlertasNoLeidas($usuario_id);
            
            echo json_encode([
                'success' => true,
                'contador' => $total
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida'
            ]);
    }
    
} catch (Exception $e) {
    error_log("Error en check_coincidence_alerts.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
