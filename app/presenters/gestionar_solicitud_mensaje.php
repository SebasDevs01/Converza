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

// Instanciar clase de notificaciones
$notificacionesTriggers = new NotificacionesTriggers($conexion);

$accion = $_POST['accion'] ?? '';
$solicitudId = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;
$usuarioActual = $_SESSION['id'];

if (empty($accion) || $solicitudId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

try {
    if ($accion === 'aceptar') {
        if (aceptarSolicitudMensaje($conexion, $solicitudId, $usuarioActual)) {
            // Obtener info de la solicitud para enviar el primer mensaje
            $stmt = $conexion->prepare("SELECT de, primer_mensaje, fecha_solicitud FROM solicitudes_mensaje WHERE id = :id");
            $stmt->execute([':id' => $solicitudId]);
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($solicitud) {
                $deUsuario = $solicitud['de'];
                $paraUsuario = $usuarioActual;
                $fechaOriginal = $solicitud['fecha_solicitud'];
                
                // Obtener nombre del usuario que acepta para la notificación
                $stmtNombre = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
                $stmtNombre->execute([':id' => $usuarioActual]);
                $datosUsuario = $stmtNombre->fetch(PDO::FETCH_ASSOC);
                $nombreUsuario = $datosUsuario['usuario'] ?? 'Usuario';
                
                // Enviar notificación al usuario que envió la solicitud
                $notificacionesTriggers->solicitudMensajeAceptada($usuarioActual, $deUsuario, $nombreUsuario);
                
                // 1. Verificar si existe conversación en c_chats
                $stmtCheckConv = $conexion->prepare("
                    SELECT id_cch FROM c_chats 
                    WHERE (de = :de1 AND para = :para1) 
                       OR (de = :de2 AND para = :para2)
                ");
                $stmtCheckConv->execute([
                    ':de1' => $deUsuario,
                    ':para1' => $paraUsuario,
                    ':de2' => $paraUsuario,
                    ':para2' => $deUsuario
                ]);
                $conversacion = $stmtCheckConv->fetch(PDO::FETCH_ASSOC);
                
                // 2. Si no existe, crear la conversación
                if (!$conversacion) {
                    $stmtCreateConv = $conexion->prepare("
                        INSERT INTO c_chats (de, para) 
                        VALUES (:de, :para)
                    ");
                    $stmtCreateConv->execute([
                        ':de' => $deUsuario,
                        ':para' => $paraUsuario
                    ]);
                    $conversacionId = $conexion->lastInsertId();
                } else {
                    $conversacionId = $conversacion['id_cch'];
                }
                
                // 3. Verificar que el mensaje no exista ya (evitar duplicados)
                $stmtCheckMsg = $conexion->prepare("
                    SELECT id_cha FROM chats 
                    WHERE de = :de AND para = :para 
                    AND mensaje = :mensaje 
                    AND DATE(fecha) = DATE(:fecha)
                ");
                $stmtCheckMsg->execute([
                    ':de' => $deUsuario,
                    ':para' => $paraUsuario,
                    ':mensaje' => $solicitud['primer_mensaje'],
                    ':fecha' => $fechaOriginal
                ]);
                
                // 4. Si no existe, insertar el mensaje con la fecha original
                if (!$stmtCheckMsg->fetch()) {
                    $stmtMsg = $conexion->prepare("
                        INSERT INTO chats (id_cch, de, para, mensaje, leido, fecha) 
                        VALUES (:id_cch, :de, :para, :mensaje, 0, :fecha)
                    ");
                    $stmtMsg->execute([
                        ':id_cch' => $conversacionId,
                        ':de' => $deUsuario,
                        ':para' => $paraUsuario,
                        ':mensaje' => $solicitud['primer_mensaje'],
                        ':fecha' => $fechaOriginal
                    ]);
                }
            }
            
            // Obtener información del usuario para agregarlo a la lista de chats
            $stmtUsuario = $conexion->prepare("
                SELECT u.id_use, u.usuario, u.avatar, u.verificado,
                'solicitud_aceptada' as tipo_relacion
                FROM usuarios u 
                WHERE u.id_use = :id
            ");
            $stmtUsuario->execute([':id' => $deUsuario]);
            $usuarioInfo = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'mensaje' => 'Solicitud aceptada. Ahora pueden chatear.',
                'usuario' => $usuarioInfo
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al aceptar solicitud']);
        }
        
    } else if ($accion === 'rechazar') {
        if (rechazarSolicitudMensaje($conexion, $solicitudId, $usuarioActual)) {
            // Obtener info de la solicitud rechazada
            $stmt = $conexion->prepare("SELECT de FROM solicitudes_mensaje WHERE id = :id");
            $stmt->execute([':id' => $solicitudId]);
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($solicitud) {
                $deUsuario = $solicitud['de'];
                
                // Obtener nombre del usuario que rechaza
                $stmtNombre = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
                $stmtNombre->execute([':id' => $usuarioActual]);
                $datosUsuario = $stmtNombre->fetch(PDO::FETCH_ASSOC);
                $nombreUsuario = $datosUsuario['usuario'] ?? 'Usuario';
                
                // Enviar notificación al usuario que envió la solicitud
                $notificacionesTriggers->solicitudMensajeRechazada($usuarioActual, $deUsuario, $nombreUsuario);
            }
            
            echo json_encode([
                'success' => true,
                'mensaje' => 'Solicitud rechazada'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al rechazar solicitud']);
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Acción inválida']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
