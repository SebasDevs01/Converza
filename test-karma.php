<?php
/**
 * 🧪 TEST DE KARMA SOCIAL
 * Verifica que el sistema de karma funciona correctamente
 */

session_start();

// Verificar sesión
if (!isset($_SESSION['id'])) {
    die("❌ Debes estar logueado para probar el sistema de karma");
}

require_once __DIR__ . '/app/models/config.php';
require_once __DIR__ . '/app/models/karma-social-helper.php';
require_once __DIR__ . '/app/models/karma-social-triggers.php';

echo "<h1>🧪 Test de Karma Social</h1>";
echo "<hr>";

$usuario_id = $_SESSION['id'];
$usuario_nombre = $_SESSION['nombre'] ?? 'Usuario';

echo "<h2>👤 Usuario: {$usuario_nombre} (ID: {$usuario_id})</h2>";

// 1. VERIFICAR TABLA
echo "<h3>1️⃣ Verificar tabla karma_social</h3>";
try {
    $stmt = $conexion->query("DESCRIBE karma_social");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Tabla karma_social existe<br>";
    echo "Columnas: " . implode(', ', $columns) . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<strong>¡La tabla karma_social NO existe! Debes crearla primero.</strong><br>";
    exit;
}

echo "<hr>";

// 2. VER KARMA ACTUAL
echo "<h3>2️⃣ Karma actual del usuario</h3>";
try {
    $karmaHelper = new KarmaSocialHelper($conexion);
    $karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
    
    echo "<pre>";
    print_r($karmaData);
    echo "</pre>";
    
    echo "<strong>Karma Total: {$karmaData['karma_total']} puntos</strong><br>";
    echo "<strong>Nivel: {$karmaData['nivel_data']['nivel']} - {$karmaData['nivel_data']['titulo']} {$karmaData['nivel_emoji']}</strong><br>";
} catch (Exception $e) {
    echo "❌ Error obteniendo karma: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 3. REGISTRAR KARMA DE PRUEBA
echo "<h3>3️⃣ Test: Registrar +5 puntos de karma</h3>";
try {
    $karmaHelper = new KarmaSocialHelper($conexion);
    
    // Intentar registrar una acción de prueba
    $resultado = $karmaHelper->registrarReaccionPositiva(
        $usuario_id,
        999, // ID de publicación ficticia
        'me_gusta' // Debe dar +5 puntos
    );
    
    if ($resultado) {
        echo "✅ Karma registrado correctamente<br>";
        
        // Ver karma actualizado
        $karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
        echo "<strong>Nuevo karma: {$karmaData['karma_total']} puntos</strong><br>";
    } else {
        echo "❌ No se pudo registrar el karma<br>";
    }
} catch (Exception $e) {
    echo "❌ Error registrando karma: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// 4. VER ÚLTIMAS 10 ACCIONES
echo "<h3>4️⃣ Últimas 10 acciones de karma</h3>";
try {
    $stmt = $conexion->prepare("
        SELECT * FROM karma_social 
        WHERE usuario_id = ? 
        ORDER BY fecha_registro DESC 
        LIMIT 10
    ");
    $stmt->execute([$usuario_id]);
    $acciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($acciones) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Tipo Acción</th>";
        echo "<th>Puntos</th>";
        echo "<th>Descripción</th>";
        echo "<th>Fecha</th>";
        echo "</tr>";
        
        foreach ($acciones as $accion) {
            $color = $accion['puntos'] > 0 ? 'green' : ($accion['puntos'] < 0 ? 'red' : 'gray');
            echo "<tr>";
            echo "<td>{$accion['id']}</td>";
            echo "<td>{$accion['tipo_accion']}</td>";
            echo "<td style='color: {$color}; font-weight: bold;'>{$accion['puntos']}</td>";
            echo "<td>{$accion['descripcion']}</td>";
            echo "<td>{$accion['fecha_registro']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "⚠️ No tienes acciones de karma registradas<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 5. VERIFICAR LOGS
echo "<h3>5️⃣ Logs de PHP</h3>";
$log_path = 'C:\xampp\php\logs\php_error_log';
if (file_exists($log_path)) {
    $logs = file_get_contents($log_path);
    $ultimas_lineas = array_slice(explode("\n", $logs), -50);
    
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: auto;'>";
    foreach ($ultimas_lineas as $linea) {
        if (strpos($linea, 'KARMA') !== false || strpos($linea, '🎯') !== false) {
            echo htmlspecialchars($linea) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "⚠️ No se encontraron logs en: {$log_path}<br>";
}

echo "<hr>";
echo "<p><a href='/Converza'>← Volver a Converza</a></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 { color: #3b82f6; }
    h2 { color: #2563eb; }
    h3 { color: #1e40af; margin-top: 20px; }
    hr { margin: 20px 0; border: 1px solid #ddd; }
    pre { background: white; padding: 10px; border-radius: 5px; }
    table { background: white; width: 100%; }
    th { background: #3b82f6; color: white; }
</style>
