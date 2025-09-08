-- Base de datos relacional para Converza (moderna, completa y lista para phpMyAdmin)
-- Incluye usuarios (admin y user), publicaciones, comentarios, likes, álbumes, fotos, amigos, mensajes, notificaciones

CREATE DATABASE IF NOT EXISTS converza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE converza;

-- Usuarios
CREATE TABLE usuarios (
    id_use INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    fecha_reg DATETIME DEFAULT CURRENT_TIMESTAMP,
    avatar VARCHAR(100) DEFAULT 'defect.jpg',
    sexo VARCHAR(10),
    tipo ENUM('user','admin') DEFAULT 'user',
    verificado TINYINT(1) DEFAULT 0
);

-- Publicaciones
CREATE TABLE publicaciones (
    id_pub INT AUTO_INCREMENT PRIMARY KEY,
    usuario INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    contenido TEXT,
    imagen INT,
    album INT,
    comentarios INT DEFAULT 0,
    likes INT DEFAULT 0,
    FOREIGN KEY (usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE
);

-- Comentarios
CREATE TABLE comentarios (
    id_com INT AUTO_INCREMENT PRIMARY KEY,
    usuario INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    publicacion INT NOT NULL,
    FOREIGN KEY (usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (publicacion) REFERENCES publicaciones(id_pub) ON DELETE CASCADE
);

-- Likes
CREATE TABLE likes (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    usuario INT NOT NULL,
    post INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (post) REFERENCES publicaciones(id_pub) ON DELETE CASCADE
);

-- Álbumes
CREATE TABLE albumes (
    id_alb INT AUTO_INCREMENT PRIMARY KEY,
    usuario INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    nombre VARCHAR(100) NOT NULL,
    FOREIGN KEY (usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE
);

-- Fotos
CREATE TABLE fotos (
    id_fot INT AUTO_INCREMENT PRIMARY KEY,
    usuario INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    ruta VARCHAR(255) NOT NULL,
    album INT,
    publicacion INT,
    FOREIGN KEY (usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (album) REFERENCES albumes(id_alb) ON DELETE SET NULL,
    FOREIGN KEY (publicacion) REFERENCES publicaciones(id_pub) ON DELETE SET NULL
);

-- Amigos / Solicitudes
CREATE TABLE amigos (
    id_ami INT AUTO_INCREMENT PRIMARY KEY,
    de INT NOT NULL,
    para INT NOT NULL,
    estado TINYINT(1) DEFAULT 0, -- 0: pendiente, 1: aceptado
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (de) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (para) REFERENCES usuarios(id_use) ON DELETE CASCADE
);

-- Mensajes (chats)
CREATE TABLE c_chats (
    id_cch INT AUTO_INCREMENT PRIMARY KEY,
    de INT NOT NULL,
    para INT NOT NULL,
    FOREIGN KEY (de) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (para) REFERENCES usuarios(id_use) ON DELETE CASCADE
);

CREATE TABLE chats (
    id_cha INT AUTO_INCREMENT PRIMARY KEY,
    id_cch INT NOT NULL,
    de INT NOT NULL,
    para INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_cch) REFERENCES c_chats(id_cch) ON DELETE CASCADE,
    FOREIGN KEY (de) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (para) REFERENCES usuarios(id_use) ON DELETE CASCADE
);

-- Notificaciones
CREATE TABLE notificaciones (
    id_not INT AUTO_INCREMENT PRIMARY KEY,
    user1 INT NOT NULL,
    user2 INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_pub INT,
    FOREIGN KEY (user1) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (user2) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (id_pub) REFERENCES publicaciones(id_pub) ON DELETE SET NULL
);

-- Panel de administración: gestionable desde la tabla usuarios (tipo = 'admin')
