-- ============================================
-- TABLA: usuario_recompensas
-- Sistema de Karma - Recompensas Desbloqueadas
-- ============================================

CREATE TABLE IF NOT EXISTS usuario_recompensas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    recompensa_id INT NOT NULL,
    equipada TINYINT(1) DEFAULT 0,
    fecha_desbloqueo DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    UNIQUE KEY unique_usuario_recompensa (usuario_id, recompensa_id),
    KEY idx_usuario (usuario_id),
    KEY idx_recompensa (recompensa_id),
    KEY idx_equipada (equipada),
    
    -- Relaciones
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (recompensa_id) REFERENCES karma_recompensas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COMENTARIOS
-- ============================================
-- usuario_id: ID del usuario que desbloqueó la recompensa
-- recompensa_id: ID de la recompensa desbloqueada
-- equipada: Si el usuario tiene la recompensa actualmente equipada
-- fecha_desbloqueo: Cuándo se desbloqueó
