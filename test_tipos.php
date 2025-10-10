<?php
require_once 'app/models/config.php';

// Copiar la función aquí para testear
function testDeterminarTipo($contenido, $imagenes, $totalImagenes) {
    echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>";
    echo "<strong>Input:</strong><br>";
    echo "- Contenido: '" . htmlspecialchars($contenido) . "' (vacío: " . (empty(trim($contenido)) ? 'SÍ' : 'NO') . ")<br>";
    echo "- Imágenes: '" . htmlspecialchars($imagenes) . "' (vacío: " . (empty($imagenes) ? 'SÍ' : 'NO') . ")<br>";
    echo "- Total imágenes: " . $totalImagenes . " (>0: " . ($totalImagenes > 0 ? 'SÍ' : 'NO') . ")<br>";
    
    $tieneTexto = !empty(trim($contenido));
    $tieneImagenes = !empty($imagenes) && $totalImagenes > 0;
    
    echo "<br><strong>Evaluación:</strong><br>";
    echo "- Tiene texto: " . ($tieneTexto ? '✅ SÍ' : '❌ NO') . "<br>";
    echo "- Tiene imágenes: " . ($tieneImagenes ? '✅ SÍ' : '❌ NO') . "<br>";
    
    echo "<br><strong>Resultado:</strong> ";
    if ($tieneTexto && $tieneImagenes) {
        echo '<span style="background: blue; color: white; padding: 4px 8px;">Texto + Imagen</span>';
    } elseif ($tieneTexto && !$tieneImagenes) {
        echo '<span style="background: gray; color: white; padding: 4px 8px;">Solo Texto</span>';
    } elseif (!$tieneTexto && $tieneImagenes) {
        echo '<span style="background: green; color: white; padding: 4px 8px;">Solo Imagen</span>';
    } else {
        echo '<span style="background: lightgray; color: black; padding: 4px 8px;">Vacío</span>';
    }
    
    echo "</div>";
}

echo "<h2>🧪 Test de Función determinarTipoPublicacion</h2>";

echo "<h3>Casos de Prueba:</h3>";

// Caso 1: Solo texto
testDeterminarTipo("Hola mundo", "", 0);

// Caso 2: Solo imagen
testDeterminarTipo("", "imagen.jpg", 1);

// Caso 3: Texto + imagen
testDeterminarTipo("Hola mundo", "imagen.jpg", 1);

// Caso 4: Vacío completo
testDeterminarTipo("", "", 0);

// Caso 5: Texto vacío pero con espacios
testDeterminarTipo("   ", "", 0);

// Caso 6: Imagen con nombre pero total 0
testDeterminarTipo("", "imagen.jpg", 0);

echo "<h3>📊 Datos Reales de la Base de Datos:</h3>";

// Obtener datos reales
$stmt = $conexion->prepare("SELECT id_pub, usuario, contenido, imagen FROM publicaciones ORDER BY id_pub DESC LIMIT 3");
$stmt->execute();
$pubs = $stmt->fetchAll();

foreach ($pubs as $pub) {
    echo "<h4>Publicación ID: {$pub['id_pub']} - Usuario: {$pub['usuario']}</h4>";
    
    // Simular el procesamiento como en admin.php
    $imagenes = '';
    $totalImagenes = 0;
    
    // Método 1: Campo imagen
    if (!empty(trim($pub['imagen']))) {
        $imagenes = trim($pub['imagen']);
        $totalImagenes = 1;
    }
    
    // Método 2: Tabla imagenes_publicacion
    try {
        $stmtImg = $conexion->prepare("SELECT COUNT(*) FROM imagenes_publicacion WHERE publicacion_id = ? OR id_publicacion = ?");
        $stmtImg->execute([$pub['id_pub'], $pub['id_pub']]);
        $imgCount = $stmtImg->fetchColumn();
        
        if ($imgCount > 0) {
            $totalImagenes += $imgCount;
            echo "<small>Encontradas $imgCount imágenes adicionales en tabla separada</small><br>";
        }
    } catch (Exception $e) {
        echo "<small>No se pudo consultar tabla imagenes_publicacion: {$e->getMessage()}</small><br>";
    }
    
    testDeterminarTipo($pub['contenido'], $imagenes, $totalImagenes);
}
?>