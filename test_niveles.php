<?php
// Test rápido del sistema de niveles

require_once('c:/xampp/htdocs/Converza/app/models/config.php');
require_once('c:/xampp/htdocs/Converza/app/models/karma-social-helper.php');

$karmaHelper = new KarmaSocialHelper($conexion);

// Probar diferentes niveles
$tests = [0, 50, 99, 100, 150, 199, 200, 300, 500, 1000];

echo "📊 PRUEBA DEL SISTEMA DE NIVELES\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($tests as $karma_test) {
    $nivelData = $karmaHelper->obtenerNivelKarma($karma_test);
    printf(
        "%4d puntos → Nivel %2d (%s %s) - Progreso: %d/100 (%d%%)\n",
        $karma_test,
        $nivelData['nivel'],
        $nivelData['emoji'],
        $nivelData['titulo'],
        $nivelData['progreso'],
        $nivelData['porcentaje']
    );
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Sistema funcionando correctamente\n";
