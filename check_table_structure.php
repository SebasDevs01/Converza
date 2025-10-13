<?php
require_once('app/models/config.php');

echo "<h2>🔍 Estructura de Tablas</h2>";

$tablas = ['amigos', 'seguidores', 'bloqueos'];

foreach ($tablas as $tabla) {
    try {
        $stmt = $conexion->prepare("DESCRIBE $tabla");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Tabla: <strong>$tabla</strong></h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        
        echo "</table><br>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error en tabla $tabla: {$e->getMessage()}</p>";
    }
}
?>
