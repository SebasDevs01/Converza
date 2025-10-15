<?php
/**
 * Verificar estructura de BD y tablas de karma
 */

require_once(__DIR__ . '/app/models/config.php');

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Verificar BD</title>";
echo "<style>
body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
.section { background: #2a2a2a; padding: 15px; margin: 10px 0; border-left: 4px solid #0f0; }
.error { border-color: #f00; color: #f00; }
.success { border-color: #0f0; color: #0f0; }
.warning { border-color: #ff0; color: #ff0; }
h2 { margin: 0 0 10px 0; }
pre { background: #000; padding: 10px; overflow-x: auto; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #0f0; padding: 8px; text-align: left; }
th { background: #0a0; color: #000; }
</style></head><body>";

echo "<h1>🔍 VERIFICACIÓN DE BASE DE DATOS</h1>";

try {
    // 1. Listar todas las tablas
    echo "<div class='section'>";
    echo "<h2>1. TODAS LAS TABLAS EN LA BD</h2>";
    $tables = $conexion->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "<table>";
    echo "<tr><th>#</th><th>Tabla</th></tr>";
    $i = 1;
    foreach ($tables as $table) {
        echo "<tr><td>$i</td><td>$table</td></tr>";
        $i++;
    }
    echo "</table>";
    echo "<p class='success'>✅ Total de tablas: " . count($tables) . "</p>";
    echo "</div>";
    
    // 2. Buscar tablas con 'karma' en el nombre
    echo "<div class='section'>";
    echo "<h2>2. TABLAS CON 'KARMA' EN EL NOMBRE</h2>";
    $karmaTablesQuery = $conexion->query("SHOW TABLES LIKE '%karma%'");
    $karmaTables = $karmaTablesQuery->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($karmaTables) > 0) {
        foreach ($karmaTables as $kt) {
            echo "<h3>✅ Tabla: $kt</h3>";
            $cols = $conexion->query("DESCRIBE $kt")->fetchAll(PDO::FETCH_ASSOC);
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
            foreach ($cols as $col) {
                echo "<tr>";
                echo "<td>" . $col['Field'] . "</td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . $col['Null'] . "</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p class='warning'>⚠️ No se encontraron tablas con 'karma' en el nombre</p>";
    }
    echo "</div>";
    
    // 3. Buscar columnas 'karma' en todas las tablas
    echo "<div class='section'>";
    echo "<h2>3. COLUMNAS 'KARMA' EN TODAS LAS TABLAS</h2>";
    $found = false;
    echo "<table>";
    echo "<tr><th>Tabla</th><th>Campo</th><th>Tipo</th><th>Default</th></tr>";
    
    foreach ($tables as $table) {
        $cols = $conexion->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            if (stripos($col['Field'], 'karma') !== false) {
                $found = true;
                echo "<tr>";
                echo "<td><strong>$table</strong></td>";
                echo "<td>" . $col['Field'] . "</td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
        }
    }
    echo "</table>";
    
    if (!$found) {
        echo "<p class='error'>❌ NO SE ENCONTRÓ NINGUNA COLUMNA 'karma' EN LA BD</p>";
        echo "<p class='warning'>⚠️ Esto explica por qué el contador siempre muestra 0</p>";
    } else {
        echo "<p class='success'>✅ Se encontraron columnas 'karma'</p>";
    }
    echo "</div>";
    
    // 4. Verificar tabla usuarios específicamente
    echo "<div class='section'>";
    echo "<h2>4. ESTRUCTURA COMPLETA TABLA 'usuarios'</h2>";
    $userCols = $conexion->query("DESCRIBE usuarios")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th><th>Extra</th></tr>";
    
    $tieneKarma = false;
    foreach ($userCols as $col) {
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
        echo "<td>" . ($col['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($tieneKarma) {
        echo "<p class='success'>✅ La tabla 'usuarios' SÍ tiene columna 'karma'</p>";
    } else {
        echo "<p class='error'>❌ La tabla 'usuarios' NO tiene columna 'karma'</p>";
        echo "<h3>🔧 SOLUCIÓN:</h3>";
        echo "<pre style='color: #ff0;'>ALTER TABLE usuarios ADD COLUMN karma INT NOT NULL DEFAULT 0 AFTER tipo;</pre>";
        echo "<p>Ejecuta este SQL en phpMyAdmin para crear la columna.</p>";
    }
    echo "</div>";
    
    // 5. Si existe karma, mostrar estadísticas
    if ($tieneKarma) {
        echo "<div class='section success'>";
        echo "<h2>5. ESTADÍSTICAS DE KARMA</h2>";
        $stats = $conexion->query("
            SELECT 
                COUNT(*) as total_usuarios,
                MIN(karma) as karma_min,
                MAX(karma) as karma_max,
                AVG(karma) as karma_avg,
                SUM(karma) as karma_total
            FROM usuarios
        ")->fetch(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Métrica</th><th>Valor</th></tr>";
        echo "<tr><td>Total Usuarios</td><td>" . $stats['total_usuarios'] . "</td></tr>";
        echo "<tr><td>Karma Mínimo</td><td>" . $stats['karma_min'] . "</td></tr>";
        echo "<tr><td>Karma Máximo</td><td>" . $stats['karma_max'] . "</td></tr>";
        echo "<tr><td>Karma Promedio</td><td>" . number_format($stats['karma_avg'], 2) . "</td></tr>";
        echo "<tr><td>Karma Total</td><td>" . number_format($stats['karma_total']) . "</td></tr>";
        echo "</table>";
        
        echo "<h3>Top 10 usuarios con más karma:</h3>";
        $topUsers = $conexion->query("
            SELECT id_use, usuario, karma 
            FROM usuarios 
            ORDER BY karma DESC 
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Karma</th></tr>";
        foreach ($topUsers as $u) {
            echo "<tr>";
            echo "<td>" . $u['id_use'] . "</td>";
            echo "<td>" . htmlspecialchars($u['usuario']) . "</td>";
            echo "<td>" . number_format($u['karma']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ ERROR</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>
