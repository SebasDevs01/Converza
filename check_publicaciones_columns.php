<?php
require_once('app/models/config.php');

$stmt = $conexion->prepare('DESCRIBE publicaciones');
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Columnas de la tabla publicaciones:</h3>";
echo "<ul>";
foreach($cols as $col) {
    echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
}
echo "</ul>";
?>
