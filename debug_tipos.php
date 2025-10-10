<?php
require_once 'app/models/config.php';
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Debug de Publicaciones - Tipos</h2>";

// Obtener publicaciones recientes para debug
$stmtPubs = $conexion->prepare("SELECT p.*, u.usuario FROM publicaciones p JOIN usuarios u ON p.usuario = u.id_use ORDER BY p.id_pub DESC LIMIT 5");
$stmtPubs->execute();
$publicaciones = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>📋 Datos RAW de Publicaciones:</h3>";

foreach ($publicaciones as $pub) {
    echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 8px;'>";
    echo "<h4>ID: {$pub['id_pub']} - Usuario: {$pub['usuario']}</h4>";
    
    // Mostrar todos los campos
    echo "<strong>Campos de la publicación:</strong><br>";
    foreach ($pub as $campo => $valor) {
        $valorMostrar = is_null($valor) ? '<em>NULL</em>' : 
                       (empty($valor) ? '<em>VACÍO</em>' : 
                       htmlspecialchars($valor));
        echo "- <strong>$campo:</strong> $valorMostrar<br>";
    }
    
    echo "<br><strong>🔍 Análisis de Tipo:</strong><br>";
    
    // Análisis manual
    $contenido = $pub['contenido'] ?? '';
    $imagen = $pub['imagen'] ?? '';
    
    echo "- Contenido vacío: " . (empty(trim($contenido)) ? '❌ SÍ' : '✅ NO') . "<br>";
    echo "- Campo imagen vacío: " . (empty($imagen) ? '❌ SÍ' : '✅ NO') . "<br>";
    echo "- Longitud contenido: " . strlen($contenido) . "<br>";
    echo "- Longitud imagen: " . strlen($imagen) . "<br>";
    
    // Verificar imágenes en tabla separada
    try {
        $stmtImg = $conexion->prepare("SELECT COUNT(*) FROM imagenes_publicacion WHERE publicacion_id = ? OR id_publicacion = ?");
        $stmtImg->execute([$pub['id_pub'], $pub['id_pub']]);
        $imgCount = $stmtImg->fetchColumn();
        echo "- Imágenes en tabla separada: $imgCount<br>";
        
        if ($imgCount > 0) {
            echo "- <strong>Archivos encontrados:</strong><br>";
            $stmtNames = $conexion->prepare("SELECT * FROM imagenes_publicacion WHERE publicacion_id = ? OR id_publicacion = ? LIMIT 3");
            $stmtNames->execute([$pub['id_pub'], $pub['id_pub']]);
            $archivos = $stmtNames->fetchAll();
            
            foreach ($archivos as $archivo) {
                echo "  · " . json_encode($archivo) . "<br>";
            }
        }
    } catch (Exception $e) {
        echo "- Error tabla imágenes: " . $e->getMessage() . "<br>";
    }
    
    // Simular la función determinarTipoPublicacion
    echo "<br><strong>🎯 Simulación de Tipo:</strong><br>";
    
    $tieneTexto = !empty(trim($contenido));
    $tieneImagenCampo = !empty($imagen);
    
    echo "- Tiene texto: " . ($tieneTexto ? '✅ SÍ' : '❌ NO') . "<br>";
    echo "- Tiene imagen (campo): " . ($tieneImagenCampo ? '✅ SÍ' : '❌ NO') . "<br>";
    
    // Determinar tipo esperado
    if ($tieneTexto && $tieneImagenCampo) {
        echo "- <strong>Tipo esperado:</strong> <span style='background: blue; color: white; padding: 2px 8px; border-radius: 4px;'>Texto + Imagen</span><br>";
    } elseif ($tieneTexto && !$tieneImagenCampo) {
        echo "- <strong>Tipo esperado:</strong> <span style='background: gray; color: white; padding: 2px 8px; border-radius: 4px;'>Solo Texto</span><br>";
    } elseif (!$tieneTexto && $tieneImagenCampo) {
        echo "- <strong>Tipo esperado:</strong> <span style='background: green; color: white; padding: 2px 8px; border-radius: 4px;'>Solo Imagen</span><br>";
    } else {
        echo "- <strong>Tipo esperado:</strong> <span style='background: lightgray; color: black; padding: 2px 8px; border-radius: 4px;'>Vacío</span><br>";
    }
    
    echo "</div>";
}

echo "<h3>🔧 Verificar Estructura de Tabla imagenes_publicacion:</h3>";
try {
    $stmt = $conexion->query("DESCRIBE imagenes_publicacion");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($col = $stmt->fetch()) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ Tabla imagenes_publicacion no existe: " . $e->getMessage();
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; }
    th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
    th { background: #f0f0f0; }
</style>