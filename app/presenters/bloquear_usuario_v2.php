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

// Verificar que se recibió el ID del usuario a bloquear
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
    errorResponse('No puedes bloquearte a ti mismo');
}

try {
    error_log("=== INICIO BLOQUEO ===");
    error_log("Bloqueador ID: $bloqueador_id");
    error_log("Bloqueado ID: $bloqueado_id");
    
    // Verificar que el usuario a bloquear existe
    $stmtUser = $conexion->prepare('SELECT id_use FROM usuarios WHERE id_use = ?');
    $stmtUser->execute([$bloqueado_id]);
    if (!$stmtUser->fetch()) {
        errorResponse('El usuario a bloquear no existe');
    }
    error_log("Usuario a bloquear existe: OK");
    
    // Crear tabla de bloqueos si no existe (versión simplificada)
    $createTable = "
        CREATE TABLE IF NOT EXISTS bloqueos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bloqueador_id INT NOT NULL,
            bloqueado_id INT NOT NULL,
            fecha_bloqueo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_bloqueo (bloqueador_id, bloqueado_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    if ($conexion->exec($createTable) === false) {
        $error = $conexion->errorInfo();
        error_log("Error creando tabla: " . print_r($error, true));
        errorResponse('Error creando tabla de bloqueos', $error);
    }
    error_log("Tabla bloqueos verificada: OK");
    
    // Iniciar transacción
    $conexion->beginTransaction();
    error_log("Transacción iniciada: OK");
    
    // Verificar si ya está bloqueado
    $stmtCheck = $conexion->prepare('SELECT id FROM bloqueos WHERE bloqueador_id = ? AND bloqueado_id = ?');
    $stmtCheck->execute([$bloqueador_id, $bloqueado_id]);
    
    if ($stmtCheck->fetch()) {
        $conexion->rollBack();
        errorResponse('Usuario ya está bloqueado');
    }
    error_log("Verificación de bloqueo existente: OK");
    
    // Eliminar amistades
    $deleteAmigos = $conexion->prepare('
        DELETE FROM amigos 
        WHERE (de = ? AND para = ?) OR (de = ? AND para = ?)
    ');
    $deleteAmigos->execute([$bloqueador_id, $bloqueado_id, $bloqueado_id, $bloqueador_id]);
    $amistades_eliminadas = $deleteAmigos->rowCount();
    error_log("Amistades eliminadas: $amistades_eliminadas");
    
    // Eliminar seguimientos
    $deleteSeguimientos = $conexion->prepare('
        DELETE FROM seguidores 
        WHERE (seguidor_id = ? AND seguido_id = ?) OR (seguidor_id = ? AND seguido_id = ?)
    ');
    $deleteSeguimientos->execute([$bloqueador_id, $bloqueado_id, $bloqueado_id, $bloqueador_id]);
    $seguimientos_eliminados = $deleteSeguimientos->rowCount();
    error_log("Seguimientos eliminados: $seguimientos_eliminados");
    
    // Insertar bloqueo
    $insertBloqueo = $conexion->prepare('
        INSERT INTO bloqueos (bloqueador_id, bloqueado_id) 
        VALUES (?, ?)
    ');
    $insertBloqueo->execute([$bloqueador_id, $bloqueado_id]);
    $bloqueo_id = $conexion->lastInsertId();
    error_log("Bloqueo insertado con ID: $bloqueo_id");
    
    // Confirmar transacción
    $conexion->commit();
    error_log("=== BLOQUEO COMPLETADO ===");
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario bloqueado correctamente',
        'data' => [
            'bloqueo_id' => $bloqueo_id,
            'amistades_eliminadas' => $amistades_eliminadas,
            'seguimientos_eliminados' => $seguimientos_eliminados
        ]
    ]);
    
} catch (PDOException $e) {
    if (isset($conexion)) {
        $conexion->rollBack();
    }
    error_log("ERROR PDO: " . $e->getMessage());
    errorResponse('Error de base de datos', $e->getMessage());
    
} catch (Exception $e) {
    if (isset($conexion)) {
        $conexion->rollBack();
    }
    error_log("ERROR GENERAL: " . $e->getMessage());
    errorResponse('Error interno del servidor', $e->getMessage());
}
?>