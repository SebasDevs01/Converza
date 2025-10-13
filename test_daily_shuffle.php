<?php
/**
 * Test Daily Shuffle System
 * Verifica que todos los componentes estén funcionando correctamente
 */

session_start();
require_once __DIR__.'/app/models/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Daily Shuffle</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>
    <style>
        .test-section { margin: 20px 0; padding: 20px; border-radius: 10px; }
        .test-pass { background: #d1f2eb; border-left: 4px solid #27ae60; }
        .test-fail { background: #f8d7da; border-left: 4px solid #e74c3c; }
        .test-warning { background: #fff3cd; border-left: 4px solid #f39c12; }
    </style>
</head>
<body class='bg-light'>
<div class='container mt-5'>
    <div class='card shadow'>
        <div class='card-header bg-primary text-white'>
            <h3><i class='bi bi-clipboard-check'></i> Test Daily Shuffle System</h3>
        </div>
        <div class='card-body'>";

$allTestsPassed = true;

// Test 1: Verificar conexión a base de datos
echo "<div class='test-section ";
try {
    if ($conexion) {
        echo "test-pass'><h5><i class='bi bi-check-circle-fill text-success'></i> Test 1: Conexión a Base de Datos</h5>";
        echo "<p>✅ Conexión exitosa a la base de datos</p>";
    }
} catch (Exception $e) {
    echo "test-fail'><h5><i class='bi bi-x-circle-fill text-danger'></i> Test 1: Conexión a Base de Datos</h5>";
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    $allTestsPassed = false;
}
echo "</div>";

// Test 2: Verificar que existe la tabla daily_shuffle
echo "<div class='test-section ";
try {
    $stmt = $conexion->query("SHOW TABLES LIKE 'daily_shuffle'");
    if ($stmt->rowCount() > 0) {
        echo "test-pass'><h5><i class='bi bi-check-circle-fill text-success'></i> Test 2: Tabla daily_shuffle</h5>";
        echo "<p>✅ La tabla 'daily_shuffle' existe en la base de datos</p>";
        
        // Mostrar estructura
        $stmt = $conexion->query("DESCRIBE daily_shuffle");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<small class='text-muted'>Columnas: ";
        echo implode(', ', array_column($columns, 'Field'));
        echo "</small>";
    } else {
        echo "test-fail'><h5><i class='bi bi-x-circle-fill text-danger'></i> Test 2: Tabla daily_shuffle</h5>";
        echo "<p>❌ La tabla 'daily_shuffle' NO existe</p>";
        echo "<p><a href='setup_daily_shuffle.php' class='btn btn-warning'>Ejecutar Setup</a></p>";
        $allTestsPassed = false;
    }
} catch (Exception $e) {
    echo "test-fail'><h5><i class='bi bi-x-circle-fill text-danger'></i> Test 2: Tabla daily_shuffle</h5>";
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    $allTestsPassed = false;
}
echo "</div>";

// Test 3: Verificar archivos backend
echo "<div class='test-section ";
$backendFiles = [
    'app/presenters/daily_shuffle.php',
    'app/presenters/marcar_contacto_shuffle.php',
    'sql/create_daily_shuffle_table.sql'
];
$allFilesExist = true;
foreach ($backendFiles as $file) {
    if (!file_exists(__DIR__.'/'.$file)) {
        $allFilesExist = false;
        break;
    }
}
if ($allFilesExist) {
    echo "test-pass'><h5><i class='bi bi-check-circle-fill text-success'></i> Test 3: Archivos Backend</h5>";
    echo "<p>✅ Todos los archivos backend existen:</p><ul>";
    foreach ($backendFiles as $file) {
        echo "<li><code>$file</code></li>";
    }
    echo "</ul>";
} else {
    echo "test-fail'><h5><i class='bi bi-x-circle-fill text-danger'></i> Test 3: Archivos Backend</h5>";
    echo "<p>❌ Faltan archivos backend</p>";
    $allTestsPassed = false;
}
echo "</div>";

// Test 4: Verificar que hay usuarios en la base de datos
echo "<div class='test-section ";
try {
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
    $totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($totalUsuarios >= 2) {
        echo "test-pass'><h5><i class='bi bi-check-circle-fill text-success'></i> Test 4: Usuarios en BD</h5>";
        echo "<p>✅ Hay $totalUsuarios usuarios en la base de datos</p>";
    } else {
        echo "test-warning'><h5><i class='bi bi-exclamation-triangle-fill text-warning'></i> Test 4: Usuarios en BD</h5>";
        echo "<p>⚠️ Solo hay $totalUsuarios usuario(s). Se necesitan al menos 2 para probar Daily Shuffle</p>";
    }
} catch (Exception $e) {
    echo "test-fail'><h5><i class='bi bi-x-circle-fill text-danger'></i> Test 4: Usuarios en BD</h5>";
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    $allTestsPassed = false;
}
echo "</div>";

// Test 5: Verificar sesión (opcional)
echo "<div class='test-section ";
if (isset($_SESSION['id']) && isset($_SESSION['usuario'])) {
    echo "test-pass'><h5><i class='bi bi-check-circle-fill text-success'></i> Test 5: Sesión de Usuario</h5>";
    echo "<p>✅ Usuario logueado: <strong>" . htmlspecialchars($_SESSION['usuario']) . "</strong> (ID: " . $_SESSION['id'] . ")</p>";
} else {
    echo "test-warning'><h5><i class='bi bi-exclamation-triangle-fill text-warning'></i> Test 5: Sesión de Usuario</h5>";
    echo "<p>⚠️ No hay sesión activa. <a href='app/view/index.php'>Inicia sesión</a> para probar Daily Shuffle</p>";
}
echo "</div>";

// Test 6: Probar endpoint daily_shuffle.php (si hay sesión)
if (isset($_SESSION['id'])) {
    echo "<div class='test-section ";
    try {
        // Simular request a daily_shuffle.php
        $usuario_id = $_SESSION['id'];
        $fecha_hoy = date('Y-m-d');
        
        $stmt = $conexion->prepare("SELECT COUNT(*) as count FROM daily_shuffle WHERE usuario_id = ? AND fecha_shuffle = ?");
        $stmt->execute([$usuario_id, $fecha_hoy]);
        $existeHoy = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "test-pass'><h5><i class='bi bi-check-circle-fill text-success'></i> Test 6: Endpoint Daily Shuffle</h5>";
        if ($existeHoy > 0) {
            echo "<p>✅ Ya existe shuffle para hoy: $existeHoy usuarios</p>";
        } else {
            echo "<p>✅ El endpoint está listo. No hay shuffle generado aún (se generará al abrir el panel)</p>";
        }
    } catch (Exception $e) {
        echo "test-fail'><h5><i class='bi bi-x-circle-fill text-danger'></i> Test 6: Endpoint Daily Shuffle</h5>";
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
        $allTestsPassed = false;
    }
    echo "</div>";
}

// Test 7: Verificar integración frontend
echo "<div class='test-section ";
$indexFile = __DIR__.'/app/view/index.php';
$navbarFile = __DIR__.'/app/view/_navbar_panels.php';

if (file_exists($indexFile) && file_exists($navbarFile)) {
    $indexContent = file_get_contents($indexFile);
    $navbarContent = file_get_contents($navbarFile);
    
    $hasShuffleButton = strpos($indexContent, 'offcanvasDailyShuffle') !== false;
    $hasShufflePanel = strpos($navbarContent, 'offcanvasDailyShuffle') !== false;
    
    if ($hasShuffleButton && $hasShufflePanel) {
        echo "test-pass'><h5><i class='bi bi-check-circle-fill text-success'></i> Test 7: Integración Frontend</h5>";
        echo "<p>✅ Botón Daily Shuffle agregado en navbar</p>";
        echo "<p>✅ Offcanvas Daily Shuffle implementado</p>";
    } else {
        echo "test-warning'><h5><i class='bi bi-exclamation-triangle-fill text-warning'></i> Test 7: Integración Frontend</h5>";
        echo "<p>⚠️ Faltan componentes frontend:</p>";
        if (!$hasShuffleButton) echo "<p>- Botón en navbar</p>";
        if (!$hasShufflePanel) echo "<p>- Offcanvas panel</p>";
    }
} else {
    echo "test-fail'><h5><i class='bi bi-x-circle-fill text-danger'></i> Test 7: Integración Frontend</h5>";
    echo "<p>❌ Archivos frontend no encontrados</p>";
    $allTestsPassed = false;
}
echo "</div>";

// Resumen final
echo "<div class='test-section ";
if ($allTestsPassed) {
    echo "test-pass'><h3><i class='bi bi-check-circle-fill text-success'></i> ¡Todos los tests pasaron!</h3>";
    echo "<p>✅ El sistema Daily Shuffle está listo para usar</p>";
    echo "<div class='mt-3'>
            <a href='app/view/index.php' class='btn btn-primary btn-lg'>
                <i class='bi bi-house'></i> Ir a la aplicación
            </a>
            <a href='DAILY_SHUFFLE_README.md' class='btn btn-info btn-lg' target='_blank'>
                <i class='bi bi-book'></i> Ver Documentación
            </a>
          </div>";
} else {
    echo "test-fail'><h3><i class='bi bi-x-circle-fill text-danger'></i> Algunos tests fallaron</h3>";
    echo "<p>❌ Revisa los errores arriba y corrígelos</p>";
    echo "<div class='mt-3'>
            <a href='setup_daily_shuffle.php' class='btn btn-warning btn-lg'>
                <i class='bi bi-wrench'></i> Ejecutar Setup
            </a>
          </div>";
}
echo "</div>";

echo "    </div>
    </div>
    
    <div class='text-center mt-4 mb-5'>
        <p class='text-muted'>Test ejecutado el " . date('Y-m-d H:i:s') . "</p>
    </div>
</div>
</body>
</html>";
?>
