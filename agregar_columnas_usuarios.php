<?php
require_once('app/models/config.php');

try {
    // Agregar columnas una por una
    $columnas = [
        "ALTER TABLE usuarios ADD COLUMN bio TEXT",
        "ALTER TABLE usuarios ADD COLUMN descripcion_corta VARCHAR(255)",
        "ALTER TABLE usuarios ADD COLUMN signo_zodiacal ENUM('aries','tauro','geminis','cancer','leo','virgo','libra','escorpio','sagitario','capricornio','acuario','piscis')",
        "ALTER TABLE usuarios ADD COLUMN genero ENUM('masculino','femenino','otro','prefiero_no_decir')",
        "ALTER TABLE usuarios ADD COLUMN mostrar_icono_genero BOOLEAN DEFAULT TRUE",
        "ALTER TABLE usuarios ADD COLUMN estado_animo ENUM('feliz','emocionado','relajado','creativo','cansado','ocupado','triste','enojado','motivado','inspirado','pensativo','nostalgico')",
        "ALTER TABLE usuarios ADD COLUMN mostrar_karma BOOLEAN DEFAULT TRUE",
        "ALTER TABLE usuarios ADD COLUMN mostrar_signo BOOLEAN DEFAULT TRUE",
        "ALTER TABLE usuarios ADD COLUMN mostrar_estado_animo BOOLEAN DEFAULT TRUE"
    ];
    
    foreach ($columnas as $sql) {
        try {
            $conexion->exec($sql);
            echo "✓ " . substr($sql, 0, 50) . "...<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "⊗ Ya existe: " . substr($sql, 0, 50) . "...<br>";
            } else {
                echo "✗ Error: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    echo "<br><strong>✅ Proceso completado!</strong><br>";
    echo "<a href='app/view'>Ir al inicio</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
