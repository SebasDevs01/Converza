<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * 🎯 API: Obtener usuarios con intereses similares
 * ═══════════════════════════════════════════════════════════════
 */

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../models/intereses-helper.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar sesión
    if (!isset($_SESSION['id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'No autorizado'
        ]);
        exit;
    }
    
    $usuario_id = $_SESSION['id'];
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
    
    $interesesHelper = new InteresesHelper($conexion);
    
    // Obtener usuarios similares
    $usuariosSimilares = $interesesHelper->obtenerUsuariosSimilares($usuario_id, $limite);
    
    echo json_encode([
        'success' => true,
        'usuarios' => $usuariosSimilares,
        'total' => count($usuariosSimilares)
    ]);
    
} catch (Exception $e) {
    error_log("Error en get_usuarios_similares.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener usuarios similares'
    ]);
}
exit;
?>
