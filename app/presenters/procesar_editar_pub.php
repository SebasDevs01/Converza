<?php
session_start();
require_once(__DIR__.'/../models/config.php');

if (!isset($_SESSION['id'])) {
    die("Error: Sesión no iniciada.");
}

if (!isset($_POST['id_pub'])) {
    die("Error: ID de publicación no recibido.");
}

$id_pub = (int)$_POST['id_pub'];
$contenido = trim($_POST['contenido'] ?? '');

$stmt = $conexion->prepare("SELECT * FROM publicaciones WHERE id_pub = :id_pub AND usuario = :usuario");
$stmt->execute([
    ':id_pub'  => $id_pub,
    ':usuario' => $_SESSION['id']
]);
$pub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pub) {
    die("Error: No tienes permiso para editar esta publicación.");
}

if ($contenido === '') {
    die("Error: El contenido no puede estar vacío.");
}

if (!empty($_POST['eliminar_imagenes'])) {
    foreach ($_POST['eliminar_imagenes'] as $img) {
        $ruta = __DIR__.'/../../public/publicaciones/'.$img;

        if (file_exists($ruta)) unlink($ruta);

        if ($img === $pub['imagen']) {
            $stmtDel = $conexion->prepare("UPDATE publicaciones SET imagen = NULL WHERE id_pub = :id_pub");
            $stmtDel->execute([':id_pub' => $id_pub]);
        } else {
            $stmtDel = $conexion->prepare("DELETE FROM imagenes_publicacion WHERE publicacion_id = :pubid AND nombre_imagen = :img");
            $stmtDel->execute([':pubid' => $id_pub, ':img' => $img]);
        }
    }
}


if (!empty($_FILES['reemplazo']['name'])) {
    foreach ($_FILES['reemplazo']['name'] as $oldImg => $newName) {
        if ($newName !== '' && $_FILES['reemplazo']['error'][$oldImg] === 0) {
            $ext = strtolower(pathinfo($newName, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $nombreNuevo = uniqid().'.'.$ext;
                $carpeta = __DIR__.'/../../public/publicaciones/';
                if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
                $destino = $carpeta.$nombreNuevo;

                if (move_uploaded_file($_FILES['reemplazo']['tmp_name'][$oldImg], $destino)) {
                    $rutaVieja = $carpeta.$oldImg;
                    if (file_exists($rutaVieja)) unlink($rutaVieja);

                    if ($oldImg === $pub['imagen']) {
                        $stmtUpd = $conexion->prepare("UPDATE publicaciones SET imagen = :img WHERE id_pub = :id_pub");
                        $stmtUpd->execute([':img' => $nombreNuevo, ':id_pub' => $id_pub]);
                    } else {
                        $stmtUpd = $conexion->prepare("UPDATE imagenes_publicacion SET nombre_imagen = :new WHERE publicacion_id = :pubid AND nombre_imagen = :old");
                        $stmtUpd->execute([
                            ':new'   => $nombreNuevo,
                            ':pubid' => $id_pub,
                            ':old'   => $oldImg
                        ]);
                    }
                }
            }
        }
    }
}

// ----------------------------------------------------------------
// 3. Manejar nuevas imágenes
// ----------------------------------------------------------------
if (!empty($_FILES['fotos']['name'])) {
    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    foreach ($_FILES['fotos']['name'] as $i => $name) {
        if ($_FILES['fotos']['error'][$i] === 0 && $name !== '') {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, $permitidas)) {
                $nombreImagen = uniqid().'.'.$ext;
                $carpeta = __DIR__.'/../../public/publicaciones/';
                if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
                $destino = $carpeta.$nombreImagen;

                if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $destino)) {
                    $stmtImg = $conexion->prepare("INSERT INTO imagenes_publicacion (publicacion_id, nombre_imagen) VALUES (:pub, :img)");
                    $stmtImg->execute([':pub' => $id_pub, ':img' => $nombreImagen]);
                }
            }
        }
    }
}

// Manejar eliminación de video
if (!empty($_POST['eliminar_video'])) {
    $rutaVideo = __DIR__.'/../../public/publicaciones/'.$_POST['eliminar_video'];
    if (file_exists($rutaVideo)) {
        if (unlink($rutaVideo)) {
            echo "Video eliminado correctamente: " . $_POST['eliminar_video'];
            $stmtDelVideo = $conexion->prepare("UPDATE publicaciones SET video = NULL WHERE id_pub = :id_pub");
            $stmtDelVideo->execute([':id_pub' => $id_pub]);
        } else {
            echo "Error: No se pudo eliminar el video.";
        }
    } else {
        echo "Error: El archivo de video no existe.";
    }
}

