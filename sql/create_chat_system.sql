-- Crear tabla chats si no existe
CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario1 INT NOT NULL,
    usuario2 INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Evitar chats duplicados
    UNIQUE KEY unique_chat (usuario1, usuario2),
    
    -- Índices para optimizar consultas
    INDEX idx_usuario1 (usuario1),
    INDEX idx_usuario2 (usuario2),
    
    -- Claves foráneas (opcional, depende de tu estructura)
    FOREIGN KEY (usuario1) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario2) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla mensajes si no existe
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    usuario_id INT NOT NULL,
    contenido TEXT NOT NULL,
    tipo ENUM('text', 'voice', 'image') DEFAULT 'text',
    eliminado TINYINT(1) DEFAULT 0,
    leido TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_chat (chat_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (created_at),
    
    -- Claves foráneas
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla reacciones si no existe
CREATE TABLE IF NOT EXISTS chat_reacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensaje_id INT NOT NULL,
    usuario_id INT NOT NULL,
    reaccion VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Evitar reacciones duplicadas del mismo usuario al mismo mensaje
    UNIQUE KEY unique_user_reaction (mensaje_id, usuario_id),
    
    -- Índices
    INDEX idx_mensaje (mensaje_id),
    INDEX idx_usuario (usuario_id),
    
    -- Claves foráneas
    FOREIGN KEY (mensaje_id) REFERENCES mensajes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);