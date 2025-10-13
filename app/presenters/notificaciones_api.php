<?php
session_start();
require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../models/notificaciones-helper.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = intval($_SESSION['id']);
$notificacionesHelper = new NotificacionesHelper($conexion);

// Obtener acción
$accion = $_GET['accion'] ?? 'obtener';

try {
    switch ($accion) {
        case 'obtener':
            // Obtener todas las notificaciones no leídas
            $notificaciones = $notificacionesHelper->obtenerNoLeidas($usuario_id);
            $total = $notificacionesHelper->contarNoLeidas($usuario_id);
            
            echo json_encode([
                'success' => true,
                'notificaciones' => $notificaciones,
                'total' => $total
            ]);
            break;
            
        case 'todas':
            // Obtener todas las notificaciones (leídas y no leídas)
            $notificaciones = $notificacionesHelper->obtenerTodas($usuario_id);
            $total = $notificacionesHelper->contarNoLeidas($usuario_id);
            
            echo json_encode([
                'success' => true,
                'notificaciones' => $notificaciones,
                'total' => $total
            ]);
            break;
            
        case 'marcar_leida':
            // Marcar una notificación como leída
            if (!isset($_POST['notificacion_id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de notificación requerido']);
                exit;
            }
            
            $notificacion_id = intval($_POST['notificacion_id']);
            $resultado = $notificacionesHelper->marcarComoLeida($notificacion_id);
            $total = $notificacionesHelper->contarNoLeidas($usuario_id);
            
            echo json_encode([
                'success' => $resultado,
                'total' => $total
            ]);
            break;
            
        case 'marcar_todas_leidas':
            // Marcar todas como leídas
            $resultado = $notificacionesHelper->marcarTodasComoLeidas($usuario_id);
            
            echo json_encode([
                'success' => $resultado,
                'total' => 0
            ]);
            break;
            
        case 'eliminar':
            // Eliminar una notificación
            if (!isset($_POST['notificacion_id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de notificación requerido']);
                exit;
            }
            
            $notificacion_id = intval($_POST['notificacion_id']);
            $resultado = $notificacionesHelper->eliminar($notificacion_id);
            $total = $notificacionesHelper->contarNoLeidas($usuario_id);
            
            echo json_encode([
                'success' => $resultado,
                'total' => $total
            ]);
            break;
            
        case 'eliminar_todas':
            // Eliminar todas las notificaciones del usuario
            $resultado = $notificacionesHelper->eliminarTodas($usuario_id);
            
            echo json_encode([
                'success' => $resultado,
                'total' => 0
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    error_log("Error en API de notificaciones: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>
