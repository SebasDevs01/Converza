<?php
require_once '../app/models/config.php';

try {
    echo "Conectando a la base de datos...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS seguidores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seguidor_id INT NOT NULL,
        seguido_id INT NOT NULL,
        fecha_seguimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (seguidor_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        FOREIGN KEY (seguido_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        
        INDEX idx_seguidor (seguidor_id),
        INDEX idx_seguido (seguido_id),
        INDEX idx_seguimiento (seguidor_id, seguido_id),
        
        UNIQUE KEY unique_seguimiento (seguidor_id, seguido_id)
    )";
    
    $conexion->exec($sql);
    echo "✅ Tabla 'seguidores' creada exitosamente!\n";
    
    // Verificar que la tabla se creó
    $result = $conexion->query("SHOW TABLES LIKE 'seguidores'");
    if ($result->rowCount() > 0) {
        echo "✅ Tabla verificada correctamente\n";
        
        // Mostrar estructura
        $estructura = $conexion->query("DESCRIBE seguidores");
        echo "\n📋 Estructura de la tabla:\n";
        while ($row = $estructura->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']}: {$row['Type']} " . ($row['Null'] == 'NO' ? '(NOT NULL)' : '(NULL)') . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>