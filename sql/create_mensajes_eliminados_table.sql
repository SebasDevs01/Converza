-- Crear tabla para mensajes eliminados por usuario (sistema tipo WhatsApp)
CREATE TABLE IF NOT EXISTS mensajes_eliminados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensaje_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_eliminacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_mensaje (mensaje_id),
    INDEX idx_usuario (usuario_id),
    
    -- Evitar duplicados: un usuario solo puede eliminar un mensaje una vez
    UNIQUE KEY unique_user_message (mensaje_id, usuario_id),
    
    -- Claves foráneas
    FOREIGN KEY (mensaje_id) REFERENCES chats(id_cha) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
);