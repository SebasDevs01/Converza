<?php
// Script para crear la tabla de reacciones
require_once(__DIR__.'/../app/models/config.php');

try {
    // Crear tabla reacciones
    $sql = "
    CREATE TABLE IF NOT EXISTS reacciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_publicacion INT NOT NULL,
        tipo_reaccion ENUM('like', 'love', 'laugh', 'wow', 'sad', 'angry') NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_pub) ON DELETE CASCADE,
        UNIQUE KEY unique_user_post_reaction (id_usuario, id_publicacion)
    )";
    
    $conexion->exec($sql);
    echo "✅ Tabla 'reacciones' creada exitosamente.\n";
    
    // Crear índices
    $indices = [
        "CREATE INDEX IF NOT EXISTS idx_reacciones_publicacion ON reacciones(id_publicacion)",
        "CREATE INDEX IF NOT EXISTS idx_reacciones_usuario ON reacciones(id_usuario)", 
        "CREATE INDEX IF NOT EXISTS idx_reacciones_tipo ON reacciones(tipo_reaccion)"
    ];
    
    foreach ($indices as $indice) {
        $conexion->exec($indice);
    }
    
    echo "✅ Índices creados exitosamente.\n";
    echo "✅ Sistema de reacciones listo para usar!\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>