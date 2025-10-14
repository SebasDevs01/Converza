<?php
/**
 * Limpieza: Eliminar tablas de personalización para reinstalar
 */

require_once('app/models/config.php');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Limpiar Base de Datos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 20px; 
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { 
            color: #dc3545; 
            text-align: center; 
            margin-bottom: 30px;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 15px 0;
            border-left: 5px solid #28a745;
        }
        .info { 
            background: #fff3cd; 
            color: #856404; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 15px 0;
            border-left: 5px solid #ffc107;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🧹 Limpieza de Base de Datos</h1>";

try {
    // Eliminar columnas de usuarios si existen
    $columnas_a_eliminar = [
        'bio', 'descripcion_corta', 'signo_zodiacal', 'genero', 'mostrar_icono_genero',
        'estado_animo', 'tema_perfil', 'color_principal', 'icono_personalizado',
        'marco_avatar', 'insignia_especial', 'mostrar_karma', 'mostrar_signo', 'mostrar_estado_animo'
    ];
    
    echo "<div class='info'>";
    echo "<h3>Eliminando columnas de la tabla usuarios...</h3>";
    foreach ($columnas_a_eliminar as $columna) {
        try {
            $conexion->exec("ALTER TABLE usuarios DROP COLUMN {$columna}");
            echo "<p>✓ Columna <code>{$columna}</code> eliminada</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "check that it exists") !== false) {
                echo "<p>⊗ Columna <code>{$columna}</code> no existía</p>";
            }
        }
    }
    echo "</div>";
    
    // Eliminar índices
    echo "<div class='info'>";
    echo "<h3>Eliminando índices...</h3>";
    $indices = ['idx_signo_zodiacal', 'idx_genero', 'idx_tema_perfil'];
    foreach ($indices as $indice) {
        try {
            $conexion->exec("ALTER TABLE usuarios DROP INDEX {$indice}");
            echo "<p>✓ Índice <code>{$indice}</code> eliminado</p>";
        } catch (PDOException $e) {
            echo "<p>⊗ Índice <code>{$indice}</code> no existía</p>";
        }
    }
    echo "</div>";
    
    // Eliminar tabla usuario_recompensas primero (tiene foreign keys)
    try {
        $conexion->exec("DROP TABLE IF EXISTS usuario_recompensas");
        echo "<div class='success'><p>✓ Tabla <code>usuario_recompensas</code> eliminada</p></div>";
    } catch (PDOException $e) {
        echo "<div class='info'><p>⊗ Tabla <code>usuario_recompensas</code> no existía</p></div>";
    }
    
    // Eliminar tabla karma_recompensas
    try {
        $conexion->exec("DROP TABLE IF EXISTS karma_recompensas");
        echo "<div class='success'><p>✓ Tabla <code>karma_recompensas</code> eliminada</p></div>";
    } catch (PDOException $e) {
        echo "<div class='info'><p>⊗ Tabla <code>karma_recompensas</code> no existía</p></div>";
    }
    
    // Eliminar tabla personalizacion_perfil si existe
    try {
        $conexion->exec("DROP TABLE IF EXISTS personalizacion_perfil");
        echo "<div class='success'><p>✓ Tabla <code>personalizacion_perfil</code> eliminada</p></div>";
    } catch (PDOException $e) {
        echo "<div class='info'><p>⊗ Tabla <code>personalizacion_perfil</code> no existía</p></div>";
    }
    
    echo "<div class='success'>";
    echo "<h2>✅ Base de datos limpiada correctamente</h2>";
    echo "<p>Ahora puedes ejecutar el setup de nuevo.</p>";
    echo "</div>";
    
    echo "<div style='text-align: center;'>";
    echo "<a href='setup_personalizacion_usuarios.php' class='btn'>🚀 Ejecutar Setup de Nuevo</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px;'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>
</body>
</html>";
?>
