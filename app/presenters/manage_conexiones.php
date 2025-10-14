<?php
/**
 * API ENDPOINT: Gestión de Conexiones Místicas
 * Permite limpiar, actualizar y obtener contador
 */

session_start();
header('Content-Type: application/json');

require_once(__DIR__ . '/../models/config.php');
require_once(__DIR__ . '/../models/conexiones-misticas-helper.php');

// Verificar autenticación
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No autenticado'
    ]);
    exit;
}

$usuario_id = $_SESSION['id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'contador';

try {
    $conexionesMisticas = new ConexionesMisticas($conexion);
    
    switch ($action) {
        case 'limpiar':
            // Limpiar conexiones del usuario
            $resultado = $conexionesMisticas->limpiarConexionesUsuario($usuario_id);
            echo json_encode($resultado);
            break;
            
        case 'actualizar':
            // Actualizar conexiones (detectar nuevas)
            ob_start();
            $resultado = $conexionesMisticas->actualizarConexionesAutomatico();
            $output = ob_get_clean();
            
            // Obtener nuevo contador
            $contador = $conexionesMisticas->obtenerContador($usuario_id);
            
            echo json_encode([
                'success' => $resultado,
                'mensaje' => $resultado ? 'Conexiones actualizadas correctamente' : 'Error al actualizar',
                'contador' => $contador,
                'log' => $output
            ]);
            break;
            
        case 'contador':
            // Solo obtener contador (más ligero)
            $contador = $conexionesMisticas->obtenerContador($usuario_id);
            
            echo json_encode([
                'success' => true,
                'contador' => $contador
            ]);
            break;
            
        case 'limpiar_y_actualizar':
            // Limpiar y luego actualizar
            $limpiar = $conexionesMisticas->limpiarConexionesUsuario($usuario_id);
            
            if ($limpiar['success']) {
                ob_start();
                $actualizar = $conexionesMisticas->actualizarConexionesAutomatico();
                ob_get_clean();
                
                $contador = $conexionesMisticas->obtenerContador($usuario_id);
                
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Conexiones renovadas completamente',
                    'eliminadas' => $limpiar['eliminadas'],
                    'contador' => $contador
                ]);
            } else {
                echo json_encode($limpiar);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida'
            ]);
    }
    
} catch (Exception $e) {
    error_log("Error en manage_conexiones.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
