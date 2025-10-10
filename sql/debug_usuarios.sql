-- Consultas SQL para verificar y reparar la tabla usuarios

-- 1. Verificar estructura de la tabla
DESCRIBE usuarios;

-- 2. Ver todos los usuarios actuales
SELECT * FROM usuarios;

-- 3. Verificar si existe la columna 'tipo'
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'converza' AND TABLE_NAME = 'usuarios';

-- 4. Si la columna 'tipo' no existe, crearla:
-- ALTER TABLE usuarios ADD COLUMN tipo ENUM('admin', 'user', 'blocked') DEFAULT 'user';

-- 5. Si existe pero no tiene los valores correctos, modificarla:
-- ALTER TABLE usuarios MODIFY COLUMN tipo ENUM('admin', 'user', 'blocked') DEFAULT 'user';

-- 6. Actualizar usuarios existentes que no tengan tipo:
-- UPDATE usuarios SET tipo = 'user' WHERE tipo IS NULL OR tipo = '';

-- 7. Crear un usuario admin si no existe:
-- UPDATE usuarios SET tipo = 'admin' WHERE usuario = 'admin1' LIMIT 1;

-- 8. Prueba manual de bloqueo (reemplaza 2 por el ID real):
-- UPDATE usuarios SET tipo = 'blocked' WHERE id_use = 2;

-- 9. Verificar el cambio:
-- SELECT id_use, usuario, tipo FROM usuarios WHERE id_use = 2;

-- 10. Desbloquear para pruebas:
-- UPDATE usuarios SET tipo = 'user' WHERE id_use = 2;