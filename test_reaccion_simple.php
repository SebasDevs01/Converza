<?php
// Test directo de inserción de reacción 'me_entristese'
require_once(__DIR__.'/app/models/config.php');

$tipo_reaccion = 'me_entristese'; // Usar el valor exacto de la BD
$id_usuario = 1;
$id_publicacion = 1;

echo "Probando inserción de '$tipo_reaccion'...\n\n";

try {
    // Borrar reacción previa
    $stmt = $conexion->prepare("DELETE FROM reacciones WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
    $stmt->execute();
    
    // Insertar nueva reacción
    $stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (:id_usuario, :id_publicacion, :tipo_reaccion, NOW())");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
    $stmt->bindParam(':tipo_reaccion', $tipo_reaccion, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo "✅ ¡ÉXITO! Reacción '$tipo_reaccion' guardada.\n\n";
        
        // Verificar
        $stmt = $conexion->prepare("SELECT * FROM reacciones WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
        $stmt->execute();
        $reaccion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reaccion) {
            echo "Reacción guardada:\n";
            print_r($reaccion);
        }
    } else {
        echo "❌ Error al guardar\n";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>