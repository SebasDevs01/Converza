-- Tabla para reacciones de mensajes de chat
CREATE TABLE IF NOT EXISTS chat_reacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensaje_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_reaccion VARCHAR(10) NOT NULL, -- emoji de la reacción
    fecha_reaccion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Claves foráneas
    FOREIGN KEY (mensaje_id) REFERENCES chats(id_cha) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    
    -- Índices para optimizar consultas
    INDEX idx_mensaje (mensaje_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_reaccion (tipo_reaccion),
    
    -- Evitar múltiples reacciones del mismo usuario al mismo mensaje (solo una reacción por usuario)
    UNIQUE KEY unique_user_message (mensaje_id, usuario_id)
);

-- Modificar tabla chats para agregar soporte a mensajes de voz
ALTER TABLE chats 
ADD COLUMN tipo_mensaje ENUM('texto', 'voz') DEFAULT 'texto' AFTER mensaje,
ADD COLUMN archivo_audio VARCHAR(255) NULL AFTER tipo_mensaje,
ADD COLUMN duracion_audio INT NULL AFTER archivo_audio;