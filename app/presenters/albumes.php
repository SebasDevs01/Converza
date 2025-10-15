<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/recompensas-aplicar-helper.php'; // 🎁 Sistema de recompensas

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$album = isset($_GET['album']) ? $_GET['album'] : '';

// 🎁 Inicializar sistema de recompensas
$recompensasHelper = new RecompensasAplicarHelper($conexion);
$temaCSS = $recompensasHelper->getTemaCSS($id);

// 1. Imágenes de la tabla fotos (álbumes clásicos)
$stmtFotos = $conexion->prepare("SELECT ruta FROM fotos WHERE usuario = :id AND album = :album ORDER BY id_fot DESC");
$stmtFotos->bindParam(':id', $id, PDO::PARAM_INT);
$stmtFotos->bindParam(':album', $album, PDO::PARAM_STR);
$stmtFotos->execute();
$fotos = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);
// 2. Imágenes y videos de publicaciones (columna imagen, video y tabla imagenes_publicacion)
$stmtPubs = $conexion->prepare("SELECT id_pub, imagen, video FROM publicaciones WHERE usuario = :id ORDER BY fecha DESC");
$stmtPubs->bindParam(':id', $id, PDO::PARAM_INT);
$stmtPubs->execute();
$pubs = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);
$imagenesPublicaciones = [];
foreach ($pubs as $pub) {
  if (!empty($pub['imagen'])) {
    $imagenesPublicaciones[] = $pub['imagen'];
  }
  if (!empty($pub['video'])) {
    $imagenesPublicaciones[] = $pub['video'];
  }
  $stmtImgs = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = :pubid");
  $stmtImgs->bindParam(':pubid', $pub['id_pub'], PDO::PARAM_INT);
  $stmtImgs->execute();
  $imgs = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
  // Evitar duplicados: si la imagen principal ya está en la tabla, no la agregues dos veces
  foreach ($imgs as $img) {
    if (!in_array($img, $imagenesPublicaciones)) {
      $imagenesPublicaciones[] = $img;
    }
  }
}
$navActive = [
    'inicio' => basename($_SERVER['PHP_SELF']) === 'index.php',
    'perfil' => strpos($_SERVER['PHP_SELF'], 'perfil.php') !== false,
    'chat' => strpos($_SERVER['PHP_SELF'], 'chat.php') !== false,
    'albumes' => strpos($_SERVER['PHP_SELF'], 'albumes.php') !== false,
    'admin' => strpos($_SERVER['PHP_SELF'], 'admin.php') !== false,
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Álbumes | Converza</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Converza/public/css/component.css" />
    <link rel="stylesheet" href="/Converza/public/css/navbar-animations.css" />
    <link rel="stylesheet" href="/Converza/public/css/karma-recompensas.css" />
    
    <?php if ($temaCSS): ?>
    <!-- 🎨 Tema personalizado equipado -->
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
      
      .album-grid-img { cursor:pointer; margin-bottom:8px; box-shadow:0 2px 8px #0001; transition:transform 0.2s; object-fit:cover; width:100%; height:100%; }
      .album-grid-img:hover { transform:scale(1.04); box-shadow:0 4px 16px #0002; }
      .btn-flecha {
        background: transparent;
        border: none;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        padding: 0.2rem 0.4rem;
        opacity: 0.85;
        transition: opacity 0.2s;
      }
      #modalGaleria .btn-flecha:hover { opacity: 1; }
      #modalGaleria .bi-arrow-left-circle, #modalGaleria .bi-arrow-right-circle {
        font-size: 2.8rem;
        color: #fff;
        filter: drop-shadow(0 2px 6px #000a);
        transition: color 0.2s;
      }
      #modalGaleria .btn-flecha:active .bi { color: #0d6efd !important; }
      .video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        transition: background 0.2s;
      }
      .video-overlay:hover {
        background: rgba(0,0,0,0.4);
      }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4 sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/Converza/app/view/index.php" style="letter-spacing:2px;">Converza</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <!-- 🏆 KARMA BADGE -->
        <li class="nav-item">
          <?php include __DIR__.'/../view/components/karma-navbar-badge.php'; ?>
        </li>
        <li class="nav-item"><a class="nav-link<?php if($navActive['inicio']) echo ' active'; ?>" href="/Converza/app/view/index.php" aria-current="<?php if($navActive['inicio']) echo 'page'; ?>"><i class="bi bi-house-door"></i> Inicio</a></li>
        <li class="nav-item"><a class="nav-link<?php if($navActive['perfil']) echo ' active'; ?>" href="/Converza/app/presenters/perfil.php?id=<?php echo (int)$_SESSION['id']; ?>" aria-current="<?php if($navActive['perfil']) echo 'page'; ?>"><i class="bi bi-person-circle"></i> Perfil</a></li>
        <li class="nav-item">
            <?php include __DIR__.'/../view/components/mensajes-badge.php'; ?>
        </li>
        <li class="nav-item"><a class="nav-link<?php if($navActive['albumes']) echo ' active'; ?>" href="/Converza/app/presenters/albumes.php?id=<?php echo (int)$_SESSION['id']; ?>" aria-current="<?php if($navActive['albumes']) echo 'page'; ?>"><i class="bi bi-images"></i> Álbumes</a></li>
        <li class="nav-item">
            <?php include __DIR__.'/../view/components/conexiones-badge.php'; ?>
        </li>
        <!-- ✨ Predicciones -->
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPredicciones" title="Predicciones">
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
            <?php include __DIR__.'/../view/components/solicitudes-badge.php'; ?>
        </li>
        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNuevos" title="Nuevos usuarios"><i class="bi bi-people"></i></a></li>
        
        <!-- 🔔 Sistema de Notificaciones -->
        <li class="nav-item">
            <?php include __DIR__.'/../view/components/notificaciones-widget.php'; ?>
        </li>
        
        <li class="nav-item"><a class="nav-link" href="/Converza/app/presenters/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
        <li class="nav-item"><a class="nav-link text-warning fw-bold<?php if($navActive['admin']) echo ' active'; ?>" href="/Converza/app/view/admin.php" aria-current="<?php if($navActive['admin']) echo 'page'; ?>"><i class="bi bi-shield-lock"></i> Panel Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php include __DIR__.'/../view/_navbar_panels.php'; ?>
<div class="container py-4">
  <div class="row g-2 mt-2">
    <?php
    $todas = array_filter(array_merge($fotos, $imagenesPublicaciones));
    if ($todas) {
      foreach ($todas as $idx => $archivo) {
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $esVideo = in_array($extension, ['mp4', 'webm', 'ogg']);
        
        if ($esVideo) {
          echo '<div class="col-6 col-md-3 d-flex align-items-stretch">';
          echo '<div class="ratio ratio-16x9 w-100 position-relative" style="cursor:pointer;" data-idx="'.$idx.'">';
          echo '<video class="rounded album-grid-img w-100 h-100" style="object-fit:cover;" muted>';
          echo '<source src="/Converza/public/publicaciones/'.htmlspecialchars($archivo).'" type="video/'.$extension.'">';
          echo '</video>';
          echo '<div class="video-overlay"><i class="bi bi-play-circle-fill text-white" style="font-size: 3rem; text-shadow: 0 0 10px rgba(0,0,0,0.8);"></i></div>';
          echo '</div></div>';
        } else {
          echo '<div class="col-6 col-md-3 d-flex align-items-stretch"><div class="ratio ratio-16x9 w-100"><img src="/Converza/public/publicaciones/'.htmlspecialchars($archivo).'" class="img-fluid rounded album-grid-img w-100 h-100" style="object-fit:cover;" data-idx="'.$idx.'" alt="Imagen" loading="lazy"></div></div>';
        }
      }
    } else {
      echo '<div class="text-center text-muted">No hay imágenes o videos en este álbum.</div>';
    }
    ?>
  </div>
</div>
<!-- Modal Galería -->
<div class="modal fade" id="modalGaleria" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark">
      <div class="modal-body p-0 position-relative d-flex justify-content-center align-items-center" style="min-height:60vh;">
        <button type="button" class="btn btn-light btn-flecha" id="prevImg" style="left:10px;"><i class="bi bi-arrow-left-circle"></i></button>
        <div id="modalMediaContainer" class="w-100 text-center">
          <img id="modalImg" src="" class="w-100 rounded" style="max-height:70vh;object-fit:contain;display:none;" alt="Imagen">
          <video id="modalVideo" class="w-100 rounded" style="max-height:70vh;object-fit:contain;display:none;" controls></video>
        </div>
        <button type="button" class="btn btn-light btn-flecha" id="nextImg" style="right:10px;"><i class="bi bi-arrow-right-circle"></i></button>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Converza/public/js/buscador.js"></script>
<script>
const archivos = <?php echo json_encode(array_values($todas)); ?>;
let idxActual = 0;
const modal = new bootstrap.Modal(document.getElementById('modalGaleria'));
// Manejar clics en imágenes
document.querySelectorAll('.album-grid-img').forEach(element => {
  element.addEventListener('click', function(){
    idxActual = parseInt(this.dataset.idx);
    mostrarArchivo();
    modal.show();
  });
});

// Manejar clics en contenedores de video
document.querySelectorAll('[data-idx]').forEach(element => {
  if (!element.classList.contains('album-grid-img')) {
    element.addEventListener('click', function(){
      idxActual = parseInt(this.dataset.idx);
      mostrarArchivo();
      modal.show();
    });
  }
});
function mostrarArchivo() {
  const archivo = archivos[idxActual];
  const src = '/Converza/public/publicaciones/' + archivo;
  const extension = archivo.split('.').pop().toLowerCase();
  const esVideo = ['mp4', 'webm', 'ogg'].includes(extension);
  
  const modalImg = document.getElementById('modalImg');
  const modalVideo = document.getElementById('modalVideo');
  
  if (esVideo) {
    modalImg.style.display = 'none';
    modalVideo.style.display = 'block';
    modalVideo.src = src;
    modalImg.src = '';
  } else {
    modalVideo.style.display = 'none';
    modalImg.style.display = 'block';
    modalImg.src = src;
    modalVideo.src = '';
  }
}
document.getElementById('prevImg').onclick = function(e){
  e.stopPropagation();
  idxActual = (idxActual - 1 + archivos.length) % archivos.length;
  mostrarArchivo();
};
document.getElementById('nextImg').onclick = function(e){
  e.stopPropagation();
  idxActual = (idxActual + 1) % archivos.length;
  mostrarArchivo();
};
document.getElementById('modalGaleria').addEventListener('keydown', function(e){
  if (e.key === 'ArrowLeft') { document.getElementById('prevImg').click(); }
  if (e.key === 'ArrowRight') { document.getElementById('nextImg').click(); }
});
document.getElementById('modalGaleria').addEventListener('hidden.bs.modal', function(){
  document.getElementById('modalImg').src = '';
  document.getElementById('modalVideo').src = '';
});
</script>

<!-- ✨ Script de Predicciones -->
<script>
// Variables globales para manejo de predicciones múltiples
let prediccionesQueue = [];
let currentIndex = 0;
let prediccionActualId = null;

document.getElementById('offcanvasPredicciones')?.addEventListener('show.bs.offcanvas', function () {
    cargarPrediccion();
});

async function cargarPrediccion() {
    const loading = document.getElementById('predicciones-loading');
    const container = document.getElementById('predicciones-container');
    const error = document.getElementById('predicciones-error');
    const completo = document.getElementById('predicciones-completo');
    
    loading.style.display = 'none';
    container.style.display = 'none';
    error.style.display = 'none';
    completo.style.display = 'none';
    
    if (prediccionesQueue.length === 0) {
        loading.style.display = 'block';
        
        try {
            const response = await fetch('/Converza/app/presenters/get_prediccion.php', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin'
            });
            
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            
            const data = await response.json();
            
            if (data.success && data.predicciones && data.predicciones.length > 0) {
                prediccionesQueue = data.predicciones;
                currentIndex = 0;
            } else {
                throw new Error(data.error || 'No hay predicciones disponibles');
            }
        } catch (err) {
            console.error('Error cargando predicciones:', err.message);
            loading.style.display = 'none';
            error.style.display = 'block';
            return;
        }
    }
    
    if (currentIndex >= prediccionesQueue.length) {
        completo.style.display = 'block';
        return;
    }
    
    mostrarPrediccionActual();
}

function mostrarPrediccionActual() {
    const container = document.getElementById('predicciones-container');
    const loading = document.getElementById('predicciones-loading');
    
    const pred = prediccionesQueue[currentIndex];
    prediccionActualId = pred.id;
    
    document.getElementById('prediccion-emoji').textContent = pred.emoji || '🔮';
    document.getElementById('prediccion-texto').textContent = pred.texto;
    document.getElementById('prediccion-categoria').textContent = pred.categoria.charAt(0).toUpperCase() + pred.categoria.slice(1);
    
    const confianzaSpan = document.getElementById('prediccion-confianza');
    confianzaSpan.textContent = pred.confianza.charAt(0).toUpperCase() + pred.confianza.slice(1);
    confianzaSpan.className = 'badge ' + (
        pred.confianza === 'alta' ? 'bg-success' :
        pred.confianza === 'media' ? 'bg-warning' : 'bg-secondary'
    );
    
    actualizarProgreso();
    
    const btnMeGusta = document.getElementById('btn-me-gusta');
    const btnNoMeGusta = document.getElementById('btn-no-me-gusta');
    btnMeGusta.disabled = false;
    btnNoMeGusta.disabled = false;
    btnMeGusta.className = 'btn btn-success btn-sm px-4 py-2 shadow-sm';
    btnNoMeGusta.className = 'btn btn-outline-secondary btn-sm px-4 py-2';
    btnMeGusta.innerHTML = '<i class="bi bi-hand-thumbs-up-fill me-1"></i> Me gusta';
    btnNoMeGusta.innerHTML = '<i class="bi bi-hand-thumbs-down me-1"></i> No me gusta';
    
    loading.style.display = 'none';
    container.style.display = 'block';
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
    
    btnMeGusta.disabled = true;
    btnNoMeGusta.disabled = true;
    
    try {
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
            if (meGusta === 1) {
                btnMeGusta.innerHTML = '<i class="bi bi-check-lg me-1"></i> ¡Gracias!';
                btnMeGusta.classList.add('fw-bold');
            } else {
                btnNoMeGusta.innerHTML = '<i class="bi bi-check-lg me-1"></i> Entendido';
                btnNoMeGusta.classList.remove('btn-outline-secondary');
                btnNoMeGusta.classList.add('btn-secondary', 'fw-bold');
            }
            
            setTimeout(() => {
                currentIndex++;
                cargarPrediccion();
            }, 1000);
        }
    } catch (err) {
        console.error('Error valorando predicción:', err);
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
            if (file_exists(__DIR__ . '/../public/avatars/' . $avatar)) {
                // Está en avatars
                echo htmlspecialchars('/Converza/public/avatars/' . $avatar, ENT_QUOTES);
            } elseif (file_exists(__DIR__ . '/../public/uploads/' . $avatar)) {
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
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Álbumes | Converza</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link rel="stylesheet" href="/Converza/public/css/component.css" />
        <style>
      .album-grid-img { cursor:pointer; margin-bottom:8px; box-shadow:0 2px 8px #0001; transition:transform 0.2s; object-fit:cover; width:100%; height:100%; }
      .album-grid-img:hover { transform:scale(1.04); box-shadow:0 4px 16px #0002; }
          .btn-flecha {
            background: transparent;
            border: none;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            padding: 0.2rem 0.4rem;
            opacity: 0.85;
            transition: opacity 0.2s;
          }
          #modalGaleria .btn-flecha:hover { opacity: 1; }
          #modalGaleria .bi-arrow-left-circle, #modalGaleria .bi-arrow-right-circle {
            font-size: 2.8rem;
            color: #fff;
            filter: drop-shadow(0 2px 6px #000a);
            transition: color 0.2s;
          }
          #modalGaleria .btn-flecha:active .bi { color: #0d6efd !important; }
        </style>
<script>
const imagenes = <?php echo json_encode(array_values($todas)); ?>;
let idxActual = 0;
const modal = new bootstrap.Modal(document.getElementById('modalGaleria'));
document.querySelectorAll('.album-grid-img').forEach(img => {
  img.addEventListener('click', function(){
    idxActual = parseInt(this.dataset.idx);
    mostrarImg();
    modal.show();
  });
});
function mostrarImg() {
  const src = '/Converza/public/publicaciones/' + imagenes[idxActual];
  document.getElementById('modalImg').src = src;
}
document.getElementById('prevImg').onclick = function(e){
  e.stopPropagation();
  idxActual = (idxActual - 1 + imagenes.length) % imagenes.length;
  mostrarImg();
};
document.getElementById('nextImg').onclick = function(e){
  e.stopPropagation();
  idxActual = (idxActual + 1) % imagenes.length;
  mostrarImg();
};
// Navegación con flechas del teclado
document.getElementById('modalGaleria').addEventListener('keydown', function(e){
  if (e.key === 'ArrowLeft') { document.getElementById('prevImg').click(); }
  if (e.key === 'ArrowRight') { document.getElementById('nextImg').click(); }
});
// Reset al cerrar
document.getElementById('modalGaleria').addEventListener('hidden.bs.modal', function(){
  document.getElementById('modalImg').src = '';
});
</script>
