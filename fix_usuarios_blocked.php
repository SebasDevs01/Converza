<?php
session_start();
require_once 'app/models/config.php';

echo "<h2>🔧 Reparando tabla usuarios - Agregando soporte para 'blocked'</h2>";

try {
    // 1. Modificar el ENUM para incluir 'blocked'
    echo "<p><strong>Paso 1:</strong> Modificando columna tipo para incluir 'blocked'...</p>";
    
    $sql = "ALTER TABLE usuarios MODIFY COLUMN tipo ENUM('user', 'admin', 'blocked') DEFAULT 'user'";
    $result = $conexion->exec($sql);
    
    echo "✅ Columna tipo modificada exitosamente!<br>";
    
    // 2. Verificar la nueva estructura
    echo "<p><strong>Paso 2:</strong> Verificando nueva estructura...</p>";
    
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuarios WHERE Field = 'tipo'");
    $stmt->execute();
    $columna = $stmt->fetch();
    
    echo "Nueva definición: <code>" . $columna['Type'] . "</code><br>";
    
    // 3. Limpiar usuarios con tipo vacío
    echo "<p><strong>Paso 3:</strong> Limpiando tipos vacíos...</p>";
    
    $stmt = $conexion->prepare("UPDATE usuarios SET tipo = 'user' WHERE tipo = '' OR tipo IS NULL");
    $stmt->execute();
    $filasLimpiadas = $stmt->rowCount();
    
    echo "✅ $filasLimpiadas usuarios con tipo vacío convertidos a 'user'<br>";
    
    // 4. Probar bloqueo ahora
    echo "<p><strong>Paso 4:</strong> Probando bloqueo del usuario ID 1...</p>";
    
    $stmt = $conexion->prepare("UPDATE usuarios SET tipo = 'blocked' WHERE id_use = 1");
    $result = $stmt->execute();
    $filasAfectadas = $stmt->rowCount();
    
    echo "Resultado: " . ($result ? '✅ true' : '❌ false') . "<br>";
    echo "Filas afectadas: $filasAfectadas<br>";
    
    // Verificar el cambio
    $stmt = $conexion->prepare("SELECT tipo FROM usuarios WHERE id_use = 1");
    $stmt->execute();
    $nuevoTipo = $stmt->fetchColumn();
    
    echo "Nuevo tipo: '<strong>$nuevoTipo</strong>'<br>";
    
    if ($nuevoTipo === 'blocked') {
        echo "🎉 <span style='color: green;'>¡ÉXITO! El bloqueo ahora funciona correctamente.</span><br>";
        
        // Desbloquear para dejar limpio
        $conexion->prepare("UPDATE usuarios SET tipo = 'user' WHERE id_use = 1")->execute();
        echo "✅ Usuario 1 desbloqueado para dejar limpio.<br>";
    } else {
        echo "❌ <span style='color: red;'>Aún hay problemas.</span><br>";
    }
    
    echo "<hr>";
    echo "<h3>🎯 Estado Final de Usuarios:</h3>";
    
    // Mostrar usuarios actualizados
    $stmt = $conexion->prepare("SELECT id_use, usuario, tipo FROM usuarios ORDER BY id_use");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Tipo</th></tr>";
    
    foreach ($usuarios as $user) {
        $color = '';
        if ($user['tipo'] === 'admin') $color = 'style="background: #e3f2fd;"';
        if ($user['tipo'] === 'blocked') $color = 'style="background: #ffebee;"';
        if ($user['tipo'] === 'user') $color = 'style="background: #e8f5e8;"';
        
        echo "<tr $color>";
        echo "<td>{$user['id_use']}</td>";
        echo "<td>{$user['usuario']}</td>";
        echo "<td><strong>{$user['tipo']}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    echo "<br>Stack trace: " . $e->getTraceAsString();
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; }
    th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
    th { background: #f0f0f0; }
    code { background: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
    hr { margin: 20px 0; }
</style>

<p><a href="admin.php" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">🔙 Volver al Admin</a></p>