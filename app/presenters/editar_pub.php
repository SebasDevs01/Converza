<?php
session_start();
require_once(__DIR__.'/../models/config.php');

// Validar sesión
if (!isset($_SESSION['id'])) {
  echo '<div class="alert alert-danger">Sesión no iniciada.</div>';
  exit();
}

// Validar ID de publicación
if (!isset($_GET['id'])) {
  echo '<div class="alert alert-danger">ID de publicación no especificado.</div>';
  exit();
}

$id_pub = (int)$_GET['id'];

// Traer la publicación del usuario logueado
$stmt = $conexion->prepare('SELECT * FROM publicaciones WHERE id_pub = :id_pub AND usuario = :usuario');
$stmt->execute([
  ':id_pub'  => $id_pub,
  ':usuario' => $_SESSION['id']
]);
$pub = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pub) {
  echo '<div class="alert alert-danger">No tienes permiso para editar esta publicación.</div>';
  exit();
}

// Obtener imágenes
$imagenes = [];
if (!empty($pub['imagen'])) {
  $imagenes[] = $pub['imagen'];
}
$stmtImgs = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = :pubid");
$stmtImgs->execute([':pubid' => $id_pub]);
$imagenes_db = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
$imagenes = array_merge($imagenes, $imagenes_db);

// Variables
$contenido = $pub['contenido'] ?? '';
?>

<!-- ✅ Modal Editar Publicación -->
<div class="modal fade show" id="modalEditarPub" tabindex="-1"
     style="display:block;background:rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Editar Publicación</h5>
        <button type="button" class="btn-close btn-close-white"
                onclick="document.getElementById('modalEditarPub').remove();"></button>
      </div>
      <div class="modal-body">

        <!-- 🔹 Formulario -->
        <form method="post" enctype="multipart/form-data" id="formEditarModal" action="/Converza/app/presenters/procesar_editar_pub.php">
          <input type="hidden" name="id_pub" value="<?php echo (int)$id_pub; ?>">

          <div class="mb-3">
            <label class="form-label">Contenido</label>
            <textarea name="contenido" class="form-control" rows="4" required><?php 
              echo htmlspecialchars($contenido); 
            ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Imágenes actuales</label>
            <div class="d-flex flex-wrap gap-3" id="imagenes-actuales">
              <?php if (!empty($imagenes)): ?>
                <?php foreach($imagenes as $img): ?>
                  <div class="border rounded p-2 text-center" style="width:120px;">
                    <img src="/TrabajoRedSocial/public/publicaciones/<?php echo htmlspecialchars($img); ?>"
                         class="img-fluid mb-2 rounded" style="max-height:100px;object-fit:cover;">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox"
                             name="eliminar_imagenes[]" value="<?php echo htmlspecialchars($img); ?>">
                      <label class="form-check-label text-danger small">Eliminar</label>
                    </div>
                    <input type="file" name="reemplazo[<?php echo htmlspecialchars($img); ?>]" 
                           class="form-control form-control-sm mt-1">
                    <small class="text-muted">Reemplazar</small>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-muted">Esta publicación no tiene imágenes.</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Agregar nuevas imágenes</label>
            <input type="file" name="fotos[]" class="form-control" multiple>
          </div>

          <div class="mb-3">
            <label class="form-label">Contenido multimedia actual</label>
            <?php if (!empty($pub['video'])): ?>
              <div class="border rounded p-2 text-center" style="width:320px;">
                <video controls class="img-fluid rounded" style="max-height:240px;object-fit:cover;">
                  <source src="/Converza/public/publicaciones/<?php echo htmlspecialchars($pub['video']); ?>" type="video/mp4">
                </video>
              </div>
            <?php elseif (!empty($pub['imagen'])): ?>
              <div class="border rounded p-2 text-center" style="width:320px;">
                <img src="/Converza/public/publicaciones/<?php echo htmlspecialchars($pub['imagen']); ?>" class="img-fluid rounded" style="max-height:240px;object-fit:cover;">
              </div>
            <?php else: ?>
              <p class="text-muted">Esta publicación no tiene contenido multimedia.</p>
            <?php endif; ?>
            <input type="file" name="reemplazo_multimedia" class="form-control form-control-sm mt-2">
            <small class="text-muted">Reemplazar por imagen o video</small>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
                onclick="document.getElementById('modalEditarPub').remove();">Cancelar</button>
        <button type="submit" form="formEditarModal" class="btn btn-primary">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
