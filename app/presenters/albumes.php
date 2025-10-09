<?php
session_start();
require_once __DIR__.'/../models/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$album = isset($_GET['album']) ? $_GET['album'] : '';
// 1. Imágenes de la tabla fotos (álbumes clásicos)
$stmtFotos = $conexion->prepare("SELECT ruta FROM fotos WHERE usuario = :id AND album = :album ORDER BY id_fot DESC");
$stmtFotos->bindParam(':id', $id, PDO::PARAM_INT);
$stmtFotos->bindParam(':album', $album, PDO::PARAM_STR);
$stmtFotos->execute();
$fotos = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);
// 2. Imágenes de publicaciones (columna imagen y tabla imagenes_publicacion)
$stmtPubs = $conexion->prepare("SELECT id_pub, imagen FROM publicaciones WHERE usuario = :id ORDER BY fecha DESC");
$stmtPubs->bindParam(':id', $id, PDO::PARAM_INT);
$stmtPubs->execute();
$pubs = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);
$imagenesPublicaciones = [];
foreach ($pubs as $pub) {
  if (!empty($pub['imagen'])) {
    $imagenesPublicaciones[] = $pub['imagen'];
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
    <link rel="stylesheet" href="/TrabajoRedSocial/public/css/component.css" />
    <style>
      .navbar.sticky-top { z-index: 1055; }
      .navbar .nav-link.active, .navbar .nav-link[aria-current="page"] {
        background: #0a1931;
        color: #fff !important;
        border-radius: 0.5rem;
        font-weight: 600;
      }
      .album-grid-img { cursor:pointer; margin-bottom:8px; box-shadow:0 2px 8px #0001; transition:transform 0.2s; }
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
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4 sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/TrabajoRedSocial/app/view/index.php" style="letter-spacing:2px;">Converza</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link<?php if($navActive['inicio']) echo ' active'; ?>" href="/TrabajoRedSocial/app/view/index.php" aria-current="<?php if($navActive['inicio']) echo 'page'; ?>"><i class="bi bi-house-door"></i> Inicio</a></li>
        <li class="nav-item"><a class="nav-link<?php if($navActive['perfil']) echo ' active'; ?>" href="/TrabajoRedSocial/app/presenters/perfil.php?id=<?php echo (int)$_SESSION['id']; ?>" aria-current="<?php if($navActive['perfil']) echo 'page'; ?>"><i class="bi bi-person-circle"></i> Perfil</a></li>
        <li class="nav-item"><a class="nav-link<?php if($navActive['chat']) echo ' active'; ?>" href="/TrabajoRedSocial/app/presenters/chat.php" aria-current="<?php if($navActive['chat']) echo 'page'; ?>"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
        <li class="nav-item"><a class="nav-link<?php if($navActive['albumes']) echo ' active'; ?>" href="/TrabajoRedSocial/app/presenters/albumes.php?id=<?php echo (int)$_SESSION['id']; ?>" aria-current="<?php if($navActive['albumes']) echo 'page'; ?>"><i class="bi bi-images"></i> Álbumes</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSearch" title="Buscar usuarios"><i class="bi bi-search"></i></a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSolicitudes" title="Solicitudes de amistad"><i class="bi bi-person-plus"></i></a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNuevos" title="Nuevos usuarios"><i class="bi bi-people"></i></a></li>
        <li class="nav-item"><a class="nav-link" href="/TrabajoRedSocial/app/presenters/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
        <li class="nav-item"><a class="nav-link text-warning fw-bold<?php if($navActive['admin']) echo ' active'; ?>" href="/TrabajoRedSocial/app/view/admin.php" aria-current="<?php if($navActive['admin']) echo 'page'; ?>"><i class="bi bi-shield-lock"></i> Panel Admin</a></li>
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
      foreach ($todas as $idx => $img) {
  echo '<div class="col-6 col-md-3 d-flex align-items-stretch"><div class="ratio ratio-16x9 w-100"><img src="/TrabajoRedSocial/public/publicaciones/'.htmlspecialchars($img).'" class="img-fluid rounded album-grid-img w-100 h-100" style="object-fit:cover;" data-idx="'.$idx.'" alt="Imagen" loading="lazy"></div></div>';
      }
    } else {
      echo '<div class="text-center text-muted">No hay imágenes en este álbum.</div>';
    }
    ?>
  </div>
</div>
<!-- Modal Galería -->
<div class="modal fade" id="modalGaleria" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark">
      <div class="modal-body p-0 position-relative">
        <button type="button" class="btn btn-light btn-flecha" id="prevImg" style="left:10px;"><i class="bi bi-arrow-left-circle"></i></button>
        <img id="modalImg" src="" class="w-100 rounded" style="max-height:70vh;object-fit:contain;" alt="Imagen">
        <button type="button" class="btn btn-light btn-flecha" id="nextImg" style="right:10px;"><i class="bi bi-arrow-right-circle"></i></button>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/TrabajoRedSocial/public/js/buscador.js"></script>
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
  const src = '/TrabajoRedSocial/public/publicaciones/' + imagenes[idxActual];
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
document.getElementById('modalGaleria').addEventListener('keydown', function(e){
  if (e.key === 'ArrowLeft') { document.getElementById('prevImg').click(); }
  if (e.key === 'ArrowRight') { document.getElementById('nextImg').click(); }
});
document.getElementById('modalGaleria').addEventListener('hidden.bs.modal', function(){
  document.getElementById('modalImg').src = '';
});
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
        <link rel="stylesheet" href="/TrabajoRedSocial/public/css/component.css" />
        <style>
          .navbar.sticky-top { z-index: 1055; }
          .navbar .nav-link.active, .navbar .nav-link[aria-current="page"] {
            background: #0a1931;
            color: #fff !important;
            border-radius: 0.5rem;
            font-weight: 600;
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
  const src = '/TrabajoRedSocial/public/publicaciones/' + imagenes[idxActual];
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
<style>
  .navbar .nav-link {
    position: relative;
    transition: background 0.2s, color 0.2s;
    border-radius: 0.5rem;
    font-weight: 500;
    color: #fff;
  }
  .navbar .nav-link.active, .navbar .nav-link[aria-current="page"] {
    color: #fff !important;
    font-weight: 700;
    background: transparent;
  }
  .navbar .nav-link.active::after, .navbar .nav-link[aria-current="page"]::after {
    content: '';
    display: block;
    margin: 0 auto;
    width: 60%;
    height: 3px;
    border-radius: 2px;
    background: #fff;
    margin-top: 2px;
    box-shadow: 0 2px 8px #0003;
    transition: width 0.2s;
  }
  .navbar .nav-link:not(.active):not([aria-current="page"])::after {
    content: '';
    display: block;
    width: 0;
    height: 3px;
    background: transparent;
    margin: 0 auto;
    transition: width 0.2s;
  }
  .navbar .nav-link:hover {
    background: rgba(10,25,49,0.12);
    color: #fff;
  }
</style>