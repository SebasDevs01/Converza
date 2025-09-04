<?php
// ==================== INITIALIZATION ====================
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("lib/config.php");

// Id de usuario de la sesión (0 si no hay)
$sessionUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

// ==================== PAGINATION LOGIC ====================
$CantidadMostrar = 5;

// Validar paginación
$compag = isset($_GET['pag']) ? max(1, intval($_GET['pag'])) : 1;

// Total publicaciones
$stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM publicaciones");
$stmt->execute();
$totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
$totalr   = (int)$totalRow['total'];

// Total de páginas
$TotalRegistro = max(1, ceil($totalr / $CantidadMostrar));
$IncrimentNum  = (($compag + 1) <= $TotalRegistro) ? ($compag + 1) : 0;

// ==================== DATABASE QUERIES ====================
// Consulta publicaciones
$offset = ($compag - 1) * $CantidadMostrar;
$stmt = $conexion->prepare("SELECT * FROM publicaciones ORDER BY id_pub DESC LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $CantidadMostrar, PDO::PARAM_INT);
$stmt->execute();

$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ==================== CSS/JS INCLUDES ==================== -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="js/likes.js"></script>

<!-- ==================== JAVASCRIPT ==================== -->
<script type="text/javascript">
$(document).ready(function() {
    // Enviar comentario con Enter
    /* $(document).on("keypress", ".enviar-btn", function(event) {
        if (event.which == 13) {
            event.preventDefault();
            
            var getpID = $(this).closest('label').attr('id').replace('record-','');
            var usuario = $("#usuario-" + getpID).val();
            var comentario = $("#comentario-" + getpID).val();
            var publicacion = getpID;
            var avatar = $("#avatar-" + getpID).val();
            var nombre = $("#nombre-" + getpID).val();

            if ($.trim(comentario) === '') {
                Swal.fire('Atención', 'Debes añadir un comentario', 'warning');
                return false;
            }

            $.ajax({
                type: "POST",
                url: "agregarcomentario.php",
                data: {usuario: usuario, comentario: comentario, publicacion: publicacion},
                success: function() {
                    var now = new Date();
                    var date_show = now.getDate() + '-' + (now.getMonth()+1) + '-' + now.getFullYear()
                                    + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();

                    $('#nuevocomentario'+getpID).append(
                        '<div class="box-comment">'+
                          '<img class="img-circle img-sm" src="avatars/'+ avatar +'">'+
                          '<div class="comment-text">'+
                            '<span class="username">'+ nombre +
                              '<span class="text-muted pull-right">'+ date_show +'</span>'+
                            '</span>'+ comentario +
                          '</div>'+
                        '</div>'
                    );
                    $("#comentario-" + getpID).val("");
                }
            });
        }
    }); */

    // Confirmación para eliminar publicación
    $(document).on("click", ".btn-eliminar", function(e) {
        e.preventDefault();
        let url = $(this).attr("href");

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás deshacer esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>

<!-- ==================== PUBLICATIONS DISPLAY ==================== -->
<?php foreach ($publicaciones as $lista): ?>
    <?php
    // ==================== GET USER DATA ====================
    $userid = (int)$lista['usuario'];
    
    $stmtUser = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :userid");
    $stmtUser->bindParam(':userid', $userid, PDO::PARAM_INT);
    $stmtUser->execute();
    $use = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // ==================== GET PHOTO DATA ====================
    $fot = null;
    if ((int)$lista['imagen'] !== 0) {
        $stmtFotos = $conexion->prepare("SELECT * FROM fotos WHERE publicacion = :publicacion LIMIT 1");
        $stmtFotos->bindParam(':publicacion', $lista['id_pub'], PDO::PARAM_INT);
        $stmtFotos->execute();
        $fot = $stmtFotos->fetch(PDO::FETCH_ASSOC);
    }

    // ==================== GET COMMENTS COUNT ====================
    $stmtComentarios = $conexion->prepare("SELECT 1 FROM comentarios WHERE publicacion = :publicacion");
    $stmtComentarios->bindParam(':publicacion', $lista['id_pub'], PDO::PARAM_INT);
    $stmtComentarios->execute();
    $numcomen = $stmtComentarios->rowCount();

    // ==================== CHECK IF USER LIKED ====================
    $stmtLike = $conexion->prepare("SELECT 1 FROM likes WHERE post = :post AND usuario = :usuario LIMIT 1");
    $stmtLike->bindParam(':post', $lista['id_pub'], PDO::PARAM_INT);
    $stmtLike->bindParam(':usuario', $sessionUserId, PDO::PARAM_INT);
    $stmtLike->execute();
    $liked = $stmtLike->rowCount() > 0;
    ?>

    <!-- PUBLICATION BOX -->
    <div class="box box-widget">
        <!-- ==================== HEADER ==================== -->
        <div class="box-header with-border">
            <div class="user-block">
                <img class="img-circle" src="public/avatars/<?php echo htmlspecialchars($use['avatar']); ?>" alt="User Image">
                <span class="description"
                      onclick="location.href='perfil.php?id=<?php echo (int)$use['id_use'];?>';"
                      style="cursor:pointer; color: #3C8DBC;">
                    <?php echo htmlspecialchars($use['usuario']);?>
                </span>
                <span class="description"><?php echo htmlspecialchars($lista['fecha']);?></span>
            </div>

            <div class="box-tools">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>

                <?php if ($sessionUserId && (int)$lista['usuario'] === $sessionUserId): ?>
                    <a href="editar_pub.php?id=<?php echo (int)$lista['id_pub']; ?>" class="btn btn-warning btn-xs">
                        <i class="fa fa-edit"></i> Editar
                    </a>
                    <a href="eliminar_pub.php?id=<?php echo (int)$lista['id_pub']; ?>" class="btn btn-danger btn-xs btn-eliminar">
                        <i class="fa fa-trash"></i> Eliminar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ==================== CONTENT ==================== -->
        <div class="box-body">
            <p><?php echo nl2br(htmlspecialchars($lista['contenido']));?></p>

            <?php if($fot): ?>
                <img src="public/publicaciones/<?php echo htmlspecialchars($fot['ruta']);?>" width="100%" alt="Imagen de la publicación">
            <?php endif; ?>

            <br><br>
            <ul class="list-inline">
                <li>
                    <?php echo $lista['id_pub'];?>
                    <a href="megusta.php" class="btn btn-default btn-xs like" id="<?php echo (int)$lista['id_pub']; ?>">
                        <i class="fa fa-thumbs-o-up"></i> <?php echo $liked ? "No me gusta" : "Me gusta"; ?>
                    </a>
                    <span id="likes_<?php echo (int)$lista['id_pub']; ?>"> (<?php echo (int)$lista['likes']; ?>)</span>
                </li>
                <li class="pull-right">
                    <span class="link-black text-sm">
                        <i class="fa fa-comments-o margin-r-5"></i> Comentarios (<?php echo $numcomen; ?>)
                    </span>
                </li>
            </ul>
        </div>

        <!-- ==================== COMMENTS SECTION ==================== -->
        <div class="box-footer box-comments">
            <?php
            // Obtener comentarios
            $stmtComentarios = $conexion->prepare("SELECT * FROM comentarios WHERE publicacion = :publicacion ORDER BY id_com DESC LIMIT 2");
            $stmtComentarios->bindParam(':publicacion', $lista['id_pub'], PDO::PARAM_INT);
            $stmtComentarios->execute();
            $comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);

            foreach ($comentarios as $com):
                $stmtUserComent = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :userid");
                $stmtUserComent->bindParam(':userid', $com['usuario'], PDO::PARAM_INT);
                $stmtUserComent->execute();
                $usec = $stmtUserComent->fetch(PDO::FETCH_ASSOC);
            ?>
                <div class="box-comment">
                    <img class="img-circle img-sm" src="public/avatars/<?php echo htmlspecialchars($usec['avatar']);?>" alt="Avatar">
                    <div class="comment-text">
                        <span class="username">
                            <?php echo htmlspecialchars($usec['usuario']);?>
                            <span class="text-muted pull-right"><?php echo htmlspecialchars($com['fecha']);?></span>
                        </span>
                        <?php echo nl2br(htmlspecialchars($com['comentario']));?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($numcomen > 2): ?>
                <br>
                <center>
                    <span onclick="location.href='publicacion.php?id=<?php echo (int)$lista['id_pub'];?>';"
                          style="cursor:pointer; color: #3C8DBC;">Ver todos los comentarios</span>
                </center>
            <?php endif; ?>

            <!-- ==================== NEW COMMENT FORM ==================== -->
            <div id="nuevocomentario<?php echo (int)$lista['id_pub'];?>"></div>
            <br>
            
            <form method="post" action="agregarcomentario.php">
                <label id="record-<?php echo (int)$lista['id_pub'];?>">
                    <input type="text" class="enviar-btn form-control input-sm"
                           style="width: 800px;"
                           placeholder="Escribe un comentario"
                           name="comentario" id="comentario-<?php echo (int)$lista['id_pub'];?>">

                    <input type="hidden" name="usuario" value="<?php echo $sessionUserId; ?>" id="usuario-<?php echo (int)$lista['id_pub'];?>">
                    <input type="hidden" name="publicacion" value="<?php echo (int)$lista['id_pub'];?>" id="publicacion-<?php echo (int)$lista['id_pub'];?>">
                    <input type="hidden" name="avatar" value="<?php echo htmlspecialchars($_SESSION['avatar'] ?? 'default.png');?>" id="avatar-<?php echo (int)$lista['id_pub'];?>">
                    <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($_SESSION['usuario'] ?? '');?>" id="nombre-<?php echo (int)$lista['id_pub'];?>">

                    <button type="submit" class="btn btn-primary btn-sm" style="margin-top:5px;">Comentar</button>
                </label>
            </form>
        </div>
    </div>
    <br><br>
<?php endforeach; ?>

<!-- ==================== PAGINATION ==================== -->
<?php if($IncrimentNum > 0): ?>
    <a href="publicaciones.php?pag=<?php echo $IncrimentNum; ?>">Siguiente</a>
<?php endif; ?>
