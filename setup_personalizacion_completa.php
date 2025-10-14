<?php
/**
 * Script de Instalación del Sistema de Personalización Completa
 * Añade: Íconos especiales, Colores de nombre, Stickers/Estados de ánimo
 */

require_once 'app/models/config.php';

echo "<h2>🎨 Instalación: Sistema de Personalización Completa</h2>";

try {
    // Agregar columnas nuevas
    echo "<h3>📋 Paso 1: Agregar columnas a tabla usuarios</h3>";
    
    $columnas = [
        "icono_especial VARCHAR(50) DEFAULT NULL COMMENT 'Ícono especial junto al nombre'",
        "color_nombre VARCHAR(50) DEFAULT NULL COMMENT 'Clase CSS para color de nombre'",
        "stickers_activos TEXT DEFAULT NULL COMMENT 'JSON con stickers activos'"
    ];
    
    foreach ($columnas as $columna) {
        try {
            $db->query("ALTER TABLE usuarios ADD COLUMN $columna");
            echo "✅ Columna agregada: " . explode(' ', $columna)[0] . "<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "ℹ️ Columna ya existe: " . explode(' ', $columna)[0] . "<br>";
            } else {
                throw $e;
            }
        }
    }
    
    // Insertar nuevas recompensas
    echo "<br><h3>🎁 Paso 2: Añadir nuevas recompensas</h3>";
    
    // ÍCONOS ESPECIALES
    echo "<h4>⭐ Íconos Especiales</h4>";
    $iconos = [
        ['Ícono Estrella ⭐', 'Ícono dorado brillante junto a tu nombre', 80],
        ['Ícono Corona 👑', 'Corona real flotante junto a tu nombre', 150],
        ['Ícono Fuego 🔥', 'Llamas ardientes junto a tu nombre', 200],
        ['Ícono Corazón 💖', 'Corazón pulsante junto a tu nombre', 120],
        ['Ícono Rayo ⚡', 'Rayo eléctrico junto a tu nombre', 180],
        ['Ícono Diamante 💎', 'Diamante brillante junto a tu nombre', 300],
    ];
    
    foreach ($iconos as $icono) {
        try {
            $stmt = $db->prepare("
                INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) 
                VALUES ('icono', ?, ?, ?, 'icono-especial.png', 'iconos')
            ");
            $stmt->execute([$icono[0], $icono[1], $icono[2]]);
            echo "✅ Añadido: {$icono[0]} ({$icono[2]} karma)<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "ℹ️ Ya existe: {$icono[0]}<br>";
            } else {
                throw $e;
            }
        }
    }
    
    // COLORES DE NOMBRE
    echo "<br><h4>🎨 Colores de Nombre</h4>";
    $colores = [
        ['Nombre Dorado', 'Tu nombre en color dorado brillante animado', 100],
        ['Nombre Arcoíris', 'Tu nombre con efecto arcoíris rotativo', 200],
        ['Nombre Fuego', 'Tu nombre con efecto de fuego ardiente', 180],
        ['Nombre Océano', 'Tu nombre con efecto de olas oceánicas', 150],
        ['Nombre Neon Cyan', 'Tu nombre con efecto neón cian brillante', 220],
        ['Nombre Neon Rosa', 'Tu nombre con efecto neón rosa intenso', 220],
        ['Nombre Galaxia', 'Tu nombre con efecto galaxia púrpura', 250],
    ];
    
    foreach ($colores as $color) {
        try {
            $stmt = $db->prepare("
                INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) 
                VALUES ('color_nombre', ?, ?, ?, 'color-nombre.png', 'colores')
            ");
            $stmt->execute([$color[0], $color[1], $color[2]]);
            echo "✅ Añadido: {$color[0]} ({$color[2]} karma)<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "ℹ️ Ya existe: {$color[0]}<br>";
            } else {
                throw $e;
            }
        }
    }
    
    // STICKERS / ESTADOS DE ÁNIMO
    echo "<br><h4>😊 Packs de Stickers</h4>";
    $stickers = [
        ['Pack Básico de Stickers', 'Stickers: Feliz 😊, Triste 😢, Emocionado 🤩', 50],
        ['Pack Premium de Stickers', 'Stickers: Relajado 😌, Motivado 💪, Creativo 🎨', 120],
        ['Pack Elite de Stickers', 'Stickers: Pensativo 🤔, Energético ⚡, Legendario 🔥', 200],
    ];
    
    foreach ($stickers as $sticker) {
        try {
            $stmt = $db->prepare("
                INSERT INTO karma_recompensas (tipo, nombre, descripcion, costo_karma, imagen, categoria) 
                VALUES ('sticker', ?, ?, ?, 'sticker-pack.png', 'stickers')
            ");
            $stmt->execute([$sticker[0], $sticker[1], $sticker[2]]);
            echo "✅ Añadido: {$sticker[0]} ({$sticker[2]} karma)<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "ℹ️ Ya existe: {$sticker[0]}<br>";
            } else {
                throw $e;
            }
        }
    }
    
    // Crear índices
    echo "<br><h3>⚡ Paso 3: Crear índices</h3>";
    try {
        $db->query("CREATE INDEX idx_usuarios_icono ON usuarios(icono_especial)");
        echo "✅ Índice creado: idx_usuarios_icono<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "ℹ️ Índice ya existe: idx_usuarios_icono<br>";
        } else {
            throw $e;
        }
    }
    
    try {
        $db->query("CREATE INDEX idx_usuarios_color_nombre ON usuarios(color_nombre)");
        echo "✅ Índice creado: idx_usuarios_color_nombre<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "ℹ️ Índice ya existe: idx_usuarios_color_nombre<br>";
        } else {
            throw $e;
        }
    }
    
    // Verificar estructura
    echo "<br><h3>🔍 Paso 4: Verificación Final</h3>";
    $stmt = $db->query("
        SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'usuarios'
          AND COLUMN_NAME IN ('icono_especial', 'color_nombre', 'stickers_activos')
    ");
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Default</th><th>Comentario</th></tr>";
    while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$col['COLUMN_NAME']}</strong></td>";
        echo "<td>{$col['COLUMN_TYPE']}</td>";
        echo "<td>{$col['COLUMN_DEFAULT']}</td>";
        echo "<td>{$col['COLUMN_COMMENT']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar recompensas
    echo "<br><h3>📊 Resumen de Recompensas</h3>";
    $totales = $db->query("
        SELECT tipo, COUNT(*) as total 
        FROM karma_recompensas 
        WHERE tipo IN ('icono', 'color_nombre', 'sticker')
        GROUP BY tipo
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($totales as $total) {
        $emoji = $total['tipo'] == 'icono' ? '⭐' : ($total['tipo'] == 'color_nombre' ? '🎨' : '😊');
        echo "<li>{$emoji} <strong>{$total['tipo']}</strong>: {$total['total']} recompensas</li>";
    }
    echo "</ul>";
    
    echo "<br><div style='padding: 20px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 15px; text-align: center;'>";
    echo "<h2>✅ ¡INSTALACIÓN COMPLETADA!</h2>";
    echo "<p><strong>Sistema de Personalización Completa Activo</strong></p>";
    echo "<p>Los usuarios ahora pueden desbloquear:</p>";
    echo "<ul style='list-style: none; padding: 0;'>";
    echo "<li>⭐ 6 Íconos Especiales (80-300 karma)</li>";
    echo "<li>🎨 7 Colores de Nombre (100-250 karma)</li>";
    echo "<li>😊 3 Packs de Stickers (50-200 karma)</li>";
    echo "</ul>";
    echo "<br>";
    echo "<a href='app/presenters/karma_tienda.php' style='display: inline-block; padding: 12px 30px; background: white; color: #667eea; text-decoration: none; border-radius: 25px; font-weight: bold;'>🛍️ VER TIENDA</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='padding: 15px; background: #f8d7da; color: #721c24; border-radius: 10px;'>";
    echo "<h3>❌ Error en la instalación</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 900px;
    margin: 50px auto;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

h2, h3, h4 {
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

table {
    background: white;
    width: 100%;
    margin: 10px 0;
}

ul {
    background: rgba(255,255,255,0.1);
    padding: 15px;
    border-radius: 10px;
    color: white;
}
</style>
