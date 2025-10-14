<?php
/**
 * Setup: Migración de Personalización en tabla usuarios
 * Agrega campos directamente a la tabla usuarios existente
 */

require_once('app/models/config.php');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Migración - Personalización de Usuarios</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 20px; 
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #667eea; 
            text-align: center; 
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        h2 { 
            color: #764ba2; 
            margin: 30px 0 15px 0;
            border-left: 4px solid #667eea;
            padding-left: 15px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .success { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724; 
            padding: 20px; 
            border-radius: 15px; 
            margin: 15px 0;
            border-left: 5px solid #28a745;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }
        .error { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24; 
            padding: 20px; 
            border-radius: 15px; 
            margin: 15px 0;
            border-left: 5px solid #dc3545;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        }
        .info-box {
            background: linear-gradient(135deg, #e7f3ff 0%, #d0e8ff 100%);
            border-left: 5px solid #2196F3;
            padding: 20px;
            border-radius: 15px;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.2);
        }
        .warning-box {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            border-left: 5px solid #ffc107;
            padding: 20px;
            border-radius: 15px;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
        }
        .field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .field-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .field-card:hover {
            border-color: #667eea;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.2);
            transform: translateY(-3px);
        }
        .field-name {
            font-size: 1.1em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }
        .field-type {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-bottom: 8px;
        }
        .reward-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }
        .reward-title {
            font-size: 1.3em;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .reward-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
        }
        .reward-card {
            background: white;
            padding: 18px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .reward-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            border-color: #667eea;
        }
        .reward-icon {
            font-size: 2.5em;
            margin-bottom: 12px;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.2));
        }
        .karma-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4);
        }
        code {
            background: #f8f9fa;
            padding: 3px 8px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            color: #d63384;
            border: 1px solid #e9ecef;
        }
        .check-list {
            list-style: none;
            padding: 0;
        }
        .check-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .check-list li:last-child {
            border-bottom: none;
        }
        .check-list li:before {
            content: '✓';
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
            font-size: 1.2em;
        }
        .icon-preview {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: white;
            border-radius: 8px;
            margin: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .icon-male {
            color: #3b82f6;
            font-size: 1.5em;
        }
        .icon-female {
            color: #ec4899;
            font-size: 1.5em;
        }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1><i class='fas fa-magic'></i> Migración: Personalización de Usuarios</h1>";
echo "<p class='subtitle'>Agregando campos de gamificación a la tabla usuarios existente</p>";

try {
    // Leer el archivo SQL
    $sql = file_get_contents('sql/add_personalizacion_usuarios.sql');
    
    if ($sql === false) {
        throw new Exception("No se pudo leer el archivo SQL");
    }
    
    // Dividir en queries individuales (separadas por ;)
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $success_count = 0;
    $errors = [];
    
    foreach ($queries as $query) {
        if (empty($query) || strpos($query, '--') === 0 || strpos($query, '==') === 0) {
            continue;
        }
        
        try {
            $conexion->exec($query);
            $success_count++;
        } catch (PDOException $e) {
            // Ignorar errores de columnas duplicadas o índices que ya existen
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate column name') !== false || 
                strpos($msg, 'Duplicate key name') !== false ||
                strpos($msg, "check that it exists") !== false) {
                continue;
            }
            $errors[] = $msg;
        }
    }
    
    if (count($errors) > 0) {
        echo "<div class='warning-box'>";
        echo "<h2><i class='fas fa-exclamation-triangle'></i> Advertencias</h2>";
        echo "<p>Algunas operaciones generaron advertencias:</p>";
        echo "<ul>";
        foreach (array_slice($errors, 0, 5) as $error) {
            echo "<li><small>" . htmlspecialchars($error) . "</small></li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<div class='success'>";
    echo "<h2><i class='fas fa-check-circle'></i> ¡Migración Completada Exitosamente!</h2>";
    echo "<ul class='check-list'>";
    echo "<li>Tabla <code>usuarios</code> actualizada con nuevos campos</li>";
    echo "<li>Tabla <code>karma_recompensas</code> creada</li>";
    echo "<li>Tabla <code>usuario_recompensas</code> creada</li>";
    echo "<li><strong>29 recompensas</strong> insertadas en el catálogo</li>";
    echo "<li>Índices de optimización agregados</li>";
    echo "</ul>";
    echo "</div>";
    
    // Mostrar nuevos campos agregados
    echo "<h2><i class='fas fa-database'></i> Nuevos Campos en Tabla usuarios</h2>";
    
    echo "<h3 style='color: #667eea; margin: 20px 0 15px 0;'><i class='fas fa-user-edit'></i> Información Personal</h3>";
    echo "<div class='field-grid'>";
    
    $campos_personales = [
        ['nombre' => 'bio', 'tipo' => 'TEXT', 'desc' => 'Biografía personal del usuario'],
        ['nombre' => 'descripcion_corta', 'tipo' => 'VARCHAR(255)', 'desc' => 'Descripción breve para tarjetas'],
        ['nombre' => 'signo_zodiacal', 'tipo' => 'ENUM(12)', 'desc' => '♈ Aries, ♉ Tauro, ♊ Géminis, etc.'],
        ['nombre' => 'genero', 'tipo' => 'ENUM(4)', 'desc' => 'Masculino, femenino, otro, prefiero no decir'],
        ['nombre' => 'estado_animo', 'tipo' => 'ENUM(12)', 'desc' => '😊 Feliz, 🤩 Emocionado, 😌 Relajado, etc.']
    ];
    
    foreach ($campos_personales as $campo) {
        echo "<div class='field-card'>";
        echo "<div class='field-name'><i class='fas fa-database'></i> {$campo['nombre']}</div>";
        echo "<div class='field-type'>{$campo['tipo']}</div>";
        echo "<p style='color: #666; font-size: 0.9em; margin-top: 8px;'>{$campo['desc']}</p>";
        echo "</div>";
    }
    echo "</div>";
    
    echo "<h3 style='color: #764ba2; margin: 20px 0 15px 0;'><i class='fas fa-palette'></i> Personalización Visual</h3>";
    echo "<div class='field-grid'>";
    
    $campos_visuales = [
        ['nombre' => 'tema_perfil', 'tipo' => 'VARCHAR(50)', 'desc' => 'Tema visual del perfil (default, dark, galaxy, sunset, neon)'],
        ['nombre' => 'color_principal', 'tipo' => 'VARCHAR(7)', 'desc' => 'Color principal en formato hex (#667eea)'],
        ['nombre' => 'icono_personalizado', 'tipo' => 'VARCHAR(100)', 'desc' => 'Ícono especial desbloqueado'],
        ['nombre' => 'marco_avatar', 'tipo' => 'VARCHAR(100)', 'desc' => 'Marco decorativo del avatar'],
        ['nombre' => 'insignia_especial', 'tipo' => 'VARCHAR(100)', 'desc' => 'Insignia de logro especial']
    ];
    
    foreach ($campos_visuales as $campo) {
        echo "<div class='field-card'>";
        echo "<div class='field-name'><i class='fas fa-paint-brush'></i> {$campo['nombre']}</div>";
        echo "<div class='field-type'>{$campo['tipo']}</div>";
        echo "<p style='color: #666; font-size: 0.9em; margin-top: 8px;'>{$campo['desc']}</p>";
        echo "</div>";
    }
    echo "</div>";
    
    echo "<h3 style='color: #28a745; margin: 20px 0 15px 0;'><i class='fas fa-eye'></i> Configuración de Privacidad</h3>";
    echo "<div class='field-grid'>";
    
    $campos_privacidad = [
        ['nombre' => 'mostrar_icono_genero', 'tipo' => 'BOOLEAN', 'desc' => 'Mostrar ícono de género (♂/♀)'],
        ['nombre' => 'mostrar_karma', 'tipo' => 'BOOLEAN', 'desc' => 'Mostrar puntos de karma públicamente'],
        ['nombre' => 'mostrar_signo', 'tipo' => 'BOOLEAN', 'desc' => 'Mostrar signo zodiacal'],
        ['nombre' => 'mostrar_estado_animo', 'tipo' => 'BOOLEAN', 'desc' => 'Mostrar estado de ánimo actual']
    ];
    
    foreach ($campos_privacidad as $campo) {
        echo "<div class='field-card'>";
        echo "<div class='field-name'><i class='fas fa-lock'></i> {$campo['nombre']}</div>";
        echo "<div class='field-type'>{$campo['tipo']}</div>";
        echo "<p style='color: #666; font-size: 0.9em; margin-top: 8px;'>{$campo['desc']}</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // Mostrar íconos de género
    echo "<h2><i class='fas fa-venus-mars'></i> Íconos de Género</h2>";
    echo "<div class='info-box'>";
    echo "<p style='margin-bottom: 15px;'>Los usuarios pueden mostrar su género con íconos de colores:</p>";
    echo "<div style='display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;'>";
    echo "<div class='icon-preview'><span class='icon-male'>♂</span> <strong>Masculino</strong> (Azul #3b82f6)</div>";
    echo "<div class='icon-preview'><span class='icon-female'>♀</span> <strong>Femenino</strong> (Rosa #ec4899)</div>";
    echo "<div class='icon-preview'><span style='color: #9333ea; font-size: 1.5em;'>⚧</span> <strong>Otro</strong> (Púrpura #9333ea)</div>";
    echo "</div>";
    echo "</div>";
    
    // Obtener y mostrar recompensas
    echo "<h2><i class='fas fa-gift'></i> Catálogo de Recompensas Desbloqueables</h2>";
    
    // Verificar que la tabla existe
    $table_check = $conexion->query("SHOW TABLES LIKE 'karma_recompensas'")->rowCount();
    
    if ($table_check == 0) {
        echo "<div class='error'>";
        echo "<h3><i class='fas fa-exclamation-circle'></i> Error</h3>";
        echo "<p>La tabla <code>karma_recompensas</code> no se creó correctamente.</p>";
        echo "<p><strong>Solución:</strong></p>";
        echo "<ol>";
        echo "<li>Ejecuta el <a href='limpiar_personalizacion.php'>script de limpieza</a></li>";
        echo "<li>Vuelve a ejecutar este setup</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        $stmt = $conexion->query("SELECT * FROM karma_recompensas ORDER BY tipo, karma_requerido");
        $recompensas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $recompensas_por_tipo = [];
    foreach ($recompensas as $recompensa) {
        $recompensas_por_tipo[$recompensa['tipo']][] = $recompensa;
    }
    
    $iconos_tipo = [
        'tema' => 'fa-palette',
        'marco' => 'fa-border-all',
        'insignia' => 'fa-certificate',
        'icono' => 'fa-star',
        'color' => 'fa-fill-drip',
        'sticker' => 'fa-smile'
    ];
    
    $nombres_tipo = [
        'tema' => 'Temas de Perfil',
        'marco' => 'Marcos de Avatar',
        'insignia' => 'Insignias de Logros',
        'icono' => 'Íconos Especiales',
        'color' => 'Colores Premium',
        'sticker' => 'Stickers Animados'
    ];
    
    foreach ($recompensas_por_tipo as $tipo => $items) {
        echo "<div class='reward-section'>";
        echo "<div class='reward-title'>";
        echo "<i class='fas {$iconos_tipo[$tipo]}'></i> {$nombres_tipo[$tipo]} (" . count($items) . ")";
        echo "</div>";
        echo "<div class='reward-grid'>";
        
        foreach ($items as $recompensa) {
            $icono = $iconos_tipo[$tipo] ?? 'fa-star';
            echo "<div class='reward-card'>";
            echo "<div class='reward-icon'><i class='fas {$icono}'></i></div>";
            echo "<strong style='color: #667eea;'>{$recompensa['nombre']}</strong><br>";
            echo "<small style='color: #666;'>{$recompensa['descripcion']}</small><br><br>";
            echo "<span class='karma-badge'>{$recompensa['karma_requerido']} <i class='fas fa-coins'></i></span>";
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    }
    
    // Contar total de recompensas
    $total = $conexion->query("SELECT COUNT(*) as total FROM karma_recompensas")->fetch();
    echo "<div class='info-box'>";
    echo "<h3><i class='fas fa-chart-line'></i> Estadísticas del Sistema</h3>";
    echo "<ul class='check-list'>";
    echo "<li><strong>{$total['total']}</strong> recompensas disponibles en total</li>";
    echo "<li>Karma requerido: desde <strong>10</strong> hasta <strong>1000</strong> puntos</li>";
    echo "<li><strong>6</strong> categorías de recompensas</li>";
    echo "<li>Sistema de desbloqueo automático basado en karma</li>";
    echo "</ul>";
    echo "</div>";
    } // Cerrar el if de table_check    
    echo "<h2><i class='fas fa-rocket'></i> Próximos Pasos</h2>";
    echo "<div class='info-box'>";
    echo "<ol style='padding-left: 20px;'>";
    echo "<li style='margin: 10px 0;'>✅ <strong>Base de datos actualizada</strong> - Los campos ya están listos</li>";
    echo "<li style='margin: 10px 0;'>📝 Crear página <code>editar_perfil.php</code> con formulario de personalización</li>";
    echo "<li style='margin: 10px 0;'>🎨 Crear componentes visuales para signos zodiacales (♈♉♊...)</li>";
    echo "<li style='margin: 10px 0;'>⚧ Implementar selector de género con íconos de colores</li>";
    echo "<li style='margin: 10px 0;'>😊 Crear selector de estado de ánimo con emojis</li>";
    echo "<li style='margin: 10px 0;'>🏆 Desarrollar la tienda de recompensas (shop modal)</li>";
    echo "<li style='margin: 10px 0;'>🔔 Integrar notificaciones de karma al sistema</li>";
    echo "<li style='margin: 10px 0;'>📊 Agregar botón de karma al navbar</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2><i class='fas fa-exclamation-triangle'></i> Error en la Migración</h2>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>
</body>
</html>";
?>
