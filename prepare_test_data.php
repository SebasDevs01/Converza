<?php
require_once(__DIR__.'/app/models/config.php');
session_start();
$_SESSION['id'] = 3; // Simular usuario ID 3

// Agregar algunas reacciones de prueba con diferentes usuarios
echo "Agregando reacciones de prueba...\n";

// Usuario 1: me_divierte
$stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (1, 1, 'me_divierte', NOW()) ON DUPLICATE KEY UPDATE tipo_reaccion = 'me_divierte'");
$stmt->execute();

// Usuario 2: me_divierte  
$stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (2, 1, 'me_divierte', NOW()) ON DUPLICATE KEY UPDATE tipo_reaccion = 'me_divierte'");
$stmt->execute();

// Usuario 3: me_divierte
$stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (3, 1, 'me_divierte', NOW()) ON DUPLICATE KEY UPDATE tipo_reaccion = 'me_divierte'");
$stmt->execute();

// Verificar nombres de usuario
echo "\nVerificando usuarios en la BD:\n";
$stmt = $conexion->prepare("SELECT id_use, usuario FROM usuarios WHERE id_use IN (1,2,3)");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($usuarios as $user) {
    echo "- ID {$user['id_use']}: {$user['usuario']}\n";
}

echo "\nReacciones insertadas. El contador debería mostrar:\n";
echo "'😂 [Usuario1], [Usuario2] y 1 más les divierte esto'\n";
?>