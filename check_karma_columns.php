<?php
require_once('app/models/config.php');

echo "=== VERIFICAR ESTRUCTURA TABLA KARMA_SOCIAL ===\n\n";

try {
    $stmt = $conexion->query('DESCRIBE karma_social');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas en la tabla:\n";
    foreach($cols as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
