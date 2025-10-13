<?php
session_start();
require_once __DIR__.'/../models/config.php';

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo 'No autorizado.';
    exit;
}

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$yo = $_SESSION['id'];


if ($id <= 0 || $id == $yo) {
    echo 'Solicitud inválida.';
    exit;
}

// Verificar que el usuario destino existe
$stmt = $conexion->prepare('SELECT id_use FROM usuarios WHERE id_use = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
if (!$stmt->fetch()) {
    echo 'El usuario no existe.';
    exit;
}

if ($action === 'agregar') {
    // Verificar bloqueo mutuo antes de enviar solicitud
    require_once __DIR__.'/../models/bloqueos-helper.php';
    $bloqueoInfo = verificarBloqueoMutuo($conexion, $yo, $id);
    if ($bloqueoInfo['bloqueado']) {
        echo 'No es posible enviar solicitud a este usuario.';
        header('Location: /Converza/app/view/index.php');
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
        echo 'Ya existe una solicitud o amistad.';
        header('Location: /Converza/app/view/index.php');
        exit;
    }

    // Insertar solicitud
    $stmt = $conexion->prepare('INSERT INTO amigos (de, para, estado, fecha) VALUES (:yo, :id, 0, NOW())');
    $stmt->bindParam(':yo', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Notificación (simulada)
    $_SESSION['notificaciones'][] = "Solicitud enviada a usuario #$id";
    echo 'Solicitud enviada correctamente.';
    header('Location: /Converza/app/view/index.php');
    exit;
}


if ($action === 'aceptar') {
    $stmt = $conexion->prepare('UPDATE amigos SET estado = 1 WHERE para = :yo AND de = :id AND estado = 0');
    $stmt->bindParam(':yo', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    // Notificación (simulada)
    $_SESSION['notificaciones'][] = "Has aceptado la solicitud de usuario #$id";
    echo 'Solicitud aceptada.';
    header('Location: /Converza/app/view/index.php');
    exit;
}

if ($action === 'rechazar') {
    $stmt = $conexion->prepare('DELETE FROM amigos WHERE para = :yo AND de = :id AND estado = 0');
    $stmt->bindParam(':yo', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    echo 'Solicitud rechazada.';
    header('Location: /Converza/app/view/index.php');
    exit;
}

if ($action === 'eliminar') {
    // Eliminar amistad confirmada (bidireccional)
    $stmt = $conexion->prepare('
        DELETE FROM amigos 
        WHERE ((de = :yo1 AND para = :id1) OR (de = :id2 AND para = :yo2))
          AND estado = 1
    ');
    $stmt->bindParam(':yo1', $yo, PDO::PARAM_INT);
    $stmt->bindParam(':id1', $id, PDO::PARAM_INT);
    $stmt->bindParam(':id2', $id, PDO::PARAM_INT);
    $stmt->bindParam(':yo2', $yo, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // También eliminar la relación de seguimiento (dejar de seguir automáticamente)
        try {
            $stmtSeguir = $conexion->prepare('
                DELETE FROM seguidores 
                WHERE seguidor_id = :yo AND seguido_id = :id
            ');
            $stmtSeguir->bindParam(':yo', $yo, PDO::PARAM_INT);
            $stmtSeguir->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtSeguir->execute();
        } catch (Exception $e) {
            // Si hay error al eliminar seguimiento, continuar igual
        }
        
        $_SESSION['notificaciones'][] = "Has eliminado la amistad con usuario #$id";
        echo 'Amistad eliminada correctamente.';
    } else {
        echo 'No se encontró la amistad para eliminar.';
    }
    
    header('Location: /Converza/app/presenters/perfil.php?id=' . $id);
    exit;
}

echo 'Acción no válida.';
exit;
