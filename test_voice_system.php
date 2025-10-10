<?php
session_start();
require_once __DIR__.'/app/models/config.php';

echo "<h1>DEBUG - Sistema de Mensajes de Voz</h1>";

try {
    // 1. Verificar si las columnas existen
    echo "<h2>1. Verificando estructura de tabla chats:</h2>";
    $stmt = $conexion->prepare("SHOW COLUMNS FROM chats");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasVoiceColumns = false;
    echo "<ul>";
    foreach($columns as $col) {
        echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
        if (in_array($col['Field'], ['tipo_mensaje', 'archivo_audio', 'duracion_audio'])) {
            $hasVoiceColumns = true;
        }
    }
    echo "</ul>";
    
    if ($hasVoiceColumns) {
        echo "<p style='color: green;'>✅ Las columnas de voz EXISTEN</p>";
    } else {
        echo "<p style='color: red;'>❌ Las columnas de voz NO EXISTEN</p>";
        echo "<p><strong>Solución:</strong> Ejecuta el archivo SQL create_chat_reactions_table.sql</p>";
    }
    
    // 2. Verificar directorio de voice messages
    echo "<h2>2. Verificando directorio de archivos de voz:</h2>";
    $voiceDir = $_SERVER['DOCUMENT_ROOT'] . '/Converza/public/voice_messages/';
    if (file_exists($voiceDir)) {
        echo "<p style='color: green;'>✅ Directorio existe: $voiceDir</p>";
        if (is_writable($voiceDir)) {
            echo "<p style='color: green;'>✅ Directorio tiene permisos de escritura</p>";
        } else {
            echo "<p style='color: red;'>❌ Sin permisos de escritura</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Directorio no existe, se creará automáticamente</p>";
    }
    
    // 3. Probar inserción simple
    echo "<h2>3. Probando inserción básica:</h2>";
    
    if (!isset($_SESSION['id'])) {
        echo "<p style='color: red;'>❌ No hay sesión activa. <a href='app/view/index.php'>Inicia sesión aquí</a></p>";
    } else {
        $de = $_SESSION['id'];
        
        // Buscar un usuario para enviar mensaje de prueba
        $stmtUsers = $conexion->prepare("SELECT id_use FROM usuarios WHERE id_use != ? LIMIT 1");
        $stmtUsers->execute([$de]);
        $otherUser = $stmtUsers->fetch(PDO::FETCH_ASSOC);
        
        if ($otherUser) {
            $para = $otherUser['id_use'];
            echo "<p>Enviando mensaje de prueba de usuario $de a usuario $para</p>";
            
            // Buscar o crear conversación
            $stmtC = $conexion->prepare("SELECT id_cch FROM c_chats WHERE (de = ? AND para = ?) OR (de = ? AND para = ?) LIMIT 1");
            $stmtC->execute([$de, $para, $para, $de]);
            $conv = $stmtC->fetch(PDO::FETCH_ASSOC);
            
            if (!$conv) {
                $stmtCreate = $conexion->prepare("INSERT INTO c_chats (de, para) VALUES (?, ?)");
                $stmtCreate->execute([$de, $para]);
                $id_cch = $conexion->lastInsertId();
                echo "<p>✅ Conversación creada con ID: $id_cch</p>";
            } else {
                $id_cch = $conv['id_cch'];
                echo "<p>✅ Conversación encontrada con ID: $id_cch</p>";
            }
            
            // Probar inserción
            try {
                if ($hasVoiceColumns) {
                    $sql = "INSERT INTO chats (id_cch, de, para, mensaje, tipo_mensaje, archivo_audio, duracion_audio, fecha, leido) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
                    $stmt = $conexion->prepare($sql);
                    $result = $stmt->execute([$id_cch, $de, $para, '', 'voz', 'test_audio.wav', 30]);
                } else {
                    $sql = "INSERT INTO chats (id_cch, de, para, mensaje, fecha, leido) VALUES (?, ?, ?, ?, NOW(), 0)";
                    $stmt = $conexion->prepare($sql);
                    $result = $stmt->execute([$id_cch, $de, $para, '[Mensaje de voz de prueba]']);
                }
                
                if ($result) {
                    echo "<p style='color: green;'>✅ INSERCIÓN EXITOSA</p>";
                    echo "<p>Mensaje ID: " . $conexion->lastInsertId() . "</p>";
                } else {
                    echo "<p style='color: red;'>❌ Error en inserción: " . print_r($stmt->errorInfo(), true) . "</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Excepción: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ No se encontraron otros usuarios</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error general: " . $e->getMessage() . "</h3>";
}
?>