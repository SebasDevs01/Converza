<?php
session_start();
require_once __DIR__ . '/app/models/config.php';
require_once __DIR__ . '/app/models/notificaciones-helper.php';

echo "<h1>🔍 Debug de Notificaciones</h1>";

// Ver usuario actual
echo "<h2>Usuario actual en sesión:</h2>";
echo "<pre>";
echo "ID: " . ($_SESSION['id'] ?? 'NO DEFINIDO') . "\n";
echo "Usuario: " . ($_SESSION['usuario'] ?? 'NO DEFINIDO') . "\n";
print_r($_SESSION);
echo "</pre>";

// Buscar todas las notificaciones en la tabla
echo "<h2>Todas las notificaciones en la tabla:</h2>";
try {
    $stmt = $conexion->query("SELECT * FROM notificaciones ORDER BY fecha_creacion DESC");
    $todas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Total en BD: <strong>" . count($todas) . "</strong></p>";
    echo "<pre>";
    print_r($todas);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Probar con el helper
if (isset($_SESSION['id'])) {
    echo "<h2>Notificaciones según el Helper:</h2>";
    $helper = new NotificacionesHelper($conexion);
    
    $noLeidas = $helper->obtenerNoLeidas($_SESSION['id']);
    $total = $helper->contarNoLeidas($_SESSION['id']);
    
    echo "<p>No leídas para usuario {$_SESSION['id']}: <strong>$total</strong></p>";
    echo "<pre>";
    print_r($noLeidas);
    echo "</pre>";
    
    // Todas las notificaciones
    $todas = $helper->obtenerTodas($_SESSION['id']);
    echo "<h2>Todas las notificaciones del usuario:</h2>";
    echo "<p>Total: <strong>" . count($todas) . "</strong></p>";
    echo "<pre>";
    print_r($todas);
    echo "</pre>";
}

// Probar la API directamente
echo "<h2>Respuesta de la API:</h2>";
echo "<p><a href='/Converza/app/presenters/notificaciones_api.php?accion=obtener' target='_blank'>Abrir API en nueva pestaña</a></p>";

// Ver usuarios en la BD
echo "<h2>Usuarios en la BD:</h2>";
try {
    $stmt = $conexion->query("SELECT id_use, usuario FROM usuarios LIMIT 5");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($usuarios);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
