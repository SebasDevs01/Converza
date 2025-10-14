<?php
require_once 'app/models/config.php';
$stmt = $conexion->query('DESCRIBE usuarios');
foreach($stmt->fetchAll() as $col) {
    if (stripos($col['Field'], 'karma') !== false) {
        echo $col['Field'] . "\n";
    }
}
?>
