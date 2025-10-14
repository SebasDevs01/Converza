<?php
/**
 * Script para crear usuario de prueba para la Tienda de Karma
 * Usuario: testingtienda
 * Contraseña: Testing2025!
 * Karma inicial: 50,000 puntos
 */

require_once __DIR__ . '/app/models/config.php';

// Datos del usuario de prueba
$nombre = "Testing Tienda";
$email = "testingtienda@converza.test";
$usuario = "testingtienda";
$contrasena = "Testing2025!";
$sexo = "otro";
$avatar = "defect.jpg";

// Hash de la contraseña
$contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

try {
    // Verificar si el usuario ya existe
    $stmt_check = $pdo->prepare("SELECT id_use FROM usuarios WHERE usuario = ? OR email = ?");
    $stmt_check->execute([$usuario, $email]);
    
    if ($stmt_check->fetch()) {
        echo "❌ El usuario 'testingtienda' ya existe.\n";
        echo "\n📋 CREDENCIALES EXISTENTES:\n";
        echo "Usuario: testingtienda\n";
        echo "Contraseña: Testing2025!\n";
        echo "\n🔄 Si quieres recrearlo, primero elimínalo manualmente desde phpMyAdmin.\n";
        exit;
    }
    
    // Insertar usuario
    $stmt_user = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, usuario, contrasena, sexo, avatar, tipo, verificado) 
        VALUES (?, ?, ?, ?, ?, ?, 'user', 1)
    ");
    
    $stmt_user->execute([
        $nombre,
        $email,
        $usuario,
        $contrasena_hash,
        $sexo,
        $avatar
    ]);
    
    $id_usuario = $pdo->lastInsertId();
    
    echo "✅ Usuario creado exitosamente (ID: $id_usuario)\n\n";
    
    // Insertar 50,000 puntos de karma inicial
    $stmt_karma = $pdo->prepare("
        INSERT INTO karma_social 
        (usuario_id, tipo_accion, puntos, descripcion, fecha_accion) 
        VALUES (?, 'regalo_admin', 50000, 'Puntos iniciales para pruebas de tienda', NOW())
    ");
    
    $stmt_karma->execute([$id_usuario]);
    
    echo "✅ Asignados 50,000 puntos de karma\n\n";
    
    // Verificar karma total
    $stmt_total = $pdo->prepare("
        SELECT COALESCE(SUM(puntos), 0) as total_karma 
        FROM karma_social 
        WHERE usuario_id = ?
    ");
    $stmt_total->execute([$id_usuario]);
    $karma_total = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_karma'];
    
    echo "═══════════════════════════════════════════\n";
    echo "🎉 USUARIO DE PRUEBA CREADO EXITOSAMENTE\n";
    echo "═══════════════════════════════════════════\n\n";
    echo "👤 Usuario: testingtienda\n";
    echo "🔑 Contraseña: Testing2025!\n";
    echo "💎 Karma disponible: " . number_format($karma_total) . " puntos\n";
    echo "📧 Email: testingtienda@converza.test\n";
    echo "👥 Nombre: Testing Tienda\n";
    echo "✓ Verificado: Sí\n\n";
    echo "═══════════════════════════════════════════\n";
    echo "📍 URL de Login: http://localhost/Converza/\n";
    echo "🛍️ URL Tienda: http://localhost/Converza/karma_tienda.php\n";
    echo "═══════════════════════════════════════════\n\n";
    echo "💡 Puedes comprar todas las recompensas para probar:\n";
    echo "   • Marcos de perfil (50-200 karma)\n";
    echo "   • Temas personalizados (100-500 karma)\n";
    echo "   • Insignias (10-200 karma)\n";
    echo "   • Iconos especiales (50-150 karma)\n";
    echo "   • Colores de nombre (30-100 karma)\n";
    echo "   • Packs de stickers (100-300 karma)\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error al crear usuario: " . $e->getMessage() . "\n";
    exit(1);
}
