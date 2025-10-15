<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../models/predicciones-helper.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

error_log("========== GET_PREDICCION.PHP - Iniciado ==========");

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
    
    // Verificar que el usuario existe en la base de datos
    $stmtCheck = $conexion->prepare("SELECT id_use FROM usuarios WHERE id_use = ?");
    $stmtCheck->execute([$usuario_id]);
    if (!$stmtCheck->fetch()) {
        error_log("❌ Usuario ID $usuario_id no existe en la BD");
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no válido'
        ]);
        exit;
    }
    
    $prediccionesHelper = new PrediccionesHelper($conexion);
    
    // Acción: obtener predicción
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        error_log("🔮 GET Predicción - Usuario: " . $usuario_id);
        
        $predicciones = $prediccionesHelper->obtenerPredicciones($usuario_id);
        
        error_log("📊 Predicciones obtenidas: " . count($predicciones));
        
        if (empty($predicciones)) {
            error_log("❌ No se pudieron generar predicciones");
            echo json_encode([
                'success' => false,
                'error' => 'No se pudieron generar predicciones'
            ]);
            exit;
        }
        
        error_log("✅ Retornando " . count($predicciones) . " predicciones");
        
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
