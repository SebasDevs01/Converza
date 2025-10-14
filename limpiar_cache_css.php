<?php
/**
 * LIMPIADOR DE CACHE CSS
 * Este script fuerza al navegador a recargar el CSS eliminando el cache
 */

// Headers anti-cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Ruta del archivo CSS
$cssFile = __DIR__ . '/public/css/karma-recompensas.css';
$cssUrl = '/Converza/public/css/karma-recompensas.css';

if (file_exists($cssFile)) {
    $lastModified = filemtime($cssFile);
    $version = date('YmdHis', $lastModified);
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>✅ Caché Limpiado</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .container {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 500px;
            }
            h1 { color: #667eea; margin-bottom: 20px; }
            .info { background: #f0f4ff; padding: 15px; border-radius: 10px; margin: 20px 0; }
            .code { font-family: 'Courier New', monospace; background: #1e1e1e; color: #4ec9b0; padding: 10px; border-radius: 5px; margin: 10px 0; overflow-x: auto; }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-decoration: none;
                border-radius: 25px;
                font-weight: 600;
                margin: 10px 5px;
                transition: transform 0.2s;
            }
            .btn:hover { transform: translateY(-2px); }
        </style>
        <link rel='stylesheet' href='{$cssUrl}?v={$version}'>
    </head>
    <body>
        <div class='container'>
            <h1>✅ Caché CSS Limpiado</h1>
            <div class='info'>
                <p><strong>Archivo:</strong> karma-recompensas.css</p>
                <p><strong>Versión:</strong> 2.0</p>
                <p><strong>Última modificación:</strong> " . date('d/m/Y H:i:s', $lastModified) . "</p>
            </div>
            <div class='code'>
                {$cssUrl}?v={$version}
            </div>
            <p style='color: #666; margin: 20px 0;'>
                ✨ El CSS se ha recargado con la nueva versión.<br>
                Todos los marcos deberían aparecer correctamente ahora.
            </p>
            <a href='/Converza' class='btn'>🏠 Ir al inicio</a>
            <a href='/Converza/perfil' class='btn'>👤 Ver mi perfil</a>
            <a href='/Converza/karma_tienda' class='btn'>🏆 Tienda de Karma</a>
        </div>
    </body>
    </html>";
} else {
    echo "❌ Error: No se encontró el archivo CSS";
}
?>
