<?php
require_once __DIR__.'/app/models/config.php';

try {
    // Listar todas las tablas
    $stmt = $conexion->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tablas en la base de datos:\n";
    foreach($tables as $table) {
        echo "- $table\n";
    }
    
    // Buscar en presenters cómo se valida la amistad
    echo "\n=== Buscando referencias a amistad en otros archivos ===\n";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>