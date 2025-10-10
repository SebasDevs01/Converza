<?php
require_once(__DIR__.'/../models/config.php');
require_once(__DIR__.'/../models/bloqueos-helper.php');
session_start();

header('Content-Type: application/json');

// Verificar si el usuario está bloqueado antes de permitir reacciones
if (isset($_SESSION['id']) && isUserBlocked($_SESSION['id'], $conexion)) {
    echo json_encode(['success' => false, 'message' => 'Usuario bloqueado. No puedes realizar esta acción.']);
    exit();
}

$id_usuario = $_POST['id_usuario'] ?? null;
$id_publicacion = $_POST['id_publicacion'] ?? null;
$tipo_reaccion = $_POST['tipo_reaccion'] ?? null;

// Debug: ver qué se está recibiendo
error_log("=== SAVE_REACTION DEBUG ===");
error_log("- Usuario: " . var_export($id_usuario, true));
error_log("- Publicación: " . var_export($id_publicacion, true));
error_log("- Tipo reacción: '" . $tipo_reaccion . "' (longitud: " . strlen($tipo_reaccion) . ")");
error_log("- Tipo reacción hex: " . bin2hex($tipo_reaccion));
error_log("- POST RAW: " . file_get_contents('php://input'));
error_log("- POST array: " . print_r($_POST, true));
error_log("- Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'no definido'));

if (!$id_usuario || !$id_publicacion || !$tipo_reaccion) {
    echo json_encode([
        'success' => false, 
        'message' => 'Datos incompletos',
        'debug' => [
            'id_usuario' => $id_usuario,
            'id_publicacion' => $id_publicacion,
            'tipo_reaccion' => $tipo_reaccion
        ]
    ]);
    exit;
}

// Verificar bloqueo con el autor de la publicación
try {
    $stmtAutor = $conexion->prepare("SELECT usuario FROM publicaciones WHERE id_pub = :id_pub");
    $stmtAutor->bindParam(':id_pub', $id_publicacion, PDO::PARAM_INT);
    $stmtAutor->execute();
    $publicacion = $stmtAutor->fetch();
    
    if ($publicacion) {
        $bloqueoInfo = verificarBloqueoMutuo($conexion, $id_usuario, $publicacion['usuario']);
        if ($bloqueoInfo['bloqueado']) {
            echo json_encode(['success' => false, 'message' => 'No puedes reaccionar a esta publicación']);
            exit;
        }
    }
} catch (Exception $e) {
    error_log("Error verificando bloqueo en reacción: " . $e->getMessage());
}

// Validar que el tipo de reacción sea válido (ortografía corregida)
$validReactions = ['me_gusta', 'me_encanta', 'me_divierte', 'me_asombra', 'me_entristece', 'me_enoja'];
error_log("Validando tipo_reaccion: '$tipo_reaccion' contra: " . implode(', ', $validReactions));

if (empty($tipo_reaccion)) {
    error_log("❌ TIPO_REACCION ESTÁ VACÍO");
    echo json_encode(['success' => false, 'message' => 'Tipo de reacción vacío']);
    exit;
}

if (!in_array($tipo_reaccion, $validReactions)) {
    error_log("❌ TIPO_REACCION NO VÁLIDO: '$tipo_reaccion'");
    echo json_encode([
        'success' => false, 
        'message' => 'Tipo de reacción no válido: ' . $tipo_reaccion,
        'valid_types' => $validReactions
    ]);
    exit;
}

// Verificar que la tabla reacciones existe, si no, crearla
try {
    $conexion->query("SELECT 1 FROM reacciones LIMIT 1");
} catch (PDOException $e) {
    // La tabla no existe, crearla
    $createTable = "
    CREATE TABLE reacciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_publicacion INT NOT NULL,
        tipo_reaccion ENUM('like', 'love', 'laugh', 'wow', 'sad', 'angry') NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_use) ON DELETE CASCADE,
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_pub) ON DELETE CASCADE,
        UNIQUE KEY unique_user_post_reaction (id_usuario, id_publicacion)
    )";
    $conexion->exec($createTable);
}

try {
    // Verificar si el usuario ya reaccionó a esta publicación
    $stmt = $conexion->prepare("SELECT id, tipo_reaccion FROM reacciones WHERE id_publicacion = :id_publicacion AND id_usuario = :id_usuario");
    $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $existingReaction = $stmt->fetch(PDO::FETCH_ASSOC);

    $action = '';

    if ($existingReaction) {
        if ($existingReaction['tipo_reaccion'] === $tipo_reaccion) {
            // Si es la misma reacción, eliminarla (toggle)
            $stmt = $conexion->prepare("DELETE FROM reacciones WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
            $stmt->execute();
            $action = 'removed';
        } else {
            // Si es diferente reacción, actualizar
            $stmt = $conexion->prepare("UPDATE reacciones SET tipo_reaccion = :tipo_reaccion, fecha = NOW() WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion");
            $stmt->bindParam(':tipo_reaccion', $tipo_reaccion, PDO::PARAM_STR);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
            $stmt->execute();
            $action = 'updated';
        }
    } else {
        // Insertar nueva reacción
        error_log("Insertando nueva reacción: usuario=$id_usuario, post=$id_publicacion, tipo='$tipo_reaccion'");
        $stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (:id_usuario, :id_publicacion, :tipo_reaccion, NOW())");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':id_publicacion', $id_publicacion, PDO::PARAM_INT);
        $stmt->bindParam(':tipo_reaccion', $tipo_reaccion, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        error_log("Resultado de inserción: " . ($result ? "SUCCESS" : "FAILED"));
        if (!$result) {
            error_log("Error SQL: " . print_r($stmt->errorInfo(), true));
        }
        $action = 'added';
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Reacción procesada',
        'action' => $action,
        'tipo_reaccion' => $action === 'removed' ? null : $tipo_reaccion
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}
?>
