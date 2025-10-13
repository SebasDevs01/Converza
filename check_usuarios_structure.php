<?php
require_once __DIR__.'/app/models/config.php';

echo "<h3>Estructura de la tabla 'usuarios':</h3>";

try {
    $stmt = $conexion->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verificar si existe la columna descripcion
    $hasDescripcion = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'descripcion') {
            $hasDescripcion = true;
            break;
        }
    }
    
    if ($hasDescripcion) {
        echo "<p style='color: green;'><strong>✅ La columna 'descripcion' existe</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ La columna 'descripcion' NO existe</strong></p>";
        echo "<p>Solución: Ejecutar el siguiente SQL para agregarla:</p>";
        echo "<pre>ALTER TABLE usuarios ADD COLUMN descripcion TEXT NULL AFTER sexo;</pre>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
