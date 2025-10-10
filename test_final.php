<?php
require_once(__DIR__.'/app/models/config.php');

echo "Test rápido del sistema:\n\n";

// Test de inserción de me_entristece
$stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (1, 1, 'me_entristece', NOW()) ON DUPLICATE KEY UPDATE tipo_reaccion = 'me_entristece'");
if ($stmt->execute()) {
    echo "✅ me_entristece: OK\n";
} else {
    echo "❌ me_entristece: Error\n";
}

// Test de inserción de me_divierte
$stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (2, 1, 'me_divierte', NOW()) ON DUPLICATE KEY UPDATE tipo_reaccion = 'me_divierte'");
if ($stmt->execute()) {
    echo "✅ me_divierte: OK\n";
} else {
    echo "❌ me_divierte: Error\n";
}

echo "\nContador debe mostrar solo una vez el resultado.\n";
?>