<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__.'/../models/config.php';

// Verificar sesión
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit();
}

// Obtener datos del POST
$accion = $_POST['accion'] ?? '';
$usuario_chat_id = (int)($_POST['usuario_id'] ?? 0);
$usuario_actual_id = (int)$_SESSION['id'];

if (!$accion || !$usuario_chat_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

try {
    if ($accion === 'archivar') {
        // Insertar en tabla de archivados (o actualizar si ya existe)
        $stmt = $conexion->prepare("
            INSERT INTO chats_archivados (usuario_id, chat_con_usuario_id)
            VALUES (:usuario_id, :chat_con)
            ON DUPLICATE KEY UPDATE fecha_archivado = NOW()
        ");
        $stmt->execute([
            ':usuario_id' => $usuario_actual_id,
            ':chat_con' => $usuario_chat_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Chat archivado correctamente',
            'accion' => 'archivado'
        ]);
        
    } elseif ($accion === 'desarchivar') {
        // Eliminar de tabla de archivados
        $stmt = $conexion->prepare("
            DELETE FROM chats_archivados 
            WHERE usuario_id = :usuario_id 
            AND chat_con_usuario_id = :chat_con
        ");
        $stmt->execute([
            ':usuario_id' => $usuario_actual_id,
            ':chat_con' => $usuario_chat_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Chat desarchivado correctamente',
            'accion' => 'desarchivado'
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (PDOException $e) {
    error_log("Error en gestionar_archivo_chat.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
