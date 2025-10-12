<?php
require_once __DIR__.'/app/models/config.php';

try {
    echo "<h1>🚀 Configurando Sistema de Eliminación Estilo WhatsApp</h1>";
    
    $sql = "CREATE TABLE IF NOT EXISTS mensajes_eliminados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mensaje_id INT NOT NULL,
        usuario_id INT NOT NULL,
        fecha_eliminacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mensaje (mensaje_id),
        INDEX idx_usuario (usuario_id),
        UNIQUE KEY unique_user_message (mensaje_id, usuario_id),
        FOREIGN KEY (mensaje_id) REFERENCES chats(id_cha) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
    )";
    
    $conexion->exec($sql);
    
    echo "<p style='color: green; font-size: 18px;'>✅ Tabla 'mensajes_eliminados' creada exitosamente</p>";
    
    // Verificar también que la tabla de reacciones existe
    $sqlReacciones = "CREATE TABLE IF NOT EXISTS chat_reacciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mensaje_id INT NOT NULL,
        usuario_id INT NOT NULL,
        tipo_reaccion VARCHAR(10) NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_reaction (mensaje_id, usuario_id),
        FOREIGN KEY (mensaje_id) REFERENCES chats(id_cha) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
    )";
    
    $conexion->exec($sqlReacciones);
    
    echo "<p style='color: green; font-size: 18px;'>✅ Tabla 'chat_reacciones' verificada/creada exitosamente</p>";
    
    // Verificar que se creó
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'mensajes_eliminados'");
    $stmt->execute();
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "<p style='color: green;'>✅ Verificación: La tabla existe correctamente</p>";
        
        // Mostrar estructura
        echo "<h2>Estructura de la tabla:</h2>";
        $stmt2 = $conexion->prepare("DESCRIBE mensajes_eliminados");
        $stmt2->execute();
        $columns = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th><th>Extra</th></tr>";
        foreach($columns as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "<td>{$col['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>🎉 ¡Sistema Completo Instalado!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>✅ Características disponibles:</h3>";
        echo "<ul>";
        echo "<li>🎤 <strong>Mensajes de voz</strong> con grabación y reproducción</li>";
        echo "<li>😊 <strong>Sistema de reacciones</strong> (una por usuario)</li>";
        echo "<li>🗑️ <strong>Eliminación estilo WhatsApp:</strong></li>";
        echo "<ul>";
        echo "<li>• <strong>Eliminar para todos</strong>: Solo el remitente, máximo 30 minutos</li>";
        echo "<li>• <strong>Eliminar para mí</strong>: Cualquier usuario, en cualquier momento</li>";
        echo "</ul>";
        echo "<li>⏯️ <strong>Reproducción de audio</strong> con pausa/resume</li>";
        echo "<li>👥 <strong>Nombres de usuario</strong> y fechas en mensajes</li>";
        echo "</ul>";
        echo "</div>";
        echo "<div style='text-align: center; margin: 30px 0;'>";
        echo "<a href='app/presenters/chat.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-size: 18px;'>🚀 Probar Sistema de Chat</a>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Error: La tabla no se pudo crear</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    echo "<p>Intenta ejecutar manualmente este SQL en phpMyAdmin:</p>";
    echo "<pre>$sql</pre>";
}
?>