// Manejar reemplazo de video
if (!empty($_FILES['reemplazo_video']['name']) && $_FILES['reemplazo_video']['error'] === 0) {
    $extVideo = strtolower(pathinfo($_FILES['reemplazo_video']['name'], PATHINFO_EXTENSION));
    if (in_array($extVideo, ['mp4', 'webm', 'ogg'])) {
        $nombreNuevoVideo = uniqid().'.'.$extVideo;
        $carpetaVideo = __DIR__.'/../../public/publicaciones/';
        if (!is_dir($carpetaVideo)) mkdir($carpetaVideo, 0777, true);
        $destinoVideo = $carpetaVideo.$nombreNuevoVideo;

        if (move_uploaded_file($_FILES['reemplazo_video']['tmp_name'], $destinoVideo)) {
            $stmtUpdVideo = $conexion->prepare("UPDATE publicaciones SET video = :video WHERE id_pub = :id_pub");
            $stmtUpdVideo->execute([':video' => $nombreNuevoVideo, ':id_pub' => $id_pub]);
        }
    }
}

// Manejar reemplazo de contenido multimedia
if (!empty($_FILES['reemplazo_multimedia']['name']) && $_FILES['reemplazo_multimedia']['error'] === 0) {
    // Depuración: Verificar si se recibe el archivo para reemplazo
    echo "Archivo recibido para reemplazo: " . $_FILES['reemplazo_multimedia']['name'] . "\n";
    $extMultimedia = strtolower(pathinfo($_FILES['reemplazo_multimedia']['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'ogg'];
    if (in_array($extMultimedia, $permitidas)) {
        $nombreNuevoMultimedia = uniqid().'.'.$extMultimedia;
        $carpetaMultimedia = __DIR__.'/../../public/publicaciones/';
        if (!is_dir($carpetaMultimedia)) mkdir($carpetaMultimedia, 0777, true);
        $destinoMultimedia = $carpetaMultimedia.$nombreNuevoMultimedia;

        if (move_uploaded_file($_FILES['reemplazo_multimedia']['tmp_name'], $destinoMultimedia)) {
            echo "Archivo movido correctamente: " . $nombreNuevoMultimedia . "\n";
            // Eliminar contenido anterior
            if (!empty($pub['video'])) {
                $rutaVideo = $carpetaMultimedia.$pub['video'];
                if (file_exists($rutaVideo)) {
                    unlink($rutaVideo);
                    echo "Video eliminado: " . $pub['video'] . "\n";
                }
                $stmtDelVideo = $conexion->prepare("UPDATE publicaciones SET video = NULL WHERE id_pub = :id_pub");
                $stmtDelVideo->execute([':id_pub' => $id_pub]);
            }
            if (!empty($pub['imagen'])) {
                $rutaImagen = $carpetaMultimedia.$pub['imagen'];
                if (file_exists($rutaImagen)) {
                    unlink($rutaImagen);
                    echo "Imagen eliminada: " . $pub['imagen'] . "\n";
                }
                $stmtDelImagen = $conexion->prepare("UPDATE publicaciones SET imagen = NULL WHERE id_pub = :id_pub");
                $stmtDelImagen->execute([':id_pub' => $id_pub]);
            }

            // Guardar nuevo contenido
            if (in_array($extMultimedia, ['jpg', 'jpeg', 'png', 'gif'])) {
                $stmtUpdImagen = $conexion->prepare("UPDATE publicaciones SET imagen = :imagen WHERE id_pub = :id_pub");
                $stmtUpdImagen->execute([':imagen' => $nombreNuevoMultimedia, ':id_pub' => $id_pub]);
                echo "Nueva imagen guardada: " . $nombreNuevoMultimedia . "\n";
            } else {
                $stmtUpdVideo = $conexion->prepare("UPDATE publicaciones SET video = :video WHERE id_pub = :id_pub");
                $stmtUpdVideo->execute([':video' => $nombreNuevoMultimedia, ':id_pub' => $id_pub]);
                echo "Nuevo video guardado: " . $nombreNuevoMultimedia . "\n";
            }
        } else {
            echo "Error: No se pudo mover el archivo.";
        }
    } else {
        echo "Error: Formato de archivo no permitido.";
    }
} else {
    echo "Error: No se recibió archivo para reemplazo o hubo un error.";
}

// Depuración adicional: Verificar datos de publicación y archivo recibido
if (isset($id_pub)) {
    echo "ID de publicación: " . $id_pub . "\n";
} else {
    echo "Error: ID de publicación no definido.\n";
}

if (!empty($_FILES['reemplazo_multimedia']['name'])) {
    echo "Archivo recibido: " . $_FILES['reemplazo_multimedia']['name'] . "\n";
    echo "Tipo de archivo: " . $_FILES['reemplazo_multimedia']['type'] . "\n";
    echo "Tamaño de archivo: " . $_FILES['reemplazo_multimedia']['size'] . " bytes\n";
} else {
    echo "Error: No se recibió archivo para reemplazo.\n";
}

// Depuración: Verificar si se eliminan correctamente los archivos anteriores
if (!empty($pub['video'])) {
    $rutaVideo = $carpetaMultimedia.$pub['video'];
    if (file_exists($rutaVideo)) {
        echo "Intentando eliminar video: " . $rutaVideo . "\n";
        if (unlink($rutaVideo)) {
            echo "Video eliminado correctamente.\n";
        } else {
            echo "Error: No se pudo eliminar el video.\n";
        }
    } else {
        echo "Error: El video no existe en la ruta especificada.\n";
    }
}

if (!empty($pub['imagen'])) {
    $rutaImagen = $carpetaMultimedia.$pub['imagen'];
    if (file_exists($rutaImagen)) {
        echo "Intentando eliminar imagen: " . $rutaImagen . "\n";
        if (unlink($rutaImagen)) {
            echo "Imagen eliminada correctamente.\n";
        } else {
            echo "Error: No se pudo eliminar la imagen.\n";
        }
    } else {
        echo "Error: La imagen no existe en la ruta especificada.\n";
    }
}

// Depuración: Verificar si se guarda el nuevo archivo
if (isset($nombreNuevoMultimedia)) {
    echo "Intentando guardar nuevo archivo: " . $nombreNuevoMultimedia . "\n";
    if (file_exists($destinoMultimedia)) {
        echo "Nuevo archivo guardado correctamente en: " . $destinoMultimedia . "\n";
    } else {
        echo "Error: No se pudo guardar el nuevo archivo.\n";
    }
}

// Validación para garantizar que solo se reciba un tipo de medio
if (!empty($_FILES['imagen']['name']) && !empty($_FILES['video']['name'])) {
    echo "Error: No se puede subir una imagen y un video al mismo tiempo.\n";
    exit;
}

// Lógica para manejar la eliminación del medio anterior
if (!empty($_FILES['imagen']['name'])) {
    $extImagen = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $permitidasImagen = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($extImagen, $permitidasImagen)) {
        $nombreNuevaImagen = uniqid().'.'.$extImagen;
        $carpetaMedios = __DIR__.'/../../public/publicaciones/';
        if (!is_dir($carpetaMedios)) mkdir($carpetaMedios, 0777, true);
        $destinoImagen = $carpetaMedios.$nombreNuevaImagen;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destinoImagen)) {
            echo "Imagen subida correctamente: " . $nombreNuevaImagen . "\n";

            // Eliminar video anterior
            if (!empty($pub['video'])) {
                $rutaVideo = $carpetaMedios.$pub['video'];
                if (file_exists($rutaVideo)) {
                    unlink($rutaVideo);
                    echo "Video eliminado: " . $pub['video'] . "\n";
                }
                $stmtDelVideo = $conexion->prepare("UPDATE publicaciones SET video = NULL WHERE id_pub = :id_pub");
                $stmtDelVideo->execute([':id_pub' => $id_pub]);
            }

            // Guardar nueva imagen
            $stmtUpdImagen = $conexion->prepare("UPDATE publicaciones SET imagen = :imagen WHERE id_pub = :id_pub");
            $stmtUpdImagen->execute([':imagen' => $nombreNuevaImagen, ':id_pub' => $id_pub]);
        } else {
            echo "Error: No se pudo mover la imagen.";
        }
    } else {
        echo "Error: Formato de imagen no permitido.";
    }
} elseif (!empty($_FILES['video']['name'])) {
    $extVideo = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
    $permitidasVideo = ['mp4', 'webm', 'ogg'];

    if (in_array($extVideo, $permitidasVideo)) {
        $nombreNuevoVideo = uniqid().'.'.$extVideo;
        $carpetaMedios = __DIR__.'/../../public/publicaciones/';
        if (!is_dir($carpetaMedios)) mkdir($carpetaMedios, 0777, true);
        $destinoVideo = $carpetaMedios.$nombreNuevoVideo;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $destinoVideo)) {
            echo "Video subido correctamente: " . $nombreNuevoVideo . "\n";

            // Eliminar imagen anterior
            if (!empty($pub['imagen'])) {
                $rutaImagen = $carpetaMedios.$pub['imagen'];
                if (file_exists($rutaImagen)) {
                    unlink($rutaImagen);
                    echo "Imagen eliminada: " . $pub['imagen'] . "\n";
                }
                $stmtDelImagen = $conexion->prepare("UPDATE publicaciones SET imagen = NULL WHERE id_pub = :id_pub");
                $stmtDelImagen->execute([':id_pub' => $id_pub]);
            }

            // Guardar nuevo video
            $stmtUpdVideo = $conexion->prepare("UPDATE publicaciones SET video = :video WHERE id_pub = :id_pub");
            $stmtUpdVideo->execute([':video' => $nombreNuevoVideo, ':id_pub' => $id_pub]);
        } else {
            echo "Error: No se pudo mover el video.";
        }
    } else {
        echo "Error: Formato de video no permitido.";
    }
}

$stmtUpd = $conexion->prepare("UPDATE publicaciones SET contenido = :contenido WHERE id_pub = :id_pub");
$stmtUpd->execute([
    ':contenido' => $contenido,
    ':id_pub'    => $id_pub
]);

header("Location: ../view/index.php");
exit;
