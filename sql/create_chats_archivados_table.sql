-- Tabla para almacenar conversaciones archivadas
CREATE TABLE IF NOT EXISTS chats_archivados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    chat_con_usuario_id INT NOT NULL,
    fecha_archivado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (chat_con_usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    UNIQUE KEY unique_archivo (usuario_id, chat_con_usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
