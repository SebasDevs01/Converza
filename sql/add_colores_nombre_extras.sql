-- ============================================
-- COLORES DE NOMBRE ADICIONALES
-- Agrega los 4 nuevos colores mostrados en el demo
-- ============================================

-- COLORES DE NOMBRE EXTRAS
INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) VALUES
('color_nombre', 'Púrpura Real', 'Color púrpura real #7C3AED', 60, 'color-purpura.png', 'colores'),
('color_nombre', 'Rosa Neón', 'Rosa vibrante #EC4899', 80, 'color-rosa-neon.png', 'colores'),
('color_nombre', 'Esmeralda', 'Verde esmeralda #10B981', 90, 'color-esmeralda.png', 'colores'),
('color_nombre', 'Oro Premium', 'Dorado premium #F59E0B', 120, 'color-oro-premium.png', 'colores');

COMMIT;
