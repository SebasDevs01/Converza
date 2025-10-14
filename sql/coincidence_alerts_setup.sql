-- ==========================================
-- INSTALADOR: Sistema de Conexiones
-- Ejecutar en phpMyAdmin o desde línea de comandos
-- ==========================================

-- 1. TABLA PARA COINCIDENCE ALERTS
CREATE TABLE IF NOT EXISTS coincidence_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    usuario_coincidente_id INT NOT NULL,
    compatibilidad INT NOT NULL,
    razon TEXT,
    leida BOOLEAN DEFAULT FALSE,
    fecha_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_fecha (usuario_id, fecha_alerta),
    INDEX idx_leida (leida),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (usuario_coincidente_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. AGREGAR COLUMNA ultima_actividad SI NO EXISTE
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 3. AGREGAR ÍNDICE PARA OPTIMIZAR CONSULTAS
CREATE INDEX IF NOT EXISTS idx_ultima_actividad ON usuarios(ultima_actividad);

-- 4. TABLA PARA CONTADOR DE CONEXIONES MÍSTICAS (cache)
CREATE TABLE IF NOT EXISTS conexiones_misticas_contador (
    usuario_id INT PRIMARY KEY,
    total_conexiones INT DEFAULT 0,
    nuevas_conexiones INT DEFAULT 0,
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ACTUALIZAR ultima_actividad DE TODOS LOS USUARIOS
UPDATE usuarios 
SET ultima_actividad = NOW() 
WHERE ultima_actividad IS NULL OR ultima_actividad = '0000-00-00 00:00:00';

-- ==========================================
-- INSTALACIÓN COMPLETADA
-- ==========================================
SELECT 
    'TABLAS CREADAS:' as mensaje,
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME = 'coincidence_alerts' AND TABLE_SCHEMA = DATABASE()) as coincidence_alerts,
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME = 'conexiones_misticas_contador' AND TABLE_SCHEMA = DATABASE()) as contador,
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'ultima_actividad' AND TABLE_SCHEMA = DATABASE()) as columna_actividad;

