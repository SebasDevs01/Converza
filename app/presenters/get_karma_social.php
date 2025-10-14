<?php
/**
 * API para obtener información de Karma Social del usuario
 * Retorna karma total, reciente, nivel y estadísticas
 */

session_start();
header('Content-Type: application/json');

require_once '../models/config.php';
require_once '../models/karma-social-helper.php';

// Permitir consulta por parámetro GET también
$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : null);

if (!$usuario_id) {
    echo json_encode(['error' => 'No autorizado o usuario no especificado']);
    exit;
}

try {
    $karmaHelper = new KarmaSocialHelper($conexion);
    
    // Obtener karma total
    $karma_total_data = $karmaHelper->obtenerKarmaTotal($usuario_id);
    $karma_total = $karma_total_data['karma_total'];
    
    // Obtener karma reciente
    $karma_reciente = $karmaHelper->obtenerKarmaReciente($usuario_id);
    
    // Obtener nivel
    $nivel = $karmaHelper->obtenerNivelKarma($karma_total);
    
    // Obtener historial
    $historial = $karmaHelper->obtenerHistorial($usuario_id, 10);
    
    // Calcular progreso al siguiente nivel
    $niveles_puntos = [0, 50, 100, 250, 500, 1000];
    $nivel_actual_idx = 0;
    
    foreach ($niveles_puntos as $idx => $puntos) {
        if ($karma_total >= $puntos) {
            $nivel_actual_idx = $idx;
        }
    }
    
    $puntos_nivel_actual = $niveles_puntos[$nivel_actual_idx];
    $puntos_siguiente_nivel = isset($niveles_puntos[$nivel_actual_idx + 1]) 
        ? $niveles_puntos[$nivel_actual_idx + 1] 
        : $puntos_nivel_actual;
    
    $progreso = $puntos_siguiente_nivel > $puntos_nivel_actual
        ? (($karma_total - $puntos_nivel_actual) / ($puntos_siguiente_nivel - $puntos_nivel_actual)) * 100
        : 100;
    
    // Respuesta
    echo json_encode([
        'success' => true,
        'karma' => [
            'total' => $karma_total,
            'acciones_totales' => $karma_total_data['acciones_totales'],
            'karma_30dias' => $karma_reciente['karma_30dias'],
            'acciones_30dias' => $karma_reciente['acciones_30dias']
        ],
        'nivel' => [
            'nombre' => $nivel['nivel'],
            'emoji' => $nivel['emoji'],
            'color' => $nivel['color'],
            'progreso' => round($progreso, 1),
            'puntos_actual' => $karma_total,
            'puntos_siguiente' => $puntos_siguiente_nivel
        ],
        'historial' => $historial,
        'multiplicador' => $karmaHelper->calcularMultiplicadorConexiones($karma_total)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al obtener karma',
        'mensaje' => $e->getMessage()
    ]);
}
?>
