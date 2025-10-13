-- Tabla para solicitudes de mensaje (como TikTok)
CREATE TABLE IF NOT EXISTS solicitudes_mensaje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    de INT NOT NULL,
    para INT NOT NULL,
    estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
    primer_mensaje TEXT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    FOREIGN KEY (de) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (para) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    UNIQUE KEY unique_solicitud (de, para)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
