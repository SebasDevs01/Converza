<?php
if (session_status() === PHP_SESSION_NONE) {
        session_start();
}
require_once(__DIR__.'/../models/config.php');
$sessionUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$CantidadMostrar = 5;
$compag = isset($_GET['pag']) ? max(1, intval($_GET['pag'])) : 1;
$offset = ($compag - 1) * $CantidadMostrar;
// Detectar nombre correcto de columna de usuario en publicaciones
$stmt = $conexion->prepare("SELECT p.*, u.usuario, u.avatar, u.id_use AS usuario_id FROM publicaciones p JOIN usuarios u ON p.usuario = u.id_use ORDER BY p.id_pub DESC LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $CantidadMostrar, PDO::PARAM_INT);
$stmt->execute();
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex flex-column gap-4">
<?php foreach ($publicaciones as $pub): ?>
    <div class="card shadow-sm border-0 mb-2">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <?php
                    $avatar = htmlspecialchars($pub['avatar']);
                    $avatarPath = __DIR__.'/../../public/avatars/'.$avatar;
                    if ($avatar && file_exists($avatarPath)) {
                        $src = '/converza/public/avatars/'.$avatar;
                        echo '<img src="'.$src.'" class="rounded-circle me-2" width="48" height="48" alt="Avatar">';
                    } else {
                        echo '<img src="/converza/public/avatars/defect.jpg" class="rounded-circle me-2" width="48" height="48" alt="Avatar por defecto">';
                    }
                ?>
                <div>
                    <span class="fw-bold text-primary" style="cursor:pointer;" onclick="location.href='../presenters/perfil.php?id=<?php echo (int)$pub['usuario_id'];?>';">
                        <?php echo htmlspecialchars($pub['usuario']);?>
                    </span>
                    <br>
                    <span class="text-muted small"><?php echo htmlspecialchars($pub['fecha']);?></span>
                </div>
                <div class="ms-auto">
                    <?php if ($sessionUserId && isset($pub['usuario_id']) && (int)$pub['usuario_id'] === $sessionUserId): ?>
                        <div class="custom-menu-wrapper position-relative d-inline-block">
                            <button class="btn btn-light btn-sm rounded-circle custom-menu-btn" type="button" data-pub-id="<?php echo (int)$pub['id_pub']; ?>" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="custom-menu shadow" id="customMenu-<?php echo (int)$pub['id_pub']; ?>" style="display:none;position:absolute;top:40px;right:0;z-index:1000;min-width:140px;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.10);">
                                <a href="#" class="d-block px-4 py-2 text-dark custom-edit" data-pub-id="<?php echo (int)$pub['id_pub']; ?>" style="text-decoration:none;font-size:1rem;">✏️ Editar</a>
                                <a href="#" class="d-block px-4 py-2 text-danger custom-delete" data-pub-id="<?php echo (int)$pub['id_pub']; ?>" style="text-decoration:none;font-size:1rem;">🗑️ Eliminar</a>
                            </div>
                        </div>
<!-- Estilos para el menú de los tres puntos -->
<style>
    .custom-menu-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px #0d6efd33;
    }
    .custom-menu {
        min-width: 140px;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        padding: 0.5rem 0;
        background: #fff;
        display: none;
    }
    .custom-menu a {
        font-size: 1rem;
        padding: 0.7rem 1.2rem;
        cursor: pointer;
        border-radius: 8px;
        transition: background 0.15s;
    }
    .custom-menu a:hover {
        background: #f0f4fa;
    }
    
    /* Estilos para el menú de comentarios */
    .comment-menu-btn {
        opacity: 0.6;
        transition: all 0.2s ease;
    }
    .comment-menu-btn:hover {
        opacity: 1;
        transform: scale(1.05);
    }
    .comment-menu-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px #0d6efd33;
    }
    .comment-menu {
        min-width: 120px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 0.25rem 0;
        background: #fff;
        display: none;
    }
    .comment-menu a {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        border-radius: 6px;
        transition: background 0.15s;
    }
    .comment-menu a:hover {
        background: #fff5f5;
    }
    
    /* Estilos para contadores de reacciones y comentarios mejorados */
    .reaction-counter, .comment-counter {
        font-size: 0.85rem;
        margin-left: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-block;
        opacity: 0.8;
        color: #6c757d;
    }
    
    .reaction-counter:hover, .comment-counter:hover {
        opacity: 1;
        transform: scale(1.05);
        color: #007bff;
    }
    
    /* Contador dentro del botón */
    .like-main-btn .reaction-counter {
        margin-left: 0.5rem;
        font-weight: 500;
    }
    
    /* Estilos para tooltip mejorado - reacciones y comentarios */
    .reaction-counter[title]:hover::after, .comment-counter[title]:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: pre-line;
        z-index: 1000;
        margin-bottom: 5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    
    /* Estilo para botones de reacción activos (SIN fondo colorido) */
    .btn-reaction-active {
        background: transparent !important;
        border: 2px solid #007bff !important;
        color: #007bff !important;
        box-shadow: none !important;
        transform: scale(1.02);
        transition: all 0.2s ease;
    }
    
    .btn-reaction-active:hover {
        transform: scale(1.05);
        background: rgba(0, 123, 255, 0.1) !important;
        border-color: #0056b3 !important;
        color: #0056b3 !important;
    }
    
    /* Asegurar que todos los botones de reacción no tengan fondo */
    .like-main-btn {
        background: transparent !important;
    }
    
    .like-main-btn:hover {
        background: rgba(0, 123, 255, 0.05) !important;
    }
    
    /* Transiciones suaves para botones de reacciones */
    .btn {
        transition: all 0.2s ease;
    }
    
    .like-text {
        transition: all 0.3s ease;
    }
