<?php

require_once '../models/config.php'; // Asegúrate de que este archivo contiene la configuración de la base de datos

header('Content-Type: application/json');

$id_usuario = $_GET['id_usuario'] ?? null;
$id_publicacion = $_GET['id_publicacion'] ?? null;

if (!$id_usuario || !$id_publicacion) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT tipo_reaccion FROM reacciones WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion");
    $stmt->execute(['id_usuario' => $id_usuario, 'id_publicacion' => $id_publicacion]);
    $reaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reaction) {
        echo json_encode(['success' => true, 'reaction' => $reaction['tipo_reaccion']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró reacción']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}