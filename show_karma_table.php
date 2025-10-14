<?php
require_once 'app/models/config.php';
echo "Columnas de karma_social:\n";
$stmt = $conexion->query('DESCRIBE karma_social');
foreach($stmt->fetchAll() as $col) {
    echo "  - " . $col['Field'] . "\n";
}
?>
