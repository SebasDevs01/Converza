-- Tabla para el sistema de chat/mensajería
CREATE TABLE IF NOT EXISTS chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emisor INT NOT NULL,
    receptor INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido TINYINT(1) DEFAULT 0,
    
    -- Claves foráneas
    FOREIGN KEY (emisor) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (receptor) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    
    -- Índices para optimizar consultas
    INDEX idx_emisor (emisor),
    INDEX idx_receptor (receptor),
    INDEX idx_fecha (fecha),
    INDEX idx_leido (leido),
    INDEX idx_conversacion (emisor, receptor, fecha)
);