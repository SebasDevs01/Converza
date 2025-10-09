-- Crear tabla de reacciones para el sistema mejorado
USE converza;

-- Crear tabla reacciones
CREATE TABLE IF NOT EXISTS reacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_publicacion INT NOT NULL,
    tipo_reaccion ENUM('like', 'love', 'laugh', 'wow', 'sad', 'angry') NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_pub) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post_reaction (id_usuario, id_publicacion)
);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_reacciones_publicacion ON reacciones(id_publicacion);
CREATE INDEX idx_reacciones_usuario ON reacciones(id_usuario);
CREATE INDEX idx_reacciones_tipo ON reacciones(tipo_reaccion);