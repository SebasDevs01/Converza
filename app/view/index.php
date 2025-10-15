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
require_once __DIR__.'/../models/recompensas-aplicar-helper.php'; // 🎁 Sistema de recompensas

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

// 🎁 Inicializar sistema de recompensas
$recompensasHelper = new RecompensasAplicarHelper($conexion);
$temaCSS = $recompensasHelper->getTemaCSS($_SESSION['id']);

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
    <link rel="stylesheet" href="/Converza/public/css/karma-recompensas.css?v=2.6" />
    
    <?php 
    // 🎨 SISTEMA DE TEMAS GLOBAL - Aplicar tema equipado a todo el sistema
    require_once __DIR__ . '/../models/tema-global-aplicar.php';
    ?>
    
    <?php if ($temaCSS): ?>
    <!-- 🎨 Tema personalizado equipado (CSS adicional - DEPRECATED, usar tema-global-aplicar.php) -->
    <style><?php echo $temaCSS; ?></style>
    <?php endif; ?>
    
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
                <!-- 🏆 Karma Button -->
                <li class="nav-item">
                    <?php include __DIR__.'/components/karma-navbar-badge.php'; ?>
                </li>
                
                <li class="nav-item"><a class="nav-link active" href="index.php" aria-current="page"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="../presenters/perfil.php?id=<?php echo (int)$_SESSION['id']; ?>"><i class="bi bi-person-circle"></i> Perfil</a></li>
                <li class="nav-item">
                    <?php include __DIR__.'/components/mensajes-badge.php'; ?>
                </li>
                <li class="nav-item"><a class="nav-link" href="../presenters/albumes.php?id=<?php echo (int)$_SESSION['id']; ?>"><i class="bi bi-images"></i> Álbumes</a></li>
                
                <!-- 🔮 Conexiones Místicas con Badge -->
                <li class="nav-item">
                    <?php include __DIR__.'/components/conexiones-badge.php'; ?>
                </li>
                
                <!-- ✨ Predicciones Divertidas -->
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPredicciones" title="Predicciones - ¿Qué dice el oráculo sobre ti?">
                        <i class="bi bi-stars"></i> <span class="d-none d-lg-inline">Predicciones</span>
                    </a>
                </li>
                
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
                        $avatarWebPath = '/Converza/public/avatars/'.$avatar;
                        if ($avatar && $avatar !== 'default_avatar.svg' && $avatarPath && file_exists($avatarPath)) {
                            echo '<img src="'.$avatarWebPath.'" class="rounded-circle me-3" width="60" height="60" style="object-fit: cover; display: block; min-width: 60px; min-height: 60px;" alt="Avatar" loading="lazy" title="Tu avatar">';
                        } else {
                            echo '<img src="/Converza/public/avatars/defect.jpg" class="rounded-circle me-3" width="60" height="60" style="object-fit: cover; display: block; min-width: 60px; min-height: 60px;" alt="Avatar por defecto" loading="lazy" title="Avatar por defecto">';
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
            <!-- ⭐ Contenedor del feed con overflow controlado -->
            <div class="scroll" data-url="../presenters/publicaciones.php" style="max-width: 100%; overflow-x: hidden; overflow-y: auto;">
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

<!-- ✨ Script de Predicciones -->
<script>
// Variables globales para manejo de predicciones múltiples
let prediccionesQueue = [];
let currentIndex = 0;
let prediccionActualId = null;

// Cargar predicción cuando se abre el offcanvas
document.getElementById('offcanvasPredicciones')?.addEventListener('show.bs.offcanvas', function () {
    cargarPrediccion();
});

async function cargarPrediccion() {
    console.log('🔵 cargarPrediccion() - INICIO');
    
    const loading = document.getElementById('predicciones-loading');
    const container = document.getElementById('predicciones-container');
    const error = document.getElementById('predicciones-error');
    const completo = document.getElementById('predicciones-completo');
    
    console.log('📋 Elementos DOM:', {
        loading: !!loading,
        container: !!container,
        error: !!error,
        completo: !!completo
    });
    
    // Ocultar todos los estados
    loading.style.display = 'none';
    container.style.display = 'none';
    error.style.display = 'none';
    completo.style.display = 'none';
    
    console.log('🔢 Estado actual - Queue length:', prediccionesQueue.length, 'Current index:', currentIndex);
    
    // Si no hay predicciones en cola, cargarlas
    if (prediccionesQueue.length === 0) {
        console.log('📥 No hay predicciones en cola, cargando...');
        loading.style.display = 'block';
        
        try {
            console.log('🔮 Cargando predicciones...');
            const response = await fetch('/Converza/app/presenters/get_prediccion.php', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            });
            
            console.log('📡 Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('✅ Data recibida:', data);
            
            if (data.success && data.predicciones && data.predicciones.length > 0) {
                prediccionesQueue = data.predicciones;
                currentIndex = 0;
                console.log(`🎯 ${prediccionesQueue.length} predicciones cargadas`);
            } else {
                throw new Error(data.error || 'No hay predicciones disponibles');
            }
        } catch (err) {
            console.error('❌ Error cargando predicciones:', err.message);
            loading.style.display = 'none';
            error.style.display = 'block';
            return;
        }
    }
    
    console.log('🔍 Verificando si terminamos: currentIndex=' + currentIndex + ', total=' + prediccionesQueue.length);
    
    // Verificar si terminamos todas las predicciones
    if (currentIndex >= prediccionesQueue.length) {
        console.log('✅ Todas las predicciones completadas');
        completo.style.display = 'block';
        return;
    }
    
    console.log('➡️ Llamando mostrarPrediccionActual()');
    // Mostrar predicción actual
    mostrarPrediccionActual();
    console.log('🔵 cargarPrediccion() - FIN');
}

function mostrarPrediccionActual() {
    console.log('🟢 mostrarPrediccionActual() - INICIO');
    
    const container = document.getElementById('predicciones-container');
    const loading = document.getElementById('predicciones-loading');
    
    console.log('📋 Elementos:', { container: !!container, loading: !!loading });
    console.log('📊 Predicción actual:', prediccionesQueue[currentIndex]);
    
    const pred = prediccionesQueue[currentIndex];
    prediccionActualId = pred.id;
    
    console.log(`🎯 Mostrando predicción ${currentIndex + 1}/${prediccionesQueue.length}: ${pred.texto} ${pred.emoji}`);
    
    // Actualizar UI
    const emojiEl = document.getElementById('prediccion-emoji');
    const textoEl = document.getElementById('prediccion-texto');
    const categoriaEl = document.getElementById('prediccion-categoria');
    const confianzaEl = document.getElementById('prediccion-confianza');
    
    console.log('📋 Elementos de predicción:', {
        emoji: !!emojiEl,
        texto: !!textoEl,
        categoria: !!categoriaEl,
        confianza: !!confianzaEl
    });
    
    if (!emojiEl || !textoEl || !categoriaEl || !confianzaEl) {
        console.error('❌ Faltan elementos del DOM para mostrar la predicción');
        return;
    }
    
    emojiEl.textContent = pred.emoji || '🔮';
    textoEl.textContent = pred.texto;
    categoriaEl.textContent = pred.categoria.charAt(0).toUpperCase() + pred.categoria.slice(1);
    
    // Confianza con colores
    confianzaEl.textContent = pred.confianza.charAt(0).toUpperCase() + pred.confianza.slice(1);
    confianzaEl.className = 'badge ' + (
        pred.confianza === 'alta' ? 'bg-success' :
        pred.confianza === 'media' ? 'bg-warning' : 'bg-secondary'
    );
    
    console.log('📊 Actualizando progreso...');
    // Actualizar progreso
    actualizarProgreso();
    
    // Resetear botones
    const btnMeGusta = document.getElementById('btn-me-gusta');
    const btnNoMeGusta = document.getElementById('btn-no-me-gusta');
    
    console.log('🔘 Botones:', { meGusta: !!btnMeGusta, noMeGusta: !!btnNoMeGusta });
    
    if (btnMeGusta && btnNoMeGusta) {
        btnMeGusta.disabled = false;
        btnNoMeGusta.disabled = false;
        btnMeGusta.className = 'btn btn-success btn-sm px-4 py-2 shadow-sm';
        btnNoMeGusta.className = 'btn btn-outline-secondary btn-sm px-4 py-2';
        btnMeGusta.innerHTML = '<i class="bi bi-hand-thumbs-up-fill me-1"></i> Me gusta';
        btnNoMeGusta.innerHTML = '<i class="bi bi-hand-thumbs-down me-1"></i> No me gusta';
    }
    
    // Mostrar contenedor
    console.log('👁️ Mostrando contenedor...');
    loading.style.display = 'none';
    container.style.display = 'block';
    
    console.log('🟢 mostrarPrediccionActual() - FIN');
}

function actualizarProgreso() {
    const total = prediccionesQueue.length;
    const actual = currentIndex + 1;
    const porcentaje = Math.round((actual / total) * 100);
    
    document.getElementById('current-number').textContent = actual;
    document.getElementById('total-number').textContent = total;
    document.getElementById('progress-percentage').textContent = porcentaje;
    document.getElementById('progress-bar').style.width = porcentaje + '%';
    document.getElementById('progress-bar').setAttribute('aria-valuenow', porcentaje);
}

async function valorarPrediccion(meGusta) {
    if (!prediccionActualId) return;
    
    const btnMeGusta = document.getElementById('btn-me-gusta');
    const btnNoMeGusta = document.getElementById('btn-no-me-gusta');
    
    // Deshabilitar botones
    btnMeGusta.disabled = true;
    btnNoMeGusta.disabled = true;
    
    try {
        console.log(`💾 Guardando valoración: ${meGusta ? 'Me gusta' : 'No me gusta'}`);
        
        const response = await fetch('/Converza/app/presenters/get_prediccion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prediccion_id: prediccionActualId,
                me_gusta: meGusta
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Feedback visual inmediato
            if (meGusta === 1) {
                btnMeGusta.innerHTML = '<i class="bi bi-check-lg me-1"></i> ¡Gracias!';
                btnMeGusta.classList.add('fw-bold');
            } else {
                btnNoMeGusta.innerHTML = '<i class="bi bi-check-lg me-1"></i> Entendido';
                btnNoMeGusta.classList.remove('btn-outline-secondary');
                btnNoMeGusta.classList.add('btn-secondary', 'fw-bold');
            }
            
            console.log('✅ Valoración guardada');
            
            // Avanzar a siguiente predicción después de 1 segundo
            setTimeout(() => {
                currentIndex++;
                cargarPrediccion();
            }, 1000);
        }
    } catch (err) {
        console.error('❌ Error valorando predicción:', err);
        btnMeGusta.disabled = false;
        btnNoMeGusta.disabled = false;
    }
}

</script>

<!-- ✨ ASISTENTE CONVERZA - Widget Flotante -->
<?php 
$widget_path = __DIR__ . '/../microservices/converza-assistant/widget/assistant-widget.php';
if (file_exists($widget_path)) {
    require_once($widget_path);
} else {
    error_log('⚠️ Widget no encontrado: ' . $widget_path);
}
?>

<!-- 🎯 Configuración del Asistente -->
<script>
    // Pasar datos del usuario al asistente
    window.USER_ID = <?php echo isset($_SESSION['id']) ? intval($_SESSION['id']) : 0; ?>;
    window.USER_NAME = "<?php echo isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario'], ENT_QUOTES) : 'Usuario'; ?>";
    window.USER_PHOTO = "<?php 
        if (isset($_SESSION['avatar']) && !empty($_SESSION['avatar']) && $_SESSION['avatar'] !== 'defect.jpg') {
            $avatar = $_SESSION['avatar'];
            
            // Verificar dónde existe el archivo físicamente
            if (file_exists(__DIR__ . '/../../public/avatars/' . $avatar)) {
                // Está en avatars
                echo htmlspecialchars('/Converza/public/avatars/' . $avatar, ENT_QUOTES);
            } elseif (file_exists(__DIR__ . '/../../public/uploads/' . $avatar)) {
                // Está en uploads
                echo htmlspecialchars('/Converza/public/uploads/' . $avatar, ENT_QUOTES);
            } elseif (strpos($avatar, 'public/') === 0) {
                // Ya tiene la ruta relativa
                echo htmlspecialchars('/Converza/' . $avatar, ENT_QUOTES);
            } else {
                // Por defecto
                echo '/Converza/public/avatars/defect.jpg';
            }
        } else {
            echo '/Converza/public/avatars/defect.jpg';
        }
    ?>";
    
    // Debug
    console.log('✨ Asistente Converza iniciado');
    console.log('   Usuario ID:', window.USER_ID);
    console.log('   Nombre:', window.USER_NAME);
    console.log('   Foto:', window.USER_PHOTO);
</script>

</body>
</html>
