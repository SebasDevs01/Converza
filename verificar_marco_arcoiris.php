<?php
/**
 * VERIFICAR MARCO ARCOÍRIS
 * Script para confirmar que el Marco Arcoíris se está aplicando correctamente
 */

require_once __DIR__ . '/app/models/config.php';
require_once __DIR__ . '/app/models/recompensas-aplicar-helper.php';

echo "\n";
echo "═══════════════════════════════════════════\n";
echo "🌈 VERIFICACIÓN: MARCO ARCOÍRIS\n";
echo "═══════════════════════════════════════════\n\n";

// 1. Verificar recompensas de tipo 'marco' en la tabla
echo "📋 PASO 1: Recompensas tipo 'marco' en BD:\n";
echo "───────────────────────────────────────────\n";
$stmt = $conexion->query("SELECT id, nombre, tipo, costo FROM karma_recompensas WHERE tipo = 'marco' ORDER BY costo");
$marcos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($marcos)) {
    echo "❌ ERROR: No hay marcos en la base de datos\n";
    exit;
}

foreach ($marcos as $marco) {
    echo "  ID: {$marco['id']}\n";
    echo "  Nombre: '{$marco['nombre']}'\n";
    echo "  Costo: {$marco['costo']} karma\n";
    echo "  Tipo: {$marco['tipo']}\n";
    echo "  ✅ Marco registrado\n";
    echo "\n";
}

// 2. Verificar usuario testingtienda
echo "\n📋 PASO 2: Usuario testingtienda:\n";
echo "───────────────────────────────────────────\n";
$stmt = $conexion->prepare("SELECT id_use, usuario, karma FROM usuarios WHERE usuario = 'testingtienda'");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "❌ ERROR: Usuario testingtienda no encontrado\n";
    exit;
}

echo "  ID: {$user['id_use']}\n";
echo "  Usuario: @{$user['usuario']}\n";
echo "  💎 Karma: {$user['karma']} puntos\n\n";

// 3. Verificar recompensas equipadas del usuario
echo "\n📋 PASO 3: Recompensas equipadas:\n";
echo "───────────────────────────────────────────\n";
$stmt = $conexion->prepare("
    SELECT kr.nombre, kr.tipo, ur.equipada
    FROM usuario_recompensas ur
    JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
    WHERE ur.usuario_id = ? AND ur.equipada = 1
");
$stmt->execute([$user['id_use']]);
$equipadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($equipadas)) {
    echo "⚠️ El usuario NO tiene recompensas equipadas\n";
} else {
    foreach ($equipadas as $rec) {
        echo "  ✅ {$rec['tipo']}: {$rec['nombre']}\n";
    }
}

// 4. Verificar marco específico del usuario
echo "\n\n📋 PASO 4: Marco activo del usuario:\n";
echo "───────────────────────────────────────────\n";
$helper = new RecompensasAplicarHelper($conexion);
$marcoClase = $helper->getMarcoClase($user['id_use']);

if ($marcoClase) {
    echo "  ✅ Clase CSS aplicada: '{$marcoClase}'\n";
    
    // Verificar que el CSS existe
    $cssFile = __DIR__ . '/public/css/karma-recompensas.css';
    $cssContent = file_get_contents($cssFile);
    
    if (strpos($cssContent, ".{$marcoClase}") !== false) {
        echo "  ✅ Estilos CSS encontrados en karma-recompensas.css\n";
        
        // Mostrar fragmento del CSS
        preg_match("/\.{$marcoClase}\s*\{[^}]+\}/", $cssContent, $matches);
        if (!empty($matches)) {
            echo "\n  📄 Fragmento CSS:\n";
            echo "  " . str_replace("\n", "\n  ", trim($matches[0])) . "\n";
        }
    } else {
        echo "  ❌ Estilos CSS NO encontrados en karma-recompensas.css\n";
    }
} else {
    echo "  ⚠️ NO tiene marco equipado\n";
}

// 5. Instrucciones para equipar Marco Arcoíris
echo "\n\n═══════════════════════════════════════════\n";
echo "🎯 INSTRUCCIONES PARA PROBAR\n";
echo "═══════════════════════════════════════════\n\n";

echo "1. Ir a: http://localhost/Converza/karma_tienda.php\n";
echo "2. Login como: testingtienda / Testing2025!\n";
echo "3. Buscar 'Marco Arcoíris' (100 karma)\n";
echo "4. Click en 'Desbloquear' y luego 'Equipar'\n";
echo "5. Ir al perfil: http://localhost/Converza/perfil.php?id={$user['id_use']}\n";
echo "6. El avatar debe tener borde arcoíris 🌈 animado\n\n";

// 6. Verificar que Marco Arcoíris existe
echo "\n═══════════════════════════════════════════\n";
echo "🌈 VERIFICACIÓN ESPECÍFICA: Marco Arcoíris\n";
echo "═══════════════════════════════════════════\n\n";

$stmt = $conexion->prepare("
    SELECT * FROM karma_recompensas 
    WHERE nombre LIKE '%Arcoíris%' OR nombre LIKE '%Arcoiris%'
");
$stmt->execute();
$arcoiris = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($arcoiris)) {
    echo "❌ ERROR: Marco Arcoíris NO existe en la base de datos\n";
    echo "\n💡 SOLUCIÓN: Ejecutar setup_karma_store.php\n\n";
} else {
    foreach ($arcoiris as $marco) {
        echo "✅ Encontrado:\n";
        echo "  ID: {$marco['id']}\n";
        echo "  Nombre: '{$marco['nombre']}'\n";
        echo "  Descripción: {$marco['descripcion']}\n";
        echo "  Costo: {$marco['costo']} karma\n";
        echo "  Tipo: {$marco['tipo']}\n\n";
        
        // Verificar si el usuario lo tiene desbloqueado
        $stmt2 = $conexion->prepare("
            SELECT equipada FROM usuario_recompensas 
            WHERE usuario_id = ? AND recompensa_id = ?
        ");
        $stmt2->execute([$user['id_use'], $marco['id']]);
        $desbloqueado = $stmt2->fetch();
        
        if ($desbloqueado) {
            if ($desbloqueado['equipada'] == 1) {
                echo "  🎊 EQUIPADO por testingtienda\n";
            } else {
                echo "  🔓 Desbloqueado pero NO equipado\n";
            }
        } else {
            echo "  🔒 NO desbloqueado por testingtienda\n";
            echo "  💡 Karma disponible: {$user['karma']} (necesita {$marco['costo']})\n";
        }
    }
}

echo "\n═══════════════════════════════════════════\n";
echo "✅ Verificación completada\n";
echo "═══════════════════════════════════════════\n\n";
?>
