-- ========================================
-- CONFIGURACIÓN SISTEMA KARMA - VERSIÓN SIMPLE
-- ========================================
-- Ejecutar línea por línea si hay problemas con el script completo

-- PASO 1: Eliminar trigger anterior si existe
DROP TRIGGER IF EXISTS after_karma_social_insert;

-- PASO 2: Verificar si karma_total_usuarios es vista y eliminarla
DROP VIEW IF EXISTS karma_total_usuarios;

-- PASO 3: Crear karma_total_usuarios como TABLA REAL
CREATE TABLE IF NOT EXISTS karma_total_usuarios (
    usuario_id INT(11) NOT NULL PRIMARY KEY,
    karma_total DECIMAL(32,0) DEFAULT 0,
    acciones_totales BIGINT(21) DEFAULT 0,
    ultima_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_karma_total (karma_total),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASO 4: Inicializar con usuarios existentes
INSERT IGNORE INTO karma_total_usuarios (usuario_id, karma_total, acciones_totales, ultima_accion)
SELECT id_use, 0, 0, NOW() FROM usuarios;

-- PASO 5: Crear el TRIGGER
DELIMITER $$
CREATE TRIGGER after_karma_social_insert
AFTER INSERT ON karma_social
FOR EACH ROW
BEGIN
    INSERT INTO karma_total_usuarios (usuario_id, karma_total, acciones_totales, ultima_accion)
    VALUES (NEW.usuario_id, NEW.puntos, 1, NOW())
    ON DUPLICATE KEY UPDATE
        karma_total = karma_total + NEW.puntos,
        acciones_totales = acciones_totales + 1,
        ultima_accion = NOW();
END$$
DELIMITER ;

-- PASO 6: Recalcular karma desde historial (si existe)
UPDATE karma_total_usuarios kt
SET 
    karma_total = COALESCE((SELECT SUM(puntos) FROM karma_social WHERE usuario_id = kt.usuario_id), 0),
    acciones_totales = COALESCE((SELECT COUNT(*) FROM karma_social WHERE usuario_id = kt.usuario_id), 0),
    ultima_accion = COALESCE((SELECT MAX(fecha_accion) FROM karma_social WHERE usuario_id = kt.usuario_id), NOW());

-- PASO 7: Recrear vista usuarios_con_karma
DROP VIEW IF EXISTS usuarios_con_karma;

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

-- PASO 8: Crear índices para rendimiento
CREATE INDEX IF NOT EXISTS idx_karma_social_usuario ON karma_social(usuario_id);
CREATE INDEX IF NOT EXISTS idx_karma_social_fecha ON karma_social(fecha_accion);
CREATE INDEX IF NOT EXISTS idx_karma_social_referencia ON karma_social(referencia_id, referencia_tipo);

-- VERIFICACIÓN FINAL
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
-- ✅ COMPLETADO
-- ========================================
