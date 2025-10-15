<?php
session_start();
require_once(__DIR__ . '/../models/config.php');
require_once(__DIR__ . '/../models/conexiones-misticas-helper.php');
require_once(__DIR__ . '/../models/intereses-helper.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

try {
    $usuarioId = $_SESSION['id'];
    
    // 🚀 GENERACIÓN AUTOMÁTICA DE CONEXIONES
    // Se ejecuta automáticamente si es necesario (sin intervención manual)
    $motor = new ConexionesMisticas($conexion);
    $actualizado = $motor->generarConexionesAutomaticas($usuarioId);
    
    // Obtener conexiones del usuario
    $conexiones = $motor->obtenerConexionesUsuario($usuarioId, 20);
    
    // Mejorar con predicciones (sistema híbrido 50/50)
    $interesesHelper = new InteresesHelper($conexion);
    $conexiones = $interesesHelper->mejorarConexionesMisticas($usuarioId, $conexiones);
    
    echo json_encode([
        'success' => true,
        'conexiones' => $conexiones,
        'total' => count($conexiones),
        'actualizado' => $actualizado
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al cargar conexiones',
        'message' => $e->getMessage()
    ]);
}
?>
