<?php
require_once('app/models/config.php');

echo "<h2>Estructura de tabla REACCIONES:</h2>";
$stmt = $conexion->prepare('DESCRIBE reacciones');
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
foreach($cols as $col) {
    echo $col['Field'] . " | " . $col['Type'] . "\n";
}
echo "</pre>";

echo "<h2>Estructura de tabla COMENTARIOS:</h2>";
$stmt = $conexion->prepare('DESCRIBE comentarios');
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
foreach($cols as $col) {
    echo $col['Field'] . " | " . $col['Type'] . "\n";
}
echo "</pre>";

echo "<h2>Estructura de tabla AMIGOS:</h2>";
$stmt = $conexion->prepare('DESCRIBE amigos');
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
foreach($cols as $col) {
    echo $col['Field'] . " | " . $col['Type'] . "\n";
}
echo "</pre>";

echo "<h2>Estructura de tabla PUBLICACIONES:</h2>";
$stmt = $conexion->prepare('DESCRIBE publicaciones');
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
foreach($cols as $col) {
    echo $col['Field'] . " | " . $col['Type'] . "\n";
}
echo "</pre>";
?>
