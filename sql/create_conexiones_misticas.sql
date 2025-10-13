-- ==================================================
-- CONEXIONES MÍSTICAS - Sistema de Serendipia Digital
-- Detecta patrones y coincidencias curiosas entre usuarios
-- ==================================================

USE converza;

-- Tabla principal de conexiones místicas
CREATE TABLE IF NOT EXISTS conexiones_misticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario1_id INT NOT NULL,
    usuario2_id INT NOT NULL,
    tipo_conexion VARCHAR(50) NOT NULL,
    descripcion TEXT,
    puntuacion INT DEFAULT 0,
    fecha_deteccion DATETIME DEFAULT CURRENT_TIMESTAMP,
    visto_usuario1 TINYINT(1) DEFAULT 0,
    visto_usuario2 TINYINT(1) DEFAULT 0,
    
    FOREIGN KEY (usuario1_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (usuario2_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    
    INDEX idx_usuario1 (usuario1_id),
    INDEX idx_usuario2 (usuario2_id),
    INDEX idx_tipo (tipo_conexion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
