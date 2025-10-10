-- Corregir la ortografía del ENUM en la tabla reacciones
-- Cambiar 'me_entristese' por 'me_entristece' (agregar la 'c')

USE converza;

-- Actualizar todas las reacciones existentes con el valor mal escrito
UPDATE reacciones SET tipo_reaccion = 'me_entristece' WHERE tipo_reaccion = 'me_entristese';

-- Modificar la estructura de la tabla para corregir el ENUM
ALTER TABLE reacciones 
MODIFY COLUMN tipo_reaccion ENUM('me_gusta','me_encanta','me_divierte','me_asombra','me_entristece','me_enoja') NOT NULL;

-- Verificar que el cambio se aplicó correctamente
SHOW COLUMNS FROM reacciones LIKE 'tipo_reaccion';

-- Mostrar registros actualizados
SELECT * FROM reacciones WHERE tipo_reaccion = 'me_entristece';