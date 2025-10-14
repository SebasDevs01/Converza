-- ====================================================================
-- SCRIPT: Cambiar ENUM de reacciones de INGLÉS a ESPAÑOL
-- ====================================================================
-- Este script convierte los valores del ENUM tipo_reaccion de inglés
-- a español y actualiza todos los registros existentes.
-- ====================================================================

USE converza;

-- Paso 1: Actualizar registros existentes (inglés → español)
UPDATE reacciones SET 
    tipo_reaccion = CASE 
        WHEN tipo_reaccion = 'like' THEN 'me_gusta'
        WHEN tipo_reaccion = 'love' THEN 'me_encanta'
        WHEN tipo_reaccion = 'haha' THEN 'me_divierte'
        WHEN tipo_reaccion = 'laugh' THEN 'me_divierte'
        WHEN tipo_reaccion = 'wow' THEN 'me_asombra'
        WHEN tipo_reaccion = 'sad' THEN 'me_entristece'
        WHEN tipo_reaccion = 'angry' THEN 'me_enoja'
        ELSE tipo_reaccion
    END;

-- Paso 2: Cambiar tipo de columna de ENUM a VARCHAR temporalmente
ALTER TABLE reacciones MODIFY tipo_reaccion VARCHAR(50) NOT NULL;

-- Paso 3: Verificar que todos los valores se actualizaron correctamente
SELECT DISTINCT tipo_reaccion, COUNT(*) as cantidad 
FROM reacciones 
GROUP BY tipo_reaccion 
ORDER BY tipo_reaccion;

-- Nota: Si prefieres mantener ENUM en español (opcional):
-- ALTER TABLE reacciones MODIFY tipo_reaccion 
--     ENUM('me_gusta', 'me_encanta', 'me_divierte', 'me_asombra', 'me_entristece', 'me_enoja') 
--     NOT NULL;

-- ====================================================================
-- RESULTADO ESPERADO:
-- ✅ tipo_reaccion ahora acepta valores en español
-- ✅ Todos los registros antiguos (like, love, etc.) convertidos
-- ✅ Nuevas reacciones se guardarán en español directamente
-- ====================================================================
