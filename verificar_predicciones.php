<?php
/**
 * Verificación Rápida - Estructura de Predicciones
 * Muestra las columnas reales de la tabla predicciones_usuarios
 */

require_once(__DIR__ . '/app/models/config.php');

$database = new Database();
$conexion = $database->getConnection();

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head><meta charset='UTF-8'><title>Verificar Estructura</title></head>";
echo "<body style='font-family: Arial; padding: 20px;'>";

echo "<h2>🔍 Verificación de Tabla: predicciones_usuarios</h2>";

try {
    // Obtener estructura de la tabla
    $stmt = $conexion->query("DESCRIBE predicciones_usuarios");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Columnas encontradas:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Columna</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    
    // Verificar si hay datos
    echo "<h3>📊 Estadísticas:</h3>";
    $stmt_count = $conexion->query("SELECT COUNT(*) as total FROM predicciones_usuarios");
    $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total de registros:</strong> {$total}</p>";
    
    // Contar por usuario
    $stmt_users = $conexion->query("
        SELECT usuario_id, COUNT(*) as votos 
        FROM predicciones_usuarios 
        WHERE visto = 1 AND me_gusta IS NOT NULL
        GROUP BY usuario_id
        ORDER BY votos DESC
        LIMIT 10
    ");
    $usuarios = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Top 10 usuarios con más votos:</h4>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #e8f4f8;'><th>Usuario ID</th><th>Votos</th></tr>";
    foreach ($usuarios as $user) {
        echo "<tr><td>{$user['usuario_id']}</td><td>{$user['votos']}</td></tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    
    // Distribución por categorías
    echo "<h4>Distribución por categorías:</h4>";
    $stmt_cat = $conexion->query("
        SELECT categoria, COUNT(*) as total 
        FROM predicciones_usuarios 
        WHERE visto = 1 AND me_gusta IS NOT NULL
        GROUP BY categoria
    ");
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #fff4e6;'><th>Categoría</th><th>Votos</th></tr>";
    foreach ($categorias as $cat) {
        echo "<tr><td>{$cat['categoria']}</td><td>{$cat['total']}</td></tr>";
    }
    echo "</table>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h4 style='margin-top: 0;'>✅ Verificación Completada</h4>";
    echo "<p>La tabla <code>predicciones_usuarios</code> existe y tiene la columna <strong>usuario_id</strong> (no id_use).</p>";
    echo "<p><strong>Columnas clave:</strong></p>";
    echo "<ul>";
    foreach ($columnas as $col) {
        if (in_array($col['Field'], ['id', 'usuario_id', 'prediccion_id', 'categoria', 'me_gusta', 'visto'])) {
            echo "<li><code>{$col['Field']}</code> - {$col['Type']}</li>";
        }
    }
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h4>❌ Error:</h4>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='diagnostico_conexiones.php'>← Volver al Diagnóstico</a></p>";
echo "</body></html>";
?>
