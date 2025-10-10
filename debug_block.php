<?php
session_start();
require_once 'app/models/config.php';

// Verificar conexión a la base de datos
try {
    // Primero verificar la estructura de la tabla usuarios
    echo "<h2>Estructura de la tabla usuarios:</h2>";
    $stmt = $conexion->prepare("DESCRIBE usuarios");
    $stmt->execute();
    $estructura = $stmt->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($estructura as $campo) {
        echo "<tr>";
        echo "<td>{$campo['Field']}</td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "<td>{$campo['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar todos los usuarios
    $stmt = $conexion->prepare("SELECT * FROM usuarios ORDER BY id_use");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    echo "<h2>Estado actual de usuarios:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Tipo</th><th>Todos los campos</th></tr>";
    
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td>{$usuario['id_use']}</td>";
        echo "<td>" . htmlspecialchars($usuario['usuario']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['tipo'] ?? 'NULL') . "</td>";
        echo "<td><small>" . json_encode($usuario) . "</small></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Probar una actualización simple
    if (isset($_GET['test_update'])) {
        $testId = (int)$_GET['test_update'];
        echo "<h3>🧪 Probando actualización del usuario ID: $testId</h3>";
        
        // Verificar que el usuario existe primero
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
        $stmt->execute([':id' => $testId]);
        $usuarioAntes = $stmt->fetch();
        
        if (!$usuarioAntes) {
            echo "❌ ERROR: No existe usuario con ID $testId<br>";
        } else {
            echo "✅ Usuario encontrado: " . json_encode($usuarioAntes) . "<br><br>";
            
            // Intentar la actualización
            echo "<strong>Ejecutando UPDATE...</strong><br>";
            $stmt = $conexion->prepare("UPDATE usuarios SET tipo = 'blocked' WHERE id_use = :id");
            
            // Debug de la consulta
            echo "SQL: UPDATE usuarios SET tipo = 'blocked' WHERE id_use = $testId<br>";
            
            $result = $stmt->execute([':id' => $testId]);
            
            echo "Resultado de execute(): " . ($result ? '✅ true' : '❌ false') . "<br>";
            echo "Filas afectadas: " . $stmt->rowCount() . "<br>";
            
            if ($result && $stmt->rowCount() > 0) {
                echo "✅ Actualización exitosa<br>";
            } else {
                echo "❌ Actualización falló - 0 filas afectadas<br>";
                
                // Debug adicional
                $errorInfo = $stmt->errorInfo();
                echo "Error Info: " . json_encode($errorInfo) . "<br>";
            }
            
            // Verificar después de la actualización
            $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
            $stmt->execute([':id' => $testId]);
            $usuarioDespues = $stmt->fetch();
            
            echo "<strong>Usuario después:</strong> " . json_encode($usuarioDespues) . "<br>";
            
            // Comparar antes y después
            if ($usuarioAntes['tipo'] !== $usuarioDespues['tipo']) {
                echo "✅ CAMBIO DETECTADO: '{$usuarioAntes['tipo']}' → '{$usuarioDespues['tipo']}'<br>";
            } else {
                echo "❌ SIN CAMBIOS: Tipo sigue siendo '{$usuarioDespues['tipo']}'<br>";
            }
        }
    }
    
    // Mostrar información de la sesión admin actual
    echo "<h3>🔐 Información de Sesión Actual:</h3>";
    echo "SESSION: " . json_encode($_SESSION) . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "<br>Stack trace: " . $e->getTraceAsString();
}
?>

<style>
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; border: 1px solid #ccc; }
    th { background: #f0f0f0; }
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2, h3 { color: #333; }
</style>

<p>
    <a href="?" style="padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">🔄 Refrescar</a> |
    <a href="?test_update=1" style="padding: 8px 16px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">🧪 Test ID 1</a> |
    <a href="?test_update=2" style="padding: 8px 16px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">🧪 Test ID 2</a> |
    <a href="?test_update=3" style="padding: 8px 16px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">🧪 Test ID 3</a>
</p>