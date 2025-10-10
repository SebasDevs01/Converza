<?php
// Script de debug para encontrar el problema exacto
session_start();
require_once __DIR__.'/app/models/config.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'No hay sesión activa']);
    exit;
}

try {
    $de = $_SESSION['id'];
    $para = 2; // Cambia este número por un ID de usuario válido
    
    echo "Debug de inserción en chats:\n";
    echo "- de: $de\n";
    echo "- para: $para\n";
    
    // Probar inserción básica
    $sql = "INSERT INTO chats (id_cch,de,para,mensaje,fecha,leido) VALUES (?,?,?,?,NOW(),0)";
    echo "- SQL: $sql\n";
    
    $stmt = $conexion->prepare($sql);
    
    $resultado = $stmt->execute([
        1, // id_cch temporal
        $de,
        $para,
        'Test mensaje'
    ]);
    
    if ($resultado) {
        echo "✅ Inserción básica exitosa!\n";
        echo "- ID del mensaje: " . $conexion->lastInsertId() . "\n";
    } else {
        echo "❌ Error en inserción básica\n";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>