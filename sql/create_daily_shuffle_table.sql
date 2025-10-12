-- Tabla para almacenar el Daily Shuffle de usuarios
CREATE TABLE IF NOT EXISTS daily_shuffle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    usuario_mostrado_id INT NOT NULL,
    fecha_shuffle DATE NOT NULL,
    ya_contactado BOOLEAN DEFAULT FALSE,
    fecha_contacto TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use),
    FOREIGN KEY (usuario_mostrado_id) REFERENCES usuarios(id_use),
    UNIQUE KEY unique_daily_pair (usuario_id, usuario_mostrado_id, fecha_shuffle),
    INDEX idx_usuario_fecha (usuario_id, fecha_shuffle),
    INDEX idx_fecha_shuffle (fecha_shuffle)
);