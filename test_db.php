<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=converza', 'root', '');
    echo "Conexión exitosa\n";
    
    // Verificar si existen las columnas en la tabla chats
    $stmt = $pdo->query("DESCRIBE chats");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Columnas en tabla chats:\n";
    foreach($columns as $column) {
        echo "- $column\n";
    }
    
    if (in_array('tipo_mensaje', $columns)) {
        echo "\n✅ La columna tipo_mensaje existe\n";
    } else {
        echo "\n❌ La columna tipo_mensaje NO existe - necesita ejecutar el SQL\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>