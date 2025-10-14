<?php
/**
 * Setup: Sistema de Personalización de Perfil
 * Crea tablas para bio, signos, género, estado de ánimo y recompensas
 */

require_once('app/models/config.php');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup - Personalización de Perfil</title>
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
            max-width: 900px; 
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
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 10px; 
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 10px; 
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        .reward-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            border: 2px solid rgba(102, 126, 234, 0.2);
        }
        .reward-title {
            font-size: 1.2em;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .reward-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .reward-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .reward-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .karma-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1><i class='fas fa-user-edit'></i> Instalación: Personalización de Perfil</h1>";
echo "<p class='subtitle'>Sistema de gamificación y personalización de perfiles</p>";

try {
    // Leer y ejecutar el SQL
    $sql = file_get_contents('sql/create_personalizacion_perfil.sql');
    
    // Dividir en queries individuales
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $conexion->exec($query);
        }
    }
    
    echo "<div class='success'>";
    echo "<h2><i class='fas fa-check-circle'></i> Instalación Completada</h2>";
    echo "<p>✅ Tabla <code>personalizacion_perfil</code> creada</p>";
    echo "<p>✅ Tabla <code>karma_recompensas</code> creada</p>";
    echo "<p>✅ Tabla <code>usuario_recompensas</code> creada</p>";
    echo "<p>✅ 24 recompensas predefinidas insertadas</p>";
    echo "</div>";
    
    // Mostrar funcionalidades
    echo "<h2><i class='fas fa-star'></i> Funcionalidades Implementadas</h2>";
    
    echo "<div class='info-box'>";
    echo "<h3>📝 Personalización de Perfil:</h3>";
    echo "<ul>";
    echo "<li><strong>Bio personalizada:</strong> Descripción corta visible en el perfil</li>";
    echo "<li><strong>Signo zodiacal:</strong> 12 signos con iconos únicos</li>";
    echo "<li><strong>Género con íconos:</strong> ♂️ Masculino (azul), ♀️ Femenino (rosa), otros</li>";
    echo "<li><strong>Estado de ánimo:</strong> 12 estados emocionales actualizables</li>";
    echo "<li><strong>Privacidad:</strong> Control sobre qué mostrar públicamente</li>";
    echo "</ul>";
    echo "</div>";
    
    // Mostrar recompensas por categoría
    echo "<h2><i class='fas fa-gift'></i> Recompensas Desbloqueables</h2>";
    
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
        'color' => 'fa-fill-drip'
    ];
    
    $nombres_tipo = [
        'tema' => 'Temas de Perfil',
        'marco' => 'Marcos de Avatar',
        'insignia' => 'Insignias',
        'icono' => 'Íconos Especiales',
        'color' => 'Colores Premium'
    ];
    
    foreach ($recompensas_por_tipo as $tipo => $items) {
        echo "<div class='reward-card'>";
        echo "<div class='reward-title'><i class='fas {$iconos_tipo[$tipo]}'></i> {$nombres_tipo[$tipo]}</div>";
        echo "<div class='reward-grid'>";
        
        foreach ($items as $recompensa) {
            echo "<div class='reward-item'>";
            echo "<div class='reward-icon'><i class='fas {$iconos_tipo[$tipo]}'></i></div>";
            echo "<strong>{$recompensa['nombre']}</strong><br>";
            echo "<small>{$recompensa['descripcion']}</small><br><br>";
            echo "<span class='karma-badge'>{$recompensa['karma_requerido']} Karma</span>";
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    }
    
    // Signos zodiacales
    echo "<h2><i class='fas fa-moon'></i> Signos Zodiacales Disponibles</h2>";
    echo "<div class='info-box'>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;'>";
    $signos = [
        'aries' => '♈ Aries',
        'tauro' => '♉ Tauro',
        'geminis' => '♊ Géminis',
        'cancer' => '♋ Cáncer',
        'leo' => '♌ Leo',
        'virgo' => '♍ Virgo',
        'libra' => '♎ Libra',
        'escorpio' => '♏ Escorpio',
        'sagitario' => '♐ Sagitario',
        'capricornio' => '♑ Capricornio',
        'acuario' => '♒ Acuario',
        'piscis' => '♓ Piscis'
    ];
    foreach ($signos as $signo => $nombre) {
        echo "<div style='text-align: center; padding: 10px; background: white; border-radius: 8px;'>{$nombre}</div>";
    }
    echo "</div>";
    echo "</div>";
    
    // Estados de ánimo
    echo "<h2><i class='fas fa-smile'></i> Estados de Ánimo Disponibles</h2>";
    echo "<div class='info-box'>";
    $estados = [
        'feliz' => '😊', 'emocionado' => '🤩', 'relajado' => '😌', 'creativo' => '🎨',
        'cansado' => '😴', 'ocupado' => '⏰', 'triste' => '😢', 'enojado' => '😠',
        'motivado' => '💪', 'inspirado' => '✨', 'pensativo' => '🤔', 'nostalgico' => '🌅'
    ];
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;'>";
    foreach ($estados as $estado => $emoji) {
        echo "<div style='text-align: center; padding: 10px; background: white; border-radius: 8px;'>";
        echo "<span style='font-size: 2em;'>{$emoji}</span><br>";
        echo "<small>" . ucfirst($estado) . "</small>";
        echo "</div>";
    }
    echo "</div>";
    echo "</div>";
    
    echo "<h2><i class='fas fa-code'></i> Próximos Pasos</h2>";
    echo "<div class='info-box'>";
    echo "<ol>";
    echo "<li>Integrar el widget de notificación en el navbar</li>";
    echo "<li>Crear página de edición de perfil</li>";
    echo "<li>Implementar la tienda de recompensas</li>";
    echo "<li>Agregar el botón de karma al navbar</li>";
    echo "<li>Personalizar la vista de perfil con nuevos campos</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2><i class='fas fa-exclamation-triangle'></i> Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>
</body>
</html>";
?>
