<?php
session_start();
require_once __DIR__.'/../models/config.php';

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo 'No autorizado.';
    exit;
}

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$yo = $_SESSION['id'];


if ($id <= 0 || $id == $yo) {
    echo 'Solicitud inválida.';
    exit;
}

// Verificar que el usuario destino existe
$stmt = $conexion->prepare('SELECT id_use FROM usuarios WHERE id_use = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
if (!$stmt->fetch()) {
    echo 'El usuario no existe.';
    exit;
}

if ($action === 'agregar') {
    // Verificar si ya existe una solicitud pendiente o amistad
    $stmt = $conexion->prepare('
        SELECT * FROM amigos 
        WHERE (de = :yo1 AND para = :id1) 
           OR (de = :id2 AND para = :yo2)
    ');
    $stmt->bindParam(':yo1', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id1', $id, PDO::PARAM_INT);
    $stmt->bindParam(':id2', $id, PDO::PARAM_INT);
    $stmt->bindParam(':yo2', $yo, PDO::PARAM_INT);
    $stmt->execute();

    $existe = $stmt->fetch();
    if ($existe) {
        echo 'Ya existe una solicitud o amistad.';
        header('Location: /Converza/app/view/index.php');
        exit;
    }

    // Insertar solicitud
    $stmt = $conexion->prepare('INSERT INTO amigos (de, para, estado, fecha) VALUES (:yo, :id, 0, NOW())');
    $stmt->bindParam(':yo', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Notificación (simulada)
    $_SESSION['notificaciones'][] = "Solicitud enviada a usuario #$id";
    echo 'Solicitud enviada correctamente.';
    header('Location: /Converza/app/view/index.php');
    exit;
}


if ($action === 'aceptar') {
    $stmt = $conexion->prepare('UPDATE amigos SET estado = 1 WHERE para = :yo AND de = :id AND estado = 0');
    $stmt->bindParam(':yo', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    // Notificación (simulada)
    $_SESSION['notificaciones'][] = "Has aceptado la solicitud de usuario #$id";
    echo 'Solicitud aceptada.';
    header('Location: /Converza/app/view/index.php');
    exit;
}

if ($action === 'rechazar') {
    $stmt = $conexion->prepare('DELETE FROM amigos WHERE para = :yo AND de = :id AND estado = 0');
    $stmt->bindParam(':yo', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    echo 'Solicitud rechazada.';
    header('Location: /Converza/app/view/index.php');
    exit;
}

echo 'Acción no válida.';
exit;
