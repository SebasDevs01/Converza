-- Tabla para el sistema de notificaciones en tiempo real
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, -- Usuario que recibirá la notificación
    tipo VARCHAR(50) NOT NULL, -- Tipo de notificación
    mensaje TEXT NOT NULL, -- Mensaje de la notificación
    referencia_id INT NULL, -- ID de referencia (publicación, usuario, mensaje, etc.)
    referencia_tipo VARCHAR(50) NULL, -- Tipo de referencia (publicacion, usuario, mensaje, comentario, etc.)
    de_usuario_id INT NULL, -- Usuario que generó la notificación
    leida TINYINT(1) DEFAULT 0, -- Si fue leída o no
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_leida TIMESTAMP NULL,
    url_redireccion VARCHAR(255) NULL, -- URL para redirigir al hacer click
    
    -- Claves foráneas
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (de_usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    
    -- Índices para optimizar consultas
    INDEX idx_usuario_leida (usuario_id, leida),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_tipo (tipo),
    INDEX idx_de_usuario (de_usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tipos de notificaciones:
-- 'solicitud_amistad' - Alguien te envió solicitud de amistad
-- 'amistad_aceptada' - Tu solicitud de amistad fue aceptada
-- 'amistad_rechazada' - Tu solicitud de amistad fue rechazada
-- 'nuevo_seguidor' - Alguien comenzó a seguirte
-- 'solicitud_mensaje' - Alguien te envió solicitud de mensaje
-- 'mensaje_aceptado' - Tu solicitud de mensaje fue aceptada
-- 'mensaje_rechazado' - Tu solicitud de mensaje fue rechazada
-- 'nuevo_mensaje' - Recibiste un nuevo mensaje
-- 'nuevo_comentario' - Alguien comentó tu publicación
-- 'nueva_publicacion' - Un amigo/seguido publicó algo nuevo
-- 'reaccion_comentario' - Alguien reaccionó a tu comentario
-- 'mencion' - Alguien te mencionó en un comentario o publicación
