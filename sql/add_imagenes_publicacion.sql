-- Tabla para asociar varias imágenes a una publicación
CREATE TABLE IF NOT EXISTS imagenes_publicacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publicacion_id INT NOT NULL,
    nombre_imagen VARCHAR(255) NOT NULL,
    FOREIGN KEY (publicacion_id) REFERENCES publicaciones(id_pub) ON DELETE CASCADE
);
