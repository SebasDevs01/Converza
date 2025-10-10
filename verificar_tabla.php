<?php
require_once(__DIR__.'/app/models/config.php');

echo "🔍 Verificando estructura de la base de datos...\n\n";

try {
    // Verificar si la tabla imagenes_publicacion existe
    $stmt = $conexion->query("SHOW TABLES LIKE 'imagenes_publicacion'");
    $tabla_existe = $stmt->fetch();
    
    if (!$tabla_existe) {
        echo "❌ Tabla 'imagenes_publicacion' NO existe. Creando...\n";
        
        // Crear la tabla
        $sql_create = "
        CREATE TABLE IF NOT EXISTS imagenes_publicacion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            publicacion_id INT NOT NULL,
            nombre_imagen VARCHAR(255) NOT NULL,
            FOREIGN KEY (publicacion_id) REFERENCES publicaciones(id_pub) ON DELETE CASCADE
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