</style>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mb-2 ps-5">
                <div class="bg-light rounded-4 p-3 mb-2" style="display:inline-block;max-width:90%;">
                    <?php echo nl2br(htmlspecialchars($pub['contenido']));?>
                </div>
                <?php
                // Mostrar imágenes antiguas (columna imagen) y nuevas (tabla imagenes_publicacion)
                $imagenes = [];
                // Imagen antigua (columna imagen)
                if (!empty($pub['imagen'])) {
                    $imagenes[] = $pub['imagen'];
                }
                // Imágenes nuevas (tabla imagenes_publicacion)
                $stmtImgs = $conexion->prepare("SELECT nombre_imagen FROM imagenes_publicacion WHERE publicacion_id = :pubid");
                $stmtImgs->bindParam(':pubid', $pub['id_pub'], PDO::PARAM_INT);
                $stmtImgs->execute();
                $imagenes_db = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
                $imagenes = array_merge($imagenes, $imagenes_db);
                if ($imagenes) {
                    echo '<div class="d-flex flex-wrap gap-2 mb-2">';
                    foreach ($imagenes as $img) {
                        echo '<img src="/converza/public/publicaciones/'.htmlspecialchars($img).'" class="rounded-3 mb-2" style="max-width:180px;max-height:180px;object-fit:cover;">';
                    }
                    echo '</div>';
                }
                ?>
                <?php
                // Mostrar videos asociados a publicaciones
                $stmtVideos = $conexion->prepare("SELECT video FROM publicaciones WHERE id_pub = :pubid AND video IS NOT NULL");
                $stmtVideos->bindParam(':pubid', $pub['id_pub'], PDO::PARAM_INT);
                $stmtVideos->execute();
                $videos = $stmtVideos->fetchAll(PDO::FETCH_COLUMN);
                if ($videos) {
                    echo '<div class="d-flex flex-wrap gap-2 mb-2">';
                    foreach ($videos as $video) {
                        echo '<video controls class="rounded-3 mb-2" style="max-width:320px;max-height:240px;object-fit:cover;">
                                <source src="/converza/public/publicaciones/'.htmlspecialchars($video).'" type="video/mp4">
                              </video>';
                    }
                    echo '</div>';
                }
                ?>
                <?php
                // Mostrar videos de YouTube embebidos
                if (!empty($pub['youtube_link'])) {
                    echo '<div class="mb-2">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/'.htmlspecialchars($pub['youtube_link']).'" frameborder="0" allowfullscreen></iframe>
                          </div>';
                }
                ?>


                <div class="border-top pt-2 mt-2">
                    <div class="d-flex justify-content-around">
                        <!-- Botón Me gusta con menú hover -->
                        <div class="like-container position-relative d-flex align-items-center">
                            <button class="btn btn-outline-secondary btn-sm like-main-btn" data-post-id="<?php echo (int)$pub['id_pub']; ?>" id="like_btn_<?php echo (int)$pub['id_pub']; ?>">
                                <span class="like-icon">👍</span> <span class="like-text">Me gusta</span>
                            </button>
                            <span class="reaction-counter ms-2" id="reaction_counter_<?php echo (int)$pub['id_pub']; ?>" style="display: none;"></span>
                            
                            <!-- Menú de reacciones -->
                            <div class="reactions-popup" style="display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 10px; background: white; border: 1px solid #ccc; border-radius: 25px; padding: 5px 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000;">
                                <div class="d-flex gap-5px">
                                    <span class="reaction-btn" data-reaction="me_gusta" data-post="<?php echo (int)$pub['id_pub']; ?>" title="Me gusta">👍</span>
                                    <span class="reaction-btn" data-reaction="me_encanta" data-post="<?php echo (int)$pub['id_pub']; ?>" title="Me encanta">❤️</span>
                                    <span class="reaction-btn" data-reaction="me_divierte" data-post="<?php echo (int)$pub['id_pub']; ?>" title="Me divierte">😂</span>
                                    <span class="reaction-btn" data-reaction="me_asombra" data-post="<?php echo (int)$pub['id_pub']; ?>" title="Me asombra">😮</span>
                                    <span class="reaction-btn" data-reaction="me_entristece" data-post="<?php echo (int)$pub['id_pub']; ?>" title="Me entristece">😢</span>
                                    <span class="reaction-btn" data-reaction="me_enoja" data-post="<?php echo (int)$pub['id_pub']; ?>" title="Me enoja">😡</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="comment-container d-flex align-items-center">
                            <button class="btn btn-outline-secondary btn-sm comment-btn" data-post-id="<?php echo (int)$pub['id_pub']; ?>" onclick="document.querySelector('#comment_form_<?php echo (int)$pub['id_pub']; ?>').scrollIntoView()">
                                <i class="bi bi-chat-dots"></i> <span class="comment-text">Comentar</span>
                            </button>
                            <span class="comment-counter ms-2" id="comment_counter_<?php echo (int)$pub['id_pub']; ?>"></span>
                        </div>
                        
                        <div class="share-container position-relative">
                            <button class="btn btn-outline-secondary btn-sm share-button" data-post-id="<?php echo (int)$pub['id_pub']; ?>">
                                <i class="bi bi-share"></i> Compartir
                            </button>
                            
                            <!-- Menú de compartir -->
                            <div class="share-menu" style="display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); margin-bottom: 10px; background: white; border: 1px solid #ccc; border-radius: 8px; padding: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; min-width: 200px;">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-success btn-sm share-whatsapp" data-post-id="<?php echo (int)$pub['id_pub']; ?>">
                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm share-facebook" data-post-id="<?php echo (int)$pub['id_pub']; ?>">
                                        <i class="bi bi-facebook"></i> Facebook
                                    </button>
                                    <button class="btn btn-outline-dark btn-sm share-twitter" data-post-id="<?php echo (int)$pub['id_pub']; ?>">
                                        <i class="bi bi-twitter-x"></i> X (Twitter)
                                    </button>
                                    <button class="btn btn-outline-info btn-sm share-telegram" data-post-id="<?php echo (int)$pub['id_pub']; ?>">
                                        <i class="bi bi-telegram"></i> Telegram
                                    </button>
                                    <hr class="my-2">
                                    <button class="btn btn-outline-secondary btn-sm share-copy" data-post-id="<?php echo (int)$pub['id_pub']; ?>">
                                        <i class="bi bi-clipboard"></i> Copiar enlace
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="ps-5 mt-2">
                <?php
                $stmtComentarios = $conexion->prepare("SELECT c.*, u.usuario as nombre_usuario, u.avatar, u.id_use FROM comentarios c JOIN usuarios u ON c.usuario = u.id_use WHERE c.publicacion = :publicacion ORDER BY c.id_com ASC");
                $stmtComentarios->bindParam(':publicacion', $pub['id_pub'], PDO::PARAM_INT);
                $stmtComentarios->execute();
                $comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
                foreach ($comentarios as $com):
                    $avatarc = htmlspecialchars($com['avatar']);
                    $avatarcPath = __DIR__.'/../../public/avatars/'.$avatarc;
                    if ($avatarc && file_exists($avatarcPath)) {
                        $srcC = '/Converza/public/avatars/'.$avatarc;
                        $imgC = '<img class="rounded-circle me-2" src="'.$srcC.'" alt="Avatar" width="32" height="32">';
                    } else {
                        $imgC = '<img class="rounded-circle me-2" src="/Converza/public/avatars/defect.jpg" alt="Avatar por defecto" width="32" height="32">';
                    }
                ?>
                <div class="d-flex align-items-center mb-2">
                    <?php echo $imgC; ?>
                    <div class="bg-light rounded-4 p-2 flex-grow-1" style="max-width:80%;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <span class="fw-bold text-primary"> <?php echo htmlspecialchars($com['nombre_usuario']);?> </span>
                                <span class="text-muted small ms-2"> <?php echo htmlspecialchars($com['fecha']);?> </span><br>
                                <?php echo nl2br(htmlspecialchars($com['comentario']));?>
                            </div>
                            <?php 
                            // Debug temporal - quitar después
                            echo "<!-- DEBUG: sessionUserId=$sessionUserId, com[usuario]={$com['usuario']}, nombre={$com['nombre_usuario']} -->";
                            
                            // Verificar si el usuario puede eliminar el comentario (dueño o admin)
                            $canDelete = false;
                            
                            // Usuario logueado
                            if ($sessionUserId > 0) {
                                // Es el dueño del comentario (c.usuario es el ID del usuario que hizo el comentario)
                                if ((int)$com['usuario'] === $sessionUserId) {
                                    $canDelete = true;
                                }
                                
                                // Es admin (verificar si existe rol de admin en sesión o BD)
                                if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
                                    $canDelete = true;
                                } elseif (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1) {
                                    $canDelete = true;
                                }
                            }
                            
                            echo "<!-- DEBUG: canDelete=" . ($canDelete ? 'true' : 'false') . " -->";
                            
                            if ($canDelete): 
                            ?>
                                <div class="comment-menu-wrapper position-relative d-inline-block ms-2 flex-shrink-0">
                                    <button class="btn btn-light btn-sm rounded-circle comment-menu-btn" type="button" data-comment-id="<?php echo (int)$com['id_com']; ?>" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;border:1px solid #dee2e6;">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="comment-menu shadow" id="commentMenu-<?php echo (int)$com['id_com']; ?>" style="display:none;position:absolute;top:30px;right:0;z-index:1000;min-width:120px;background:#fff;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                                        <a href="#" class="d-block px-3 py-2 text-danger comment-delete" data-comment-id="<?php echo (int)$com['id_com']; ?>" style="text-decoration:none;font-size:0.9rem;">🗑️ Eliminar</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <form id="comment_form_<?php echo (int)$pub['id_pub']; ?>" action="/Converza/app/presenters/agregarcomentario.php" method="POST">
                    <input type="text" class="enviar-btn form-control" placeholder="Escribe un comentario" name="comentario" required>
                    <input type="hidden" name="usuario" value="<?php echo $sessionUserId; ?>">
                    <input type="hidden" name="publicacion" value="<?php echo (int)$pub['id_pub'];?>">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send"></i></button>
                </form>
            </div>
        </div>
    </div>

