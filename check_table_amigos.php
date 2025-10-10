<?php
require_once __DIR__.'/app/models/config.php';

try {
    // Verificar estructura de tabla amigos
    $stmt = $conexion->query("DESCRIBE amigos");
    $columns = $stmt->fetchAll();
    
    echo "Estructura de la tabla 'amigos':\n";
    foreach($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Key']}\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>