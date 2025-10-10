<?php
session_start();
require_once __DIR__.'/../models/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$mensaje_id = intval($_POST['mensaje_id'] ?? $_GET['mensaje_id'] ?? 0);
$usuario_id = $_SESSION['id'];

switch ($action) {
    case 'add_reaction':
        $tipo_reaccion = $_POST['tipo_reaccion'] ?? '';
        
        if (empty($tipo_reaccion) || $mensaje_id === 0) {
            echo json_encode(['error' => 'Datos faltantes']);
            exit;
        }
        
        try {
            // Verificar si ya existe alguna reacción del usuario a este mensaje
            $stmtCheck = $conexion->prepare("
                SELECT tipo_reaccion FROM chat_reacciones 
                WHERE mensaje_id = :mensaje_id AND usuario_id = :usuario_id
            ");
            $stmtCheck->execute([
                ':mensaje_id' => $mensaje_id,
                ':usuario_id' => $usuario_id
            ]);
            $existingReaction = $stmtCheck->fetch();
            
            if ($existingReaction) {
                if ($existingReaction['tipo_reaccion'] === $tipo_reaccion) {
                    // Si es la misma reacción, eliminarla (toggle)
                    $stmtDelete = $conexion->prepare("
                        DELETE FROM chat_reacciones 
                        WHERE mensaje_id = :mensaje_id AND usuario_id = :usuario_id
                    ");
                    $stmtDelete->execute([
                        ':mensaje_id' => $mensaje_id,
                        ':usuario_id' => $usuario_id
                    ]);
                    $action_taken = 'removed';
                } else {
                    // Cambiar a la nueva reacción
                    $stmtUpdate = $conexion->prepare("
                        UPDATE chat_reacciones 
                        SET tipo_reaccion = :tipo_reaccion, fecha_reaccion = NOW()
                        WHERE mensaje_id = :mensaje_id AND usuario_id = :usuario_id
                    ");
                    $stmtUpdate->execute([
                        ':mensaje_id' => $mensaje_id,
                        ':usuario_id' => $usuario_id,
                        ':tipo_reaccion' => $tipo_reaccion
                    ]);
                    $action_taken = 'changed';
                }
            } else {
                // Agregar nueva reacción
                $stmtInsert = $conexion->prepare("
                    INSERT INTO chat_reacciones (mensaje_id, usuario_id, tipo_reaccion) 
                    VALUES (:mensaje_id, :usuario_id, :tipo_reaccion)
                ");
                $stmtInsert->execute([
                    ':mensaje_id' => $mensaje_id,
                    ':usuario_id' => $usuario_id,
                    ':tipo_reaccion' => $tipo_reaccion
                ]);
                $action_taken = 'added';
            }
            
            // Obtener conteo actualizado de reacciones para este mensaje
            $reactions = getMessageReactions($conexion, $mensaje_id);
            
            echo json_encode([
                'success' => true,
                'action' => $action_taken,
                'reactions' => $reactions
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
        }
        break;
        
    case 'get_reactions':
        if ($mensaje_id === 0) {
            echo json_encode(['error' => 'ID de mensaje requerido']);
            exit;
        }
        
        try {
            $reactions = getMessageReactions($conexion, $mensaje_id);
            echo json_encode([
                'success' => true,
                'reactions' => $reactions
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al obtener reacciones: ' . $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

function getMessageReactions($conexion, $mensaje_id) {
    $stmt = $conexion->prepare("
        SELECT 
            cr.tipo_reaccion,
            COUNT(*) as total,
            GROUP_CONCAT(u.usuario SEPARATOR ', ') as usuarios,
            MAX(CASE WHEN cr.usuario_id = :user_id THEN 1 ELSE 0 END) as user_reacted
        FROM chat_reacciones cr
        INNER JOIN usuarios u ON cr.usuario_id = u.id_use
        WHERE cr.mensaje_id = :mensaje_id
        GROUP BY cr.tipo_reaccion
        ORDER BY total DESC
    ");
    $stmt->execute([
        ':mensaje_id' => $mensaje_id,
        ':user_id' => $_SESSION['id'] ?? 0
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>