<?php endforeach; ?>
</div>
<!-- Bootstrap JS para dropdowns -->
<script>
// Menú personalizado de los tres puntos
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.custom-menu-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            let pubId = btn.getAttribute('data-pub-id');
            document.querySelectorAll('.custom-menu').forEach(m => m.style.display = 'none');
            let menu = document.getElementById('customMenu-' + pubId);
            if (menu) menu.style.display = 'block';
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-menu').forEach(m => m.style.display = 'none');
        document.querySelectorAll('.comment-menu').forEach(m => m.style.display = 'none');
    });

    // Menú de comentarios (3 puntos)
    document.querySelectorAll('.comment-menu-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            let commentId = btn.getAttribute('data-comment-id');
            document.querySelectorAll('.comment-menu').forEach(m => m.style.display = 'none');
            let menu = document.getElementById('commentMenu-' + commentId);
            if (menu) menu.style.display = 'block';
        });
    });

    // Eliminar comentario
    document.querySelectorAll('.comment-delete').forEach(function(delBtn) {
        delBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let commentId = delBtn.getAttribute('data-comment-id');
            if (confirm('¿Seguro que deseas eliminar este comentario?')) {
                fetch('/Converza/app/presenters/eliminar_comentario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        comentario_id: parseInt(commentId)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload(); // Recargar para actualizar los comentarios
                    } else {
                        alert('Error al eliminar el comentario: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
            }
        });
    });
    // Eliminar publicación AJAX
    document.querySelectorAll('.custom-delete').forEach(function(delBtn) {
        delBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let pubId = delBtn.getAttribute('data-pub-id');
            if (confirm('¿Seguro que deseas eliminar esta publicación?')) {
                fetch('../presenters/eliminar_pub.php?id=' + pubId)
                  .then(() => location.reload());
            }
        });
    });
    // Editar publicación (modal)
    document.querySelectorAll('.custom-edit').forEach(function(editBtn) {
        editBtn.addEventListener('click', function(e) {
            e.preventDefault();
            let pubId = editBtn.getAttribute('data-pub-id');
            // Mostrar modal con AJAX (simple)
            fetch('../presenters/editar_pub.php?id=' + pubId + '&modal=1')
                .then(r => r.text())
                .then(html => {
                    let modal = document.createElement('div');
                    modal.innerHTML = html;
                    document.body.appendChild(modal);
                });
        });
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).on('click', '.like', function(e) {
    e.preventDefault();

    const postId = $(this).data('id');
    const $btn = $(this);

    $.post('/Converza/app/presenters/megusta.php', { id: postId }, function(res) {
        if (res.error) {
            alert(res.error);
        } else {
            $('#likes_' + postId).text(res.likes);
            $btn.html(res.text);
        }
    }, 'json');
});

// Sistema de reacciones simple y funcional
document.addEventListener('DOMContentLoaded', function() {
    const reactions = {
        me_gusta: '👍', me_encanta: '❤️', me_divierte: '😂', me_asombra: '😮', me_entristece: '😢', me_enoja: '😡'
    };
    
    const reactionNames = {
        me_gusta: 'Me gusta', me_encanta: 'Me encanta', me_divierte: 'Me divierte', 
        me_asombra: 'Me asombra', me_entristece: 'Me entristece', me_enoja: 'Me enoja'
    };

    // Inicializar cada publicación
    document.querySelectorAll('.like-container').forEach(container => {
        const likeBtn = container.querySelector('.like-main-btn');
        const reactionsPopup = container.querySelector('.reactions-popup');
        const postId = likeBtn.dataset.postId;
        let currentUserReaction = null;

        // Cargar estado inicial
        loadReactionsData(postId);

        // Mostrar menú de reacciones en hover (solo en botón, NO en contador)
        likeBtn.addEventListener('mouseenter', () => {
            reactionsPopup.style.display = 'block';
        });

        container.addEventListener('mouseleave', () => {
            setTimeout(() => {
                if (!reactionsPopup.matches(':hover')) {
                    reactionsPopup.style.display = 'none';
                }
            }, 300);
        });

        // Clic en botón principal (Me gusta rápido)
        likeBtn.addEventListener('click', (e) => {
            if (reactionsPopup.style.display === 'none' || !reactionsPopup.style.display) {
                sendReaction(postId, 'me_gusta');
            }
        });

        // Clics en reacciones específicas
        container.querySelectorAll('.reaction-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Debug detallado del elemento
                console.log('🎯 ELEMENTO CLICKEADO:', btn);
                console.log('  - getAttribute data-reaction:', btn.getAttribute('data-reaction'));
                console.log('  - getAttribute data-post:', btn.getAttribute('data-post'));
                console.log('  - dataset.reaction:', btn.dataset.reaction);
                console.log('  - dataset.post:', btn.dataset.post);
                
                const reactionType = btn.getAttribute('data-reaction') || btn.dataset.reaction;
                const postIdFromBtn = btn.getAttribute('data-post') || btn.dataset.post;
                
                console.log('  - TIPO FINAL:', `"${reactionType}"`);
                console.log('  - POST FINAL:', `"${postIdFromBtn}"`);
                
                if (!reactionType) {
                    alert('ERROR: No se pudo obtener el tipo de reacción');
                    return;
                }
                
                sendReaction(postIdFromBtn, reactionType);
                reactionsPopup.style.display = 'none';
            });
        });

        function loadReactionsData(postId) {
            Promise.all([
                fetch(`/Converza/app/presenters/get_reactions.php?postId=${postId}`),
                fetch(`/Converza/app/presenters/get_comentarios.php?postId=${postId}`)
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(([reactionsData, commentsData]) => {
                console.log(`📊 Datos recibidos para post ${postId}:`);
                console.log('  - Reacciones:', reactionsData);
                console.log('  - Comentarios:', commentsData);
                
                // Actualizar reacciones
                if (reactionsData.success) {
                    currentUserReaction = reactionsData.userReaction;
                    console.log(`👤 Reacción del usuario actual: "${currentUserReaction}"`);
                    updateLikeButton(currentUserReaction);
                    updateReactionsSummary(reactionsData.reactions, postId);
                } else {
                    console.error('❌ Error en datos de reacciones:', reactionsData);
                }
                
                // Actualizar comentarios
                if (commentsData.success) {
                    updateCommentsSummary(commentsData.total, commentsData.comentarios, postId);
                } else {
                    console.error('❌ Error en datos de comentarios:', commentsData);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function sendReaction(postId, reactionType) {
            console.log('Enviando reacción:', reactionType, 'para post:', postId);
            
            // Verificar que tenemos sesión
            const userId = <?php echo isset($_SESSION['id']) ? $_SESSION['id'] : 0; ?>;
            console.log('ID de usuario desde sesión:', userId);
            if (userId === 0) {
                alert('Debes iniciar sesión para reaccionar');
                return;
            }
            
            const formData = new FormData();
            formData.append('id_usuario', userId);
            formData.append('id_publicacion', postId);
            formData.append('tipo_reaccion', reactionType);
            
            // Debug: verificar todo antes de enviar
            console.log('📤 ANÁLISIS COMPLETO ANTES DE ENVIAR:');
            console.log('  - Usuario ID:', userId, '(tipo:', typeof userId, ')');
            console.log('  - Post ID:', postId, '(tipo:', typeof postId, ')');
            console.log('  - Reaction Type:', `"${reactionType}"`, '(tipo:', typeof reactionType, ', longitud:', reactionType?.length, ')');
            
            if (!reactionType || reactionType.length === 0) {
                console.error('🚨 REACTION TYPE ESTÁ VACÍO!');
                alert('Error: Tipo de reacción vacío');
                return;
            }
            
            // Verificar FormData completo
            console.log('  - FormData contents:');
            for (let pair of formData.entries()) {
                console.log(`    ${pair[0]}: "${pair[1]}" (longitud: ${pair[1].length})`);
            }

            fetch('/Converza/app/presenters/save_reaction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Status de respuesta:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Respuesta completa del servidor:', data);
                if (data.success) {
                    currentUserReaction = data.tipo_reaccion;
                    updateLikeButton(currentUserReaction);
                    setTimeout(() => loadReactionsData(postId), 100); // Pequeño delay para asegurar que se actualice
                } else {
                    console.error('Error del servidor:', data.message);
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error de red:', error);
                alert('Error de conexión');
            });
        }

        function updateLikeButton(reaction) {
            const icon = likeBtn.querySelector('.like-icon');
            const text = likeBtn.querySelector('.like-text');
            
            console.log(`🔄 Actualizando botón con reacción del usuario: "${reaction}"`);
            
            // Animación de cambio
            likeBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                likeBtn.style.transform = 'scale(1)';
            }, 150);
            
            if (reaction && reactions[reaction]) {
                console.log(`✅ Usuario reaccionó con: ${reactions[reaction]} ${reactionNames[reaction]}`);
                // Mostrar la reacción específica del usuario
                icon.textContent = reactions[reaction];
                text.textContent = reactionNames[reaction];
                
                // Estilo activo (usuario ha reaccionado)
                likeBtn.classList.remove('btn-outline-secondary', 'btn-primary', 'btn-reaction-active');
                likeBtn.classList.add('btn-outline-primary');
                
                // Aplicar estilos sin fondo colorido
                likeBtn.style.backgroundColor = 'transparent';
                likeBtn.style.borderColor = '#007bff';
                likeBtn.style.color = '#007bff';
                
            } else {
                console.log(`🔄 Usuario no ha reaccionado`);
                // Estado por defecto - Me gusta
                icon.textContent = '👍';
                text.textContent = 'Me gusta';
                
                // Estado por defecto (usuario no ha reaccionado)
                likeBtn.classList.remove('btn-primary', 'btn-reaction-active', 'btn-outline-primary');
                likeBtn.classList.add('btn-outline-secondary');
                
                // Aplicar estilos sin fondo
                likeBtn.style.backgroundColor = 'transparent';
                likeBtn.style.borderColor = '#6c757d';
                likeBtn.style.color = '#6c757d';
            }
        }

        function updateReactionsSummary(reactionsArray, postId) {
            const counterElement = document.getElementById(`reaction_counter_${postId}`);
            console.log('Actualizando contador para post:', postId);
            console.log('Elemento contador encontrado:', counterElement);
            console.log('Datos de reacciones recibidos:', reactionsArray);
            
            if (!counterElement) {
                console.error('No se encontró elemento contador para post:', postId);
                return;
            }
            
            if (!reactionsArray || reactionsArray.length === 0) {
                counterElement.innerHTML = '(0)';
                counterElement.title = 'Sin reacciones';
                counterElement.style.display = 'inline-block';
                return;
            }

            let total = 0;
            let tooltip = '';

            // Ordenar por total descendente
            reactionsArray.sort((a, b) => parseInt(b.total) - parseInt(a.total));

            // Mostrar solo la reacción más popular en formato "❤️ Me encanta (2)"
            const topReaction = reactionsArray[0];
            const count = parseInt(topReaction.total);
            total = reactionsArray.reduce((sum, r) => sum + parseInt(r.total), 0);
            
            console.log(`🔍 Procesando reacción principal:`, topReaction);
            console.log(`  - Tipo: "${topReaction.tipo_reaccion}"`);
            console.log(`  - Count: ${count}`);
            console.log(`  - Total general: ${total}`);
            
            const emoji = reactions[topReaction.tipo_reaccion];
            const reactionName = reactionNames[topReaction.tipo_reaccion];
            
            console.log(`  - Emoji encontrado:`, emoji);
            console.log(`  - Nombre encontrado:`, reactionName);
            
            if (!emoji || !reactionName) {
                console.error(`❌ Reacción no encontrada para tipo: "${topReaction.tipo_reaccion}"`);
                console.log('Reacciones disponibles:', Object.keys(reactions));
                return;
            }

            // Construir tooltip detallado para hover
            reactionsArray.forEach((reaction) => {
                const reactionCount = parseInt(reaction.total);
                const reactionEmoji = reactions[reaction.tipo_reaccion];
                const reactionText = reactionNames[reaction.tipo_reaccion];
                const usuarios = reaction.usuarios ? reaction.usuarios.split(', ') : [];
                
                // Formato más natural para el tooltip
                if (reactionCount === 1) {
                    tooltip += `${usuarios[0]} ${reactionText.toLowerCase()} esto\n`;
                } else if (reactionCount === 2) {
                    tooltip += `${usuarios[0]} y ${usuarios[1]} les ${reactionText.toLowerCase()} esto\n`;
                } else {
                    tooltip += `${usuarios[0]}, ${usuarios[1]} y ${reactionCount - 2} más les ${reactionText.toLowerCase()} esto\n`;
                }
            });

            // Formato solo numérico: "(2)" o "y 3 más" 
            let displayText = '';
            
            if (reactionsArray.length > 1) {
                // Múltiples tipos: "y 3 más"
                displayText = `y ${reactionsArray.length - 1} más (${total})`;
            } else {
                // Un solo tipo: "(2)"
                displayText = `(${count})`;
            }

            console.log('Texto final del contador:', displayText);
            console.log('Tooltip:', tooltip.trim());

            // Mostrar solo cuando hay reacciones
            if (total > 0) {
                counterElement.innerHTML = displayText;
                counterElement.title = tooltip.trim();
                counterElement.style.display = 'inline-block';
            } else {
                counterElement.innerHTML = '';
                counterElement.title = '';
                counterElement.style.display = 'none';
            }
        }

        function updateCommentsSummary(total, comentarios, postId) {
            const counterElement = document.getElementById(`comment_counter_${postId}`);
            
            if (total === 0) {
                counterElement.textContent = '(0)';
                counterElement.title = 'Sin comentarios';
                return;
            }

            // Obtener nombres únicos de usuarios que comentaron
            const usuarios = [...new Set(comentarios.map(comment => comment.usuario))];
            
            let tooltip = '';
            if (usuarios.length === 1) {
                tooltip = `${usuarios[0]} comentó esto`;
            } else if (usuarios.length === 2) {
                tooltip = `${usuarios[0]} y ${usuarios[1]} comentaron esto`;
            } else if (usuarios.length > 2) {
                tooltip = `${usuarios[0]}, ${usuarios[1]} y ${usuarios.length - 2} más comentaron esto`;
            }

            counterElement.textContent = `(${total})`;
            counterElement.title = tooltip;
            counterElement.style.cursor = 'pointer';
        }
    });

    // Sistema de compartir con menús
    document.querySelectorAll('.share-container').forEach(container => {
        const shareBtn = container.querySelector('.share-button');
        const shareMenu = container.querySelector('.share-menu');
        const postId = shareBtn.dataset.postId;
        const postUrl = `${window.location.origin}/Converza/app/presenters/publicacion.php?id=${postId}`;
        const shareText = `¡Mira esta publicación en Converza!`;

        // Mostrar/ocultar menú
        shareBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Cerrar otros menús abiertos
            document.querySelectorAll('.share-menu').forEach(menu => {
                if (menu !== shareMenu) menu.style.display = 'none';
            });
            // Toggle este menú
            shareMenu.style.display = shareMenu.style.display === 'none' ? 'block' : 'none';
        });

        // Ocultar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                shareMenu.style.display = 'none';
            }
        });

        // WhatsApp
        container.querySelector('.share-whatsapp').addEventListener('click', () => {
            const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText + ' ' + postUrl)}`;
            window.open(whatsappUrl, '_blank');
            shareMenu.style.display = 'none';
        });

        // Facebook
        container.querySelector('.share-facebook').addEventListener('click', () => {
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(postUrl)}`;
            window.open(facebookUrl, '_blank', 'width=600,height=400');
            shareMenu.style.display = 'none';
        });

        // Twitter/X
        container.querySelector('.share-twitter').addEventListener('click', () => {
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(postUrl)}`;
            window.open(twitterUrl, '_blank', 'width=600,height=400');
            shareMenu.style.display = 'none';
        });

        // Telegram
        container.querySelector('.share-telegram').addEventListener('click', () => {
            const telegramUrl = `https://t.me/share/url?url=${encodeURIComponent(postUrl)}&text=${encodeURIComponent(shareText)}`;
            window.open(telegramUrl, '_blank');
            shareMenu.style.display = 'none';
        });

        // Copiar enlace
        container.querySelector('.share-copy').addEventListener('click', () => {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(postUrl).then(() => {
                    const btn = container.querySelector('.share-copy');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check"></i> ¡Copiado!';
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                    }, 2000);
                });
            } else {
                prompt('Copia este enlace:', postUrl);
            }
            shareMenu.style.display = 'none';
        });
    });
});
</script>

