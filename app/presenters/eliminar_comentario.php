<?php
require(__DIR__.'/../models/config.php');
session_start();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos
$input = json_decode(file_get_contents('php://input'), true);
$comentario_id = isset($input['comentario_id']) ? (int)$input['comentario_id'] : 0;
$usuario_id = $_SESSION['usuario_id'];

if ($comentario_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID de comentario no válido']);
    exit;
}

try {
    // Verificar que el comentario existe y pertenece al usuario
    $stmt_check = $conexion->prepare("SELECT id_com, usuario FROM comentarios WHERE id_com = :id_com");
    $stmt_check->execute([':id_com' => $comentario_id]);
    $comentario = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    if (!$comentario) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Comentario no encontrado']);
        exit;
    }
    
    // Verificar que el usuario es el propietario del comentario
    if ((int)$comentario['usuario'] !== $usuario_id) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para eliminar este comentario']);
        exit;
    }
    
    // Eliminar el comentario
    $stmt_delete = $conexion->prepare("DELETE FROM comentarios WHERE id_com = :id_com");
    $stmt_delete->execute([':id_com' => $comentario_id]);
    
    if ($stmt_delete->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Comentario eliminado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el comentario']);
    }
    
} catch (PDOException $e) {
    error_log("Error al eliminar comentario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor']);
}
?>