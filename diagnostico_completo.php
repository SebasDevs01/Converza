<?php
session_start();
require_once __DIR__.'/app/models/config.php';

echo "<h1>DIAGNÓSTICO COMPLETO - BASE DE DATOS</h1>";

try {
    // 1. Verificar tabla chats
    echo "<h2>1. Estructura tabla CHATS:</h2>";
    $stmt = $conexion->prepare("DESCRIBE chats");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th><th>Extra</th></tr>";
    foreach($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar tabla c_chats
    echo "<h2>2. Estructura tabla C_CHATS:</h2>";
    $stmt2 = $conexion->prepare("DESCRIBE c_chats");
    $stmt2->execute();
    $columns2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th><th>Extra</th></tr>";
    foreach($columns2 as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Probar inserción paso a paso
    echo "<h2>3. Prueba de inserción PASO A PASO:</h2>";
    
    if (!isset($_SESSION['id'])) {
        echo "<p style='color: red;'>❌ No hay sesión. <a href='app/view/index.php'>Inicia sesión</a></p>";
    } else {
        $de = $_SESSION['id'];
        echo "<p>Usuario logueado: ID $de</p>";
        
        // Encontrar otro usuario
        $stmtUser = $conexion->prepare("SELECT id_use FROM usuarios WHERE id_use != ? LIMIT 1");
        $stmtUser->execute([$de]);
        $otherUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        if (!$otherUser) {
            echo "<p style='color: red;'>❌ No hay otros usuarios en la BD</p>";
        } else {
            $para = $otherUser['id_use'];
            echo "<p>Usuario destino: ID $para</p>";
            
            // PASO 1: Buscar conversación
            echo "<h3>PASO 1: Buscar conversación</h3>";
            $stmtC = $conexion->prepare("SELECT id_cch FROM c_chats WHERE (de = ? AND para = ?) OR (de = ? AND para = ?) LIMIT 1");
            $stmtC->execute([$de, $para, $para, $de]);
            $conv = $stmtC->fetch(PDO::FETCH_ASSOC);
            
            if (!$conv) {
                echo "<p>No existe conversación, creando...</p>";
                try {
                    $stmtCreate = $conexion->prepare("INSERT INTO c_chats (de, para) VALUES (?, ?)");
                    $result = $stmtCreate->execute([$de, $para]);
                    if ($result) {
                        $id_cch = $conexion->lastInsertId();
                        echo "<p style='color: green;'>✅ Conversación creada: ID $id_cch</p>";
                    } else {
                        echo "<p style='color: red;'>❌ Error creando conversación: " . print_r($stmtCreate->errorInfo(), true) . "</p>";
                    }
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Excepción creando conversación: " . $e->getMessage() . "</p>";
                }
            } else {
                $id_cch = $conv['id_cch'];
                echo "<p style='color: green;'>✅ Conversación encontrada: ID $id_cch</p>";
            }
            
            // PASO 2: Insertar mensaje básico
            if (isset($id_cch)) {
                echo "<h3>PASO 2: Insertar mensaje básico</h3>";
                
                try {
                    $sql = "INSERT INTO chats (id_cch, de, para, mensaje, fecha, leido) VALUES (?, ?, ?, ?, NOW(), 0)";
                    echo "<p>SQL: <code>$sql</code></p>";
                    
                    $params = [$id_cch, $de, $para, '[TEST - Mensaje de voz]'];
                    echo "<p>Parámetros: " . print_r($params, true) . "</p>";
                    
                    $stmtMsg = $conexion->prepare($sql);
                    $resultado = $stmtMsg->execute($params);
                    
                    if ($resultado) {
                        $messageId = $conexion->lastInsertId();
                        echo "<p style='color: green;'>✅ MENSAJE INSERTADO! ID: $messageId</p>";
                        
                        // PASO 3: Actualizar con datos de voz
                        echo "<h3>PASO 3: Actualizar con datos de voz</h3>";
                        try {
                            $updateSql = "UPDATE chats SET tipo_mensaje = ?, archivo_audio = ?, duracion_audio = ? WHERE id_cha = ?";
                            $updateStmt = $conexion->prepare($updateSql);
                            $updateResult = $updateStmt->execute(['voz', 'test_audio.wav', 30, $messageId]);
                            
                            if ($updateResult) {
                                echo "<p style='color: green;'>✅ ACTUALIZACIÓN EXITOSA - Mensaje completo de voz creado</p>";
                            } else {
                                echo "<p style='color: orange;'>⚠️ Actualización falló: " . print_r($updateStmt->errorInfo(), true) . "</p>";
                                echo "<p>Pero el mensaje básico sí se creó</p>";
                            }
                        } catch (Exception $e) {
                            echo "<p style='color: orange;'>⚠️ Error en actualización: " . $e->getMessage() . "</p>";
                            echo "<p>Pero el mensaje básico sí se creó</p>";
                        }
                        
                    } else {
                        echo "<p style='color: red;'>❌ Error insertando mensaje: " . print_r($stmtMsg->errorInfo(), true) . "</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Excepción insertando mensaje: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
    // 4. Mostrar últimos mensajes
    echo "<h2>4. Últimos 5 mensajes en la tabla:</h2>";
    $stmtLast = $conexion->prepare("SELECT id_cha, id_cch, de, para, mensaje, tipo_mensaje, archivo_audio, duracion_audio, fecha FROM chats ORDER BY id_cha DESC LIMIT 5");
    $stmtLast->execute();
    $lastMessages = $stmtLast->fetchAll(PDO::FETCH_ASSOC);
    
    if ($lastMessages) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Conv</th><th>De</th><th>Para</th><th>Mensaje</th><th>Tipo</th><th>Audio</th><th>Duración</th><th>Fecha</th></tr>";
        foreach($lastMessages as $msg) {
            echo "<tr>";
            echo "<td>{$msg['id_cha']}</td>";
            echo "<td>{$msg['id_cch']}</td>";
            echo "<td>{$msg['de']}</td>";
            echo "<td>{$msg['para']}</td>";
            echo "<td>" . htmlspecialchars($msg['mensaje']) . "</td>";
            echo "<td>{$msg['tipo_mensaje']}</td>";
            echo "<td>{$msg['archivo_audio']}</td>";
            echo "<td>{$msg['duracion_audio']}</td>";
            echo "<td>{$msg['fecha']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay mensajes en la tabla</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>ERROR GENERAL: " . $e->getMessage() . "</h3>";
}
?>