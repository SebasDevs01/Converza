<?php
/**
 * Endpoint para obtener el karma actualizado del usuario
 * Ubicación: /Converza/app/presenters/get_karma.php
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../models/karma-social-helper.php';

try {
    // Verificar sesión
    if (!isset($_SESSION['id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'No hay sesión activa'
        ]);
        exit;
    }

    $usuario_id = (int)$_SESSION['id'];
    
    // Obtener karma desde karma_total_usuarios (sistema correcto)
    $stmt = $conexion->prepare("
        SELECT karma_total, acciones_totales 
        FROM karma_total_usuarios 
        WHERE usuario_id = ?
    ");
    $stmt->execute([$usuario_id]);
    $karmaData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$karmaData) {
        // Si no existe registro, crearlo con valores iniciales
        $stmtCreate = $conexion->prepare("
            INSERT INTO karma_total_usuarios (usuario_id, karma_total, acciones_totales, ultima_accion)
            VALUES (?, 0, 0, NOW())
        ");
        $stmtCreate->execute([$usuario_id]);
        
        $karmaTotal = 0;
        $accionesTotales = 0;
    } else {
        $karmaTotal = intval($karmaData['karma_total']);
        $accionesTotales = intval($karmaData['acciones_totales']);
    }
    
    // Obtener nivel usando KarmaSocialHelper
    $karmaHelper = new KarmaSocialHelper($conexion);
    $nivelData = $karmaHelper->obtenerNivelKarma($karmaTotal);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'karma_actualizado' => [
            'karma' => (string)$karmaTotal, // STRING para consistencia
            'nivel' => $nivelData['nivel'] ?? 1,
            'nivel_titulo' => $nivelData['titulo'] ?? 'Novato',
            'nivel_emoji' => $nivelData['emoji'] ?? '🌱',
            'acciones_totales' => $accionesTotales
        ]
    ]);
            'nivel_emoji' => $nivelData['emoji'] ?? '🌱'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener karma: ' . $e->getMessage()
    ]);
}
