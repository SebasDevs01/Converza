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
    
    // Verificar que existe el bloqueo
    $stmtCheck = $conexion->prepare('SELECT id FROM bloqueos WHERE bloqueador_id = ? AND bloqueado_id = ?');
    $stmtCheck->execute([$bloqueador_id, $bloqueado_id]);
    
    if (!$stmtCheck->fetch()) {
        errorResponse('Este usuario no está bloqueado');
    }
    error_log("Bloqueo encontrado: OK");
    
    // Eliminar el bloqueo
    $deleteBloqueo = $conexion->prepare('DELETE FROM bloqueos WHERE bloqueador_id = ? AND bloqueado_id = ?');
    $deleteBloqueo->execute([$bloqueador_id, $bloqueado_id]);
    
    if ($deleteBloqueo->rowCount() > 0) {
        error_log("Bloqueo eliminado correctamente");
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario desbloqueado correctamente',
            'data' => [
                'bloqueador_id' => $bloqueador_id,
                'desbloqueado_id' => $bloqueado_id
            ]
        ]);
    } else {
        errorResponse('No se pudo eliminar el bloqueo');
    }
    
} catch (PDOException $e) {
    error_log("ERROR PDO DESBLOQUEO: " . $e->getMessage());
    errorResponse('Error de base de datos', $e->getMessage());
    
} catch (Exception $e) {
    error_log("ERROR GENERAL DESBLOQUEO: " . $e->getMessage());
    errorResponse('Error interno del servidor', $e->getMessage());
}
?>