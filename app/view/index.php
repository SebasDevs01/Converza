<?php
file_put_contents(__DIR__.'/../../public/log_publicar.txt', date('Y-m-d H:i:s')." - LLEGA AL INICIO\n", FILE_APPEND);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(__DIR__.'/../../public/log_publicar.txt', date('Y-m-d H:i:s')." - POST recibido. Campos: ".json_encode($_POST)."\n", FILE_APPEND);
    // Log de archivos recibidos
    if (isset($_FILES['fotos'])) {
        file_put_contents(__DIR__.'/../../public/log_publicar.txt', date('Y-m-d H:i:s')." - FILES['fotos']: ".json_encode($_FILES['fotos'])."\n", FILE_APPEND);
    } else {
        file_put_contents(__DIR__.'/../../public/log_publicar.txt', date('Y-m-d H:i:s')." - FILES['fotos']: NO RECIBIDO\n", FILE_APPEND);
    }
    $POST_DIAGNOSTIC = false;
} else {
    $POST_DIAGNOSTIC = false;
}
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/socialnetwork-lib.php';
// Eliminar todas las publicaciones si se solicita por el usuario (solo para admin o debug)
if (isset($_GET['eliminar_todo']) && isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') {
    $conexion->exec('DELETE FROM publicaciones');
    $conexion->exec('DELETE FROM comentarios');
    // Opcional: eliminar imágenes físicas
    $dir = __DIR__.'/../../public/publicaciones/';
    foreach (glob($dir.'*.*') as $file) {
        if (is_file($file)) unlink($file);
    }
    header('Location: index.php');
    exit();
}
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario']) || !isset($_SESSION['avatar'])) {
    header("Location: login.php");
    exit();
}
$mensaje = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['publicacion']) || (isset($_FILES['fotos']) && isset($_FILES['fotos']['name']) && $_FILES['fotos']['name'][0] !== ''))) {
    $publicacion = trim($_POST['publicacion'] ?? '');
    $album_id = null;
    $logFile = __DIR__.'/../../public/log_publicar.txt';
    function logPublicar($msg) {
        global $logFile;
        file_put_contents($logFile, date('Y-m-d H:i:s')." - ".$msg."\n", FILE_APPEND);
    }
    logPublicar('Intento de publicación: usuario_id='.(isset($_SESSION['id']) ? $_SESSION['id'] : 'N/A').', texto='.substr($publicacion,0,100));
    if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
        $error = "Sesión no iniciada. Por favor, vuelve a iniciar sesión.";
        logPublicar('Error: Sesión no iniciada.');
    } elseif (!isset($conexion) || !$conexion) {
        $error = "Error de conexión a la base de datos.";
        logPublicar('Error: Conexión PDO no disponible.');
    } else {
        $imagenesGuardadas = [];
        if (isset($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
            $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            foreach ($_FILES['fotos']['name'] as $i => $name) {
                if (!$_FILES['fotos']['error'][$i] && $_FILES['fotos']['tmp_name'][$i] && $_FILES['fotos']['name'][$i] !== '') {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (in_array($ext, $permitidas)) {
                        $nombreImagen = uniqid().'.'.$ext;
                        $carpeta = __DIR__.'/../../public/publicaciones/';
                        if (!is_dir($carpeta)) {
                            mkdir($carpeta, 0777, true);
                        }
                        $destino = $carpeta.$nombreImagen;
                        if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $destino)) {
                            $imagenesGuardadas[] = $nombreImagen;
                            logPublicar('Imagen subida correctamente: '.$nombreImagen);
                        } else {
                            $error = 'No se pudo guardar la imagen '.$name.' en el servidor.';
                            logPublicar('Error: No se pudo guardar la imagen '.$name.' en el servidor.');
                        }
                    } else {
                        $error = 'Formato de imagen no permitido: '.$name;
                        logPublicar('Error: Formato de imagen no permitido: '.$name);
                    }
                }
            }
        }
        if (!$error && ($publicacion !== '' || count($imagenesGuardadas) > 0)) {
            try {
                $stmtPub = $conexion->prepare("INSERT INTO publicaciones (usuario, contenido, imagen, album, fecha) VALUES (:usuario, :contenido, NULL, :album, NOW())");
                $stmtPub->bindParam(':usuario', $_SESSION['id'], PDO::PARAM_INT);
                $stmtPub->bindParam(':contenido', $publicacion, PDO::PARAM_STR);
                $stmtPub->bindParam(':album', $album_id, PDO::PARAM_INT);
                $stmtPub->execute();
                $pubId = $conexion->lastInsertId();
                // Guardar imágenes en la tabla imagenes_publicacion
                foreach ($imagenesGuardadas as $img) {
                    $stmtImg = $conexion->prepare("INSERT INTO imagenes_publicacion (publicacion_id, nombre_imagen) VALUES (:pub, :img)");
                    $stmtImg->bindParam(':pub', $pubId, PDO::PARAM_INT);
                    $stmtImg->bindParam(':img', $img, PDO::PARAM_STR);
                    $stmtImg->execute();
                }
                // Manejo de videos
                if (isset($_FILES['videos']) && is_array($_FILES['videos']['name'])) {
                    $permitidas = ['mp4', 'webm', 'ogg'];
                    foreach ($_FILES['videos']['name'] as $i => $name) {
                        if (!$_FILES['videos']['error'][$i] && $_FILES['videos']['tmp_name'][$i] && $_FILES['videos']['name'][$i] !== '') {
                            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                            if (in_array($ext, $permitidas)) {
                                $nombreVideo = uniqid().'.'.$ext;
                                $carpeta = __DIR__.'/../../public/publicaciones/';
                                if (!is_dir($carpeta)) {
                                    mkdir($carpeta, 0777, true);
                                }
                                $destino = $carpeta.$nombreVideo;
                                if (move_uploaded_file($_FILES['videos']['tmp_name'][$i], $destino)) {
                                    $stmtVideo = $conexion->prepare("UPDATE publicaciones SET video = :video WHERE id_pub = :pubid");
                                    $stmtVideo->bindParam(':video', $nombreVideo, PDO::PARAM_STR);
                                    $stmtVideo->bindParam(':pubid', $pubId, PDO::PARAM_INT);
                                    $stmtVideo->execute();
                                } else {
                                    echo "Error: No se pudo guardar el video $name en el servidor.";
                                }
                            } else {
                                echo "Error: Formato de video no permitido: $name.";
                            }
                        }
                    }
                }
                $mensaje = "¡Publicación creada exitosamente!";
                logPublicar('Publicación creada por usuario '.$_SESSION['id'].' ('.$_SESSION['usuario'].') con '.count($imagenesGuardadas).' imagen(es).');
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } catch (PDOException $e) {
                $error = "Error al crear la publicación. Inténtalo nuevamente. (".$e->getMessage().")";
                logPublicar('Error PDO: '.$e->getMessage());
            }
        } elseif (!$error) {
            $error = "No puedes crear una publicación vacía.";
            logPublicar('Error: Publicación vacía.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inicio | Converza</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/converza/public/css/component.css" />
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php" style="letter-spacing:2px;">Converza</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="../presenters/perfil.php?id=<?php echo (int)$_SESSION['id']; ?>"><i class="bi bi-person-circle"></i> Perfil</a></li>
                <li class="nav-item"><a class="nav-link" href="../presenters/chat.php"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
                <li class="nav-item"><a class="nav-link" href="../presenters/albumes.php?id=<?php echo (int)$_SESSION['id']; ?>"><i class="bi bi-images"></i> Álbumes</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" title="Buscar usuarios"><i class="bi bi-search"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSolicitudes" title="Solicitudes de amistad"><i class="bi bi-person-plus"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNuevos" title="Nuevos usuarios"><i class="bi bi-people"></i></a></li>
                <li class="nav-item"><a class="nav-link" href="../presenters/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="admin.php"><i class="bi bi-shield-lock"></i> Panel Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
    <?php include __DIR__.'/_navbar_panels.php'; ?>
    <?php
    // Mostrar notificaciones de la sesión
    if (!empty($_SESSION['notificaciones'])) {
        echo '<div class="container pt-3">';
        foreach ($_SESSION['notificaciones'] as $n) {
            echo '<div class="alert alert-info alert-dismissible fade show" role="alert">'
                .'<i class="bi bi-bell me-2"></i> '.htmlspecialchars($n)
                .'<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
                .'</div>';
        }
        echo '</div>';
        unset($_SESSION['notificaciones']);
    }
    ?>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 mb-4">
            <!-- Feed de publicaciones -->
            <form action="" method="POST" enctype="multipart/form-data" id="form-publicar">
                <div class="d-flex align-items-center mb-3">
                    <?php
                        $avatar = htmlspecialchars($_SESSION['avatar']);
                        $avatarPath = realpath(__DIR__.'/../../public/avatars/'.$avatar);
                        $avatarWebPath = '/converza/public/avatars/'.$avatar;
                        if ($avatar && $avatar !== 'default_avatar.svg' && $avatarPath && file_exists($avatarPath)) {
                            echo '<img src="'.$avatarWebPath.'" class="rounded-circle me-3" width="60" height="60" alt="Avatar" loading="lazy" title="Tu avatar">';
                        } else {
                            echo '<img src="/converza/public/avatars/defect.jpg" class="rounded-circle me-3" width="60" height="60" alt="Avatar por defecto" loading="lazy" title="Avatar por defecto">';
                        }
                    ?>
                    <textarea name="publicacion" class="form-control" rows="2" placeholder="¿Qué estás pensando?"></textarea>
                    <input type="file" name="fotos[]" id="file-input" class="d-none" accept="image/jpeg,image/png,image/gif" multiple>
                    <label for="file-input" class="btn btn-outline-primary ms-2" title="Adjuntar archivo"><i class="bi bi-paperclip"></i></label>
                    <input type="file" name="videos[]" id="video-input" class="d-none" accept="video/mp4,video/webm,video/ogg" multiple>
                    <label for="video-input" class="btn btn-outline-primary ms-2" title="Adjuntar video"><i class="bi bi-camera-video"></i></label>
                    <button type="submit" name="publicar" class="btn btn-primary ms-2" title="Publicar"><i class="bi bi-send"></i> Publicar</button>
                </div>
                <div id="preview-container" class="mb-2 d-flex flex-wrap gap-2"></div>
                <?php if ($mensaje): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <?php if ($POST_DIAGNOSTIC): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-bug me-2"></i> <b>Diagnóstico:</b> El servidor recibió un POST.<br>
                    <code><?php echo htmlspecialchars(json_encode($_POST)); ?></code>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
            </form>
            <div class="scroll" data-url="../presenters/publicaciones.php">
                <?php include __DIR__.'/../presenters/publicaciones.php'; ?>
            </div>
        </div>
    <!-- Panel lateral eliminado, ahora todo en offcanvas -->
        </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/converza/public/js/jquery.jscroll.js"></script>
<script>
// Buscador de usuarios en línea (AJAX)
$(document).ready(function() {
    $('#buscador-usuarios').on('input', function() {
        var query = $(this).val();
        if (query.length > 1) {
            $.ajax({
                url: '../presenters/buscar_usuarios.php',
                method: 'GET',
                data: { q: query },
                success: function(data) {
                    $('#resultados-busqueda').html(data);
                }
            });
        } else {
            $('#resultados-busqueda').empty();
        }
    });

    // Previsualización de imagen antes de publicar
    // Previsualización de múltiples imágenes antes de publicar
    let selectedFiles = [];
    function renderPreviews() {
        const container = $('#preview-container');
        container.empty();
        if (selectedFiles.length === 0) {
            container.hide();
            return;
        }
        container.show();
        selectedFiles.forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const preview = $('<div class="position-relative d-inline-block">')
                    .css({width:'120px',height:'120px'});
                const img = $('<img class="rounded-3 border">')
                    .attr('src', ev.target.result)
                    .css({width:'120px',height:'120px',objectFit:'cover',display:'block'});
                const btn = $('<button type="button" title="Eliminar">&times;</button>');
                btn.removeClass(); // Elimina cualquier clase heredada
                btn.css({
                    position: 'absolute',
                    top: '6px',
                    right: '6px',
                    width: '26px',
                    height: '26px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 2,
                    fontSize: '1.3rem',
                    lineHeight: '1',
                    background: '#0d6efd',
                    border: 'none',
                    color: '#fff',
                    borderRadius: '8px',
                    boxShadow: '0 1px 4px rgba(0,0,0,0.15)',
                    cursor: 'pointer',
                    padding: 0
                });
                btn.find('span,svg').css({margin:'0 auto'});
                btn.on('click', function() {
                    selectedFiles.splice(idx,1);
                    renderPreviews();
                });
                preview.append(img).append(btn);
                container.append(preview);
            };
            reader.readAsDataURL(file);
        });
    }

    // Drag & Drop para imágenes
    const $form = $('#form-publicar');
    $form.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('border-primary');
    });
    $form.on('dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('border-primary');
    });
    $form.on('drop', function(e) {
        const files = Array.from(e.originalEvent.dataTransfer.files);
        // Solo imágenes permitidas
        const valid = files.filter(f => /^image\/(jpeg|png|gif)$/.test(f.type));
        valid.forEach(f => {
            if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size)) {
                selectedFiles.push(f);
            }
        });
        renderPreviews();
    });
    $('#file-input').on('change', function(e) {
        const newFiles = Array.from(this.files);
        // Evitar duplicados por nombre y tamaño
        newFiles.forEach(f => {
            if (!selectedFiles.some(sf => sf.name === f.name && sf.size === f.size)) {
                selectedFiles.push(f);
            }
        });
        renderPreviews();
        // Limpiar el input para permitir volver a seleccionar el mismo archivo si se elimina
        this.value = '';
    });
    // Al enviar el formulario, crear un nuevo input file con los archivos seleccionados
    $('#form-publicar').on('submit', function(e) {
        if (selectedFiles.length > 0) {
            // Elimina cualquier input file existente
            $(this).find('input[type="file"]').remove();
            // Crea uno nuevo y lo agrega al form
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'fotos[]';
            input.multiple = true;
            input.style.display = 'none';
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            input.files = dt.files;
            this.appendChild(input);
        }
    });

    // Feedback visual al publicar
    const form = document.getElementById('form-publicar');
    if(form) {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if(btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publicando...';
            }
        });
    }

    // Scroll infinito para publicaciones (solo carga publicaciones.php, no el index completo)
    $('.scroll').jscroll({
        loadingHtml: '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>',
        padding: 20,
        nextSelector: '.jscroll-next',
        contentSelector: '.scroll',
        callback: function() {
            // Opcional: código extra después de cargar más publicaciones
        }
    });
});
</script>
</body>
</html>