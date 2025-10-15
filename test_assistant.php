<?php
/**
 * Test del Asistente - Diagnóstico
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST ASISTENTE CONVERZA ===\n\n";

// 1. Verificar archivos
echo "1. Verificando archivos...\n";
$files = [
    'API' => __DIR__.'/app/microservices/converza-assistant/api/assistant.php',
    'IntentClassifier' => __DIR__.'/app/microservices/converza-assistant/engine/IntentClassifier.php',
    'ResponseGenerator' => __DIR__.'/app/microservices/converza-assistant/engine/ResponseGenerator.php',
    'ContextManager' => __DIR__.'/app/microservices/converza-assistant/engine/ContextManager.php',
    'Config' => __DIR__.'/app/models/config.php',
    'KarmaHelper' => __DIR__.'/app/models/karma-social-helper.php'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "   ✅ $name: OK\n";
    } else {
        echo "   ❌ $name: NO EXISTE - $path\n";
    }
}

// 2. Test de conexión a BD
echo "\n2. Probando conexión a BD...\n";
try {
    require_once(__DIR__.'/app/models/config.php');
    if (isset($conexion) && $conexion) {
        echo "   ✅ Conexión establecida\n";
        
        // Probar query
        $stmt = $conexion->query("SELECT COUNT(*) as count FROM usuarios");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   ✅ Usuarios en BD: " . $result['count'] . "\n";
    } else {
        echo "   ❌ Variable \$conexion no existe\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 3. Test del ContextManager
echo "\n3. Probando ContextManager...\n";
try {
    require_once(__DIR__.'/app/microservices/converza-assistant/engine/ContextManager.php');
    $contextManager = new ContextManager();
    $context = $contextManager->getUserContext(1);
    echo "   ✅ Contexto obtenido:\n";
    echo "      - Usuario: " . $context['username'] . "\n";
    echo "      - Karma: " . $context['karma'] . "\n";
    echo "      - Nivel: " . $context['nivel'] . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 4. Test del IntentClassifier
echo "\n4. Probando IntentClassifier...\n";
try {
    require_once(__DIR__.'/app/microservices/converza-assistant/engine/IntentClassifier.php');
    $classifier = new IntentClassifier();
    $intent = $classifier->classify("¿Cómo gano karma?");
    echo "   ✅ Intent detectado:\n";
    echo "      - Nombre: " . $intent['name'] . "\n";
    echo "      - Confianza: " . round($intent['confidence'] * 100, 2) . "%\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 5. Test del ResponseGenerator
echo "\n5. Probando ResponseGenerator...\n";
try {
    require_once(__DIR__.'/app/microservices/converza-assistant/engine/ResponseGenerator.php');
    $generator = new ResponseGenerator();
    $response = $generator->generate($intent, $context);
    echo "   ✅ Respuesta generada:\n";
    echo "      " . substr($response['answer'], 0, 100) . "...\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 6. Test completo (simulando POST)
echo "\n6. Test completo (simulación POST)...\n";
try {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = ['question' => '¿Cómo gano karma?', 'user_id' => 1];
    
    ob_start();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/Converza/app/microservices/converza-assistant/api/assistant.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['question' => '¿Cómo gano karma?', 'user_id' => 1]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    ob_end_clean();
    
    if ($httpCode === 200) {
        echo "   ✅ API respondió correctamente (HTTP $httpCode)\n";
        $json = json_decode($result, true);
        if ($json && isset($json['success']) && $json['success']) {
            echo "   ✅ Respuesta válida recibida\n";
            echo "      Intent: " . ($json['intent'] ?? 'N/A') . "\n";
        } else {
            echo "   ⚠️ API respondió pero con error:\n";
            echo "      " . ($json['error'] ?? 'Error desconocido') . "\n";
        }
    } else {
        echo "   ❌ Error HTTP: $httpCode\n";
        echo "   Respuesta: " . substr($result, 0, 200) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
