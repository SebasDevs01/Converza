<?php
require_once(__DIR__.'/app/models/config.php');
session_start();

header('Content-Type: text/plain');

echo "=== PRUEBA DIRECTA DE SAVE_REACTION ===\n\n";

// Simular exactamente lo que envía JavaScript
$_POST = [
    'id_usuario' => '14',
    'id_publicacion' => '154', 
    'tipo_reaccion' => 'love'
];

echo "POST simulado:\n";
print_r($_POST);
echo "\n";

$id_usuario = $_POST['id_usuario'] ?? null;
$id_publicacion = $_POST['id_publicacion'] ?? null;
$tipo_reaccion = $_POST['tipo_reaccion'] ?? null;

echo "Variables extraídas:\n";
echo "- id_usuario: '$id_usuario' (longitud: " . strlen($id_usuario) . ")\n";
echo "- id_publicacion: '$id_publicacion' (longitud: " . strlen($id_publicacion) . ")\n";
echo "- tipo_reaccion: '$tipo_reaccion' (longitud: " . strlen($tipo_reaccion) . ")\n";
echo "- tipo_reaccion hex: " . bin2hex($tipo_reaccion) . "\n\n";

// Validar
$validReactions = ['like', 'love', 'laugh', 'wow', 'sad', 'angry'];
$isValid = in_array($tipo_reaccion, $validReactions);
echo "¿Es válido '$tipo_reaccion'?: " . ($isValid ? "SÍ" : "NO") . "\n";
echo "Reacciones válidas: " . implode(', ', $validReactions) . "\n\n";

if ($isValid) {
    echo "=== PROBANDO INSERCIÓN DIRECTA ===\n";
    
    try {
        // Limpiar datos anteriores
        $stmt = $conexion->prepare("DELETE FROM reacciones WHERE id_publicacion = :id_pub AND id_usuario = :id_user");
        $stmt->bindParam(':id_pub', $id_publicacion, PDO::PARAM_INT);
        $stmt->bindParam(':id_user', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        echo "✅ Datos anteriores eliminados\n";
        
        // Insertar nueva reacción
        echo "Insertando: usuario=$id_usuario, post=$id_publicacion, tipo='$tipo_reaccion'\n";
        
        $stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$id_usuario, $id_publicacion, $tipo_reaccion]);
        
        echo "Resultado inserción: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
        
        if (!$result) {
            echo "Error: " . print_r($stmt->errorInfo(), true) . "\n";
        }
        
        // Verificar qué se guardó
        $stmt = $conexion->prepare("SELECT * FROM reacciones WHERE id_publicacion = ? AND id_usuario = ?");
        $stmt->execute([$id_publicacion, $id_usuario]);
        $saved = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\n=== VERIFICACIÓN ===\n";
        if ($saved) {
            echo "✅ Registro encontrado:\n";
            echo "- ID: " . $saved['id'] . "\n";
            echo "- Usuario: " . $saved['id_usuario'] . "\n";
            echo "- Publicación: " . $saved['id_publicacion'] . "\n";
            echo "- Tipo: '" . $saved['tipo_reaccion'] . "' (longitud: " . strlen($saved['tipo_reaccion']) . ")\n";
            echo "- Fecha: " . $saved['fecha'] . "\n";
            
            if (empty($saved['tipo_reaccion'])) {
                echo "❌ EL TIPO ESTÁ VACÍO EN LA BASE DE DATOS!\n";
            } else {
                echo "✅ Tipo guardado correctamente\n";
            }
        } else {
            echo "❌ No se encontró el registro\n";
        }
        
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
}
?>