<?php
require_once __DIR__.'/app/models/config.php';

try {
    // Verificar todas las tablas relacionadas con amistades
    $tables = ['amigos', 'seguidores'];
    
    foreach($tables as $table) {
        echo "=== Tabla '$table' ===\n";
        try {
            $stmt = $conexion->query("DESCRIBE $table");
            $columns = $stmt->fetchAll();
            
            foreach($columns as $column) {
                echo "- {$column['Field']} ({$column['Type']}) {$column['Key']} {$column['Null']} {$column['Default']}\n";
            }
        } catch(Exception $e) {
            echo "Error con tabla $table: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // También verificar datos de ejemplo
    echo "=== Datos de ejemplo en 'amigos' ===\n";
    try {
        $stmt = $conexion->query("SELECT * FROM amigos LIMIT 3");
        $rows = $stmt->fetchAll();
        foreach($rows as $row) {
            print_r($row);
        }
    } catch(Exception $e) {
        echo "Error obteniendo datos: " . $e->getMessage() . "\n";
    }
    
} catch(Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
}
?>