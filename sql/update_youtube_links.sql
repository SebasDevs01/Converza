-- Modificar la tabla publicaciones para incluir una columna para enlaces de YouTube
ALTER TABLE publicaciones ADD COLUMN youtube_link VARCHAR(255) DEFAULT NULL;