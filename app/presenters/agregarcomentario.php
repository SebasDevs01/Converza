<?php
require(__DIR__.'/../models/config.php'); // Aquí tienes tu conexión PDO en $conexion
require_once(__DIR__.'/../models/bloqueos-helper.php');

// Verificar si el usuario está logueado (opcional, depende de tu sistema)
session_start();

// Verificar si el usuario está bloqueado antes de permitir comentarios
if (isset($_SESSION['id']) && isUserBlocked($_SESSION['id'], $conexion)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Usuario bloqueado. No puedes realizar esta acción.']);
    exit();
}

// Cambiar la ruta base para reflejar la URL correcta del proyecto
if (!defined('BASE_URL')) {
    define('BASE_URL', '/converza/app/view/');
}

// Cambiar la ruta de redireccionamiento para que se ajuste correctamente
if (!defined('REDIRECT_URL')) {
    define('REDIRECT_URL', '/converza/app/view/agregarcomentario.php');
}

// Redirigir en caso de error
if (!file_exists(__FILE__)) {
    header('Location: ' . BASE_URL . 'error.php');
    exit;
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

                // Obtener usuario de la publicación para notificación
                $stmt2 = $conexion->prepare("SELECT usuario FROM publicaciones WHERE id_pub = :id_pub");
                $stmt2->execute([':id_pub' => $publicacion]);
                $ll = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($ll) {
                    $usuario2 = (int)$ll['usuario'];

                    // Solo crear notificación si el comentario no es del mismo usuario que hizo la publicación
                    if ($usuario !== $usuario2) {
                        $stmt3 = $conexion->prepare("INSERT INTO notificaciones (user1, user2, tipo, leido, fecha, id_pub) 
                                                    VALUES (:user1, :user2, 'ha comentado', 0, NOW(), :id_pub)");
                        $stmt3->execute([
                            ':user1' => $usuario,
                            ':user2' => $usuario2,
                            ':id_pub' => $publicacion
                        ]);
                    }
                }

                // Respuesta exitosa
                $response = [
                    'status' => 'success',
                    'message' => 'Tu comentario ha sido publicado.'
                ];

            } catch (PDOException $e) {
                error_log("Error en base de datos: " . $e->getMessage());
                $response = [
                    'status' => 'error',
                    'message' => 'Ocurrió un problema al guardar el comentario. Por favor, inténtalo de nuevo.'
                ];
            } catch (Exception $e) {
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

    // Si es una petición AJAX, devolver JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Ajustar la lógica para redirigir directamente al index
    if ($response['status'] === 'success') {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}
?>
