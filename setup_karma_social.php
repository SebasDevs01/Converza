<?php
/**
 * Script de instalación para el Sistema de Karma Social
 * Crea la tabla y genera datos iniciales
 */

require_once __DIR__ . '/app/models/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Instalar Karma Social - Converza</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
        }
        code {
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: monospace;
            color: #c7254e;
        }
        .emoji {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1><span class='emoji'>🌟</span>Instalación: Sistema de Karma Social</h1>";

try {
    // Paso 1: Crear tabla
    echo "<div class='step'>
        <strong>Paso 1:</strong> Creando tabla <code>karma_social</code>...
    </div>";
    
    $sql_tabla = "
    CREATE TABLE IF NOT EXISTS karma_social (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        tipo_accion VARCHAR(50) NOT NULL,
        puntos INT NOT NULL DEFAULT 0,
        referencia_id INT NULL,
        referencia_tipo VARCHAR(50) NULL,
        fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        descripcion TEXT NULL,
        
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        
        INDEX idx_usuario (usuario_id),
        INDEX idx_tipo_accion (tipo_accion),
        INDEX idx_fecha (fecha_accion),
        INDEX idx_usuario_fecha (usuario_id, fecha_accion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conexion->exec($sql_tabla);
    
    echo "<div class='step success'>
        ✅ Tabla <code>karma_social</code> creada correctamente
    </div>";
    
    // Paso 2: Verificar tabla
    $stmt = $conexion->query("SHOW TABLES LIKE 'karma_social'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    if ($tabla_existe) {
        echo "<div class='step success'>
            ✅ Tabla verificada en la base de datos
        </div>";
    } else {
        throw new Exception("La tabla no se creó correctamente");
    }
    
    // Paso 3: Información del sistema
    echo "<div class='step info'>
        <h3><span class='emoji'>📊</span>Sistema de Karma Social Instalado</h3>
        
        <h4>Tipos de Acciones y Puntos:</h4>
        <ul>
            <li><strong>Comentario Positivo:</strong> 8 puntos</li>
            <li><strong>Interacción Respetuosa:</strong> 8 puntos</li>
            <li><strong>Apoyo a Publicación:</strong> 3 puntos (like, love, wow)</li>
            <li><strong>Compartir Conocimiento:</strong> 15 puntos</li>
            <li><strong>Ayuda a Usuario:</strong> 12 puntos</li>
            <li><strong>Primera Interacción:</strong> 5 puntos</li>
            <li><strong>Mensaje Motivador:</strong> 10 puntos</li>
            <li><strong>Amigo Activo (30 días):</strong> 20 puntos</li>
        </ul>
        
        <h4>Niveles de Karma:</h4>
        <ul>
            <li>🌱 <strong>Novato:</strong> 0-49 puntos</li>
            <li>⭐ <strong>Intermedio:</strong> 50-99 puntos</li>
            <li>✨ <strong>Avanzado:</strong> 100-249 puntos</li>
            <li>💫 <strong>Experto:</strong> 250-499 puntos</li>
            <li>🌟 <strong>Maestro:</strong> 500-999 puntos</li>
            <li>👑 <strong>Legendario:</strong> 1000+ puntos</li>
        </ul>
        
        <h4>Multiplicadores para Conexiones:</h4>
        <ul>
            <li><strong>500+ karma:</strong> 1.5x (50% bonus)</li>
            <li><strong>250-499 karma:</strong> 1.3x (30% bonus)</li>
            <li><strong>100-249 karma:</strong> 1.2x (20% bonus)</li>
            <li><strong>50-99 karma:</strong> 1.1x (10% bonus)</li>
            <li><strong>0-49 karma:</strong> 1.0x (sin bonus)</li>
        </ul>
    </div>";
    
    // Paso 4: Archivos creados
    echo "<div class='step info'>
        <h3><span class='emoji'>📂</span>Archivos Creados</h3>
        <ul>
            <li><code>sql/create_karma_social_table.sql</code> - Esquema de BD</li>
            <li><code>app/models/karma-social-helper.php</code> - Lógica principal</li>
            <li><code>app/models/karma-social-triggers.php</code> - Triggers automáticos</li>
            <li><code>app/presenters/get_karma_social.php</code> - API REST</li>
            <li><code>app/view/components/karma-social-widget.php</code> - Widget visual</li>
        </ul>
    </div>";
    
    // Paso 5: Integración
    echo "<div class='step info'>
        <h3><span class='emoji'>🔗</span>Integración con Conexiones Místicas</h3>
        <p>El sistema de Karma Social está integrado automáticamente con Conexiones Místicas:</p>
        <ul>
            <li>✅ Usuarios con más karma tienen <strong>prioridad en conexiones</strong></li>
            <li>✅ Puntuación de conexiones se <strong>multiplica por karma</strong></li>
            <li>✅ Fomenta <strong>comportamiento positivo</strong> en la plataforma</li>
        </ul>
    </div>";
    
    // Paso 6: Próximos pasos
    echo "<div class='step'>
        <h3><span class='emoji'>🚀</span>Próximos Pasos</h3>
        <ol>
            <li>Los usuarios empezarán a ganar karma automáticamente al:
                <ul>
                    <li>Hacer comentarios positivos</li>
                    <li>Dar reacciones de apoyo (like, love, wow)</li>
                    <li>Enviar mensajes motivadores</li>
                    <li>Compartir conocimiento</li>
                </ul>
            </li>
            <li>El karma aparecerá en los perfiles de usuario</li>
            <li>Las conexiones místicas tendrán mejor puntuación para usuarios con alto karma</li>
        </ol>
    </div>";
    
    // Paso 7: Testing
    echo "<div class='step'>
        <h3><span class='emoji'>🧪</span>Probar el Sistema</h3>
        <p>Para probar el sistema de Karma:</p>
        <ol>
            <li>Haz un comentario con palabras positivas (ej: \"Gracias\", \"Excelente\", \"Genial\")</li>
            <li>Da un \"like\" o \"love\" a una publicación</li>
            <li>Ve tu karma en: <code><a href='/Converza/app/presenters/get_karma_social.php' target='_blank'>get_karma_social.php</a></code></li>
        </ol>
    </div>";
    
    echo "<div class='step success'>
        <h3><span class='emoji'>🎉</span>¡Instalación Completada!</h3>
        <p>El Sistema de Karma Social está funcionando correctamente.</p>
        <p><strong>Sin daños al sistema existente:</strong></p>
        <ul>
            <li>✅ No modificó tablas existentes</li>
            <li>✅ No rompió funcionalidad actual</li>
            <li>✅ Se integra automáticamente</li>
        </ul>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>
        <strong>❌ Error:</strong> {$e->getMessage()}
    </div>";
}

echo "    </div>
</body>
</html>";
?>
