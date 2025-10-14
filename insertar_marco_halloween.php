<?php
require_once __DIR__ . '/app/models/config.php';

echo "\n🎃 INSERTAR MARCO DE HALLOWEEN EN LA BASE DE DATOS\n\n";

try {
    // Verificar si ya existe
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM karma_recompensas WHERE nombre = 'Marco Halloween'");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if ($existe > 0) {
        echo "⚠️  El marco de Halloween ya existe en la base de datos.\n";
        
        // Mostrar info
        $stmt = $conexion->prepare("SELECT * FROM karma_recompensas WHERE nombre = 'Marco Halloween'");
        $stmt->execute();
        $marco = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Información del marco:\n";
        echo "  ID: {$marco['id']}\n";
        echo "  Nombre: {$marco['nombre']}\n";
        echo "  Tipo: {$marco['tipo']}\n";
        echo "  Costo: {$marco['karma_requerido']} karma\n";
        echo "  Descripción: {$marco['descripcion']}\n\n";
        
    } else {
        // Insertar el marco de Halloween
        $sql = "INSERT INTO karma_recompensas (nombre, tipo, karma_requerido, descripcion, activo) 
                VALUES ('Marco Halloween', 'marco', 1500, 'Marco místico de Halloween con calabazas flotantes 🎃', TRUE)";
        
        $conexion->exec($sql);
        
        echo "✅ Marco de Halloween insertado correctamente!\n\n";
        echo "Detalles:\n";
        echo "  📛 Nombre: Marco Halloween\n";
        echo "  🎨 Tipo: marco\n";
        echo "  💎 Costo: 1500 karma\n";
        echo "  🎃 Ícono: 🎃\n";
        echo "  📝 Descripción: Marco místico de Halloween con calabazas flotantes\n\n";
        echo "🔧 Clase CSS: .marco-halloween\n";
        echo "🌈 Colores: Naranja (#ff6600) y Morado (#8b00ff)\n";
        echo "✨ Efectos: 3 capas animadas + calabazas flotantes\n\n";
    }
    
    // Mostrar todos los marcos disponibles
    echo "📋 MARCOS DISPONIBLES EN LA TIENDA:\n\n";
    $stmt = $conexion->query("SELECT nombre, karma_requerido FROM karma_recompensas WHERE tipo = 'marco' ORDER BY karma_requerido");
    $marcos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($marcos as $m) {
        echo "  🖼️  {$m['nombre']} - {$m['karma_requerido']} karma\n";
    }
    
    echo "\n✅ Proceso completado!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}
?>
