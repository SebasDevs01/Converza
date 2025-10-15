<!DOCTYPE html>
<html>
<head>
    <title>Test Widget - Converza</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🧪 Test de Widget del Asistente</h1>
    
    <h2>Verificación de Archivos:</h2>
    <?php
    // Rutas a verificar
    $checks = [
        'Widget HTML' => __DIR__ . '/app/microservices/converza-assistant/widget/assistant-widget.html',
        'Widget PHP' => __DIR__ . '/app/microservices/converza-assistant/widget/assistant-widget.php',
        'Widget CSS' => __DIR__ . '/app/microservices/converza-assistant/widget/assistant-widget.css',
        'Widget JS' => __DIR__ . '/app/microservices/converza-assistant/widget/assistant-widget.js',
        'Index (desde app/view)' => __DIR__ . '/app/microservices/converza-assistant/widget/assistant-widget.php',
        'Perfil (desde app/presenters)' => __DIR__ . '/app/microservices/converza-assistant/widget/assistant-widget.php',
    ];
    
    foreach ($checks as $name => $path) {
        $exists = file_exists($path);
        $class = $exists ? 'success' : 'error';
        $status = $exists ? '✓ EXISTE' : '✗ NO EXISTE';
        echo "<p class='$class'>[$status] $name</p>";
        echo "<pre>$path</pre>";
    }
    ?>
    
    <h2>Test de Inclusión:</h2>
    <?php
    echo "<p>Intentando incluir el widget...</p>";
    
    $widget_path = __DIR__ . '/app/microservices/converza-assistant/widget/assistant-widget.php';
    
    if (file_exists($widget_path)) {
        echo "<p class='success'>✓ Archivo encontrado, incluyendo...</p>";
        
        ob_start();
        require_once($widget_path);
        $output = ob_get_clean();
        
        if (!empty($output)) {
            echo "<p class='success'>✓ Widget incluido correctamente</p>";
            echo "<p>El botón flotante debería aparecer abajo a la derecha →</p>";
        } else {
            echo "<p class='error'>✗ Widget no produjo output</p>";
        }
    } else {
        echo "<p class='error'>✗ Archivo no encontrado en: $widget_path</p>";
    }
    ?>
    
    <h2>Instrucciones:</h2>
    <ol>
        <li>Si ves el botón flotante ✨ abajo a la derecha: <strong class="success">TODO FUNCIONA</strong></li>
        <li>Si NO ves el botón:
            <ul>
                <li>Presiona F12 para abrir la consola</li>
                <li>Busca errores en rojo</li>
                <li>Verifica que los archivos CSS y JS se carguen</li>
            </ul>
        </li>
        <li>Luego prueba en:
            <ul>
                <li><a href="/converza">http://localhost/converza</a> (index)</li>
                <li>Página de perfil</li>
                <li>Página de álbumes</li>
            </ul>
        </li>
    </ol>
    
    <hr>
    <p><small>Archivo: test-widget.php | Fecha: <?php echo date('Y-m-d H:i:s'); ?></small></p>
</body>
</html>
