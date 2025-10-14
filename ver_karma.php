<?php
require_once('app/models/config.php');

echo "<h2>🌟 SISTEMA DE KARMA SOCIAL</h2>";
echo "<h3>📊 Dónde están guardados los puntos:</h3>";
echo "<p><strong>Tabla en MySQL:</strong> <code>karma_social</code></p>";

try {
    // Mostrar estructura de la tabla
    echo "<h4>🗂️ Estructura de la tabla karma_social:</h4>";
    echo "<pre>";
    $stmt = $conexion->query('DESCRIBE karma_social');
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columnas as $col) {
        echo $col['Field'] . " | " . $col['Type'] . "\n";
    }
    echo "</pre>";
    
    // Mostrar registros de karma
    echo "<h4>📝 Registros de Karma (últimos 10):</h4>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #4ade80; color: white;'>";
    echo "<th>ID</th><th>Usuario</th><th>Tipo Acción</th><th>Puntos</th><th>Fecha</th>";
    echo "</tr>";
    
    $stmt = $conexion->query('SELECT * FROM karma_social ORDER BY fecha_accion DESC LIMIT 10');
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($registros) > 0) {
        foreach($registros as $r) {
            echo "<tr>";
            echo "<td>" . ($r['id'] ?? 'N/A') . "</td>";
            echo "<td>" . ($r['usuario_id'] ?? 'N/A') . "</td>";
            echo "<td>" . ($r['tipo_accion'] ?? 'N/A') . "</td>";
            echo "<td><strong>" . ($r['puntos'] ?? 0) . "</strong></td>";
            echo "<td>" . ($r['fecha_accion'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5' style='text-align:center;'>⚠️ Aún no hay karma registrado</td></tr>";
    }
    echo "</table>";
    
    // Mostrar suma total por usuario
    echo "<br><h4>🏆 Total de Puntos por Usuario:</h4>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #fbbf24; color: white;'>";
    echo "<th>Usuario ID</th><th>Nombre</th><th>Total Karma</th><th>Nivel</th>";
    echo "</tr>";
    
    $stmt2 = $conexion->query('
        SELECT 
            k.usuario_id,
            u.usuario as nombre,
            SUM(k.puntos) as total
        FROM karma_social k
        INNER JOIN usuarios u ON k.usuario_id = u.id_use
        GROUP BY k.usuario_id
        ORDER BY total DESC
    ');
    $totales = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($totales) > 0) {
        foreach($totales as $t) {
            $karma = $t['total'];
            
            // Calcular nivel
            if ($karma >= 1000) {
                $nivel = "👑 Legendario";
            } elseif ($karma >= 500) {
                $nivel = "🌟 Maestro";
            } elseif ($karma >= 250) {
                $nivel = "💫 Experto";
            } elseif ($karma >= 100) {
                $nivel = "✨ Avanzado";
            } elseif ($karma >= 50) {
                $nivel = "⭐ Intermedio";
            } else {
                $nivel = "🌱 Novato";
            }
            
            echo "<tr>";
            echo "<td>" . $t['usuario_id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($t['nombre']) . "</strong></td>";
            echo "<td style='font-size: 18px; color: #059669;'><strong>" . $karma . " puntos</strong></td>";
            echo "<td>" . $nivel . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4' style='text-align:center;'>⚠️ No hay puntos acumulados aún</td></tr>";
    }
    echo "</table>";
    
    echo "<br><h4>💡 Explicación:</h4>";
    echo "<ul>";
    echo "<li>Cada vez que un usuario hace una <strong>acción positiva</strong>, se guarda en la tabla <code>karma_social</code></li>";
    echo "<li>La tabla tiene: <strong>id_usuario</strong>, <strong>tipo_accion</strong>, <strong>puntos_otorgados</strong>, <strong>fecha_accion</strong></li>";
    echo "<li>Los puntos se <strong>SUMAN automáticamente</strong> con SQL: <code>SUM(puntos_otorgados)</code></li>";
    echo "<li>El sistema calcula el nivel según el total de puntos</li>";
    echo "</ul>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
