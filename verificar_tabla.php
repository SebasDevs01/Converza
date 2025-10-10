<?php
require_once(__DIR__.'/app/models/config.php');

try {
    echo "Estructura de la tabla 'chats':\n";
    $stmt = $conexion->query("DESCRIBE chats");
    $columns = $stmt->fetchAll();
    
    foreach($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) Key:{$column['Key']} Null:{$column['Null']}\n";
    }
        )";
        
        $conexion->exec($sql_create);
        echo "✅ Tabla 'imagenes_publicacion' creada correctamente.\n";
    } else {
        echo "✅ Tabla 'imagenes_publicacion' existe.\n";
        
        // Verificar estructura
        echo "\n📋 Estructura de la tabla:\n";
        $stmt = $conexion->query("DESCRIBE imagenes_publicacion");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']} ({$row['Type']})\n";
        }
    }
    
    // Verificar también la tabla comentarios
    echo "\n📋 Estructura de tabla comentarios:\n";
    $stmt = $conexion->query("DESCRIBE comentarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Verificación completada.\n";
?>