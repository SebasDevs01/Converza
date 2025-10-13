-- Agregar UNIQUE constraint para prevenir conexiones duplicadas
-- Ejecutar en phpMyAdmin

USE converza;

-- Primero eliminar duplicados existentes
DELETE t1 FROM conexiones_misticas t1
INNER JOIN conexiones_misticas t2 
WHERE t1.id > t2.id 
AND t1.usuario1_id = t2.usuario1_id 
AND t1.usuario2_id = t2.usuario2_id 
AND t1.tipo_conexion = t2.tipo_conexion;

-- Agregar índice único para prevenir futuros duplicados
ALTER TABLE conexiones_misticas 
ADD UNIQUE KEY unique_conexion (usuario1_id, usuario2_id, tipo_conexion);
