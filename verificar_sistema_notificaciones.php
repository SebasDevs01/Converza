<?php
/**
 * Script para verificar y configurar el sistema de notificaciones
 */

require_once __DIR__.'/app/models/config.php';

echo "<h1>🔔 Verificación del Sistema de Notificaciones</h1>";

// 1. Verificar si existe la tabla antigua de notificaciones
echo "<h2>1. Verificando tabla de notificaciones antigua</h2>";
try {
    $stmt = $conexion->query("SHOW TABLES LIKE 'notificaciones'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: orange;'>⚠️ Tabla 'notificaciones' encontrada</p>";
        
        // Verificar estructura
        $stmt = $conexion->query("DESCRIBE notificaciones");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Columnas encontradas:</h3><ul>";
        foreach ($columnas as $col) {
            echo "<li>$col</li>";
        }
        echo "</ul>";
        
        // Verificar si tiene la estructura nueva
        if (in_array('usuario_id', $columnas) && in_array('de_usuario_id', $columnas)) {
            echo "<p style='color: green;'>✅ La tabla tiene la estructura NUEVA (correcta)</p>";
        } else {
            echo "<p style='color: red;'>❌ La tabla tiene la estructura ANTIGUA</p>";
            echo "<p><strong>¿Quieres migrar a la nueva estructura?</strong></p>";
            echo "<form method='POST'>";
            echo "<button type='submit' name='migrar' value='1' style='background: green; color: white; padding: 10px; border: none; cursor: pointer;'>MIGRAR AHORA</button>";
            echo "</form>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ No existe tabla de notificaciones</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: {$e->getMessage()}</p>";
}

// 2. Si se solicita migración
if (isset($_POST['migrar'])) {
    echo "<h2>2. Ejecutando migración...</h2>";
    
    try {
        // Hacer backup de la tabla antigua
        $conexion->exec("CREATE TABLE notificaciones_backup_old AS SELECT * FROM notificaciones");
        echo "<p style='color: green;'>✅ Backup creado: notificaciones_backup_old</p>";
        
        // Eliminar tabla antigua
        $conexion->exec("DROP TABLE notificaciones");
        echo "<p style='color: green;'>✅ Tabla antigua eliminada</p>";
        
        // Crear tabla nueva
        $sql = file_get_contents(__DIR__.'/sql/create_notificaciones_table.sql');
        $conexion->exec($sql);
        echo "<p style='color: green;'>✅ Tabla nueva creada con estructura correcta</p>";
        
        echo "<p style='color: green; font-weight: bold;'>🎉 ¡Migración completada exitosamente!</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en migración: {$e->getMessage()}</p>";
    }
}

// 3. Verificar archivos necesarios
echo "<h2>3. Verificando archivos del sistema</h2>";
$archivos = [
    'app/models/notificaciones-helper.php',
    'app/models/notificaciones-triggers.php',
    'sql/create_notificaciones_table.sql'
];

foreach ($archivos as $archivo) {
    if (file_exists(__DIR__.'/'.$archivo)) {
        echo "<p style='color: green;'>✅ $archivo</p>";
    } else {
        echo "<p style='color: red;'>❌ $archivo NO EXISTE</p>";
    }
}

// 4. Probar creación de notificación de prueba
echo "<h2>4. Prueba de creación de notificación</h2>";
if (isset($_POST['test_notificacion'])) {
    try {
        require_once __DIR__.'/app/models/notificaciones-triggers.php';
        
        // Obtener un usuario de prueba
        $stmt = $conexion->query("SELECT id_use, usuario FROM usuarios LIMIT 1");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            require_once __DIR__.'/app/models/notificaciones-helper.php';
            
            $helper = new NotificacionesHelper($conexion);
            $resultado = $helper->crear(
                $usuario['id_use'],
                'test',
                '<strong>Prueba</strong> Esta es una notificación de prueba del sistema ✅',
                null,
                null,
                'test',
                '/Converza/app/view/index.php'
            );
            
            if ($resultado) {
                echo "<p style='color: green;'>✅ Notificación de prueba creada correctamente para usuario: {$usuario['usuario']}</p>";
                
                // Contar notificaciones del usuario
                $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ?");
                $stmt->execute([$usuario['id_use']]);
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>El usuario <strong>{$usuario['usuario']}</strong> ahora tiene <strong>{$count['total']}</strong> notificación(es)</p>";
            } else {
                echo "<p style='color: red;'>❌ No se pudo crear la notificación de prueba</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ No hay usuarios en la base de datos</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en prueba: {$e->getMessage()}</p>";
    }
}

echo "<form method='POST'>";
echo "<button type='submit' name='test_notificacion' value='1' style='background: blue; color: white; padding: 10px; border: none; cursor: pointer; margin-top: 10px;'>PROBAR SISTEMA DE NOTIFICACIONES</button>";
echo "</form>";

// 5. Contar notificaciones existentes
echo "<h2>5. Estadísticas</h2>";
try {
    $stmt = $conexion->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas
        FROM notificaciones
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total de notificaciones: <strong>{$stats['total']}</strong></p>";
    echo "<p>No leídas: <strong>{$stats['no_leidas']}</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>No se pudieron obtener estadísticas</p>";
}

?>
