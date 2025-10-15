<?php
/**
 * 🧪 TEST KARMA - Verificar estado de karma del usuario
 */

session_start();
require_once(__DIR__ . '/app/models/config.php');

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Karma</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
.section { background: #2a2a2a; padding: 15px; margin: 10px 0; border-left: 4px solid #0f0; }
.error { border-color: #f00; color: #f00; }
.success { border-color: #0f0; color: #0f0; }
.warning { border-color: #ff0; color: #ff0; }
h2 { margin: 0 0 10px 0; }
pre { background: #000; padding: 10px; overflow-x: auto; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #0f0; padding: 8px; text-align: left; }
th { background: #0a0; color: #000; }
</style></head><body>";

echo "<h1>🧪 TEST KARMA - DIAGNÓSTICO COMPLETO</h1>";

// 1. Verificar sesión
echo "<div class='section " . (isset($_SESSION['id']) ? 'success' : 'error') . "'>";
echo "<h2>1. SESIÓN</h2>";
if (isset($_SESSION['id'])) {
    echo "<p>✅ Usuario ID: " . $_SESSION['id'] . "</p>";
    echo "<p>✅ Nombre: " . ($_SESSION['usuario'] ?? 'N/A') . "</p>";
    $usuario_id = $_SESSION['id'];
} else {
    echo "<p>❌ No hay sesión activa</p>";
    echo "<p>Por favor inicia sesión primero.</p>";
    echo "</body></html>";
    exit;
}
echo "</div>";

// 2. Verificar conexión BD
echo "<div class='section " . (isset($conexion) ? 'success' : 'error') . "'>";
echo "<h2>2. BASE DE DATOS</h2>";
if (isset($conexion)) {
    echo "<p>✅ Conexión establecida</p>";
} else {
    echo "<p>❌ No hay conexión a la BD</p>";
    echo "</body></html>";
    exit;
}
echo "</div>";

// 3. Obtener karma actual del usuario
echo "<div class='section'>";
echo "<h2>3. KARMA ACTUAL DEL USUARIO</h2>";
try {
    $stmt = $conexion->prepare("SELECT id_use, usuario, karma, fecha_registro FROM usuarios WHERE id_use = ?");
    $stmt->execute([$usuario_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userData) {
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>ID</td><td>" . $userData['id_use'] . "</td></tr>";
        echo "<tr><td>Nombre</td><td>" . htmlspecialchars($userData['usuario']) . "</td></tr>";
        echo "<tr><td><strong>KARMA</strong></td><td><strong>" . $userData['karma'] . "</strong></td></tr>";
        echo "<tr><td>Fecha Registro</td><td>" . $userData['fecha_registro'] . "</td></tr>";
        echo "</table>";
        
        $karma_actual = intval($userData['karma']);
        
        if ($karma_actual == 0) {
            echo "<p class='warning'>⚠️ El usuario tiene 0 puntos de karma</p>";
        } else {
            echo "<p class='success'>✅ Usuario tiene karma positivo</p>";
        }
    } else {
        echo "<p class='error'>❌ Usuario no encontrado en BD</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 4. Verificar estructura de tabla usuarios
echo "<div class='section'>";
echo "<h2>4. ESTRUCTURA TABLA 'usuarios'</h2>";
try {
    $stmt = $conexion->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tieneKarma = false;
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        if ($col['Field'] == 'karma') {
            $tieneKarma = true;
            echo "<tr style='background: #0a0; color: #000;'>";
        } else {
            echo "<tr>";
        }
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($tieneKarma) {
        echo "<p class='success'>✅ Columna 'karma' existe</p>";
    } else {
        echo "<p class='error'>❌ Columna 'karma' NO existe - NECESITAS CREARLA</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 5. Test de UPDATE
echo "<div class='section'>";
echo "<h2>5. TEST DE UPDATE (SIMULACIÓN)</h2>";
echo "<p>Simulando: UPDATE usuarios SET karma = karma + 10 WHERE id_use = $usuario_id</p>";

try {
    // Obtener karma antes
    $stmt = $conexion->prepare("SELECT karma FROM usuarios WHERE id_use = ?");
    $stmt->execute([$usuario_id]);
    $antes = intval($stmt->fetchColumn());
    
    echo "<p>Karma ANTES: <strong>$antes</strong></p>";
    
    // Simular update
    $stmt = $conexion->prepare("UPDATE usuarios SET karma = karma + 10 WHERE id_use = ?");
    $resultado = $stmt->execute([$usuario_id]);
    $rowsAffected = $stmt->rowCount();
    
    // Obtener karma después
    $stmt = $conexion->prepare("SELECT karma FROM usuarios WHERE id_use = ?");
    $stmt->execute([$usuario_id]);
    $despues = intval($stmt->fetchColumn());
    
    echo "<p>Karma DESPUÉS: <strong>$despues</strong></p>";
    echo "<p>Diferencia: <strong>" . ($despues - $antes) . "</strong></p>";
    echo "<p>Rows affected: <strong>$rowsAffected</strong></p>";
    
    if ($resultado && $despues == ($antes + 10)) {
        echo "<p class='success'>✅ UPDATE funciona correctamente</p>";
        
        // Revertir cambio
        $stmt = $conexion->prepare("UPDATE usuarios SET karma = karma - 10 WHERE id_use = ?");
        $stmt->execute([$usuario_id]);
        echo "<p class='warning'>⚠️ Cambio revertido (karma vuelve a $antes)</p>";
    } else {
        echo "<p class='error'>❌ UPDATE no funcionó como esperado</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 6. Verificar reacciones recientes
echo "<div class='section'>";
echo "<h2>6. REACCIONES RECIENTES DEL USUARIO</h2>";
try {
    $stmt = $conexion->prepare("
        SELECT r.*, p.contenido 
        FROM reacciones r
        JOIN publicaciones p ON r.id_publicacion = p.id_pub
        WHERE r.id_usuario = ?
        ORDER BY r.fecha DESC
        LIMIT 10
    ");
    $stmt->execute([$usuario_id]);
    $reacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($reacciones) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Post</th><th>Tipo</th><th>Fecha</th></tr>";
        foreach ($reacciones as $r) {
            echo "<tr>";
            echo "<td>" . $r['id'] . "</td>";
            echo "<td>" . substr($r['contenido'], 0, 50) . "...</td>";
            echo "<td>" . $r['tipo_reaccion'] . "</td>";
            echo "<td>" . $r['fecha'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p class='success'>✅ Usuario tiene " . count($reacciones) . " reacciones recientes</p>";
    } else {
        echo "<p class='warning'>⚠️ Usuario no tiene reacciones recientes</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 7. Logs de errores recientes
echo "<div class='section'>";
echo "<h2>7. REVISAR LOGS</h2>";
echo "<p>Revisa el archivo de log de PHP para ver los mensajes de debug:</p>";
echo "<pre>tail -f " . ini_get('error_log') . " | grep 'KARMA DEBUG'</pre>";
echo "<p>O en Windows PowerShell:</p>";
echo "<pre>Get-Content '" . ini_get('error_log') . "' -Wait | Select-String 'KARMA DEBUG'</pre>";
echo "</div>";

echo "<div class='section success'>";
echo "<h2>✅ TEST COMPLETADO</h2>";
echo "<p>Ahora da una reacción en el feed y revisa los logs para ver el flujo completo.</p>";
echo "</div>";

echo "</body></html>";
?>
