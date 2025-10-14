<?php
/**
 * INSTALADOR DE COLORES DE NOMBRE ADICIONALES
 * Agrega 4 nuevos colores de nombre a la base de datos
 */

require_once __DIR__ . '/app/models/config.php';

echo "====================================================\n";
echo "   INSTALADOR DE COLORES DE NOMBRE ADICIONALES\n";
echo "====================================================\n\n";

try {
    // Usar la conexión PDO del config.php
    echo "✓ Conectado a la base de datos: converza\n\n";
    
    // Verificar si los colores ya existen
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM karma_recompensas WHERE nombre IN (?, ?, ?, ?)");
    $check_stmt->execute(['Púrpura Real', 'Rosa Neón', 'Esmeralda', 'Oro Premium']);
    $existing = $check_stmt->fetchColumn();
    
    if ($existing > 0) {
        echo "⚠️  Ya existen $existing colores de los 4 nuevos en la base de datos.\n";
        echo "¿Deseas eliminarlos y reinstalar? (s/n): ";
        $respuesta = trim(fgets(STDIN));
        
        if (strtolower($respuesta) === 's') {
            $pdo->exec("DELETE FROM karma_recompensas WHERE nombre IN ('Púrpura Real', 'Rosa Neón', 'Esmeralda', 'Oro Premium')");
            echo "✓ Colores anteriores eliminados.\n\n";
        } else {
            echo "❌ Instalación cancelada.\n";
            exit;
        }
    }
    
    // Insertar los 4 nuevos colores
    echo "📦 Instalando 4 nuevos colores de nombre...\n\n";
    
    $colores = [
        ['Púrpura Real', 'Color púrpura real #7C3AED', 60, 'color-purpura.png'],
        ['Rosa Neón', 'Rosa vibrante #EC4899', 80, 'color-rosa-neon.png'],
        ['Esmeralda', 'Verde esmeralda #10B981', 90, 'color-esmeralda.png'],
        ['Oro Premium', 'Dorado premium #F59E0B', 120, 'color-oro-premium.png']
    ];
    
    $insert_stmt = $pdo->prepare("
        INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) 
        VALUES ('color_nombre', ?, ?, ?, ?, 'colores')
    ");
    
    $count = 0;
    foreach ($colores as $color) {
        try {
            $insert_stmt->execute($color);
            $count++;
            echo "  ✓ {$color[0]} - {$color[2]} Karma\n";
        } catch (PDOException $e) {
            echo "  ❌ Error al instalar {$color[0]}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n====================================================\n";
    echo "  ✅ INSTALACIÓN COMPLETADA\n";
    echo "====================================================\n";
    echo "  • $count colores nuevos instalados\n";
    echo "  • Total de colores disponibles: " . ($count + 7) . "\n\n";
    
    // Mostrar todos los colores disponibles
    echo "📋 COLORES DE NOMBRE DISPONIBLES:\n\n";
    $colores_stmt = $pdo->query("SELECT nombre, costo_karma FROM karma_recompensas WHERE tipo = 'color_nombre' ORDER BY costo_karma ASC");
    $num = 1;
    while ($row = $colores_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  $num. {$row['nombre']} - {$row['costo_karma']} Karma\n";
        $num++;
    }
    
    echo "\n🎨 Los nuevos colores están listos para usar en:\n";
    echo "   • karma_tienda.php (vista previa con palabra 'NOMBRE')\n";
    echo "   • karma-recompensas.css (animaciones y gradientes)\n";
    echo "   • recompensas-aplicar-helper.php (mapeo de clases CSS)\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
