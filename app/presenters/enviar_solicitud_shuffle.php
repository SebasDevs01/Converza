<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/bloqueos-helper.php';

// Establecer encabezado JSON
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$yo = $_SESSION['id'];

if ($id <= 0 || $id == $yo) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
    exit;
}

try {
    // Verificar que el usuario destino existe
    $stmt = $conexion->prepare('SELECT id_use, usuario FROM usuarios WHERE id_use = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'El usuario no existe']);
        exit;
    }
    
    // Verificar bloqueo mutuo
    $bloqueoInfo = verificarBloqueoMutuo($conexion, $yo, $id);
    if ($bloqueoInfo['bloqueado']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No es posible enviar solicitud a este usuario']);
        exit;
    }
    
    // Verificar si ya existe una solicitud pendiente o amistad
    $stmt = $conexion->prepare('
        SELECT * FROM amigos 
        WHERE (de = :yo1 AND para = :id1) 
           OR (de = :id2 AND para = :yo2)
    ');
    $stmt->bindParam(':yo1', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id1', $id, PDO::PARAM_INT);
    $stmt->bindParam(':id2', $id, PDO::PARAM_INT);
    $stmt->bindParam(':yo2', $yo, PDO::PARAM_INT);
    $stmt->execute();

    $existe = $stmt->fetch();
    if ($existe) {
        echo json_encode(['success' => false, 'error' => 'Ya existe una solicitud o amistad con este usuario']);
        exit;
    }

    // Insertar solicitud de amistad
    $stmt = $conexion->prepare('INSERT INTO amigos (de, para, estado, fecha) VALUES (:yo, :id, 0, NOW())');
    $stmt->bindParam(':yo', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // NO seguir automáticamente - dejar que el usuario decida si quiere seguir
    // Si la solicitud se acepta, serán AMIGOS (no seguidores)
    // Si la solicitud se rechaza, el usuario puede seguir manualmente si quiere

    echo json_encode([
        'success' => true, 
        'mensaje' => 'Solicitud de amistad enviada a ' . htmlspecialchars($usuario['usuario'])
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
