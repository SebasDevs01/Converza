<?php
/**
 * Script para arreglar conexiones duplicadas
 * Ejecutar una sola vez para limpiar la base de datos
 */

require_once(__DIR__ . '/app/models/config.php');

echo "<h2>🔧 Reparando Conexiones Místicas...</h2>";

try {
    // Paso 1: Eliminar duplicados existentes
    echo "<p>📋 Paso 1: Eliminando conexiones duplicadas...</p>";
    
    $sql1 = "
        DELETE t1 FROM conexiones_misticas t1
        INNER JOIN conexiones_misticas t2 
        WHERE t1.id > t2.id 
        AND t1.usuario1_id = t2.usuario1_id 
        AND t1.usuario2_id = t2.usuario2_id 
        AND t1.tipo_conexion = t2.tipo_conexion
    ";
    
    $stmt1 = $conexion->exec($sql1);
    echo "<p class='text-success'>✅ Duplicados eliminados: $stmt1 registros</p>";
    
    // Paso 2: Verificar si ya existe el índice único
    echo "<p>🔍 Paso 2: Verificando índice único...</p>";
    
    $checkIndex = "
        SELECT COUNT(*) as existe
        FROM information_schema.statistics 
        WHERE table_schema = 'converza' 
        AND table_name = 'conexiones_misticas' 
        AND index_name = 'unique_conexion'
    ";
    
    $stmt = $conexion->query($checkIndex);
    $existe = $stmt->fetch(PDO::FETCH_ASSOC)['existe'];
    
    if ($existe > 0) {
        echo "<p class='text-info'>ℹ️ El índice único ya existe, omitiendo...</p>";
    } else {
        // Paso 3: Agregar índice único
        echo "<p>🔑 Paso 3: Agregando índice único...</p>";
        
        $sql2 = "
            ALTER TABLE conexiones_misticas 
            ADD UNIQUE KEY unique_conexion (usuario1_id, usuario2_id, tipo_conexion)
        ";
        
        $conexion->exec($sql2);
        echo "<p class='text-success'>✅ Índice único agregado correctamente</p>";
    }
    
    // Paso 4: Verificar resultados
    echo "<p>📊 Paso 4: Verificando resultados...</p>";
    
    $count = $conexion->query("SELECT COUNT(*) as total FROM conexiones_misticas")->fetch()['total'];
    echo "<p class='text-primary'><strong>Total de conexiones únicas: $count</strong></p>";
    
    echo "<hr>";
    echo "<h3 class='text-success'>✅ ¡Reparación Completada!</h3>";
    echo "<p>Ahora cada combinación de usuarios + tipo de conexión es única.</p>";
    echo "<p><a href='detectar_conexiones.php' class='btn btn-primary'>Ejecutar Detector de Conexiones</a></p>";
    echo "<p><a href='app/view/index.php' class='btn btn-secondary'>Volver al Feed</a></p>";
    
} catch (Exception $e) {
    echo "<p class='text-danger'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; padding: 40px; background: #f8f9fa; }
    h2 { color: #0d6efd; }
    p { padding: 8px; border-radius: 4px; }
    .text-success { background: #d4edda; color: #155724; }
    .text-info { background: #d1ecf1; color: #0c5460; }
    .text-primary { background: #cfe2ff; color: #084298; }
    .text-danger { background: #f8d7da; color: #721c24; }
    .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; text-decoration: none; border-radius: 5px; }
    .btn-primary { background: #0d6efd; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
</style>
