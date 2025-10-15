-- ═══════════════════════════════════════════════════════════════
-- 🔧 AGREGAR COLUMNA KARMA A TABLA USUARIOS
-- ═══════════════════════════════════════════════════════════════

-- Verificar si la columna ya existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = 'usuarios';
SET @columnname = 'karma';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT "La columna karma ya existe, no se requiere acción" AS mensaje;',
  'ALTER TABLE usuarios ADD COLUMN karma INT NOT NULL DEFAULT 0 AFTER tipo;'
));

PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- Verificar que se agregó correctamente
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'usuarios'
  AND COLUMN_NAME = 'karma';

-- Mostrar estadísticas de karma
SELECT 
    COUNT(*) as total_usuarios,
    MIN(karma) as karma_minimo,
    MAX(karma) as karma_maximo,
    AVG(karma) as karma_promedio,
    SUM(karma) as karma_total
FROM usuarios;

-- Mostrar primeros 10 usuarios con su karma
SELECT id_use, usuario, karma, tipo
FROM usuarios
ORDER BY id_use ASC
LIMIT 10;
