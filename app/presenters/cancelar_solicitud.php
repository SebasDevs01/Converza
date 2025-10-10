<?php
session_start();
require_once '../models/config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que se recibió el ID del usuario
if (!isset($_POST['usuario_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
    exit();
}

$usuario_id = (int)$_POST['usuario_id'];
$mi_id = (int)$_SESSION['id'];

try {
    // Verificar que existe una solicitud enviada
    $stmtVerificar = $conexion->prepare("SELECT id_ami FROM amigos WHERE de = :mi_id AND para = :usuario_id AND estado = 0");
    $stmtVerificar->bindParam(':mi_id', $mi_id, PDO::PARAM_INT);
    $stmtVerificar->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmtVerificar->execute();
    
    if ($stmtVerificar->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No se encontró la solicitud']);
        exit();
    }
    
    // Cancelar la solicitud (eliminar el registro)
    $stmtCancelar = $conexion->prepare("DELETE FROM amigos WHERE de = :mi_id AND para = :usuario_id AND estado = 0");
    $stmtCancelar->bindParam(':mi_id', $mi_id, PDO::PARAM_INT);
    $stmtCancelar->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmtCancelar->execute();
    
    if ($stmtCancelar->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Solicitud cancelada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cancelar la solicitud']);
    }
    
} catch (Exception $e) {
    error_log("Error al cancelar solicitud: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>