<?php
require_once(__DIR__.'/app/models/config.php');

// Verificar estructura de la tabla reacciones
echo "<h2>Estructura de la tabla reacciones:</h2>";
try {
    $stmt = $conexion->query("DESCRIBE reacciones");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Verificar si existen reacciones para la publicación 154
echo "<h2>Reacciones para publicación 154:</h2>";
try {
    $stmt = $conexion->prepare("SELECT * FROM reacciones WHERE id_publicacion = 154");
    $stmt->execute();
    $reacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($reacciones);
    echo "</pre>";
    
    if (empty($reacciones)) {
        echo "<p>No hay reacciones para esta publicación.</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Verificar todas las publicaciones disponibles
echo "<h2>Últimas 5 publicaciones:</h2>";
try {
    $stmt = $conexion->query("SELECT id_pub, usuario, contenido FROM publicaciones ORDER BY id_pub DESC LIMIT 5");
    $pubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($pubs);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>