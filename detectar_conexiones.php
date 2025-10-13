<?php
/**
 * Script para detectar conexiones místicas
 * Ejecutar manualmente o como cron job
 */

require_once(__DIR__ . '/app/models/config.php');
require_once(__DIR__ . '/app/models/conexiones-misticas-helper.php');

echo "╔════════════════════════════════════════╗\n";
echo "║   🔮 CONEXIONES MÍSTICAS 🔮           ║\n";
echo "║   Detector de Serendipia Digital      ║\n";
echo "╚════════════════════════════════════════╝\n\n";

$motor = new ConexionesMisticas($conexion);
$motor->detectarConexiones();

echo "\n✨ ¡Proceso completado! ✨\n";
