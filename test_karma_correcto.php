<?php
/**
 * Test del sistema de karma con tablas correctas
 * Ubicación: /Converza/test_karma_correcto.php
 */

require_once(__DIR__ . '/app/models/config.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistema Karma Correcto</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            font-size: 1.5em;
        }
        .success {
            background: #d4edda;
            border-left: 5px solid #28a745;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-left: 5px solid #17a2b8;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .stat-card {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin: 10px;
            min-width: 200px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }
        .stat-card p {
            opacity: 0.9;
            font-size: 0.9em;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #ddd;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin: 2px;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-info { background: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔥 Test Sistema de Karma Correcto</h1>

<?php
try {
    // ========================================
    // 1. VERIFICAR TABLAS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>1. 📊 Verificar Tablas de Karma</h2>";
    
    $tablasRequeridas = ['karma_social', 'karma_total_usuarios', 'usuarios_con_karma'];
    $tablasExisten = [];
    
    foreach ($tablasRequeridas as $tabla) {
        try {
            $result = $conexion->query("SELECT 1 FROM $tabla LIMIT 1");
            $tablasExisten[$tabla] = true;
            echo "<div class='success'>✅ Tabla <strong>$tabla</strong> existe</div>";
        } catch (PDOException $e) {
            $tablasExisten[$tabla] = false;
            echo "<div class='error'>❌ Tabla <strong>$tabla</strong> NO existe</div>";
        }
    }
    echo "</div>";
    
    // ========================================
    // 2. VERIFICAR TRIGGER
    // ========================================
    echo "<div class='section'>";
    echo "<h2>2. ⚙️ Verificar Trigger</h2>";
    
    $triggerQuery = $conexion->query("SHOW TRIGGERS LIKE 'karma_social'");
    $triggers = $triggerQuery->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($triggers) > 0) {
        echo "<div class='success'>";
        echo "✅ <strong>Trigger encontrado:</strong><br>";
        foreach ($triggers as $trigger) {
            echo "<div class='badge badge-success'>" . $trigger['Trigger'] . "</div> ";
            echo "<div class='badge badge-info'>" . $trigger['Event'] . "</div> ";
            echo "<div class='badge badge-info'>" . $trigger['Timing'] . "</div>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>❌ No se encontró el trigger <strong>after_karma_social_insert</strong></div>";
        echo "<div class='warning'>⚠️ El trigger es necesario para auto-actualizar karma_total_usuarios</div>";
    }
    echo "</div>";
    
    // ========================================
    // 3. ESTRUCTURA DE TABLAS
    // ========================================
    echo "<div class='section'>";
    echo "<h2>3. 🏗️ Estructura de Tablas</h2>";
    
    // karma_social
    if ($tablasExisten['karma_social']) {
        echo "<h3>Tabla: karma_social</h3>";
        $cols = $conexion->query("DESCRIBE karma_social")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
        foreach ($cols as $col) {
            echo "<tr>";
            echo "<td><strong>" . $col['Field'] . "</strong></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // karma_total_usuarios
    if ($tablasExisten['karma_total_usuarios']) {
        echo "<h3>Tabla: karma_total_usuarios</h3>";
        $cols = $conexion->query("DESCRIBE karma_total_usuarios")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
        foreach ($cols as $col) {
            echo "<tr>";
            echo "<td><strong>" . $col['Field'] . "</strong></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // ========================================
    // 4. ESTADÍSTICAS GENERALES
    // ========================================
    echo "<div class='section'>";
    echo "<h2>4. 📈 Estadísticas del Sistema</h2>";
    
    if ($tablasExisten['karma_social'] && $tablasExisten['karma_total_usuarios']) {
        // Total usuarios con karma
        $totalUsuariosKarma = $conexion->query("SELECT COUNT(*) FROM karma_total_usuarios")->fetchColumn();
        
        // Total acciones registradas
        $totalAcciones = $conexion->query("SELECT COUNT(*) FROM karma_social")->fetchColumn();
        
        // Karma total acumulado
        $karmaAcumulado = $conexion->query("SELECT SUM(COALESCE(karma_total, 0)) FROM karma_total_usuarios")->fetchColumn();
        
        // Usuarios totales
        $totalUsuarios = $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        
        echo "<div style='text-align: center;'>";
        echo "<div class='stat-card'><h3>$totalUsuarios</h3><p>Usuarios Totales</p></div>";
        echo "<div class='stat-card'><h3>$totalUsuariosKarma</h3><p>Con Karma Registrado</p></div>";
        echo "<div class='stat-card'><h3>$totalAcciones</h3><p>Acciones Totales</p></div>";
        echo "<div class='stat-card'><h3>" . number_format($karmaAcumulado) . "</h3><p>Karma Acumulado</p></div>";
        echo "</div>";
        
        // Top 10 usuarios
        echo "<h3>🏆 Top 10 Usuarios con Más Karma</h3>";
        $topUsers = $conexion->query("
            SELECT 
                u.id_use,
                u.usuario,
                u.nombre,
                kt.karma_total,
                kt.acciones_totales,
                kt.ultima_accion
            FROM usuarios u
            JOIN karma_total_usuarios kt ON u.id_use = kt.usuario_id
            ORDER BY kt.karma_total DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($topUsers) > 0) {
            echo "<table>";
            echo "<tr><th>#</th><th>Usuario</th><th>Nombre</th><th>Karma</th><th>Acciones</th><th>Última Acción</th></tr>";
            $pos = 1;
            foreach ($topUsers as $user) {
                echo "<tr>";
                echo "<td><strong>$pos</strong></td>";
                echo "<td>@" . htmlspecialchars($user['usuario']) . "</td>";
                echo "<td>" . htmlspecialchars($user['nombre']) . "</td>";
                echo "<td><span class='badge badge-success'>" . number_format($user['karma_total']) . " pts</span></td>";
                echo "<td>" . number_format($user['acciones_totales']) . "</td>";
                echo "<td>" . ($user['ultima_accion'] ?? 'N/A') . "</td>";
                echo "</tr>";
                $pos++;
            }
            echo "</table>";
        } else {
            echo "<div class='info'>ℹ️ No hay usuarios con karma registrado aún</div>";
        }
        
        // Últimas 10 acciones
        echo "<h3>📝 Últimas 10 Acciones de Karma</h3>";
        $ultimasAcciones = $conexion->query("
            SELECT 
                ks.id,
                u.usuario,
                ks.tipo_accion,
                ks.puntos,
                ks.descripcion,
                ks.fecha_accion
            FROM karma_social ks
            JOIN usuarios u ON ks.usuario_id = u.id_use
            ORDER BY ks.fecha_accion DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($ultimasAcciones) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Usuario</th><th>Acción</th><th>Puntos</th><th>Descripción</th><th>Fecha</th></tr>";
            foreach ($ultimasAcciones as $accion) {
                $badgeClass = $accion['puntos'] > 0 ? 'badge-success' : 'badge-danger';
                echo "<tr>";
                echo "<td>" . $accion['id'] . "</td>";
                echo "<td>@" . htmlspecialchars($accion['usuario']) . "</td>";
                echo "<td><span class='badge badge-info'>" . htmlspecialchars($accion['tipo_accion']) . "</span></td>";
                echo "<td><span class='badge $badgeClass'>" . ($accion['puntos'] > 0 ? '+' : '') . $accion['puntos'] . "</span></td>";
                echo "<td>" . htmlspecialchars($accion['descripcion'] ?? '') . "</td>";
                echo "<td>" . $accion['fecha_accion'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='info'>ℹ️ No hay acciones registradas aún</div>";
        }
    }
    echo "</div>";
    
    // ========================================
    // 5. SIMULACIÓN DE INSERCIÓN
    // ========================================
    echo "<div class='section'>";
    echo "<h2>5. 🧪 Simulación de Inserción</h2>";
    
    if ($tablasExisten['karma_social'] && $tablasExisten['karma_total_usuarios']) {
        echo "<div class='info'>";
        echo "<strong>💡 Código para registrar karma:</strong>";
        echo "<pre>";
        echo '$stmtInsertKarma = $conexion->prepare("
    INSERT INTO karma_social 
    (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion, fecha_accion)
    VALUES 
    (:usuario_id, :tipo_accion, :puntos, :referencia_id, :referencia_tipo, :descripcion, NOW())
");

$resultado = $stmtInsertKarma->execute([
    \':usuario_id\' => $id_usuario,
    \':tipo_accion\' => \'reaccion_me_gusta\',
    \':puntos\' => 5,
    \':referencia_id\' => $id_publicacion,
    \':referencia_tipo\' => \'publicacion\',
    \':descripcion\' => \'👍 Me gusta\'
]);';
        echo "</pre>";
        echo "<p>✅ El trigger <strong>after_karma_social_insert</strong> se encargará automáticamente de actualizar <strong>karma_total_usuarios</strong></p>";
        echo "</div>";
    }
    echo "</div>";
    
    // ========================================
    // 6. DIAGNÓSTICO FINAL
    // ========================================
    echo "<div class='section'>";
    echo "<h2>6. ✅ Diagnóstico Final</h2>";
    
    $problemas = [];
    $exitoso = [];
    
    if (!$tablasExisten['karma_social']) {
        $problemas[] = "Tabla karma_social no existe";
    } else {
        $exitoso[] = "Tabla karma_social configurada";
    }
    
    if (!$tablasExisten['karma_total_usuarios']) {
        $problemas[] = "Tabla karma_total_usuarios no existe";
    } else {
        $exitoso[] = "Tabla karma_total_usuarios configurada";
    }
    
    if (count($triggers) === 0) {
        $problemas[] = "Trigger after_karma_social_insert no existe";
    } else {
        $exitoso[] = "Trigger after_karma_social_insert configurado";
    }
    
    if (count($problemas) > 0) {
        echo "<div class='error'>";
        echo "<h3>❌ Problemas Encontrados:</h3>";
        echo "<ul>";
        foreach ($problemas as $problema) {
            echo "<li>$problema</li>";
        }
        echo "</ul>";
        echo "<p><strong>Solución:</strong> Ejecuta el archivo <code>sql/configurar_sistema_karma.sql</code> en phpMyAdmin</p>";
        echo "</div>";
    }
    
    if (count($exitoso) > 0) {
        echo "<div class='success'>";
        echo "<h3>✅ Configuración Correcta:</h3>";
        echo "<ul>";
        foreach ($exitoso as $item) {
            echo "<li>$item</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    if (count($problemas) === 0) {
        echo "<div class='success' style='padding: 30px; text-align: center; font-size: 1.2em;'>";
        echo "<h3 style='color: #28a745; font-size: 2em; margin-bottom: 15px;'>🎉 ¡SISTEMA COMPLETAMENTE FUNCIONAL!</h3>";
        echo "<p>Todas las tablas y triggers están configurados correctamente.</p>";
        echo "<p>El sistema de karma está listo para usar.</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<div class='error'>";
    echo "<h3>❌ Error Fatal</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    echo "</div>";
}
?>

    </div>
</body>
</html>
