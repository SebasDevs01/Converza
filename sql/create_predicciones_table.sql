-- ═══════════════════════════════════════════════════════════════
-- 📊 TABLA: predicciones_usuarios
-- Sistema de predicciones divertidas sobre gustos/intereses
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS predicciones_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria VARCHAR(50) NOT NULL, -- 'musica', 'comida', 'hobbies', 'viajes', 'personalidad'
    prediccion TEXT NOT NULL, -- Texto de la predicción
    emoji VARCHAR(10), -- Emoji representativo
    confianza ENUM('baja', 'media', 'alta') DEFAULT 'media',
    fecha_generada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    visto TINYINT(1) DEFAULT 0,
    me_gusta TINYINT(1) DEFAULT NULL, -- NULL = no valorado, 1 = sí, 0 = no
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_generada),
    INDEX idx_visto (visto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice compuesto para búsquedas rápidas
CREATE INDEX idx_usuario_visto ON predicciones_usuarios(usuario_id, visto);
