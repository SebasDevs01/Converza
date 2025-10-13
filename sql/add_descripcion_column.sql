-- Script SQL para agregar la columna 'descripcion' a la tabla usuarios
-- Ejecuta esto si quieres que los usuarios puedan tener biografías/descripciones

-- Agregar columna descripcion (biografía del usuario)
ALTER TABLE usuarios 
ADD COLUMN descripcion TEXT NULL 
AFTER sexo;

-- Actualizar descripción de usuarios existentes (opcional)
UPDATE usuarios 
SET descripcion = CONCAT('¡Hola! Soy ', nombre, ' y me encanta conectar con nuevas personas en Converza.')
WHERE descripcion IS NULL;
