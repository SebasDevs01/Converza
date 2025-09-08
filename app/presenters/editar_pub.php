 <?php
session_start();
require_once(__DIR__.'/../models/config.php');


// Si es modal, no redirigir, solo mostrar error simple
if (!isset($_SESSION['id'])) {
    if (isset($_GET['modal'])) {
        echo '<div class="alert alert-danger">Sesión no iniciada.</div>';
        exit();
    }
    header('Location: ../view/login.php');
    exit();
}
if (!isset($_GET['id'])) {
    if (isset($_GET['modal'])) {
        echo '<div class="alert alert-danger">ID de publicación no especificado.</div>';
        exit();
    }
    header('Location: ../view/index.php');
    exit();
}

$id_pub = (int)$_GET['id'];

// Validar que la publicación sea del usuario logueado
$stmt = $conexion->prepare('SELECT * FROM publicaciones WHERE id_pub = :id_pub AND usuario = :usuario');
$stmt->bindParam(':id_pub', $id_pub, PDO::PARAM_INT);
$stmt->bindParam(':usuario', $_SESSION['id'], PDO::PARAM_INT);
$stmt->execute();
$pub = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$pub) {
    if (isset($_GET['modal'])) {
        echo '<div class="alert alert-danger">No tienes permiso para editar esta publicación.</div>';
        exit();
    }
    header('Location: ../view/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = trim($_POST['contenido'] ?? '');
    // LOG de depuración robusto
    $log = fopen(__DIR__.'/../../public/log_publicar.txt', 'a');
    fwrite($log, date('Y-m-d H:i:s')." - POST recibido. Campos: ".json_encode($_POST)."\n");
    if (!empty($_FILES)) {
        foreach ($_FILES as $k => $v) {
            fwrite($log, date('Y-m-d H:i:s')." - FILES['$k']: ".json_encode($v)."\n");
        }
    } else {
        fwrite($log, date('Y-m-d H:i:s')." - FILES: NO RECIBIDO\n");
    }
    fclose($log);
    // Imágenes que el usuario quiere mantener
    $imagenes_actuales = isset($_POST['imagenes_actuales']) ? (array)$_POST['imagenes_actuales'] : [];
    // Si no hay imágenes actuales, asegúrate de que sea un array vacío
    if (!is_array($imagenes_actuales)) $imagenes_actuales = [];
    // Obtener todas las imágenes actuales (columna y tabla)
    $todas_imagenes = [];
    if (!empty($pub['imagen'])) {
        $todas_imagenes[] = $pub['imagen'];
    }
    $stmtImgs = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = :pubid");
    $stmtImgs->bindParam(':pubid', $id_pub, PDO::PARAM_INT);
    $stmtImgs->execute();
    $imagenes_db = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
    $todas_imagenes = array_merge($todas_imagenes, $imagenes_db);

    // Eliminar imágenes que el usuario quitó
    $imagenes_a_eliminar = array_diff($todas_imagenes, $imagenes_actuales);
    foreach ($imagenes_a_eliminar as $img) {
        // Eliminar físicamente
        $ruta = __DIR__.'/../../public/publicaciones/'.$img;
        if (file_exists($ruta)) unlink($ruta);
        // Eliminar de la base de datos
        if ($img === $pub['imagen']) {
            $stmt = $conexion->prepare('UPDATE publicaciones SET imagen = NULL WHERE id_pub = :id_pub');
            $stmt->bindParam(':id_pub', $id_pub, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $conexion->prepare('DELETE FROM imagenes_publicacion WHERE publicacion_id = :pubid AND nombre_imagen = :img');
            $stmt->bindParam(':pubid', $id_pub, PDO::PARAM_INT);
            $stmt->bindParam(':img', $img, PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    // Guardar nuevas imágenes
    $nuevas = $_FILES['fotos'] ?? null;
    $imagenes_nuevas_guardadas = [];
    if ($nuevas && isset($nuevas['name']) && is_array($nuevas['name'])) {
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        for ($i = 0; $i < count($nuevas['name']); $i++) {
            if ($nuevas['error'][$i] === 0 && $nuevas['name'][$i] !== '') {
                $ext = strtolower(pathinfo($nuevas['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, $permitidas)) {
                    $nombreImagen = uniqid().'.'.$ext;
                    $carpeta = __DIR__.'/../../public/publicaciones/';
                    if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
                    $destino = $carpeta.$nombreImagen;
                    if (move_uploaded_file($nuevas['tmp_name'][$i], $destino)) {
                        $imagenes_nuevas_guardadas[] = $nombreImagen;
                        // Guardar en la tabla imagenes_publicacion
                        $stmt = $conexion->prepare('INSERT INTO imagenes_publicacion (publicacion_id, nombre_imagen) VALUES (:pub, :img)');
                        $stmt->bindParam(':pub', $id_pub, PDO::PARAM_INT);
                        $stmt->bindParam(':img', $nombreImagen, PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }
            }
        }
    }

    if ($contenido !== '') {
        $stmt = $conexion->prepare('UPDATE publicaciones SET contenido = :contenido WHERE id_pub = :id_pub');
        $stmt->bindParam(':contenido', $contenido, PDO::PARAM_STR);
        $stmt->bindParam(':id_pub', $id_pub, PDO::PARAM_INT);
        $stmt->execute();
        if (isset($_GET['modal'])) {
            // No mostrar nada, solo cerrar el modal por JS
            echo '';
            exit();
        }
        header('Location: ../view/index.php');
        exit();
    } else {
        $error = 'El contenido no puede estar vacío.';
    }
} else {
    $contenido = $pub['contenido'];
    $error = '';
}
?>
<?php if (isset($_GET['modal'])): ?>
<!-- Modal edición publicación -->
<div class="modal fade show" id="modalEditarPub" tabindex="-1" style="display:block;background:rgba(0,0,0,0.25);" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Publicación</h5>
                <button type="button" class="btn-close btn-close-white" aria-label="Cerrar" onclick="this.closest('.modal').remove();"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
                <?php endif; ?>
                <form id="formEditarPub" method="post" enctype="multipart/form-data" autocomplete="off" data-id="<?php echo (int)$id_pub; ?>">
                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido</label>
                        <textarea name="contenido" id="contenido" class="form-control" rows="4" required><?php echo htmlspecialchars($contenido); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imágenes de la publicación</label>
                        <?php
                            // Construir el array de imágenes ANTES del HTML para asegurar que JS lo reciba bien
                            $imagenes = [];
                            if (!empty($pub['imagen'])) {
                                $imagenes[] = $pub['imagen'];
                            }
                            $stmtImgs = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = :pubid");
                            $stmtImgs->bindParam(':pubid', $id_pub, PDO::PARAM_INT);
                            $stmtImgs->execute();
                            $imagenes_db = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
                            $imagenes = array_merge($imagenes, $imagenes_db);
                            $imagenes = array_filter($imagenes, function($img) { return !empty($img); });
                            $imagenes = array_values($imagenes);
                        ?>
                        <div class="d-flex flex-wrap gap-2" id="imagenes-todas" data-imagenes='<?php echo htmlspecialchars(json_encode($imagenes), ENT_QUOTES, "UTF-8"); ?>'></div>
                        <small class="text-muted">Haz clic en la X para eliminar una imagen (actual o nueva).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Agregar nuevas imágenes</label>
                        <input type="file" name="fotos[]" id="nuevas_imagenes" class="d-none" accept="image/jpeg,image/png,image/gif" multiple>
                        <label for="nuevas_imagenes" class="btn btn-outline-primary" title="Adjuntar archivo"><i class="bi bi-paperclip"></i></label>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove();">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>

let imagenesActuales = [];

function renderTodasImagenes() {
    const container = $('#imagenes-todas');
    container.empty();
    // Imágenes actuales
    // Mostrar todas las imágenes (viejas y nuevas) en el orden correcto
    imagenesActuales.forEach((img, idx) => {
        const preview = $('<div class="position-relative d-inline-block preview-html">').css({width:'90px',height:'90px'});
        let imgTag;
        let isFile = false;
        // Si es un objeto File (nueva), mostrar su preview
        if (typeof img === 'object' && img._previewUrl) {
            imgTag = $('<img class="rounded-3 border">').attr('src', img._previewUrl).css({width:'90px',height:'90px',objectFit:'cover',display:'block'});
            isFile = true;
        } else {
            imgTag = $('<img class="rounded-3 border">').attr('src', '/TrabajoRedSocial/public/publicaciones/' + img).css({width:'90px',height:'90px',objectFit:'cover',display:'block'});
        }
        const btn = $('<button type="button" class="btn-eliminar-imagen" title="Eliminar">&times;</button>');
        btn.attr('data-idx', idx);
        btn.css({position:'absolute',top:'4px',right:'4px',width:'22px',height:'22px',display:'flex',alignItems:'center',justifyContent:'center',zIndex:2,fontSize:'1.1rem',lineHeight:'1',background:'#0d6efd',border:'none',color:'#fff',borderRadius:'6px',boxShadow:'0 1px 4px rgba(0,0,0,0.15)',cursor:'pointer',padding:0});
        preview.append(imgTag).append(btn);
        if (!isFile) preview.append($('<input type="hidden" name="imagenes_actuales[]">').val(img));
        container.append(preview);
    });
}

function inicializarEditarModal() {
    try {
        let dataImgs = $('#imagenes-todas').attr('data-imagenes');
        imagenesActuales = JSON.parse(dataImgs) || [];
        if (!Array.isArray(imagenesActuales)) imagenesActuales = [];
        // Log de depuración
        console.log('Imágenes iniciales para previsualizar:', imagenesActuales);
    } catch(e) {
        imagenesActuales = [];
        console.error('Error al parsear data-imagenes:', e);
    }
    renderTodasImagenes();
    // Delegación para eliminar cualquier imagen (vieja o nueva)
    $('#imagenes-todas').off('click', '.btn-eliminar-imagen').on('click', '.btn-eliminar-imagen', function(e) {
        e.preventDefault();
        const idx = parseInt($(this).attr('data-idx'));
        if (!isNaN(idx) && idx >= 0 && idx < imagenesActuales.length) {
            const img = imagenesActuales[idx];
            if (typeof img === 'object' && img._previewUrl) URL.revokeObjectURL(img._previewUrl);
            imagenesActuales.splice(idx, 1);
            renderTodasImagenes();
        }
    });
}

$(document).ready(function() {
    // Inicializar imágenes desde el backend
    inicializarEditarModal();

    $('#nuevas_imagenes').on('change', function(){
        Array.from(this.files).forEach(f=>{
            if(!imagenesActuales.some(i=>typeof i==='object' && i.name===f.name && i.size===f.size)){
                f._previewUrl = URL.createObjectURL(f);
                imagenesActuales.push(f);
            }
        });
        renderTodasImagenes();
        $(this).val('');
    });

    $('#formEditarPub').on('submit', function(e){
        e.preventDefault();
        const form = this;
        const data = new FormData();
        // Agregar contenido
        data.append('contenido', $('#contenido').val());
        // Agregar imágenes actuales y nuevas
        imagenesActuales.forEach(img=>{
            if (typeof img === 'object') {
                data.append('fotos[]', img);
            } else {
                data.append('imagenes_actuales[]', img);
            }
        });
        // Siempre enviar el parámetro id correcto en la URL
        const id = $(form).data('id');
        let url = window.location.pathname + window.location.search;
        if (!/id=/.test(url)) {
            url += (url.includes('?') ? '&' : '?') + 'id=' + encodeURIComponent(id) + '&modal=1';
        }
        fetch(url, {method:'POST',body:data})
            .then(r=>r.text())
            .then(()=>{
                $('#modalEditarPub').remove();
                if($('.scroll').length){ $('.scroll').load('../presenters/publicaciones.php'); } else { location.reload(); }
            });
    });
});
</script>
</script>
</script>
<?php else: ?>
<!DOCTYPE html>
<html lang="es">
<head>
        <meta charset="UTF-8">
        <title>Editar Publicación | Converza</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
        <div class="row justify-content-center">
                <div class="col-md-6">
                        <div class="card shadow-lg">
                                <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Editar Publicación</h5>
                                </div>
                                <div class="card-body">
                                        <?php if (!empty($error)): ?>
                                                <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
                                        <?php endif; ?>
                                        <form method="post">
                                                <div class="mb-3">
                                                        <label for="contenido" class="form-label">Contenido</label>
                                                        <textarea name="contenido" id="contenido" class="form-control" rows="4" required><?php echo htmlspecialchars($contenido); ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                <a href="../view/index.php" class="btn btn-secondary">Cancelar</a>
                                        </form>
                                </div>
                        </div>
                </div>
        </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>
