<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../models/predicciones-helper.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar sesión
    if (!isset($_SESSION['id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'No autorizado'
        ]);
        exit;
    }
    
    $usuario_id = $_SESSION['id'];
    $prediccionesHelper = new PrediccionesHelper($conexion);
    
    // Acción: obtener predicción
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $predicciones = $prediccionesHelper->obtenerPredicciones($usuario_id);
        
        if (empty($predicciones)) {
            echo json_encode([
                'success' => false,
                'error' => 'No se pudieron generar predicciones'
            ]);
            exit;
        }
        
        // Marcar la primera como vista
        $prediccionesHelper->marcarVista($predicciones[0]['id']);
        
        // Formato de respuesta para múltiples predicciones
        $prediccionesFormateadas = array_map(function($p) {
            return [
                'id' => $p['id'],
                'texto' => $p['prediccion'],
                'categoria' => $p['categoria'],
                'emoji' => $p['emoji'],
                'confianza' => $p['confianza']
            ];
        }, $predicciones);
        
        echo json_encode([
            'success' => true,
            'predicciones' => $prediccionesFormateadas,
            'total' => count($prediccionesFormateadas),
            'current_index' => 0
        ]);
        exit;
    }
    
    // Acción: valorar predicción (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['prediccion_id']) || !isset($data['me_gusta'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Datos incompletos'
            ]);
            exit;
        }
        
        $prediccion_id = (int)$data['prediccion_id'];
        $me_gusta = (int)$data['me_gusta']; // 1 = me gusta, 0 = no me gusta
        
        $resultado = $prediccionesHelper->valorarPrediccion($prediccion_id, $me_gusta);
        
        echo json_encode([
            'success' => $resultado,
            'mensaje' => $me_gusta ? '¡Gracias por tu valoración! 👍' : 'Gracias por tu feedback 👎'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    
} catch (Exception $e) {
    error_log("Error en get_prediccion.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al generar predicción'
    ]);
}
exit;
?>
