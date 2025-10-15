<?php
/**
 * 🤖 HEALTH CHECK - Verificar estado del microservicio
 */

header('Content-Type: application/json; charset=utf-8');

$health = [
    'status' => 'healthy',
    'service' => 'converza-assistant',
    'version' => '1.0.0',
    'timestamp' => date('c'),
    'checks' => []
];

// Check 1: Archivos de engine
$engineFiles = [
    'IntentClassifier.php',
    'ResponseGenerator.php',
    'ContextManager.php'
];

$enginePath = __DIR__.'/../engine/';
$engineOk = true;

foreach ($engineFiles as $file) {
    if (!file_exists($enginePath . $file)) {
        $engineOk = false;
        break;
    }
}

$health['checks']['engine'] = $engineOk ? 'ok' : 'error';

// Check 2: Base de conocimientos
$kbFiles = [
    'karma-kb.json',
    'reactions-kb.json',
    'notifications-kb.json',
    'social-kb.json',
    'general-kb.json'
];

$kbPath = __DIR__.'/../knowledge/';
$kbOk = true;
$kbCount = 0;

foreach ($kbFiles as $file) {
    if (file_exists($kbPath . $file)) {
        $kbCount++;
    } else {
        $kbOk = false;
    }
}

$health['checks']['knowledge_base'] = [
    'status' => $kbOk ? 'ok' : 'partial',
    'files_loaded' => $kbCount,
    'files_expected' => count($kbFiles)
];

// Check 3: Base de datos (opcional)
try {
    require_once(__DIR__.'/../../models/config.php');
    global $conexion;
    
    $stmt = $conexion->query("SELECT 1");
    $health['checks']['database'] = 'ok';
} catch (Exception $e) {
    $health['checks']['database'] = 'error';
    $health['status'] = 'degraded';
}

// Check 4: Widget files
$widgetFiles = [
    'assistant-widget.html',
    'assistant-widget.css',
    'assistant-widget.js'
];

$widgetPath = __DIR__.'/../widget/';
$widgetOk = true;

foreach ($widgetFiles as $file) {
    if (!file_exists($widgetPath . $file)) {
        $widgetOk = false;
        break;
    }
}

$health['checks']['widget'] = $widgetOk ? 'ok' : 'error';

// Estado general
if (!$engineOk || !$widgetOk) {
    $health['status'] = 'unhealthy';
}

// Responder
http_response_code($health['status'] === 'healthy' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
