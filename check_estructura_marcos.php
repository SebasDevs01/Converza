<?php
require_once __DIR__ . '/app/models/config.php';

$stmt = $conexion->query('DESCRIBE karma_recompensas');
echo "Estructura de karma_recompensas:\n\n";
foreach($stmt->fetchAll() as $col) {
    echo $col['Field'] . " | " . $col['Type'] . "\n";
}
?>
