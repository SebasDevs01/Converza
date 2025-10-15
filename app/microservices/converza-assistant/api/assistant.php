<?php
/**
 * 🤖 CONVERZA ASSISTANT - Endpoint Principal
 * Recibe preguntas y devuelve respuestas inteligentes sobre Converza
 */

// Iniciar output buffering para evitar salidas previas
ob_start();

// Suprimir errores menores que puedan romper el JSON
error_reporting(E_ERROR | E_PARSE);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Leer input
$input = json_decode(file_get_contents('php://input'), true);
$question = trim($input['question'] ?? '');
$user_id = intval($input['user_id'] ?? 0);

// Log para debugging
error_log("📥 Assistant API - Question: '$question', User ID: $user_id");

// Validar input
if (empty($question)) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Pregunta vacía',
        'suggestion' => '¿En qué puedo ayudarte?'
    ]);
    exit;
}

// Rate limiting simple (10 preguntas por minuto)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$now = time();
$_SESSION['assistant_requests'] = $_SESSION['assistant_requests'] ?? [];
$_SESSION['assistant_requests'] = array_filter($_SESSION['assistant_requests'], fn($t) => $t > $now - 60);

if (count($_SESSION['assistant_requests']) >= 10) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'error' => 'Demasiadas preguntas. Por favor espera un momento.',
        'retry_after' => 60
    ]);
    exit;
}

$_SESSION['assistant_requests'][] = $now;

// Cargar dependencias
require_once(__DIR__.'/../engine/IntentClassifier.php');
require_once(__DIR__.'/../engine/ResponseGenerator.php');
require_once(__DIR__.'/../engine/ContextManager.php');
require_once(__DIR__.'/../engine/LearningSystem.php');

try {
    // Obtener conexión a BD
    require_once(__DIR__.'/../../../models/config.php');
    global $conexion;
    
    // 1. Obtener contexto del usuario
    $contextManager = new ContextManager();
    
    error_log("🔍 Assistant: Llamando getUserContext con user_id = $user_id");
    $userContext = $contextManager->getUserContext($user_id);
    error_log("✅ Assistant: Contexto obtenido - Usuario: " . ($userContext['username'] ?? 'N/A') . ", Karma: " . ($userContext['karma'] ?? 'N/A'));
    
    // Agregar la pregunta actual al contexto
    $userContext['last_question'] = $question;
    
    // 2. Inicializar sistema de aprendizaje
    $learningSystem = new LearningSystem($conexion);
    $conversationPatterns = $learningSystem->getConversationPatterns();
    
    // 3. Intentar respuesta inteligente basada en patrones
    $smartResponse = $learningSystem->generateSmartResponse($question, $conversationPatterns);
    
    if ($smartResponse) {
        // Respuesta inteligente encontrada (hora, fecha, etc.)
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'response' => array_merge($smartResponse, ['context' => $userContext]),
            'type' => 'smart_learning'
        ]);
        exit;
    }
    
    // 4. Clasificar intención
    $classifier = new IntentClassifier();
    $intent = $classifier->classify($question);
    
    // 5. Generar respuesta
    $generator = new ResponseGenerator();
    $response = $generator->generate($intent, $userContext);
    
    // Limpiar buffer y enviar solo JSON
    ob_end_clean();
    
    // 4. Responder
    echo json_encode([
        'success' => true,
        'answer' => $response['answer'],
        'intent' => $intent['name'],
        'confidence' => $intent['confidence'],
        'suggestions' => $response['suggestions'] ?? [],
        'links' => $response['links'] ?? [],
        'context' => [
            'user_karma' => $userContext['karma'] ?? 0,
            'user_level' => $userContext['nivel'] ?? 1,
            'user_name' => $userContext['username'] ?? 'Usuario',
            'user_photo' => $userContext['foto_perfil'] ?? '/Converza/public/avatars/defect.jpg'
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Assistant Error: " . $e->getMessage());
    
    // Limpiar buffer y enviar solo JSON
    ob_end_clean();
    
    echo json_encode([
        'success' => false,
        'error' => 'Error al procesar tu pregunta',
        'debug' => $e->getMessage(),
        'suggestion' => 'Intenta reformular tu pregunta o contacta a soporte.'
    ]);
}
