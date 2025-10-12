<?php
session_start();
require_once __DIR__.'/../models/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['usuario'])) {
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $messageId = intval($input['messageId'] ?? 0);
    $deleteType = $input['deleteType'] ?? 'for_me';
    $userId = $_SESSION['id'];
    
    if ($messageId === 0) {
        echo json_encode(['error' => 'ID de mensaje inválido']);
        exit;
    }
    
    // Verificar que el mensaje existe
    $stmtCheck = $conexion->prepare("SELECT id_cha, de, para, fecha, archivo_audio FROM chats WHERE id_cha = ?");
    $stmtCheck->execute([$messageId]);
    $message = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        echo json_encode(['error' => 'Mensaje no encontrado']);
        exit;
    }
    
    // Verificar que el usuario es parte de la conversación
    if ($message['de'] != $userId && $message['para'] != $userId) {
        echo json_encode(['error' => 'No tienes permisos para eliminar este mensaje']);
        exit;
    }
    
    // Verificar que solo el remitente puede eliminar "para todos" (SIN límite de tiempo)
    if ($deleteType === 'for_everyone') {
        if ($message['de'] != $userId) {
            echo json_encode(['error' => 'Solo el remitente puede eliminar para todos']);
            exit;
        }
        // Ya no hay verificación de tiempo - se puede eliminar en cualquier momento
    }
    
    // Crear tabla de eliminados si no existe
    try {
        $conexion->exec("CREATE TABLE IF NOT EXISTS mensajes_eliminados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mensaje_id INT NOT NULL,
            usuario_id INT NOT NULL,
            fecha_eliminacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_mensaje (mensaje_id),
            INDEX idx_usuario (usuario_id),
            UNIQUE KEY unique_user_message (mensaje_id, usuario_id),
            FOREIGN KEY (mensaje_id) REFERENCES chats(id_cha) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
        )");
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error creando tabla: ' . $e->getMessage()]);
        exit;
    }

    if ($deleteType === 'for_everyone') {
        // Eliminar para todos: eliminar mensaje completamente de la base de datos
        // Esto hace que desaparezca para ambos usuarios
        $stmtDelete = $conexion->prepare("DELETE FROM chats WHERE id_cha = ?");
        $result = $stmtDelete->execute([$messageId]);
        
        if ($result) {
            // También eliminar el archivo de audio si existe
            if (!empty($message['archivo_audio'])) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . '/Converza/public/voice_messages/' . $message['archivo_audio'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Limpiar registros de mensajes_eliminados para este mensaje (ya no existe)
            $conexion->prepare("DELETE FROM mensajes_eliminados WHERE mensaje_id = ?")->execute([$messageId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Mensaje eliminado para todos',
                'deleteType' => 'for_everyone'
            ]);
        } else {
            echo json_encode(['error' => 'Error al eliminar mensaje']);
        }
        
    } else {
        // Eliminar solo para mí: marcar como eliminado solo para este usuario
        // El mensaje sigue existiendo para el otro usuario
        $stmtHide = $conexion->prepare("INSERT IGNORE INTO mensajes_eliminados (mensaje_id, usuario_id) VALUES (?, ?)");
        $result = $stmtHide->execute([$messageId, $userId]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Mensaje eliminado para ti',
                'deleteType' => 'for_me'
            ]);
        } else {
            echo json_encode(['error' => 'Error al ocultar mensaje']);
        }
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>