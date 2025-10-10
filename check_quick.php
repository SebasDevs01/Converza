<?php
require_once 'app/models/config.php';
header('Content-Type: text/plain; charset=utf-8');

echo "=== VERIFICACIÓN RÁPIDA DE TABLAS ===\n\n";

// 1. Verificar publicaciones
echo "1. TABLA PUBLICACIONES:\n";
try {
    $stmt = $conexion->query("DESCRIBE publicaciones");
    while ($row = $stmt->fetch()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\n2. TABLA IMAGENES_PUBLICACION:\n";
try {
    $stmt = $conexion->query("DESCRIBE imagenes_publicacion");
    while ($row = $stmt->fetch()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    // Contar registros
    $stmt = $conexion->query("SELECT COUNT(*) FROM imagenes_publicacion");
    $count = $stmt->fetchColumn();
    echo "  REGISTROS: $count\n";
    
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. OTRAS TABLAS CON 'IMAGEN':\n";
try {
    $stmt = $conexion->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        if (stripos($row[0], 'imagen') !== false) {
            echo "  - {$row[0]}\n";
        }
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. PUBLICACIONES CON IMÁGENES (primeras 3):\n";
try {
    $stmt = $conexion->query("SELECT id_pub, usuario, imagen FROM publicaciones WHERE imagen IS NOT NULL AND imagen != '' LIMIT 3");
    while ($row = $stmt->fetch()) {
        echo "  - ID:{$row['id_pub']} Usuario:{$row['usuario']} Imagen:{$row['imagen']}\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
?>