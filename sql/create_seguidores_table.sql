-- Tabla para el sistema de seguimiento de usuarios
CREATE TABLE IF NOT EXISTS seguidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seguidor_id INT NOT NULL,
    seguido_id INT NOT NULL,
    fecha_seguimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Claves foráneas
    FOREIGN KEY (seguidor_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (seguido_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    
    -- Índices para optimizar consultas
    INDEX idx_seguidor (seguidor_id),
    INDEX idx_seguido (seguido_id),
    INDEX idx_seguimiento (seguidor_id, seguido_id),
    
    -- Evitar que un usuario se siga a sí mismo o siga al mismo usuario dos veces
    UNIQUE KEY unique_seguimiento (seguidor_id, seguido_id),
    CONSTRAINT chk_no_auto_follow CHECK (seguidor_id != seguido_id)
);