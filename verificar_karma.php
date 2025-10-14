<?php
/**
 * Script de verificación: Comprobar karma del usuario
 */

require_once(__DIR__.'/app/models/config.php');

$usuario_id = 15; // vane15

echo "=== VERIFICACIÓN DE KARMA ===\n\n";

// 1. Karma total
$stmt = $conexion->prepare("SELECT SUM(puntos) as total FROM karma_social WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "1. Karma Total: " . ($result['total'] ?? 0) . " puntos\n\n";

// 2. Últimas 5 acciones
$stmt = $conexion->prepare("
    SELECT tipo_accion, puntos, descripcion, fecha_accion 
    FROM karma_social 
    WHERE usuario_id = ? 
    ORDER BY fecha_accion DESC 
    LIMIT 5
");
$stmt->execute([$usuario_id]);
echo "2. Últimas 5 acciones:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "   - {$row['tipo_accion']}: {$row['puntos']} puntos - {$row['descripcion']} ({$row['fecha_accion']})\n";
}

// 3. Compras en tienda
$stmt = $conexion->prepare("
    SELECT * 
    FROM karma_social 
    WHERE usuario_id = ? AND tipo_accion = 'compra_tienda'
    ORDER BY fecha_accion DESC
");
$stmt->execute([$usuario_id]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\n3. Compras en tienda (" . count($compras) . "):\n";
if (count($compras) > 0) {
    foreach ($compras as $compra) {
        echo "   - {$compra['descripcion']}: {$compra['puntos']} puntos ({$compra['fecha_accion']})\n";
    }
} else {
    echo "   ¡NO HAY REGISTROS DE COMPRAS!\n";
    echo "   Esto significa que el descuento NO se está aplicando.\n";
}

// 4. Recompensas desbloqueadas
$stmt = $conexion->prepare("
    SELECT ur.*, kr.nombre, kr.karma_requerido
    FROM usuario_recompensas ur
    JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
    WHERE ur.usuario_id = ?
");
$stmt->execute([$usuario_id]);
echo "\n4. Recompensas desbloqueadas:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "   - {$row['nombre']} (Costo: {$row['karma_requerido']}) - Equipada: " . ($row['equipada'] ? 'Sí' : 'No') . "\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>
