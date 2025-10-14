<?php
/**
 * GET KARMA - Endpoint AJAX
 * Retorna el karma actual del usuario en formato JSON
 */

session_start();
require_once(__DIR__.'/../models/config.php');
require_once(__DIR__.'/../models/karma-social-helper.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

try {
    $karmaHelper = new KarmaSocialHelper($conexion);
    $karmaData = $karmaHelper->obtenerKarmaUsuario($_SESSION['id']);
    
    // Obtener nivel numérico
    $nivelData = $karmaData['nivel_data'] ?? ['nivel' => 1];
    
    echo json_encode([
        'success' => true,
        'karma' => $karmaData['karma_total'],
        'nivel' => $nivelData['nivel'], // Número: 1, 2, 3...
        'nivel_titulo' => $nivelData['titulo'] ?? $karmaData['nivel'], // Texto: "Novato", "Intermedio"...
        'nivel_emoji' => $karmaData['nivel_emoji'],
        'proxima_recompensa' => $karmaData['proxima_recompensa']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener karma: ' . $e->getMessage()
    ]);
}
