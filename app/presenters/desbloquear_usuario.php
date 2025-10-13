<?php
session_start();
require_once __DIR__ . '/../models/config.php';

// Configurar respuesta JSON
header('Content-Type: application/json; charset=utf-8');

// Función para respuesta de error
function errorResponse($message, $debug = null) {
    echo json_encode([
        'success' => false, 
        'message' => $message,
        'debug' => $debug
    ]);
    exit;
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    errorResponse('No autorizado');
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Método no permitido');
}

// Verificar que se recibió el ID del usuario a desbloquear
if (!isset($_POST['usuario_id']) || empty($_POST['usuario_id'])) {
    errorResponse('ID de usuario requerido', $_POST);
}

$bloqueador_id = intval($_SESSION['id']);
$bloqueado_id = intval($_POST['usuario_id']);

// Validaciones básicas
if ($bloqueador_id <= 0 || $bloqueado_id <= 0) {
    errorResponse('IDs de usuario inválidos');
}

if ($bloqueador_id == $bloqueado_id) {
    errorResponse('No puedes desbloquearte a ti mismo');
}

try {
    error_log("=== INICIO DESBLOQUEO ===");
    error_log("Bloqueador ID: $bloqueador_id");
    error_log("Usuario a desbloquear ID: $bloqueado_id");
    
    // Verificar que el usuario a desbloquear existe
    $stmtUser = $conexion->prepare('SELECT id_use FROM usuarios WHERE id_use = ?');
    $stmtUser->execute([$bloqueado_id]);
    if (!$stmtUser->fetch()) {
        errorResponse('El usuario no existe');
    }
    error_log("Usuario a desbloquear existe: OK");
    
    // Intentar eliminar el bloqueo directamente (sin verificar primero si existe)
    $deleteBloqueo = $conexion->prepare('DELETE FROM bloqueos WHERE bloqueador_id = ? AND bloqueado_id = ?');
    $deleteBloqueo->execute([$bloqueador_id, $bloqueado_id]);
    
    // Si se eliminó al menos un registro o si no existía el bloqueo, considerarlo exitoso
    $bloqueoExistia = $deleteBloqueo->rowCount() > 0;
    
    // Registrar si existía o no el bloqueo
    if ($bloqueoExistia) {
        error_log("Bloqueo eliminado correctamente");
    } else {
        error_log("El bloqueo no existía, pero se considera operación exitosa");
    }
    
    // Verificar si hay conversación previa entre estos usuarios
    $stmtConversacion = $conexion->prepare('
        SELECT COUNT(*) as total 
        FROM chats 
        WHERE (de = ? AND para = ?) 
           OR (de = ? AND para = ?)
    ');
    $stmtConversacion->execute([$bloqueador_id, $bloqueado_id, $bloqueado_id, $bloqueador_id]);
    $resultConv = $stmtConversacion->fetch(PDO::FETCH_ASSOC);
    $tieneConversacion = $resultConv['total'] > 0;
    
    error_log("Conversación previa encontrada: " . ($tieneConversacion ? 'SI' : 'NO') . " ({$resultConv['total']} mensajes)");
    
    // Obtener datos del usuario desbloqueado para el frontend
    $stmtUsuario = $conexion->prepare('
        SELECT u.id_use, u.usuario, u.nombre, u.avatar,
               CASE 
                   WHEN EXISTS (SELECT 1 FROM amigos WHERE 
                       ((de = ? AND para = u.id_use) OR (de = u.id_use AND para = ?))
                       AND estado = 1
                   ) THEN "amigo"
                   WHEN EXISTS (SELECT 1 FROM seguidores WHERE 
                       (seguidor_id = ? AND seguido_id = u.id_use) AND
                       EXISTS (SELECT 1 FROM seguidores WHERE seguidor_id = u.id_use AND seguido_id = ?)
                   ) THEN "seguidor_mutuo"
                   ELSE "ninguno"
               END as tipo_relacion
        FROM usuarios u
        WHERE u.id_use = ?
    ');
    $stmtUsuario->execute([
        $bloqueador_id, $bloqueador_id, 
        $bloqueador_id, $bloqueador_id,
        $bloqueado_id
    ]);
    $usuarioData = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
    
    // Responder con éxito, independientemente de si el bloqueo existía o no
    echo json_encode([
        'success' => true,
        'message' => 'Usuario desbloqueado correctamente',
        'data' => [
            'bloqueador_id' => $bloqueador_id,
            'desbloqueado_id' => $bloqueado_id,
            'tiene_conversacion' => $tieneConversacion,
            'mensajes_previos' => $resultConv['total'],
            'usuario' => $usuarioData,
            'bloqueo_existia' => $bloqueoExistia
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("ERROR PDO DESBLOQUEO: " . $e->getMessage());
    errorResponse('Error de base de datos', $e->getMessage());
    
} catch (Exception $e) {
    error_log("ERROR GENERAL DESBLOQUEO: " . $e->getMessage());
    errorResponse('Error interno del servidor', $e->getMessage());
}
?>