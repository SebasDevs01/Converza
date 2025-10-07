<?php
require_once(__DIR__.'/../models/config.php');

header('Content-Type: application/json');

$id_usuario = $_POST['id_usuario'] ?? null;
$id_publicacion = $_POST['id_publicacion'] ?? null;
$tipo_reaccion = $_POST['tipo_reaccion'] ?? null;

if (!$id_usuario || !$id_publicacion || !$tipo_reaccion) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Verificar si el usuario ya reaccionó a esta publicación
    $stmt = $conexion->prepare("SELECT id FROM reacciones WHERE id_publicacion = :id_publicacion AND id_usuario = :id_usuario");
    $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $existingReaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingReaction) {
        // Actualizar la reacción existente
        $stmt = $conexion->prepare("UPDATE reacciones SET tipo_reaccion = :tipo_reaccion, fecha = NOW() WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion");
        $stmt->bindParam(':tipo_reaccion', $tipo_reaccion, PDO::PARAM_STR);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Insertar nueva reacción
        $stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (:id_usuario, :id_publicacion, :tipo_reaccion, NOW())");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_reaccion', $tipo_reaccion, PDO::PARAM_STR);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Reacción guardada']);
} catch (PDOException $e) {
    error_log("Error SQL: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
