-- Tabla para el sistema de bloqueos de usuarios
CREATE TABLE IF NOT EXISTS bloqueos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bloqueador_id INT NOT NULL,
    bloqueado_id INT NOT NULL,
    fecha_bloqueo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_bloqueador (bloqueador_id),
    INDEX idx_bloqueado (bloqueado_id),
    INDEX idx_relacion (bloqueador_id, bloqueado_id),
    
    -- Evitar bloqueos duplicados y auto-bloqueos
    UNIQUE KEY unique_bloqueo (bloqueador_id, bloqueado_id),
    CONSTRAINT chk_no_auto_block CHECK (bloqueador_id != bloqueado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Añadir foreign keys después (en caso de que ya existan datos)
ALTER TABLE bloqueos 
ADD CONSTRAINT fk_bloqueador 
FOREIGN KEY (bloqueador_id) REFERENCES usuarios(id_use) ON DELETE CASCADE;

ALTER TABLE bloqueos 
ADD CONSTRAINT fk_bloqueado 
FOREIGN KEY (bloqueado_id) REFERENCES usuarios(id_use) ON DELETE CASCADE;