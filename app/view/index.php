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
require_once __DIR__.'/../models/notificaciones-triggers.php';
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

// Instanciar sistema de notificaciones
$notificacionesTriggers = new NotificacionesTriggers($conexion);

// Verificar si el usuario está bloqueado
if (isUserBlocked($_SESSION['id'], $conexion)) {
    session_destroy();
    header("Location: login.php?error=blocked");
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
        $videosGuardados = [];
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
        
        // Procesar videos antes de la validación
        if (!$error && isset($_FILES['videos']) && is_array($_FILES['videos']['name'])) {
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
                            $videosGuardados[] = $nombreVideo;
                            logPublicar('Video subido correctamente: '.$nombreVideo);
                        } else {
                            $error = 'No se pudo guardar el video '.$name.' en el servidor.';
                            logPublicar('Error: No se pudo guardar el video '.$name.' en el servidor.');
                        }
                    } else {
                        $error = 'Formato de video no permitido: '.$name;
                        logPublicar('Error: Formato de video no permitido: '.$name);
                    }
                }
            }
        }
        
        if (!$error && ($publicacion !== '' || count($imagenesGuardadas) > 0 || count($videosGuardados) > 0)) {
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
                
                // Guardar videos ya procesados
                foreach ($videosGuardados as $video) {
                    $stmtVideo = $conexion->prepare("UPDATE publicaciones SET video = :video WHERE id_pub = :pubid");
                    $stmtVideo->bindParam(':video', $video, PDO::PARAM_STR);
                    $stmtVideo->bindParam(':pubid', $pubId, PDO::PARAM_INT);
                    $stmtVideo->execute();
                }
                
                // 🔔 Notificar a seguidores y amigos sobre nueva publicación
                $stmtUsuario = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
                $stmtUsuario->execute([':id' => $_SESSION['id']]);
                $datosUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
                $nombreUsuario = $datosUsuario['usuario'] ?? $_SESSION['usuario'];
                $notificacionesTriggers->notificarNuevaPublicacion($conexion, $_SESSION['id'], $nombreUsuario, $pubId, $publicacion);
                
                $mensaje = "¡Publicación creada exitosamente!";
                logPublicar('Publicación creada por usuario '.$_SESSION['id'].' ('.$_SESSION['usuario'].') con '.count($imagenesGuardadas).' imagen(es) y '.count($videosGuardados).' video(s).');
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
    <link rel="stylesheet" href="/Converza/public/css/component.css" />
    <link rel="stylesheet" href="/Converza/public/css/navbar-animations.css" />
    <style>
        /* Estilo para contador de notificaciones estilo Facebook */
        .notification-badge {
            min-width: 16px;
            height: 16px;
            font-size: 0.55rem;
            line-height: 1.2;
            padding: 2px 4px;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            top: -5px !important;
            right: 5px !important;
            transform: none !important;
            z-index: 10;
        }
        
        /* Asegurar que los enlaces tengan posición relativa */
        .nav-link.position-relative {
            position: relative !important;
            display: inline-block !important;
        }
        
        /* Estilos para drag & drop */
        #form-publicar {
            transition: all 0.3s ease;
            border: 2px dashed transparent;
            border-radius: 10px;
            padding: 15px;
        }
        
        #form-publicar.border-primary {
            border-color: #0d6efd !important;
            background-color: #f8f9ff !important;
        }
        
        /* Estilos para previsualizaciones */
        #preview-container {
            max-height: 300px;
            overflow-y: auto;
        }
        
        /* Hover effect para previews */
        #preview-container .position-relative:hover {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }
        
        /* Botón eliminar mejorado - azul */
        #preview-container button:hover {
            background-color: #0b5ed7 !important;
            transform: scale(1.1);
        }
        
        /* Placeholder animado para drag */
        .drag-placeholder {
            border: 3px dashed #0d6efd;
            background: linear-gradient(45deg, transparent 25%, rgba(13, 110, 253, 0.1) 25%, rgba(13, 110, 253, 0.1) 50%, transparent 50%);
            background-size: 20px 20px;
            animation: movePattern 2s linear infinite;
        }
        
        @keyframes movePattern {
            0% { background-position: 0 0; }
            100% { background-position: 20px 20px; }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4 sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php" style="letter-spacing:2px;">Converza</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link active" href="index.php" aria-current="page"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="../presenters/perfil.php?id=<?php echo (int)$_SESSION['id']; ?>"><i class="bi bi-person-circle"></i> Perfil</a></li>
                <li class="nav-item">
                    <?php include __DIR__.'/components/mensajes-badge.php'; ?>
                </li>
                <li class="nav-item"><a class="nav-link" href="../presenters/albumes.php?id=<?php echo (int)$_SESSION['id']; ?>"><i class="bi bi-images"></i> Álbumes</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDailyShuffle" title="Daily Shuffle - Descubre nuevas personas">
                        <i class="bi bi-shuffle"></i> Shuffle
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" title="Buscar usuarios"><i class="bi bi-search"></i></a></li>
                <li class="nav-item">
                    <?php include __DIR__.'/components/solicitudes-badge.php'; ?>
                </li>
                <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNuevos" title="Nuevos usuarios"><i class="bi bi-people"></i></a></li>
                
                <!-- 🔔 Sistema de Notificaciones -->
                <li class="nav-item">
                    <?php include __DIR__.'/components/notificaciones-widget.php'; ?>
                </li>
                
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Converza/public/js/jquery.jscroll.js"></script>
<script src="/Converza/public/js/buscador.js"></script>
<script src="/Converza/public/js/drag-drop.js"></script>
<script>
// Funcionalidad del buscador ahora está en buscador.js

$(document).ready(function() {
    console.log('✅ Index.php cargado - usando drag-drop.js para todo');

    // Scroll infinito para publicaciones
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
