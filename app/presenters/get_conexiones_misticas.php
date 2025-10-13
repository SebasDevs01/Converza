<?php
session_start();
require_once(__DIR__ . '/../models/config.php');
require_once(__DIR__ . '/../models/conexiones-misticas-helper.php');
require_once(__DIR__ . '/../models/conexiones-misticas-usuario-helper.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

try {
    $usuarioId = $_SESSION['id'];
    
    // 🔄 DETECCIÓN AUTOMÁTICA INTELIGENTE
    // Solo actualiza si han pasado más de 6 horas desde la última detección
    $checkUpdate = $conexion->prepare("
        SELECT MAX(fecha_deteccion) as ultima_actualizacion
        FROM conexiones_misticas
        WHERE usuario1_id = ? OR usuario2_id = ?
    ");
    $checkUpdate->execute([$usuarioId, $usuarioId]);
    $ultima = $checkUpdate->fetch(PDO::FETCH_ASSOC);
    
    $necesitaActualizar = false;
    
    if (!$ultima || !$ultima['ultima_actualizacion']) {
        // Usuario nuevo, nunca ha tenido conexiones
        $necesitaActualizar = true;
    } else {
        // Verificar si han pasado más de 6 horas
        $horasDiferencia = (strtotime('now') - strtotime($ultima['ultima_actualizacion'])) / 3600;
        if ($horasDiferencia >= 6) {
            $necesitaActualizar = true;
        }
    }
    
    // Si necesita actualización, ejecutar detección solo para este usuario
    if ($necesitaActualizar) {
        $motorUsuario = new ConexionesMisticasUsuario($conexion, $usuarioId);
        $motorUsuario->detectarConexionesUsuario();
    }
    
    // Obtener conexiones del usuario
    $motor = new ConexionesMisticas($conexion);
    $conexiones = $motor->obtenerConexionesUsuario($usuarioId, 20);
    
    echo json_encode([
        'success' => true,
        'conexiones' => $conexiones,
        'total' => count($conexiones),
        'actualizado' => $necesitaActualizar
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al cargar conexiones',
        'message' => $e->getMessage()
    ]);
}
?>
