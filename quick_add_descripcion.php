<?php
/**
 * Script RÁPIDO para agregar columna descripcion
 * Solo ejecuta y listo
 */
require_once __DIR__.'/app/models/config.php';

try {
    // Verificar si ya existe
    $check = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'descripcion'");
    
    if ($check->rowCount() > 0) {
        echo "✅ La columna 'descripcion' ya existe!<br>";
    } else {
        // Agregar la columna
        $conexion->exec("
            ALTER TABLE usuarios 
            ADD COLUMN descripcion TEXT NULL 
            AFTER sexo
        ");
        echo "✅ Columna 'descripcion' agregada exitosamente!<br>";
        
        // Agregar descripciones predeterminadas
        $conexion->exec("
            UPDATE usuarios 
            SET descripcion = CONCAT('¡Hola! Soy ', nombre, ' 👋')
            WHERE descripcion IS NULL OR descripcion = ''
        ");
        echo "✅ Descripciones predeterminadas agregadas!<br>";
    }
    
    echo "<br><a href='app/view/index.php'>Ir a Converza</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
