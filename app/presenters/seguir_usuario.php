<?php
session_start();
require_once __DIR__ . '/../models/config.php';
require_once __DIR__ . '/../models/bloqueos-helper.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado', 'debug' => 'Sesión no iniciada']);
    exit;
}

$usuarioActual = (int)$_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $usuarioSeguir = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
    
    // Validaciones básicas
    if (!$usuarioSeguir || $usuarioSeguir === $usuarioActual) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuario inválido']);
        exit;
    }
    
    // Verificar bloqueo mutuo antes de cualquier acción
    $bloqueoInfo = verificarBloqueoMutuo($conexion, $usuarioActual, $usuarioSeguir);
    if ($bloqueoInfo['bloqueado']) {
        http_response_code(403);
        echo json_encode(['error' => 'No es posible seguir a este usuario']);
        exit;
    }
    
    // Verificar que el usuario a seguir existe
    try {
        $stmt = $conexion->prepare("SELECT id_use, usuario FROM usuarios WHERE id_use = ? AND tipo != 'blocked'");
        $stmt->execute([$usuarioSeguir]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            exit;
        }
        
        if ($accion === 'seguir') {
            // Intentar seguir al usuario
            try {
                $stmt = $conexion->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
                $stmt->execute([$usuarioActual, $usuarioSeguir]);
                
                // Obtener números actualizados
                $seguidores = obtenerContadorSeguidores($conexion, $usuarioSeguir);
                $siguiendo = obtenerContadorSiguiendo($conexion, $usuarioActual);
                
                echo json_encode([
                    'success' => true,
                    'accion' => 'seguido',
                    'mensaje' => 'Ahora sigues a ' . htmlspecialchars($usuario['usuario']),
                    'seguidores' => $seguidores,
                    'siguiendo' => $siguiendo
                ]);
                
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    echo json_encode(['error' => 'Ya sigues a este usuario']);
                } else {
                    echo json_encode(['error' => 'Error al seguir usuario']);
                }
            }
            
        } elseif ($accion === 'dejar_seguir') {
            // Dejar de seguir al usuario
            $stmt = $conexion->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
            $resultado = $stmt->execute([$usuarioActual, $usuarioSeguir]);
            
            if ($stmt->rowCount() > 0) {
                // Obtener números actualizados
                $seguidores = obtenerContadorSeguidores($conexion, $usuarioSeguir);
                $siguiendo = obtenerContadorSiguiendo($conexion, $usuarioActual);
                
                echo json_encode([
                    'success' => true,
                    'accion' => 'no_seguido',
                    'mensaje' => 'Ya no sigues a ' . htmlspecialchars($usuario['usuario']),
                    'seguidores' => $seguidores,
                    'siguiendo' => $siguiendo
                ]);
            } else {
                echo json_encode(['error' => 'No seguías a este usuario']);
            }
            
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Acción inválida']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener estado de seguimiento
    $usuarioConsultar = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;
    
    if (!$usuarioConsultar) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuario requerido']);
        exit;
    }
    
    try {
        // Verificar si ya sigue al usuario
        $stmt = $conexion->prepare("SELECT COUNT(*) as siguiendo FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $stmt->execute([$usuarioActual, $usuarioConsultar]);
        $resultado = $stmt->fetch();
        
        // Obtener contadores
        $seguidores = obtenerContadorSeguidores($conexion, $usuarioConsultar);
        $siguiendo = obtenerContadorSiguiendo($conexion, $usuarioConsultar);
        
        echo json_encode([
            'siguiendo' => (bool)$resultado['siguiendo'],
            'seguidores' => $seguidores,
            'siguiendo_count' => $siguiendo
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error del servidor']);
    }
}

// Función auxiliar para obtener número de seguidores
function obtenerContadorSeguidores($conexion, $usuarioId) {
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM seguidores WHERE seguido_id = ?");
    $stmt->execute([$usuarioId]);
    $resultado = $stmt->fetch();
    return (int)$resultado['total'];
}

// Función auxiliar para obtener número de usuarios seguidos
function obtenerContadorSiguiendo($conexion, $usuarioId) {
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM seguidores WHERE seguidor_id = ?");
    $stmt->execute([$usuarioId]);
    $resultado = $stmt->fetch();
    return (int)$resultado['total'];
}
?>