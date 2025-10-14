-- ============================================
-- SISTEMA DE PERSONALIZACIÓN COMPLETA
-- Añade columnas para íconos, colores de nombre y stickers
-- ============================================

-- Agregar columna para ícono especial junto al nombre
ALTER TABLE usuarios 
ADD COLUMN icono_especial VARCHAR(50) DEFAULT NULL 
COMMENT 'Ícono especial junto al nombre (estrella, corona, fuego, corazon, rayo, diamante)';

-- Agregar columna para color de nombre personalizado
ALTER TABLE usuarios 
ADD COLUMN color_nombre VARCHAR(50) DEFAULT NULL 
COMMENT 'Clase CSS para color de nombre (dorado, arcoiris, fuego, oceano, neon-cyan, neon-rosa, galaxia)';

-- Agregar columna para stickers/estados de ánimo
ALTER TABLE usuarios 
ADD COLUMN stickers_activos TEXT DEFAULT NULL 
COMMENT 'JSON con stickers activos del usuario';

-- Actualizar tabla karma_recompensas con nuevas recompensas

-- ÍCONOS ESPECIALES (desbloqueables con karma)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) VALUES
('icono', 'Ícono Estrella ⭐', 'Ícono dorado brillante junto a tu nombre', 80, 'icono-estrella.png', 'iconos'),
('icono', 'Ícono Corona 👑', 'Corona real flotante junto a tu nombre', 150, 'icono-corona.png', 'iconos'),
('icono', 'Ícono Fuego 🔥', 'Llamas ardientes junto a tu nombre', 200, 'icono-fuego.png', 'iconos'),
('icono', 'Ícono Corazón 💖', 'Corazón pulsante junto a tu nombre', 120, 'icono-corazon.png', 'iconos'),
('icono', 'Ícono Rayo ⚡', 'Rayo eléctrico junto a tu nombre', 180, 'icono-rayo.png', 'iconos'),
('icono', 'Ícono Diamante 💎', 'Diamante brillante junto a tu nombre', 300, 'icono-diamante.png', 'iconos');

-- COLORES DE NOMBRE (desbloqueables con karma)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) VALUES
('color_nombre', 'Nombre Dorado', 'Tu nombre en color dorado brillante animado', 100, 'color-dorado.png', 'colores'),
('color_nombre', 'Nombre Arcoíris', 'Tu nombre con efecto arcoíris rotativo', 200, 'color-arcoiris.png', 'colores'),
('color_nombre', 'Nombre Fuego', 'Tu nombre con efecto de fuego ardiente', 180, 'color-fuego.png', 'colores'),
('color_nombre', 'Nombre Océano', 'Tu nombre con efecto de olas oceánicas', 150, 'color-oceano.png', 'colores'),
('color_nombre', 'Nombre Neon Cyan', 'Tu nombre con efecto neón cian brillante', 220, 'color-neon-cyan.png', 'colores'),
('color_nombre', 'Nombre Neon Rosa', 'Tu nombre con efecto neón rosa intenso', 220, 'color-neon-rosa.png', 'colores'),
('color_nombre', 'Nombre Galaxia', 'Tu nombre con efecto galaxia púrpura', 250, 'color-galaxia.png', 'colores');

-- STICKERS / ESTADOS DE ÁNIMO (desbloqueables con karma)
INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) VALUES
('sticker', 'Pack Básico de Stickers', 'Stickers: Feliz 😊, Triste 😢, Emocionado 🤩', 50, 'sticker-basico.png', 'stickers'),
('sticker', 'Pack Premium de Stickers', 'Stickers: Relajado 😌, Motivado 💪, Creativo 🎨', 120, 'sticker-premium.png', 'stickers'),
('sticker', 'Pack Elite de Stickers', 'Stickers: Pensativo 🤔, Energético ⚡, Legendario 🔥', 200, 'sticker-elite.png', 'stickers');

-- Crear índices para mejorar rendimiento
CREATE INDEX idx_usuarios_icono ON usuarios(icono_especial);
CREATE INDEX idx_usuarios_color_nombre ON usuarios(color_nombre);

-- Verificar estructura actualizada
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'converza_db'
  AND TABLE_NAME = 'usuarios'
  AND COLUMN_NAME IN ('icono_especial', 'color_nombre', 'stickers_activos')
ORDER BY ORDINAL_POSITION;

COMMIT;
