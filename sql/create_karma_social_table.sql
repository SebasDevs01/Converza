-- Tabla para el Sistema de Karma Social
-- Registra las buenas acciones de los usuarios y su puntuación

CREATE TABLE IF NOT EXISTS karma_social (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_accion VARCHAR(50) NOT NULL,
    puntos INT NOT NULL DEFAULT 0,
    referencia_id INT NULL,
    referencia_tipo VARCHAR(50) NULL,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT NULL,
    
    -- Claves foráneas
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    
    -- Índices para optimizar consultas
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo_accion (tipo_accion),
    INDEX idx_fecha (fecha_accion),
    INDEX idx_usuario_fecha (usuario_id, fecha_accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tipos de acciones y puntos:
-- 'comentario_positivo' - Comentario con palabras positivas (5-10 pts según longitud)
-- 'interaccion_respetuosa' - Respuesta constructiva a otros usuarios (8 pts)
-- 'apoyo_publicacion' - Reacción positiva (like, love) (3 pts)
-- 'compartir_conocimiento' - Comentario educativo/útil (15 pts)
-- 'ayuda_usuario' - Responder preguntas de otros (12 pts)
-- 'primera_interaccion' - Primera interacción con nuevo usuario (5 pts)
-- 'mensaje_motivador' - Mensaje privado de apoyo (10 pts)
-- 'reaccion_constructiva' - Reacción diferente a angry/sad (3 pts)
-- 'sin_reportes' - Bonus mensual si no tiene reportes (50 pts)
-- 'amigo_activo' - Mantener amistad activa por 30 días (20 pts)

-- Vista para obtener karma total por usuario
CREATE OR REPLACE VIEW karma_total_usuarios AS
SELECT 
    usuario_id,
    SUM(puntos) as karma_total,
    COUNT(*) as acciones_totales,
    MAX(fecha_accion) as ultima_accion
FROM karma_social
GROUP BY usuario_id;

-- Vista para obtener karma de últimos 30 días
CREATE OR REPLACE VIEW karma_reciente_usuarios AS
SELECT 
    usuario_id,
    SUM(puntos) as karma_30dias,
    COUNT(*) as acciones_30dias
FROM karma_social
WHERE fecha_accion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY usuario_id;

-- Vista combinada con información de usuario
CREATE OR REPLACE VIEW usuarios_con_karma AS
SELECT 
    u.id_use,
    u.usuario,
    u.nombre,
    u.avatar,
    COALESCE(kt.karma_total, 0) as karma_total,
    COALESCE(kt.acciones_totales, 0) as acciones_totales,
    COALESCE(kr.karma_30dias, 0) as karma_reciente,
    COALESCE(kr.acciones_30dias, 0) as acciones_recientes,
    kt.ultima_accion
FROM usuarios u
LEFT JOIN karma_total_usuarios kt ON u.id_use = kt.usuario_id
LEFT JOIN karma_reciente_usuarios kr ON u.id_use = kr.usuario_id;
