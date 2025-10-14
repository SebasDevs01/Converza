<?php
/**
 * Script para verificar y actualizar karma del usuario testingtienda
 */

require_once __DIR__ . '/app/models/config.php';

$usuario = "testingtienda";

try {
    // Obtener ID del usuario
    $stmt_user = $pdo->prepare("SELECT id_use, nombre, email FROM usuarios WHERE usuario = ?");
    $stmt_user->execute([$usuario]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Usuario no encontrado\n";
        exit(1);
    }
    
    $id_usuario = $user['id_use'];
    
    // Obtener karma actual
    $stmt_karma = $pdo->prepare("
        SELECT COALESCE(SUM(puntos), 0) as total_karma 
        FROM karma_social 
        WHERE usuario_id = ?
    ");
    $stmt_karma->execute([$id_usuario]);
    $karma_actual = $stmt_karma->fetch(PDO::FETCH_ASSOC)['total_karma'];
    
    echo "═══════════════════════════════════════════\n";
    echo "👤 Usuario: {$user['nombre']} (@{$usuario})\n";
    echo "💎 Karma actual: " . number_format($karma_actual) . " puntos\n";
    echo "═══════════════════════════════════════════\n\n";
    
    // Si tiene menos de 50,000, agregar la diferencia
    if ($karma_actual < 50000) {
        $puntos_necesarios = 50000 - $karma_actual;
        
        $stmt_add = $pdo->prepare("
            INSERT INTO karma_social 
            (usuario_id, tipo_accion, puntos, descripcion, fecha_accion) 
            VALUES (?, 'regalo_admin', ?, 'Recarga de puntos para pruebas de tienda', NOW())
        ");
        
        $stmt_add->execute([$id_usuario, $puntos_necesarios]);
        
        echo "✅ Se agregaron " . number_format($puntos_necesarios) . " puntos\n";
        echo "💎 Nuevo total: 50,000 puntos\n\n";
    } else {
        echo "✅ El usuario ya tiene suficientes puntos\n\n";
    }
    
    echo "═══════════════════════════════════════════\n";
    echo "🎉 CREDENCIALES DEL USUARIO DE PRUEBA\n";
    echo "═══════════════════════════════════════════\n\n";
    echo "👤 Usuario: testingtienda\n";
    echo "🔑 Contraseña: Testing2025!\n";
    echo "💎 Karma disponible: 50,000 puntos\n";
    echo "📧 Email: {$user['email']}\n";
    echo "👥 Nombre: {$user['nombre']}\n\n";
    echo "═══════════════════════════════════════════\n";
    echo "📍 URL de Login: http://localhost/Converza/\n";
    echo "🛍️ URL Tienda: http://localhost/Converza/karma_tienda.php\n";
    echo "═══════════════════════════════════════════\n\n";
    echo "💡 Recompensas disponibles para probar:\n";
    echo "   ✨ 5 Marcos de perfil (50-200 karma)\n";
    echo "   🎨 9 Temas personalizados (100-500 karma)\n";
    echo "   🏆 6 Insignias (10-200 karma)\n";
    echo "   ⭐ 6 Iconos especiales (50-150 karma)\n";
    echo "   🌈 9 Colores de nombre (30-100 karma)\n";
    echo "   🎁 3 Packs de stickers (100-300 karma)\n";
    echo "   📊 Total: 29 recompensas únicas\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