<style>
/* Sistema de reacciones limpio */
.interactions-info {
    font-size: 14px;
    color: #6c757d;
}

.interactions-info span:hover {
    text-decoration: underline;
}

.like-container {
    position: relative;
    display: inline-block;
}

.like-main-btn {
    transition: all 0.2s ease;
}

.reactions-popup {
    white-space: nowrap;
}

.reaction-btn {
    font-size: 24px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.reaction-btn:hover {
    transform: scale(1.2);
    background-color: #f0f0f0;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-sm span {
        display: none;
    }
    
    .btn-sm {
        padding: 0.375rem;
        min-width: 45px;
        justify-content: center;
    }
    
    .like-main-btn .like-icon {
        margin-right: 0 !important;
    }
}

.reaction-option:active {
    transform: scale(1.1);
}

/* Botones de acción uniformes */
.btn-light {
    border: 1px solid #dee2e6;
    background: white;
    transition: all 0.2s ease;
}

.btn-light:hover {
    background: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Modal de reacciones */
.modal-body .reaction-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 8px;
    margin-bottom: 4px;
}

.modal-body .reaction-user:hover {
    background: #f8f9fa;
}

.modal-body .reaction-user img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.modal-body .reaction-user .reaction-emoji {
    font-size: 18px;
    margin-left: auto;
}

/* Responsive */
@media (max-width: 768px) {
    .reaction-main-btn span.reaction-text {
        display: none;
    }
    
    .reaction-main-btn {
        min-width: auto;
        padding: 6px 12px;
    }
}
</style>

<script>
// Manejar eliminación de comentarios
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.eliminar-comentario').forEach(button => {
        button.addEventListener('click', function() {
            const comentarioId = this.dataset.comentarioId;
            
            if (confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
                fetch('/Converza/app/presenters/eliminar_comentario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({comentario_id: comentarioId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Eliminar el comentario del DOM
                        this.closest('.d-flex.align-items-center.mb-2').remove();
                        
                        // Mostrar mensaje de éxito (opcional)
                        // alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al eliminar el comentario');
                });
            }
        });
    });
});
</script>
