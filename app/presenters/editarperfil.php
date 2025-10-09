<?php
session_start();
include __DIR__.'/../models/config.php';
include __DIR__.'/../models/socialnetwork-lib.php';

// Evitar mostrar errores de advertencia
error_reporting(E_ALL & ~E_NOTICE);

// Redirigir si no está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // seguridad

    if ($_SESSION['id'] != $id) {
        header("Location: login.php");
        exit();
    }

    // Traer datos del usuario
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $use = $stmt->fetch(PDO::FETCH_ASSOC);

    // Actualizar datos
    if (isset($_POST['actualizar'])) {
        $nombre = trim($_POST['nombre']);
        $usuario = trim($_POST['usuario']);
        $email = trim($_POST['email']);
        $sexo = $_POST['sexo'];

        // Comprobar usuario único
        $stmtCheck = $conexion->prepare("SELECT id_use FROM usuarios WHERE usuario = :usuario AND id_use != :id");
        $stmtCheck->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$resCheck) {
            // Manejar avatar de forma óptima: solo 1 archivo por usuario, extensión jpg
            $avatarDefault = 'default_avatar.svg';
            $avatarDir = __DIR__.'/../../public/avatars/';
            $avatarName = $id . '.jpg';
            $avatarPath = $avatarDir . $avatarName;
            $errorAvatar = '';
            // Eliminar avatares viejos (jpg, png, jpeg, gif)
            foreach (['jpg','jpeg','png','gif'] as $ext) {
                $old = $avatarDir . $id . '.' . $ext;
                if (file_exists($old)) @unlink($old);
            }
            if (!empty($_FILES['avatar']['tmp_name'])) {
                $fileTmp = $_FILES['avatar']['tmp_name'];
                $imgInfo = getimagesize($fileTmp);
                if ($imgInfo !== false) {
                    $image = null;
                    switch ($imgInfo[2]) {
                        case IMAGETYPE_JPEG:
                            $image = imagecreatefromjpeg($fileTmp);
                            break;
                        case IMAGETYPE_PNG:
                            $image = imagecreatefrompng($fileTmp);
                            break;
                        case IMAGETYPE_GIF:
                            $image = imagecreatefromgif($fileTmp);
                            break;
                    }
                    if ($image) {
                        if (imagejpeg($image, $avatarPath, 90)) {
                            imagedestroy($image);
                            $avatarFinal = $avatarName;
                        } else {
                            $errorAvatar = 'Error al guardar el archivo JPG.';
                            $avatarFinal = $use['avatar'] ?: $avatarDefault;
                        }
                    } else {
                        $errorAvatar = 'Error al procesar la imagen (formato no soportado o corrupto).';
                        $avatarFinal = $use['avatar'] ?: $avatarDefault;
                    }
                } else {
                    $errorAvatar = 'No se pudo obtener información de la imagen.';
                    $avatarFinal = $use['avatar'] ?: $avatarDefault;
                }
            } else {
                // Si el usuario elimina su avatar, poner el SVG por defecto
                if (isset($_POST['eliminar_avatar']) && $_POST['eliminar_avatar'] == '1') {
                    $avatarFinal = $avatarDefault;
                } else {
                    $avatarFinal = $use['avatar'] ?: $avatarDefault;
                }
            }

            // Actualizar datos en la BD
            $stmtUpdate = $conexion->prepare("UPDATE usuarios SET nombre = :nombre, usuario = :usuario, email = :email, sexo = :sexo, avatar = :avatar WHERE id_use = :id");
            $stmtUpdate->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':email', $email, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':sexo', $sexo, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':avatar', $avatarFinal, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmtUpdate->execute()) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['avatar'] = $avatarFinal;
                if ($errorAvatar) {
                    $error = $errorAvatar;
                } else {
                    header("Location: /converza/app/view?id=$id");
                    exit();
                }
            } else {
                $error = "Error al actualizar tus datos en la base de datos.";
                if ($errorAvatar) $error .= "<br>".$errorAvatar;
            }
        } else {
            $error = "El nombre de usuario ya está en uso, escoja otro.";
        }
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Editar mi perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-lg p-4 w-100" style="max-width: 520px;">
                     <div class="d-flex justify-content-between align-items-center mb-4">
                         <h2 class="mb-0 text-center flex-grow-1">Editar perfil</h2>
                         <a href="/converza/app/presenters/perfil.php?id=<?php echo (int)$id; ?>" class="btn btn-light btn-sm ms-2" title="Cerrar" style="border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:none;"><span aria-hidden="true">&times;</span></a>
                     </div>
        <form method="post" enctype="multipart/form-data">
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($use['nombre']); ?>">
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" value="<?php echo htmlspecialchars($use['usuario']); ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($use['email']); ?>">
            </div>
            <div class="mb-3">
                <label for="avatar" class="form-label">Cambiar avatar</label>
                <div class="d-flex align-items-center gap-3">
                    <input type="file" id="avatar" name="avatar" accept="image/*" style="display:none" onchange="previewAvatar(event)">
                    <label for="avatar" class="btn btn-outline-secondary mb-0" style="display:inline-flex;align-items:center;gap:4px;cursor:pointer;">
                        <i class="bi bi-paperclip fs-5"></i>
                        <span>Adjuntar</span>
                    </label>
                    <span id="avatarPreviewContainer">
                    <?php
                    $avatarActual = $use['avatar'];
                    $avatarWebPath = '/converza/public/avatars/' . $id . '.jpg';
                    $avatarPath = __DIR__.'/../../public/avatars/' . $id . '.jpg';
                    if ($avatarActual && $avatarActual !== 'default_avatar.svg' && file_exists($avatarPath)) {
                        echo '<img src="'.$avatarWebPath.'" class="rounded-circle border bg-secondary" width="60" height="60" alt="Avatar actual" id="avatarPreview" style="object-fit:cover;">';
                    } else {
                        // SVG avatar universal
                        echo '<svg id="avatarPreview" class="rounded-circle border bg-secondary" width="60" height="60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60"><circle cx="30" cy="30" r="30" fill="#adb5bd"/><text x="50%" y="58%" text-anchor="middle" fill="#fff" font-size="28" font-family="Arial" dy=".3em">👤</text></svg>';
                    }
                    ?>
                    </span>
                </div>
            </div>
            <script>
            function previewAvatar(event) {
                const input = event.target;
                const container = document.getElementById('avatarPreviewContainer');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        container.innerHTML = '<img id="avatarPreview" src="' + e.target.result + '" class="rounded-circle border bg-secondary" width="60" height="60" alt="Avatar previsualizado" style="object-fit:cover;">';
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
            </script>
            <div class="mb-3">
                <label class="form-label">Sexo</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sexo" id="sexoH" value="H" <?php if ($use['sexo'] == 'H') echo 'checked'; ?>>
                    <label class="form-check-label" for="sexoH">Hombre</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sexo" id="sexoM" value="M" <?php if ($use['sexo'] == 'M') echo 'checked'; ?>>
                    <label class="form-check-label" for="sexoM">Mujer</label>
                </div>
            </div>

            <button type="submit" name="actualizar" class="btn btn-primary w-100">Actualizar datos</button>

        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
