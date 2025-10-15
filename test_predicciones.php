<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/app/models/config.php';
require_once __DIR__ . '/app/models/predicciones-helper.php';

echo "<h1>🔮 Test de Predicciones</h1>";

// Verificar sesión
if (!isset($_SESSION['id'])) {
    echo "<p style='color: red;'>❌ No hay sesión activa. Por favor, inicia sesión primero.</p>";
    echo "<p><a href='/Converza/app/view/index.php'>Ir a Inicio</a></p>";
    exit;
}

$usuario_id = $_SESSION['id'];

echo "<p>Usuario ID de sesión: <strong>$usuario_id</strong></p>";

// Verificar que el usuario existe
$stmtCheck = $conexion->prepare("SELECT id_use, nombre, usuario FROM usuarios WHERE id_use = ?");
$stmtCheck->execute([$usuario_id]);
$usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<p style='color: red;'>❌ Usuario con ID $usuario_id no existe en la base de datos.</p>";
    exit;
}

echo "<p>✅ Usuario encontrado: <strong>{$usuario['usuario']}</strong> ({$usuario['nombre']})</p>";
echo "<hr>";

try {
    $prediccionesHelper = new PrediccionesHelper($conexion);
    
    echo "<h2>1️⃣ Verificando tabla...</h2>";
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'predicciones_usuarios'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    echo $tableExists ? "✅ Tabla existe<br>" : "❌ Tabla NO existe<br>";
    
    echo "<h2>2️⃣ Contando predicciones existentes...</h2>";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM predicciones_usuarios WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 Total: $count predicciones<br>";
    
    echo "<h2>3️⃣ Obteniendo predicciones no vistas...</h2>";
    $stmt = $conexion->prepare("SELECT * FROM predicciones_usuarios WHERE usuario_id = ? AND visto = 0");
    $stmt->execute([$usuario_id]);
    $noVistas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📊 No vistas: " . count($noVistas) . "<br>";
    
    if (count($noVistas) > 0) {
        echo "<h3>Predicciones existentes:</h3><ul>";
        foreach ($noVistas as $pred) {
            echo "<li>[{$pred['id']}] {$pred['emoji']} {$pred['categoria']}: {$pred['prediccion']} (Confianza: {$pred['confianza']})</li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>4️⃣ Generando nuevas predicciones...</h2>";
    
    // Limpiar predicciones anteriores
    $stmtDelete = $conexion->prepare("DELETE FROM predicciones_usuarios WHERE usuario_id = ?");
    $stmtDelete->execute([$usuario_id]);
    echo "🧹 Predicciones anteriores limpiadas<br>";
    
    $predicciones = $prediccionesHelper->generarPrediccion($usuario_id);
    echo "✅ Generadas: " . count($predicciones) . " predicciones<br>";
    
    echo "<h3>Predicciones generadas:</h3><ul>";
    foreach ($predicciones as $pred) {
        echo "<li>{$pred['categoria']}: {$pred['texto']} (Confianza: {$pred['confianza']})</li>";
    }
    echo "</ul>";
    
    echo "<h2>5️⃣ Verificando en BD...</h2>";
    $stmt = $conexion->prepare("SELECT * FROM predicciones_usuarios WHERE usuario_id = ? AND visto = 0");
    $stmt->execute([$usuario_id]);
    $enBD = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "📊 En BD: " . count($enBD) . " predicciones<br>";
    
    if (count($enBD) > 0) {
        echo "<h3>Predicciones en BD:</h3><ul>";
        foreach ($enBD as $pred) {
            echo "<li>[{$pred['id']}] {$pred['emoji']} {$pred['categoria']}: {$pred['prediccion']}</li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>6️⃣ Test de obtenerPredicciones()...</h2>";
    $prediccionesObtenidas = $prediccionesHelper->obtenerPredicciones($usuario_id);
    echo "📊 Obtenidas: " . count($prediccionesObtenidas) . " predicciones<br>";
    
    if (count($prediccionesObtenidas) > 0) {
        echo "<h3>Resultado final:</h3><ul>";
        foreach ($prediccionesObtenidas as $pred) {
            echo "<li>[{$pred['id']}] {$pred['emoji']} {$pred['categoria']}: {$pred['prediccion']}</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr><h2>✅ Test completado con éxito</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
