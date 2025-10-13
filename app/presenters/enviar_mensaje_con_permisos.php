<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/chat-permisos-helper.php';
require_once __DIR__.'/../models/notificaciones-triggers.php';

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
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
$de = $_SESSION['id'];

// Instanciar sistema de notificaciones
$notificacionesTriggers = new NotificacionesTriggers($conexion);

if ($para <= 0 || empty($mensaje)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

try {
    // Verificar permisos de chat
    $permisos = verificarPermisoChat($conexion, $de, $para);
    
    if ($permisos['puede_chatear']) {
        // Puede chatear libremente - enviar mensaje directamente
        $stmt = $conexion->prepare("
            INSERT INTO chats (de, para, mensaje, leido, fecha) 
            VALUES (:de, :para, :mensaje, 0, NOW())
        ");
        $stmt->execute([
            ':de' => $de,
            ':para' => $para,
            ':mensaje' => $mensaje
        ]);
        
        // Obtener nombre del usuario que envía el mensaje
        $stmtNombre = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
        $stmtNombre->execute([':id' => $de]);
        $datosUsuario = $stmtNombre->fetch(PDO::FETCH_ASSOC);
        $nombreUsuario = $datosUsuario['usuario'] ?? 'Usuario';
        
        // Enviar notificación de nuevo mensaje
        $notificacionesTriggers->nuevoMensaje($de, $para, $nombreUsuario, $mensaje);
        
        echo json_encode([
            'success' => true,
            'tipo' => 'mensaje_enviado',
            'mensaje' => 'Mensaje enviado correctamente',
            'tipo_relacion' => $permisos['tipo_relacion']
        ]);
        
    } else if ($permisos['necesita_solicitud']) {
        // No puede chatear - necesita enviar solicitud de mensaje (SOLO 1 MENSAJE)
        $solicitudExistente = tieneSolicitudMensajePendiente($conexion, $de, $para);
        
        if ($solicitudExistente) {
            // Ya tiene solicitud pendiente - NO puede enviar más mensajes
            if ($solicitudExistente['estado'] === 'pendiente') {
                echo json_encode([
                    'success' => false,
                    'tipo' => 'solicitud_pendiente',
                    'error' => 'Ya enviaste un mensaje a este usuario. Espera a que acepte tu solicitud para poder chatear.',
                    'primer_mensaje' => $solicitudExistente['primer_mensaje']
                ]);
            } else if ($solicitudExistente['estado'] === 'rechazada') {
                echo json_encode([
                    'success' => false,
                    'tipo' => 'solicitud_rechazada',
                    'error' => 'Este usuario rechazó tu solicitud de mensaje anterior.'
                ]);
            }
        } else {
            // Crear solicitud de mensaje con el ÚNICO mensaje permitido
            if (crearSolicitudMensaje($conexion, $de, $para, $mensaje)) {
                // Obtener nombre del usuario que envía la solicitud
                $stmtNombre = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
                $stmtNombre->execute([':id' => $de]);
                $datosUsuario = $stmtNombre->fetch(PDO::FETCH_ASSOC);
                $nombreUsuario = $datosUsuario['usuario'] ?? 'Usuario';
                
                // Enviar notificación al usuario destino
                $notificacionesTriggers->solicitudMensajeEnviada($de, $para, $nombreUsuario, $mensaje);
                
                echo json_encode([
                    'success' => true,
                    'tipo' => 'solicitud_creada',
                    'mensaje' => '📬 Solicitud de mensaje enviada. Solo puedes enviar 1 mensaje hasta que el usuario lo acepte.',
                    'limitado' => true
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Error al crear solicitud de mensaje'
                ]);
            }
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
