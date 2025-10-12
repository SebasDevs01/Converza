<?php
session_start();
require_once __DIR__.'/app/models/config.php';

echo "<h1>🔧 Debug del Sistema de Chat</h1>";

// 1. Verificar sesión
echo "<h2>1. Información de sesión</h2>";
if(isset($_SESSION['usuario'])) {
    echo "<p>✅ Usuario logueado: " . $_SESSION['usuario'] . " (ID: " . $_SESSION['id'] . ")</p>";
} else {
    echo "<p>❌ No hay usuario logueado - <a href='app/view/index.php'>Ir a login</a></p>";
}

// 2. Verificar base de datos
echo "<h2>2. Estado de la base de datos</h2>";
try {
    $conexion->exec("SELECT 1");
    echo "<p>✅ Conexión a base de datos: OK</p>";
} catch(Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// 3. Verificar tablas necesarias
$tablasNecesarias = [
    'chats' => 'Mensajes del chat',
    'usuarios' => 'Usuarios del sistema', 
    'chat_reacciones' => 'Sistema de reacciones',
    'mensajes_eliminados' => 'Mensajes eliminados (WhatsApp style)'
];

echo "<h3>Tablas del sistema:</h3>";
foreach($tablasNecesarias as $tabla => $descripcion) {
    try {
        $stmt = $conexion->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabla]);
        $exists = $stmt->fetchAll(); // Usar fetchAll para evitar problemas de buffering
        $stmt->closeCursor(); // Cerrar el cursor
        
        if($exists) {
            echo "<p>✅ <strong>$tabla</strong>: Existe ($descripcion)</p>";
        } else {
            echo "<p>❌ <strong>$tabla</strong>: NO existe ($descripcion)</p>";
            echo "<button onclick='crearTabla(\"$tabla\")' class='btn btn-primary'>Crear $tabla</button><br><br>";
        }
    } catch(Exception $e) {
        echo "<p>⚠️ Error verificando $tabla: " . $e->getMessage() . "</p>";
    }
}

// 4. Crear las tablas faltantes
echo "<h2>3. Crear tablas faltantes</h2>";
echo "<button onclick='crearTodasTablas()' class='btn btn-success btn-lg'>🚀 Crear TODAS las tablas necesarias</button>";

// 5. Test de mensajes
if(isset($_SESSION['id'])) {
    echo "<h2>4. Últimos mensajes en el sistema</h2>";
    try {
        $stmt = $conexion->prepare("SELECT c.*, u1.usuario as de_usuario, u2.usuario as para_usuario 
                                   FROM chats c 
                                   LEFT JOIN usuarios u1 ON c.de = u1.id_use 
                                   LEFT JOIN usuarios u2 ON c.para = u2.id_use 
                                   ORDER BY c.id_cha DESC LIMIT 5");
        $stmt->execute();
        $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if($mensajes) {
            echo "<table border='1' style='width:100%; border-collapse:collapse;'>";
            echo "<tr><th>ID</th><th>De</th><th>Para</th><th>Mensaje</th><th>Fecha</th><th>Tipo</th></tr>";
            foreach($mensajes as $msg) {
                echo "<tr>";
                echo "<td>{$msg['id_cha']}</td>";
                echo "<td>{$msg['de_usuario']}</td>";
                echo "<td>{$msg['para_usuario']}</td>";
                echo "<td>" . substr($msg['mensaje'], 0, 50) . "</td>";
                echo "<td>{$msg['fecha']}</td>";
                echo "<td>{$msg['tipo_mensaje']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay mensajes en el sistema</p>";
        }
    } catch(Exception $e) {
        echo "<p>Error obteniendo mensajes: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>5. Prueba el chat</h2>";
echo "<p><a href='app/presenters/chat.php' class='btn btn-primary btn-lg'>🚀 Ir al Chat</a></p>";

?>

<script>
function crearTodasTablas() {
    fetch('setup_delete_system.php')
        .then(response => response.text())
        .then(data => {
            document.body.innerHTML = data;
        });
}

function crearTabla(tabla) {
    alert('Creando tabla: ' + tabla);
    crearTodasTablas();
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { padding: 10px 15px; margin: 5px; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
.btn-primary { background: #007bff; color: white; }
.btn-success { background: #28a745; color: white; }
.btn-lg { font-size: 18px; padding: 15px 25px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
</style>