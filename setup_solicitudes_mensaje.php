<?php
/**
 * Script para configurar el sistema de solicitudes de mensaje
 * Ejecutar una sola vez para crear la tabla
 */

require_once __DIR__.'/app/models/config.php';

echo "<h2>Configurando sistema de solicitudes de mensaje...</h2>";

try {
    // Crear tabla solicitudes_mensaje
    $sql = "
    CREATE TABLE IF NOT EXISTS solicitudes_mensaje (
        id INT AUTO_INCREMENT PRIMARY KEY,
        de INT NOT NULL,
        para INT NOT NULL,
        estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
        primer_mensaje TEXT NULL,
        fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_respuesta TIMESTAMP NULL,
        FOREIGN KEY (de) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        FOREIGN KEY (para) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        UNIQUE KEY unique_solicitud (de, para),
        KEY idx_para_estado (para, estado),
        KEY idx_fecha (fecha_solicitud)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conexion->exec($sql);
    echo "<p style='color: green;'>✅ Tabla 'solicitudes_mensaje' creada correctamente</p>";
    
    // Verificar que la tabla existe
    $stmt = $conexion->query("SHOW TABLES LIKE 'solicitudes_mensaje'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabla verificada correctamente</p>";
        
        // Mostrar estructura
        echo "<h3>Estructura de la tabla:</h3>";
        $stmt = $conexion->query("DESCRIBE solicitudes_mensaje");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Sistema de solicitudes de mensaje configurado exitosamente</h3>";
    echo "<p><strong>Funcionalidades implementadas:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Chat libre entre amigos confirmados</li>";
    echo "<li>✅ Chat libre entre seguidores mutuos (NUEVO)</li>";
    echo "<li>✅ Solicitud de mensaje para usuarios sin relación (NUEVO - estilo TikTok)</li>";
    echo "<li>✅ Sistema de aceptar/rechazar solicitudes de mensaje</li>";
    echo "</ul>";
    
    echo "<p><a href='/Converza/app/view/index.php'>← Volver al inicio</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
