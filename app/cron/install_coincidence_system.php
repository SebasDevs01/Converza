<?php
/**
 * INSTALADOR: Crear tablas necesarias para Coincidence Alerts y Conexiones Místicas
 */

$host = 'localhost';
$db   = 'converza';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conexion = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

echo "🚀 ========================================\n";
echo "   INSTALADOR: Sistema de Conexiones\n";
echo "========================================\n\n";

try {
    // 1. Crear tabla coincidence_alerts
    echo "📋 Creando tabla coincidence_alerts...\n";
    $conexion->exec("
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabla coincidence_alerts creada\n\n";
    
    // 2. Agregar columna ultima_actividad si no existe
    echo "📋 Verificando columna ultima_actividad...\n";
    try {
        $conexion->exec("
            ALTER TABLE usuarios 
            ADD COLUMN ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
        echo "   ✅ Columna ultima_actividad agregada\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ️ Columna ya existe\n\n";
        } else {
            throw $e;
        }
    }
    
    // 3. Crear índice para ultima_actividad
    echo "📋 Creando índice para ultima_actividad...\n";
    try {
        $conexion->exec("
            CREATE INDEX idx_ultima_actividad ON usuarios(ultima_actividad)
        ");
        echo "   ✅ Índice creado\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') !== false) {
            echo "   ℹ️ Índice ya existe\n\n";
        } else {
            throw $e;
        }
    }
    
    // 4. Crear tabla conexiones_misticas_contador
    echo "📋 Creando tabla conexiones_misticas_contador...\n";
    $conexion->exec("
        CREATE TABLE IF NOT EXISTS conexiones_misticas_contador (
            usuario_id INT PRIMARY KEY,
            total_conexiones INT DEFAULT 0,
            nuevas_conexiones INT DEFAULT 0,
            ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Tabla conexiones_misticas_contador creada\n\n";
    
    // 5. Verificar si ya existe la tabla conexiones_misticas
    echo "📋 Verificando tabla conexiones_misticas...\n";
    $stmt = $conexion->query("SHOW TABLES LIKE 'conexiones_misticas'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Tabla conexiones_misticas ya existe\n\n";
    } else {
        echo "   ⚠️ ADVERTENCIA: Tabla conexiones_misticas NO existe\n";
        echo "   Por favor, créala manualmente o ejecuta el script correspondiente\n\n";
    }
    
    // 6. Actualizar ultima_actividad de todos los usuarios
    echo "📋 Actualizando ultima_actividad de usuarios...\n";
    $conexion->exec("
        UPDATE usuarios 
        SET ultima_actividad = NOW() 
        WHERE ultima_actividad IS NULL OR ultima_actividad = '0000-00-00 00:00:00'
    ");
    echo "   ✅ Usuarios actualizados\n\n";
    
    echo "🎉 ========================================\n";
    echo "   INSTALACIÓN COMPLETADA CON ÉXITO!\n";
    echo "========================================\n\n";
    
    echo "📝 PRÓXIMOS PASOS:\n";
    echo "1. Agrega los scripts JS a tu index.php:\n";
    echo "   <script src=\"public/js/coincidence-alerts.js\"></script>\n";
    echo "   <script src=\"public/js/conexiones-misticas-manager.js\"></script>\n\n";
    
    echo "2. Configura el CRON Job (cada 6 horas):\n";
    echo "   0 */6 * * * php " . realpath(__DIR__ . '/cron_actualizar_conexiones.php') . "\n\n";
    
    echo "3. O ejecuta manualmente:\n";
    echo "   php app/cron/cron_actualizar_conexiones.php\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n\n";
    exit(1);
}
?>
