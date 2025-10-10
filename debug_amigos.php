<?php
require_once __DIR__.'/app/models/config.php';

try {
    // Verificar estructura de tabla amigos
    $stmt = $conexion->query("DESCRIBE amigos");
    $columns = $stmt->fetchAll();
    
    echo "Estructura de la tabla 'amigos':\n";
    foreach($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) Key:{$column['Key']} Null:{$column['Null']} Default:{$column['Default']}\n";
    }
    
    echo "\nEjemplo de datos:\n";
    $stmt = $conexion->query("SELECT * FROM amigos LIMIT 3");
    $rows = $stmt->fetchAll();
    foreach($rows as $row) {
        print_r($row);
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>