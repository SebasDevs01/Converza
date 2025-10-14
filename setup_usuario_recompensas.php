<?php
/**
 * Script para crear la tabla usuario_recompensas
 * Ejecutar: php setup_usuario_recompensas.php
 */

require_once __DIR__.'/app/models/config.php';

try {
    echo "🔄 Creando tabla usuario_recompensas...\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS usuario_recompensas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        recompensa_id INT NOT NULL,
        equipada TINYINT(1) DEFAULT 0,
        fecha_desbloqueo DATETIME DEFAULT CURRENT_TIMESTAMP,
        
        -- Índices
        UNIQUE KEY unique_usuario_recompensa (usuario_id, recompensa_id),
        KEY idx_usuario (usuario_id),
        KEY idx_recompensa (recompensa_id),
        KEY idx_equipada (equipada),
        
        -- Relaciones
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        FOREIGN KEY (recompensa_id) REFERENCES karma_recompensas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conexion->exec($sql);
    
    echo "✅ Tabla usuario_recompensas creada exitosamente!\n";
    echo "✅ Columna 'equipada' agregada\n";
    echo "✅ Índices y foreign keys configurados\n\n";
    
    // Verificar estructura
    $stmt = $conexion->query("DESCRIBE usuario_recompensas");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Estructura de la tabla:\n";
    echo "┌─────────────────────┬──────────────┬──────────┐\n";
    echo "│ Campo               │ Tipo         │ Null     │\n";
    echo "├─────────────────────┼──────────────┼──────────┤\n";
    
    foreach ($columnas as $col) {
        printf("│ %-19s │ %-12s │ %-8s │\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null']
        );
    }
    echo "└─────────────────────┴──────────────┴──────────┘\n";
    
    echo "\n✅ Todo listo! Ahora puedes usar karma_tienda.php sin errores.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
