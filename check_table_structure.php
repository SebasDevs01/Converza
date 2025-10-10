<?php
require_once(__DIR__.'/app/models/config.php');

echo "=== ESTRUCTURA DE LA TABLA REACCIONES ===\n\n";

try {
    $stmt = $conexion->prepare("DESCRIBE reacciones");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($columns as $col) {
        echo "Campo: " . $col['Field'] . "\n";
        echo "Tipo: " . $col['Type'] . "\n";
        echo "Nulo: " . $col['Null'] . "\n";
        echo "Clave: " . $col['Key'] . "\n";
        echo "Default: " . $col['Default'] . "\n";
        echo "Extra: " . $col['Extra'] . "\n";
        echo "-------------------\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>