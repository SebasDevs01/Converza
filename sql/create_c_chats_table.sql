-- Tabla para conversaciones (c_chats)
CREATE TABLE IF NOT EXISTS c_chats (
    id_cch INT AUTO_INCREMENT PRIMARY KEY,
    de INT NOT NULL,
    para INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (de) REFERENCES usuarios(id_use),
    FOREIGN KEY (para) REFERENCES usuarios(id_use),
    UNIQUE KEY unique_conversation (de, para)
);