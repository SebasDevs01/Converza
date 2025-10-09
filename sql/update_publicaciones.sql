-- Modificar la tabla publicaciones para incluir una columna para videos
ALTER TABLE publicaciones ADD COLUMN video VARCHAR(255) DEFAULT NULL;

-- Modificar la tabla fotos para permitir videos si es necesario
ALTER TABLE fotos ADD COLUMN tipo ENUM('imagen', 'video') DEFAULT 'imagen';