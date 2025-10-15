-- ========================================
-- CONFIGURACIÓN COMPLETA SISTEMA DE KARMA
-- ========================================
-- Este script configura el sistema de karma usando las tablas existentes:
-- - karma_social: registra cada acción individual
-- - karma_total_usuarios: mantiene el total acumulado
-- - usuarios_con_karma: vista para consultas rápidas

-- ========================================
-- 1. CREAR TRIGGER PARA AUTO-ACTUALIZAR karma_total_usuarios
-- ========================================

-- Eliminar trigger si existe
DROP TRIGGER IF EXISTS after_karma_social_insert;

DELIMITER $$

CREATE TRIGGER after_karma_social_insert
AFTER INSERT ON karma_social
FOR EACH ROW
BEGIN
    -- Insertar o actualizar el karma total del usuario
    INSERT INTO karma_total_usuarios (usuario_id, karma_total, acciones_totales, ultima_accion)
    VALUES (
        NEW.usuario_id,
        NEW.puntos,
        1,
        NOW()
    )
    ON DUPLICATE KEY UPDATE
        karma_total = karma_total + NEW.puntos,
        acciones_totales = acciones_totales + 1,
        ultima_accion = NOW();
END$$

DELIMITER ;

-- ========================================
-- 2. VERIFICAR Y CONVERTIR karma_total_usuarios EN TABLA REAL
-- ========================================

-- Primero, verificar si es una vista y eliminarla
DROP VIEW IF EXISTS karma_total_usuarios;

-- Crear karma_total_usuarios como TABLA REAL (no vista)
CREATE TABLE IF NOT EXISTS karma_total_usuarios (
    usuario_id INT(11) NOT NULL PRIMARY KEY,
    karma_total DECIMAL(32,0) DEFAULT 0,
    acciones_totales BIGINT(21) DEFAULT 0,
    ultima_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_karma_total (karma_total),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inicializar registros para usuarios existentes
INSERT IGNORE INTO karma_total_usuarios (usuario_id, karma_total, acciones_totales, ultima_accion)
SELECT 
    u.id_use,
    0,
    0,
    NOW()
FROM usuarios u;

-- ========================================
-- 3. RECALCULAR KARMA DESDE karma_social (si hay datos históricos)
-- ========================================

-- Actualizar totales basándose en el historial existente en karma_social
UPDATE karma_total_usuarios kt
SET 
    karma_total = COALESCE((
        SELECT SUM(ks.puntos)
        FROM karma_social ks
        WHERE ks.usuario_id = kt.usuario_id
    ), 0),
    acciones_totales = COALESCE((
        SELECT COUNT(*)
        FROM karma_social ks
        WHERE ks.usuario_id = kt.usuario_id
    ), 0),
    ultima_accion = COALESCE((
        SELECT MAX(ks.fecha_accion)
        FROM karma_social ks
        WHERE ks.usuario_id = kt.usuario_id
    ), NOW());

-- ========================================
-- 4. RECREAR LA VISTA usuarios_con_karma
-- ========================================

-- Eliminar vista si existe
DROP VIEW IF EXISTS usuarios_con_karma;

-- Recrear la vista usuarios_con_karma usando la tabla real karma_total_usuarios
CREATE VIEW usuarios_con_karma AS
SELECT 
    u.id_use,
    u.usuario,
    u.nombre,
    u.avatar,
    COALESCE(kt.karma_total, 0) as karma_total,
    COALESCE(kt.acciones_totales, 0) as acciones_totales,
    COALESCE(kr.karma_30dias, 0) as karma_reciente,
    COALESCE(kr.acciones_30dias, 0) as acciones_recientes,
    kt.ultima_accion
FROM usuarios u
LEFT JOIN karma_total_usuarios kt ON u.id_use = kt.usuario_id
LEFT JOIN karma_reciente_usuarios kr ON u.id_use = kr.usuario_id;

-- ========================================
-- 5. ÍNDICES PARA RENDIMIENTO
-- ========================================

-- Asegurar índices en karma_social
CREATE INDEX IF NOT EXISTS idx_karma_social_usuario ON karma_social(usuario_id);
CREATE INDEX IF NOT EXISTS idx_karma_social_fecha ON karma_social(fecha_accion);
CREATE INDEX IF NOT EXISTS idx_karma_social_referencia ON karma_social(referencia_id, referencia_tipo);

-- ========================================
-- 6. VERIFICACIÓN
-- ========================================

-- Mostrar estadísticas después de configurar
SELECT 
    'Usuarios totales' as metrica,
    COUNT(*) as valor
FROM usuarios
UNION ALL
SELECT 
    'Usuarios con karma registrado',
    COUNT(*)
FROM karma_total_usuarios
UNION ALL
SELECT 
    'Total acciones karma',
    COUNT(*)
FROM karma_social
UNION ALL
SELECT 
    'Karma total acumulado',
    SUM(COALESCE(karma_total, 0))
FROM karma_total_usuarios;

-- ========================================
-- ✅ SISTEMA CONFIGURADO
-- ========================================
-- El sistema ahora:
-- 1. Registra cada acción en karma_social
-- 2. Actualiza automáticamente karma_total_usuarios vía trigger
-- 3. Permite consultas rápidas desde usuarios_con_karma
-- 4. Mantiene historial completo de acciones
