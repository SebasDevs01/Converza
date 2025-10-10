-- SQL para limpiar columnas duplicadas en tabla chats
-- Ejecutar esto en phpMyAdmin para solucionar los duplicados

-- Primero, hacer un respaldo de la estructura actual
CREATE TABLE chats_backup AS SELECT * FROM chats;

-- Ver la estructura actual (para debug)
DESCRIBE chats;

-- Si hay columnas duplicadas, necesitaremos recrear la tabla limpiamente
-- IMPORTANTE: Solo ejecutar si realmente hay duplicados problemáticos

/*
-- Paso 1: Crear tabla temporal con estructura correcta
CREATE TABLE chats_temp (
    id_cha INT AUTO_INCREMENT PRIMARY KEY,
    id_cch INT NOT NULL,
    de INT NOT NULL,
    para INT NOT NULL,
    mensaje TEXT NOT NULL,
    tipo_mensaje ENUM('texto', 'voz') DEFAULT 'texto',
    archivo_audio VARCHAR(255) NULL,
    duracion_audio INT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido TINYINT DEFAULT 0
);

-- Paso 2: Copiar datos (ajustar según columnas existentes)
INSERT INTO chats_temp (id_cha, id_cch, de, para, mensaje, fecha, leido)
SELECT id_cha, id_cch, de, para, mensaje, fecha, leido FROM chats;

-- Paso 3: Eliminar tabla original y renombrar
DROP TABLE chats;
RENAME TABLE chats_temp TO chats;
*/

-- ALTERNATIVA MÁS SEGURA: Solo verificar estructura
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'chats' AND TABLE_SCHEMA = 'converza'
ORDER BY ORDINAL_POSITION;