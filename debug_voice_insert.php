<?php
session_start();
require_once __DIR__.'/app/models/config.php';

try {
    // Verificar estructura de la tabla chats
    echo "<h2>Estructura de la tabla chats:</h2>";
    $stmt = $conexion->prepare("DESCRIBE chats");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($columns as $col) {
        echo "<p>- {$col['Field']} ({$col['Type']}) - {$col['Null']} - Default: {$col['Default']}</p>";
    }
    
    // Probar inserción con datos de prueba
    echo "<h2>Probando inserción:</h2>";
    
    $id_cch = 1; // Asegúrate de que existe una conversación con ID 1
    $de = $_SESSION['id'] ?? 1;
    $para = 2; // ID de usuario que existe
    $fileName = 'test_voice.wav';
    $duracion = 30;
    
    echo "<p>Datos a insertar:</p>";
    echo "<ul>";
    echo "<li>id_cch: $id_cch</li>";
    echo "<li>de: $de</li>";
    echo "<li>para: $para</li>";
    echo "<li>mensaje: (vacío)</li>";
    echo "<li>tipo_mensaje: voz</li>";
    echo "<li>archivo_audio: $fileName</li>";
    echo "<li>duracion_audio: $duracion</li>";
    echo "</ul>";
    
    $sql = "INSERT INTO chats (id_cch, de, para, mensaje, tipo_mensaje, archivo_audio, duracion_audio, fecha, leido) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
    echo "<p>SQL: $sql</p>";
    
    $stmtMsg = $conexion->prepare($sql);
    
    $params = [
        $id_cch,
        $de,
        $para,
        '',
        'voz',
        $fileName,
        $duracion
    ];
    
    echo "<p>Parámetros (" . count($params) . "):</p>";
    foreach($params as $i => $param) {
        echo "<li>" . ($i+1) . ": " . var_export($param, true) . "</li>";
    }
    
    $resultado = $stmtMsg->execute($params);
    
    if ($resultado) {
        echo "<h3 style='color: green;'>✅ INSERCIÓN EXITOSA</h3>";
        $newId = $conexion->lastInsertId();
        echo "<p>Nuevo mensaje ID: $newId</p>";
    } else {
        echo "<h3 style='color: red;'>❌ ERROR EN INSERCIÓN</h3>";
        print_r($stmtMsg->errorInfo());
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ EXCEPCIÓN: " . $e->getMessage() . "</h3>";
}
?>