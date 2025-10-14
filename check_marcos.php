<?php
require_once __DIR__ . '/app/models/config.php';
require_once __DIR__ . '/app/models/recompensas-aplicar-helper.php';

echo "\n🌈 VERIFICACIÓN RÁPIDA: MARCOS DE PERFIL\n\n";

try {
    // Verificar marcos
    $stmt = $conexion->query("SELECT nombre FROM karma_recompensas WHERE tipo = 'marco'");
    $marcos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Marcos en BD:\n";
    foreach ($marcos as $nombre) {
        echo "  - $nombre\n";
    }
    
    // Verificar testingtienda
    echo "\nUsuario testingtienda:\n";
    $stmt = $conexion->query("SELECT id_use FROM usuarios WHERE usuario = 'testingtienda'");
    $user = $stmt->fetch();
    echo "  ID: {$user['id_use']}\n";
    
    // Karma total
    $stmt = $conexion->prepare("SELECT SUM(puntos) as total FROM karma_social WHERE usuario_id = ?");
    $stmt->execute([$user['id_use']]);
    $karma = $stmt->fetchColumn() ?: 0;
    echo "  Karma: $karma\n";
    
    // Verificar marco equipado
    $helper = new RecompensasAplicarHelper($conexion);
    $marcoClase = $helper->getMarcoClase($user['id_use']);
    
    echo "\nMarco equipado:\n";
    if ($marcoClase) {
        echo "  ✅ Clase CSS: $marcoClase\n";
    } else {
        echo "  ⚠️ Sin marco equipado\n";
    }
    
    echo "\n✅ Verificación completada\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}
?>
