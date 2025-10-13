<?php
/**
 * INSTALADOR AUTOMÁTICO - CONEXIONES MÍSTICAS
 * Ejecuta este archivo UNA VEZ para instalar el sistema completo
 */

require_once(__DIR__ . '/app/models/config.php');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Instalador - Conexiones Místicas</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
        }
        h1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .step h3 {
            margin-top: 0;
            color: #667eea;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            opacity: 0.9;
        }
        pre {
            background: #2d3748;
            color: #68d391;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>🔮 Instalador de Conexiones Místicas</h1>";
echo "<p class='subtitle'>Instalación automática del sistema de serendipia digital</p>";

// PASO 1: Crear tabla
echo "<div class='step'>";
echo "<h3>Paso 1: Creando tabla en la base de datos</h3>";

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS conexiones_misticas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario1_id INT NOT NULL,
        usuario2_id INT NOT NULL,
        tipo_conexion VARCHAR(50) NOT NULL,
        descripcion TEXT,
        puntuacion INT DEFAULT 0,
        fecha_deteccion DATETIME DEFAULT CURRENT_TIMESTAMP,
        visto_usuario1 TINYINT(1) DEFAULT 0,
        visto_usuario2 TINYINT(1) DEFAULT 0,
        
        FOREIGN KEY (usuario1_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        FOREIGN KEY (usuario2_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        
        INDEX idx_usuario1 (usuario1_id),
        INDEX idx_usuario2 (usuario2_id),
        INDEX idx_tipo (tipo_conexion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";
    
    $conexion->exec($sql);
    echo "<p class='success'>✅ Tabla 'conexiones_misticas' creada correctamente</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al crear tabla: " . $e->getMessage() . "</p>";
}

echo "</div>";

// PASO 2: Detectar conexiones iniciales
echo "<div class='step'>";
echo "<h3>Paso 2: Detectando conexiones místicas iniciales</h3>";

try {
    require_once(__DIR__ . '/app/models/conexiones-misticas-helper.php');
    
    $motor = new ConexionesMisticas($conexion);
    
    echo "<p>🔍 Analizando gustos compartidos...</p>";
    echo "<p>💬 Analizando intereses comunes...</p>";
    echo "<p>👥 Analizando amigos de amigos...</p>";
    echo "<p>🕐 Analizando patrones de actividad...</p>";
    
    ob_start();
    $motor->detectarConexiones();
    $output = ob_get_clean();
    
    echo "<p class='success'>✅ Conexiones detectadas correctamente</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al detectar conexiones: " . $e->getMessage() . "</p>";
}

echo "</div>";

// PASO 3: Verificar archivos
echo "<div class='step'>";
echo "<h3>Paso 3: Verificando archivos del sistema</h3>";

$archivos = [
    'app/models/conexiones-misticas-helper.php' => 'Motor de análisis',
    'app/presenters/widget_conexiones_misticas.php' => 'Widget del feed',
    'app/presenters/conexiones_misticas.php' => 'Página completa',
    'detectar_conexiones.php' => 'Script ejecutable',
    'sql/create_conexiones_misticas.sql' => 'Archivo SQL'
];

foreach ($archivos as $archivo => $descripcion) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "<p>✅ <strong>$descripcion:</strong> $archivo</p>";
    } else {
        echo "<p class='error'>❌ <strong>$descripcion:</strong> $archivo (NO ENCONTRADO)</p>";
    }
}

echo "</div>";

// PASO 4: Instrucciones finales
echo "<div class='step'>";
echo "<h3>Paso 4: Integración con el sistema</h3>";
echo "<p><strong>Para mostrar el widget en el feed, agrega esta línea a <code>app/view/index.php</code>:</strong></p>";
echo "<pre>&lt;?php include __DIR__.'/../presenters/widget_conexiones_misticas.php'; ?&gt;</pre>";
echo "<p>Colócala ANTES de las publicaciones, aproximadamente en la línea 340.</p>";
echo "</div>";

// Resumen final
echo "<div class='step success'>";
echo "<h3>🎉 ¡Instalación Completada!</h3>";
echo "<p><strong>Sistema de Conexiones Místicas instalado correctamente.</strong></p>";
echo "<p>Puedes:</p>";
echo "<ul>";
echo "<li>Ver tus conexiones en: <a href='app/presenters/conexiones_misticas.php' target='_blank'>Página de Conexiones</a></li>";
echo "<li>Ejecutar análisis en: <a href='detectar_conexiones.php' target='_blank'>Detector de Conexiones</a></li>";
echo "<li>Leer documentación en: <code>CONEXIONES_MISTICAS_README.md</code></li>";
echo "</ul>";
echo "<a href='app/view/index.php' class='btn'>Ir al Feed Principal</a>";
echo "</div>";

echo "</div></body></html>";
?>
