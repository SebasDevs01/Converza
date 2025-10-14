<?php
// ==========================================
// IMPORTANTE: No debe haber NINGÚN espacio o texto antes de <?php
// ==========================================

// Deshabilitar TODOS los errores para JSON limpio
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../comentarios_debug.log');

// Limpiar CUALQUIER salida antes de enviar JSON
ob_start();

// Iniciar sesión PRIMERO (si no está iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require(__DIR__.'/../models/config.php'); // Aquí tienes tu conexión PDO en $conexion
require_once(__DIR__.'/../models/bloqueos-helper.php');
require_once(__DIR__.'/../models/notificaciones-triggers.php');

// 🌟 Sistema de Karma Social (opcional - no romper si no existe)
$karmaTriggers = null;
if (file_exists(__DIR__.'/../models/karma-social-triggers.php')) {
    require_once(__DIR__.'/../models/karma-social-triggers.php');
    $karmaTriggers = new KarmaSocialTriggers($conexion);
}

// Instanciar sistema de notificaciones
$notificacionesTriggers = new NotificacionesTriggers($conexion);

// Verificar si el usuario está bloqueado antes de permitir comentarios
if (isset($_SESSION['id']) && isUserBlocked($_SESSION['id'], $conexion)) {
    ob_end_clean(); // Limpiar buffer
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Usuario bloqueado. No puedes realizar esta acción.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    if (!empty($_POST['usuario']) && !empty($_POST['comentario']) && !empty($_POST['publicacion'])) {
        // Sanear y asignar variables
        $usuario     = (int)trim($_POST['usuario']);
        $comentario  = trim($_POST['comentario']);
        $publicacion = (int)$_POST['publicacion'];

        // Validar que los IDs sean válidos
        if ($usuario > 0 && $publicacion > 0 && strlen($comentario) > 0) {
            try {
                // Verificar que la publicación existe y obtener su autor
                $stmt_check = $conexion->prepare("SELECT id_pub, usuario FROM publicaciones WHERE id_pub = :id_pub");
                $stmt_check->execute([':id_pub' => $publicacion]);
                $pub_data = $stmt_check->fetch();
                
                if (!$pub_data) {
                    throw new Exception("La publicación no existe.");
                }
                
                // Verificar bloqueo con el autor de la publicación
                $bloqueoInfo = verificarBloqueoMutuo($conexion, $usuario, $pub_data['usuario']);
                if ($bloqueoInfo['bloqueado']) {
                    throw new Exception("No puedes comentar en esta publicación.");
                }

                // Insertar comentario
                $stmt = $conexion->prepare("INSERT INTO comentarios (usuario, comentario, publicacion) 
                                            VALUES (:usuario, :comentario, :publicacion)");
                $stmt->execute([
                    ':usuario' => $usuario,
                    ':comentario' => $comentario,
                    ':publicacion' => $publicacion
                ]);
                
                // Obtener el ID del comentario recién insertado
                $comentarioId = (int)$conexion->lastInsertId();
                
                if ($comentarioId === 0) {
                    // Fallback: buscar el último comentario insertado
                    $stmtLastId = $conexion->prepare("
                        SELECT id_com FROM comentarios 
                        WHERE usuario = :usuario AND publicacion = :publicacion 
                        ORDER BY id_com DESC LIMIT 1
                    ");
                    $stmtLastId->execute([
                        ':usuario' => $usuario,
                        ':publicacion' => $publicacion
                    ]);
                    $lastComment = $stmtLastId->fetch(PDO::FETCH_ASSOC);
                    $comentarioId = (int)($lastComment['id_com'] ?? 0);
                }

                // Obtener usuario de la publicación para notificación
                $stmt2 = $conexion->prepare("SELECT usuario FROM publicaciones WHERE id_pub = :id_pub");
                $stmt2->execute([':id_pub' => $publicacion]);
                $ll = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($ll) {
                    $usuario2 = (int)$ll['usuario'];

                    // Solo crear notificación si el comentario no es del mismo usuario que hizo la publicación
                    if ($usuario !== $usuario2) {
                        // Obtener nombre del comentador
                        $stmtNombre = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
                        $stmtNombre->execute([':id' => $usuario]);
                        $datosComentador = $stmtNombre->fetch(PDO::FETCH_ASSOC);
                        $nombreComentador = $datosComentador['usuario'] ?? 'Usuario';
                        
                        // Enviar notificación usando el sistema de triggers
                        $notificacionesTriggers->nuevoComentario($usuario, $usuario2, $nombreComentador, $publicacion, $comentario);
                        
                        // 🌟 REGISTRAR KARMA SOCIAL AUTOMÁTICAMENTE (si está disponible)
                        if ($karmaTriggers !== null) {
                            $karmaTriggers->nuevoComentario($usuario, $comentarioId, $comentario);
                        }
                        
                        // NOTA: No insertamos en tabla notificaciones porque el sistema de triggers YA lo hace
                        // Si tu tabla usa la estructura VIEJA (user1, user2), descomentar:
                        /*
                        $stmt3 = $conexion->prepare("INSERT INTO notificaciones (user1, user2, tipo, leido, fecha, id_pub) 
                                                    VALUES (:user1, :user2, 'ha comentado', 0, NOW(), :id_pub)");
                        $stmt3->execute([
                            ':user1' => $usuario,
                            ':user2' => $usuario2,
                            ':id_pub' => $publicacion
                        ]);
                        */
                    }
                }

                // Respuesta exitosa con datos del comentario
                // Obtener datos del usuario que comentó
                $stmtUser = $conexion->prepare("SELECT usuario, avatar FROM usuarios WHERE id_use = :id");
                $stmtUser->execute([':id' => $usuario]);
                $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
                
                // 🚀 OPTIMIZACIÓN: Incluir karma actualizado en la respuesta
                $karmaActualizado = null;
                if (isset($_SESSION['id'])) {
                    try {
                        require_once(__DIR__.'/../models/karma-social-helper.php');
                        $karmaHelper = new KarmaSocialHelper($conexion);
                        $karmaData = $karmaHelper->obtenerKarmaUsuario($_SESSION['id']);
                        
                        $karmaActualizado = [
                            'karma' => $karmaData['karma_total'],
                            'nivel' => $karmaData['nivel_data']['nivel'] ?? 1,
                            'nivel_titulo' => $karmaData['nivel_data']['titulo'] ?? $karmaData['nivel'],
                            'nivel_emoji' => $karmaData['nivel_emoji']
                        ];
                    } catch (Exception $e) {
                        error_log("Error obteniendo karma actualizado: " . $e->getMessage());
                    }
                }
                
                $response = [
                    'status' => 'success',
                    'message' => 'Tu comentario ha sido publicado.',
                    'comentario' => [
                        'id' => $comentarioId, // Usar el ID capturado
                        'usuario' => $userData['usuario'] ?? 'Usuario',
                        'avatar' => $userData['avatar'] ?? 'defect.jpg',
                        'comentario' => htmlspecialchars($comentario),
                        'fecha' => date('Y-m-d H:i:s')
                    ],
                    'karma_actualizado' => $karmaActualizado // 🚀 Karma incluido en la respuesta
                ];

            } catch (PDOException $e) {
                error_log("ERROR PDO: " . $e->getMessage());
                $response = [
                    'status' => 'error',
                    'message' => 'Ocurrió un problema al guardar el comentario. Por favor, inténtalo de nuevo.'
                ];
            } catch (Exception $e) {
                error_log("ERROR Exception: " . $e->getMessage());
                $response = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }

        } else {
            $response = [
                'status' => 'error',
                'message' => 'Datos no válidos.'
            ];
        }
    } else {
        $response = [
            'status' => 'warning',
            'message' => 'Por favor, complete todos los campos.'
        ];
    }

    // Siempre devolver JSON para compatibilidad con AJAX
    ob_end_clean(); // Limpiar cualquier salida accidental
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}
?>
