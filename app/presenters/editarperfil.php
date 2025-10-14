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
        
        // Nuevos campos de personalización
        $bio = trim($_POST['bio'] ?? '');
        $descripcion_corta = trim($_POST['descripcion_corta'] ?? '');
        $signo_zodiacal = $_POST['signo_zodiacal'] ?? null;
        $genero = $_POST['genero'] ?? null;
        $mostrar_icono_genero = isset($_POST['mostrar_icono_genero']) ? 1 : 0;
        $estado_animo = $_POST['estado_animo'] ?? null;
        $mostrar_karma = isset($_POST['mostrar_karma']) ? 1 : 0;
        $mostrar_signo = isset($_POST['mostrar_signo']) ? 1 : 0;
        $mostrar_estado_animo = isset($_POST['mostrar_estado_animo']) ? 1 : 0;

        // Comprobar usuario único
        $stmtCheck = $conexion->prepare("SELECT id_use FROM usuarios WHERE usuario = :usuario AND id_use != :id");
        $stmtCheck->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$resCheck) {
            // Manejar avatar de forma óptima: solo 1 archivo por usuario, extensión jpg
            $avatarDefault = 'defect.jpg';
            $avatarDir = __DIR__.'/../../public/avatars/';
            $avatarName = $id . '.jpg';
            $avatarPath = $avatarDir . $avatarName;
            $errorAvatar = '';
            $avatarFinal = $use['avatar']; // Mantener el avatar actual por defecto
            
            // Solo procesar avatar si se subió un archivo nuevo
            if (!empty($_FILES['avatar']['tmp_name'])) {
                // Eliminar avatares viejos solo si se está subiendo uno nuevo
                foreach (['jpg','jpeg','png','gif'] as $ext) {
                    $old = $avatarDir . $id . '.' . $ext;
                    if (file_exists($old)) @unlink($old);
                }
                
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
                        }
                    } else {
                        $errorAvatar = 'Error al procesar la imagen (formato no soportado o corrupto).';
                    }
                } else {
                    $errorAvatar = 'No se pudo obtener información de la imagen.';
                }
            } elseif (isset($_POST['eliminar_avatar']) && $_POST['eliminar_avatar'] == '1') {
                // Si el usuario explícitamente quiere eliminar su avatar
                foreach (['jpg','jpeg','png','gif'] as $ext) {
                    $old = $avatarDir . $id . '.' . $ext;
                    if (file_exists($old)) @unlink($old);
                }
                $avatarFinal = $avatarDefault;
            }
            // Si no hay archivo nuevo ni solicitud de eliminar, mantener $avatarFinal = $use['avatar']

            // Actualizar datos en la BD
            $stmtUpdate = $conexion->prepare("UPDATE usuarios SET 
                nombre = :nombre, 
                usuario = :usuario, 
                email = :email, 
                sexo = :sexo, 
                avatar = :avatar,
                bio = :bio,
                descripcion_corta = :descripcion_corta,
                signo_zodiacal = :signo_zodiacal,
                genero = :genero,
                mostrar_icono_genero = :mostrar_icono_genero,
                estado_animo = :estado_animo,
                mostrar_karma = :mostrar_karma,
                mostrar_signo = :mostrar_signo,
                mostrar_estado_animo = :mostrar_estado_animo
                WHERE id_use = :id");
            $stmtUpdate->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':email', $email, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':sexo', $sexo, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':avatar', $avatarFinal, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':bio', $bio, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':descripcion_corta', $descripcion_corta, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':signo_zodiacal', $signo_zodiacal, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':genero', $genero, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':mostrar_icono_genero', $mostrar_icono_genero, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':estado_animo', $estado_animo, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':mostrar_karma', $mostrar_karma, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':mostrar_signo', $mostrar_signo, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':mostrar_estado_animo', $mostrar_estado_animo, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmtUpdate->execute()) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['avatar'] = $avatarFinal;
                if ($errorAvatar) {
                    $error = $errorAvatar;
                } else {
                    header("Location: perfil.php?id=$id");
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
    header("Location: perfil.php?id=".$_SESSION['id']);
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
    <style>
        .zodiac-icon { font-size: 2em; cursor: pointer; opacity: 0.5; transition: all 0.3s; }
        .zodiac-icon:hover, .zodiac-icon.selected { opacity: 1; transform: scale(1.2); }
        .mood-icon { font-size: 1.8em; cursor: pointer; opacity: 0.5; transition: all 0.3s; }
        .mood-icon:hover, .mood-icon.selected { opacity: 1; transform: scale(1.15); }
        .gender-option { cursor: pointer; padding: 15px; border: 2px solid #dee2e6; border-radius: 10px; transition: all 0.3s; }
        .gender-option:hover { border-color: #667eea; background: #f8f9fa; }
        .gender-option.selected { border-color: #667eea; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); }
        .gender-icon-male { color: #3b82f6; font-size: 2em; }
        .gender-icon-female { color: #ec4899; font-size: 2em; }
        .gender-icon-other { color: #9333ea; font-size: 2em; }
    </style>
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="card shadow-lg p-4 w-100" style="max-width: 700px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 text-center flex-grow-1">Editar perfil</h2>
            <a href="/converza/app/presenters/perfil.php?id=<?php echo (int)$id; ?>" class="btn btn-light btn-sm ms-2" title="Cerrar" style="border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;box-shadow:none;"><span aria-hidden="true">&times;</span></a>
        </div>
        
        <!-- Tabs de navegación -->
        <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">
                    <i class="bi bi-person"></i> Básico
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="personalization-tab" data-bs-toggle="tab" data-bs-target="#personalization" type="button">
                    <i class="bi bi-palette"></i> Personalización
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button">
                    <i class="bi bi-shield-check"></i> Privacidad
                </button>
            </li>
        </ul>

        <form method="post" enctype="multipart/form-data">
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            
            <div class="tab-content" id="profileTabsContent">
                
                <!-- TAB 1: Información Básica -->
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($use['nombre']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" name="usuario" id="usuario" class="form-control" value="<?php echo htmlspecialchars($use['usuario']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($use['email']); ?>" required>
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
                            $avatarWebPath = '/Converza/public/avatars/' . $id . '.jpg';
                            $avatarPath = __DIR__.'/../../public/avatars/' . $id . '.jpg';
                            if ($avatarActual && $avatarActual !== 'default_avatar.svg' && file_exists($avatarPath)) {
                                echo '<img src="'.$avatarWebPath.'" class="rounded-circle border bg-secondary" width="60" height="60" alt="Avatar actual" id="avatarPreview" style="object-fit:cover;">';
                            } else {
                                echo '<svg id="avatarPreview" class="rounded-circle border bg-secondary" width="60" height="60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60"><circle cx="30" cy="30" r="30" fill="#adb5bd"/><text x="50%" y="58%" text-anchor="middle" fill="#fff" font-size="28" font-family="Arial" dy=".3em">👤</text></svg>';
                            }
                            ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Personalización -->
                <div class="tab-pane fade" id="personalization" role="tabpanel">
                    <div class="mb-4">
                        <label for="bio" class="form-label">Biografía</label>
                        <textarea name="bio" id="bio" class="form-control" rows="3" maxlength="500" placeholder="Cuéntanos sobre ti..."><?php echo htmlspecialchars($use['bio'] ?? ''); ?></textarea>
                        <small class="text-muted">Máximo 500 caracteres</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="descripcion_corta" class="form-label">Descripción corta</label>
                        <input type="text" name="descripcion_corta" id="descripcion_corta" class="form-control" maxlength="100" placeholder="Una frase que te describa" value="<?php echo htmlspecialchars($use['descripcion_corta'] ?? ''); ?>">
                        <small class="text-muted">Aparece en tu tarjeta de perfil</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Signo Zodiacal</label>
                        <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                            <?php
                            $signos = [
                                'aries' => '♈', 'tauro' => '♉', 'geminis' => '♊', 'cancer' => '♋',
                                'leo' => '♌', 'virgo' => '♍', 'libra' => '♎', 'escorpio' => '♏',
                                'sagitario' => '♐', 'capricornio' => '♑', 'acuario' => '♒', 'piscis' => '♓'
                            ];
                            foreach ($signos as $key => $icon) {
                                $selected = ($use['signo_zodiacal'] ?? '') == $key ? 'selected' : '';
                                echo "<span class='zodiac-icon {$selected}' data-signo='{$key}' title='".ucfirst($key)."'>{$icon}</span>";
                            }
                            ?>
                        </div>
                        <input type="hidden" name="signo_zodiacal" id="signo_zodiacal" value="<?php echo htmlspecialchars($use['signo_zodiacal'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Género</label>
                        <div class="row g-2">
                            <?php
                            $generos = [
                                'masculino' => ['icon' => '♂', 'class' => 'male', 'label' => 'Masculino'],
                                'femenino' => ['icon' => '♀', 'class' => 'female', 'label' => 'Femenino'],
                                'otro' => ['icon' => '⚧', 'class' => 'other', 'label' => 'Otro'],
                                'prefiero_no_decir' => ['icon' => '❓', 'class' => 'other', 'label' => 'Prefiero no decir']
                            ];
                            foreach ($generos as $key => $data) {
                                $selected = ($use['genero'] ?? '') == $key ? 'selected' : '';
                                echo "<div class='col-6'>";
                                echo "<div class='gender-option text-center {$selected}' data-genero='{$key}'>";
                                echo "<div class='gender-icon-{$data['class']}'>{$data['icon']}</div>";
                                echo "<small>{$data['label']}</small>";
                                echo "</div>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                        <input type="hidden" name="genero" id="genero" value="<?php echo htmlspecialchars($use['genero'] ?? ''); ?>">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="mostrar_icono_genero" id="mostrar_icono_genero" <?php if ($use['mostrar_icono_genero'] ?? true) echo 'checked'; ?>>
                            <label class="form-check-label" for="mostrar_icono_genero">
                                Mostrar ícono de género en mi perfil
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">¿Cómo te sientes hoy?</label>
                        <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                            <?php
                            $estados = [
                                'feliz' => '😊', 'emocionado' => '🤩', 'relajado' => '😌', 'creativo' => '🎨',
                                'cansado' => '😴', 'ocupado' => '⏰', 'triste' => '😢', 'enojado' => '😠',
                                'motivado' => '💪', 'inspirado' => '✨', 'pensativo' => '🤔', 'nostalgico' => '🌅'
                            ];
                            foreach ($estados as $key => $emoji) {
                                $selected = ($use['estado_animo'] ?? '') == $key ? 'selected' : '';
                                echo "<span class='mood-icon {$selected}' data-mood='{$key}' title='".ucfirst($key)."'>{$emoji}</span>";
                            }
                            ?>
                        </div>
                        <input type="hidden" name="estado_animo" id="estado_animo" value="<?php echo htmlspecialchars($use['estado_animo'] ?? ''); ?>">
                    </div>
                </div>

                <!-- TAB 3: Privacidad -->
                <div class="tab-pane fade" id="privacy" role="tabpanel">
                    <p class="text-muted mb-4">Controla qué información quieres mostrar públicamente en tu perfil</p>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="mostrar_karma" id="mostrar_karma" <?php if ($use['mostrar_karma'] ?? true) echo 'checked'; ?>>
                        <label class="form-check-label" for="mostrar_karma">
                            <i class="bi bi-star text-warning"></i> Mostrar mis puntos de Karma
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="mostrar_signo" id="mostrar_signo" <?php if ($use['mostrar_signo'] ?? true) echo 'checked'; ?>>
                        <label class="form-check-label" for="mostrar_signo">
                            <i class="bi bi-moon-stars text-primary"></i> Mostrar mi signo zodiacal
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="mostrar_estado_animo" id="mostrar_estado_animo" <?php if ($use['mostrar_estado_animo'] ?? true) echo 'checked'; ?>>
                        <label class="form-check-label" for="mostrar_estado_animo">
                            <i class="bi bi-emoji-smile text-success"></i> Mostrar mi estado de ánimo
                        </label>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i> <strong>Tip:</strong> Puedes cambiar estos ajustes en cualquier momento
                    </div>
                </div>

            </div>

            <button type="submit" name="actualizar" class="btn btn-primary w-100 mt-4">
                <i class="bi bi-check-circle"></i> Guardar cambios
            </button>
        </form>
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

// Selector de signos zodiacales
document.querySelectorAll('.zodiac-icon').forEach(icon => {
    icon.addEventListener('click', function() {
        document.querySelectorAll('.zodiac-icon').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('signo_zodiacal').value = this.dataset.signo;
    });
});

// Selector de género
document.querySelectorAll('.gender-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.gender-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('genero').value = this.dataset.genero;
    });
});

// Selector de estado de ánimo
document.querySelectorAll('.mood-icon').forEach(icon => {
    icon.addEventListener('click', function() {
        document.querySelectorAll('.mood-icon').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('estado_animo').value = this.dataset.mood;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>