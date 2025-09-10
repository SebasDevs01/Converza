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

$stmtUpd = $conexion->prepare("UPDATE publicaciones SET contenido = :contenido WHERE id_pub = :id_pub");
$stmtUpd->execute([
    ':contenido' => $contenido,
    ':id_pub'    => $id_pub
]);

header("Location: ../view/index.php");
exit;
