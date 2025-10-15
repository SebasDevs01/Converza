<?php
/**
 * CRON JOB: Actualización automática de Conexiones Místicas
 * Ejecutar cada 6 horas: 0 6 * * * php /path/to/cron_actualizar_conexiones.php*/
 

require_once(__DIR__ . '/../models/config.php');
require_once(__DIR__ . '/../models/conexiones-misticas-helper.php');

// Solo permitir ejecución desde CLI o localhost
if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die("⛔ Acceso denegado. Solo desde CLI o localhost.\n");
}

echo "🔮 ========================================\n";
echo "   CRON JOB: Conexiones Místicas\n";
echo "   " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

try {
    $conexionesMisticas = new ConexionesMisticas($conexion);
    
    // Actualizar conexiones
    echo "🚀 Iniciando actualización automática...\n\n";
    $resultado = $conexionesMisticas->actualizarConexionesAutomatico();
    
    if ($resultado) {
        echo "\n✅ Actualización completada con éxito!\n";
        echo "📅 Próxima ejecución: " . date('Y-m-d H:i:s', strtotime('+6 hours')) . "\n";
    } else {
        echo "\n❌ Error en la actualización\n";
    }
    
} catch (Exception $e) {
    echo "\n⚠️ ERROR: " . $e->getMessage() . "\n";
    error_log("Error en cron_actualizar_conexiones.php: " . $e->getMessage());
}

echo "\n========================================\n";
?>
