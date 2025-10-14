-- =====================================================
-- Migración: Agregar campos de personalización a usuarios
-- =====================================================
-- Este script agrega campos de personalización y gamificación
-- a la tabla usuarios existente sin crear tablas nuevas

USE converza;

-- Agregar campos de personalización de perfil
ALTER TABLE usuarios
ADD COLUMN bio TEXT COMMENT 'Biografía personal del usuario',
ADD COLUMN descripcion_corta VARCHAR(255) COMMENT 'Descripción breve mostrada en tarjetas',
ADD COLUMN signo_zodiacal ENUM(
    'aries', 'tauro', 'geminis', 'cancer', 
    'leo', 'virgo', 'libra', 'escorpio',
    'sagitario', 'capricornio', 'acuario', 'piscis'
) COMMENT 'Signo zodiacal del usuario',
ADD COLUMN genero ENUM('masculino', 'femenino', 'otro', 'prefiero_no_decir') COMMENT 'Género del usuario',
ADD COLUMN mostrar_icono_genero BOOLEAN DEFAULT TRUE COMMENT 'Mostrar ícono de género (♂/♀)',
ADD COLUMN estado_animo ENUM(
    'feliz', 'emocionado', 'relajado', 'creativo',
    'cansado', 'ocupado', 'triste', 'enojado',
    'motivado', 'inspirado', 'pensativo', 'nostalgico'
) COMMENT 'Estado de ánimo actual del usuario';

-- Agregar campos de personalización visual
ALTER TABLE usuarios
ADD COLUMN tema_perfil VARCHAR(50) DEFAULT 'default' COMMENT 'Tema visual del perfil',
ADD COLUMN color_principal VARCHAR(7) DEFAULT '#667eea' COMMENT 'Color principal del perfil (hex)',
ADD COLUMN icono_personalizado VARCHAR(100) COMMENT 'Ícono especial desbloqueado',
ADD COLUMN marco_avatar VARCHAR(100) COMMENT 'Marco decorativo del avatar',
ADD COLUMN insignia_especial VARCHAR(100) COMMENT 'Insignia de logro especial';

-- Agregar campos de privacidad
ALTER TABLE usuarios
ADD COLUMN mostrar_karma BOOLEAN DEFAULT TRUE COMMENT 'Mostrar puntos de karma públicamente',
ADD COLUMN mostrar_signo BOOLEAN DEFAULT TRUE COMMENT 'Mostrar signo zodiacal',
ADD COLUMN mostrar_estado_animo BOOLEAN DEFAULT TRUE COMMENT 'Mostrar estado de ánimo actual';

-- =====================================================
-- Tabla de recompensas desbloqueables (catálogo)
-- =====================================================
CREATE TABLE IF NOT EXISTS karma_recompensas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('tema', 'marco', 'insignia', 'icono', 'color', 'sticker') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    karma_requerido INT NOT NULL DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_karma_requerido (karma_requerido),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de recompensas desbloqueadas por usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS usuario_recompensas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    recompensa_id INT NOT NULL,
    fecha_desbloqueo DATETIME DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT FALSE COMMENT 'Si está equipada actualmente',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (recompensa_id) REFERENCES karma_recompensas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_recompensa (usuario_id, recompensa_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insertar recompensas predefinidas
-- =====================================================

-- TEMAS DE PERFIL (4 temas)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, karma_requerido) VALUES
('tema', 'Tema Oscuro Premium', 'Tema oscuro elegante con gradientes morados', 50),
('tema', 'Tema Galaxy', 'Tema espacial con estrellas y nebulosas', 100),
('tema', 'Tema Sunset', 'Colores cálidos de atardecer', 150),
('tema', 'Tema Neon', 'Estilo cyberpunk con colores neón', 200);

-- MARCOS DE AVATAR (5 marcos)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, karma_requerido) VALUES
('marco', 'Marco Dorado', 'Marco dorado brillante para tu avatar', 30),
('marco', 'Marco Diamante', 'Marco de diamante con destellos', 100),
('marco', 'Marco de Fuego', 'Llamas animadas alrededor del avatar', 150),
('marco', 'Marco Arcoíris', 'Gradiente multicolor vibrante', 200),
('marco', 'Marco Legendario', 'Aura legendaria con partículas doradas', 500);

-- INSIGNIAS (6 insignias)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, karma_requerido) VALUES
('insignia', 'Insignia Novato', 'Primera insignia de tu camino', 10),
('insignia', 'Insignia Intermedio', 'Has demostrado ser constante', 50),
('insignia', 'Insignia Avanzado', 'Eres un miembro activo destacado', 100),
('insignia', 'Insignia Experto', 'Tu influencia es notable', 250),
('insignia', 'Insignia Maestro', 'Has alcanzado la maestría', 500),
('insignia', 'Insignia Legendario', 'Leyenda viviente de Converza', 1000);

-- ÍCONOS ESPECIALES (4 íconos)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, karma_requerido) VALUES
('icono', 'Estrella Dorada', 'Estrella dorada brillante', 75),
('icono', 'Corona Imperial', 'Corona de oro imperial', 200),
('icono', 'Rayo Eléctrico', 'Símbolo de energía', 150),
('icono', 'Corazón Brillante', 'Corazón con destellos', 100);

-- COLORES PREMIUM (4 colores)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, karma_requerido) VALUES
('color', 'Púrpura Real', 'Color púrpura real #7C3AED', 60),
('color', 'Oro Premium', 'Dorado premium #F59E0B', 120),
('color', 'Esmeralda', 'Verde esmeralda #10B981', 90),
('color', 'Rosa Neón', 'Rosa vibrante #EC4899', 80);

-- STICKERS ESPECIALES (5 stickers - BONUS)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, karma_requerido) VALUES
('sticker', 'Sticker Fuego', 'Emoji de fuego animado 🔥', 25),
('sticker', 'Sticker Estrella', 'Estrella brillante ✨', 35),
('sticker', 'Sticker Cohete', 'Cohete espacial 🚀', 45),
('sticker', 'Sticker Confeti', 'Explosión de confeti 🎉', 55),
('sticker', 'Sticker Unicornio', 'Unicornio mágico 🦄', 100);
