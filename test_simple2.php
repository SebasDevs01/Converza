<?php
require_once(__DIR__.'/app/models/config.php');

echo "TEST: Inserción directa de 'me_entristese'\n";

$stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (1, 1, 'me_entristese', NOW()) ON DUPLICATE KEY UPDATE tipo_reaccion = 'me_entristese', fecha = NOW()");

if ($stmt->execute()) {
    echo "ÉXITO: Reacción insertada/actualizada\n";
} else {
    echo "ERROR: ";
    print_r($stmt->errorInfo());
}

// Verificar
$stmt2 = $conexion->prepare("SELECT * FROM reacciones WHERE id_usuario = 1 AND id_publicacion = 1");
$stmt2->execute();
$result = $stmt2->fetch();

if ($result) {
    echo "VERIFICACIÓN: Reacción encontrada: " . $result['tipo_reaccion'] . "\n";
} else {
    echo "ERROR: No se encontró la reacción\n";
}
?>