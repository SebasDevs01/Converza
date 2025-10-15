<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Asistente</h1>";

// Test 1: Archivos
echo "<h2>1. Archivos</h2>";
$apiFile = __DIR__.'/app/microservices/converza-assistant/api/assistant.php';
echo file_exists($apiFile) ? "✅ API existe<br>" : "❌ API no existe<br>";

// Test 2: Conexión
echo "<h2>2. Conexión</h2>";
try {
    require_once(__DIR__.'/app/models/config.php');
    echo isset($conexion) ? "✅ Conexión OK<br>" : "❌ Sin conexión<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Context Manager
echo "<h2>3. Context Manager</h2>";
try {
    require_once(__DIR__.'/app/microservices/converza-assistant/engine/ContextManager.php');
    $cm = new ContextManager();
    $ctx = $cm->getUserContext(1);
    echo "✅ Usuario: " . $ctx['username'] . "<br>";
    echo "✅ Karma: " . $ctx['karma'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 4: IntentClassifier
echo "<h2>4. Intent Classifier</h2>";
try {
    require_once(__DIR__.'/app/microservices/converza-assistant/engine/IntentClassifier.php');
    $ic = new IntentClassifier();
    $intent = $ic->classify("¿Cómo gano karma?");
    echo "✅ Intent: " . $intent['name'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 5: Response Generator
echo "<h2>5. Response Generator</h2>";
try {
    require_once(__DIR__.'/app/microservices/converza-assistant/engine/ResponseGenerator.php');
    $rg = new ResponseGenerator();
    $response = $rg->generate($intent, $ctx);
    echo "✅ Respuesta: " . substr($response['answer'], 0, 100) . "...<br>";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>✅ Test completado</h2>";
