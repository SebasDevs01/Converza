<?php
// Incluir archivo de configuración
$ruta_config = __DIR__ . '/app/models/config.php';
if (!file_exists($ruta_config)) {
    die("Error: No se encuentra el archivo config.php en: $ruta_config");
}
require_once($ruta_config);

// La conexión $conexion ya está disponible desde config.php
if (!isset($conexion) || !$conexion) {
    die("Error: No se pudo establecer conexión con la base de datos");
}

try {
    // Obtener todos los usuarios con su estado de tipo
    $stmt = $conexion->prepare("SELECT id_use, usuario, tipo FROM usuarios ORDER BY usuario");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estado de Usuarios</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Tipo</th><th>Estado</th></tr>";
    
    foreach ($usuarios as $user) {
        $estado = ($user['tipo'] === 'blocked') ? "🚫 BLOQUEADO" : "✅ Activo";
        $color = ($user['tipo'] === 'blocked') ? "background-color: #ffcccc;" : "";
        
        echo "<tr style='$color'>";
        echo "<td>{$user['id_use']}</td>";
        echo "<td><strong>{$user['usuario']}</strong></td>";
        echo "<td>{$user['tipo']}</td>";
        echo "<td>$estado</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Contar usuarios bloqueados
    $stmtCount = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE tipo = 'blocked'");
    $stmtCount->execute();
    $blockedCount = $stmtCount->fetchColumn();
    
    echo "<p><strong>Total usuarios bloqueados:</strong> $blockedCount</p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
