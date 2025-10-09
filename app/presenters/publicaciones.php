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
    
    /* Estilos para contadores de reacciones mejorados */
    .reaction-counter {
        font-size: 0.9rem;
        margin-left: 0.5rem;
        cursor: pointer;
        transition: color 0.2s ease;
    }
    
    .reaction-counter:hover {
        color: #007bff;
    }
    
    /* Estilo para botones de reacción activos */
    .btn-reaction-active {
        background: linear-gradient(45deg, #007bff, #6610f2) !important;
        border: none !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(0,123,255,0.3);
        transform: scale(1.02);
        transition: all 0.2s ease;
    }
    
    .btn-reaction-active:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,123,255,0.4);
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
                        <div class="like-container position-relative">
                            <button class="btn btn-outline-secondary btn-sm like-main-btn" data-post-id="<?php echo (int)$pub['id_pub']; ?>" id="like_btn_<?php echo (int)$pub['id_pub']; ?>">
                                <span class="like-icon">👍</span> <span class="like-text">Me gusta</span> <span class="reaction-counter" id="reaction_counter_<?php echo (int)$pub['id_pub']; ?>"></span>
                            </button>
                            
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
                        
                        <button class="btn btn-outline-secondary btn-sm comment-btn" data-post-id="<?php echo (int)$pub['id_pub']; ?>" onclick="document.querySelector('#comment_form_<?php echo (int)$pub['id_pub']; ?>').scrollIntoView()">
                            <i class="bi bi-chat-dots"></i> <span class="comment-text">Comentar</span> <span class="comment-counter" id="comment_counter_<?php echo (int)$pub['id_pub']; ?>"></span>
                        </button>
                        
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
                $stmtComentarios = $conexion->prepare("SELECT c.*, u.usuario, u.avatar FROM comentarios c JOIN usuarios u ON c.usuario = u.id_use WHERE c.publicacion = :publicacion ORDER BY c.id_com ASC");
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
                            <div>
                                <span class="fw-bold text-primary"> <?php echo htmlspecialchars($com['usuario']);?> </span>
                                <span class="text-muted small ms-2"> <?php echo htmlspecialchars($com['fecha']);?> </span><br>
                                <?php echo nl2br(htmlspecialchars($com['comentario']));?>
                            </div>
                            <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $com['usuario']): ?>
                                <button class="btn btn-link btn-sm text-danger p-0 ms-2 eliminar-comentario" 
                                        data-comentario-id="<?php echo $com['id_com']; ?>" 
                                        title="Eliminar comentario">
                                    <i class="bi bi-trash3"></i>
                                </button>
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

        // Mostrar menú de reacciones en hover
        container.addEventListener('mouseenter', () => {
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
            
            console.log(`🔄 Actualizando botón con reacción: "${reaction}"`);
            
            // Animación de cambio
            likeBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                likeBtn.style.transform = 'scale(1)';
            }, 150);
            
            if (reaction && reactions[reaction]) {
                console.log(`✅ Cambiando a: ${reactions[reaction]} ${reactionNames[reaction]}`);
                // Mostrar la reacción específica seleccionada
                icon.textContent = reactions[reaction];
                text.textContent = reactionNames[reaction];
                likeBtn.classList.remove('btn-outline-secondary', 'btn-primary');
                likeBtn.classList.add('btn-reaction-active');
                
                // Mostrar mensaje personalizado temporalmente
                const originalText = text.textContent;
                text.textContent = `¡${reactionNames[reaction]}!`;
                setTimeout(() => {
                    text.textContent = originalText;
                }, 1500);
            } else {
                console.log(`🔄 Volviendo a estado por defecto`);
                // Estado por defecto
                icon.textContent = '👍';
                text.textContent = 'Me gusta';
                likeBtn.classList.remove('btn-primary', 'btn-reaction-active');
                likeBtn.classList.add('btn-outline-secondary');
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
                counterElement.innerHTML = '';
                counterElement.title = '';
                return;
            }

            let total = 0;
            let displayText = '';
            let tooltip = '';

            // Ordenar por total descendente
            reactionsArray.sort((a, b) => parseInt(b.total) - parseInt(a.total));

            reactionsArray.forEach((reaction, index) => {
                const count = parseInt(reaction.total);
                total += count;
                
                console.log(`🔍 Procesando reacción:`, reaction);
                console.log(`  - Tipo: "${reaction.tipo_reaccion}"`);
                console.log(`  - Count: ${count}`);
                console.log(`  - Usuarios: "${reaction.usuarios}"`);
                
                const emoji = reactions[reaction.tipo_reaccion];
                const reactionName = reactionNames[reaction.tipo_reaccion];
                
                console.log(`  - Emoji encontrado:`, emoji);
                console.log(`  - Nombre encontrado:`, reactionName);
                
                if (!emoji || !reactionName) {
                    console.error(`❌ Reacción no encontrada para tipo: "${reaction.tipo_reaccion}"`);
                    console.log('Reacciones disponibles:', Object.keys(reactions));
                    return; // Saltar esta reacción
                }
                
                const usuarios = reaction.usuarios ? reaction.usuarios.split(', ') : [];
                
                // Para el tooltip
                tooltip += `${emoji} ${reactionName} (${count}):\n`;
                usuarios.forEach(user => {
                    tooltip += `  • ${user}\n`;
                });
                tooltip += '\n';
                
                // Para el display (mostrar emojis de las reacciones más populares)
                if (index < 3) { // Mostrar máximo 3 emojis diferentes
                    displayText += `${emoji}${count > 1 ? count : ''} `;
                }
            });

            // Si hay más de 3 tipos de reacciones, mostrar +X
            if (reactionsArray.length > 3) {
                const remaining = reactionsArray.slice(3).reduce((sum, r) => sum + parseInt(r.total), 0);
                displayText += `+${remaining}`;
            }

            console.log('Texto final del contador:', displayText);
            console.log('Total de reacciones:', total);

            counterElement.innerHTML = `<span class="text-muted small">${displayText.trim()}</span>`;
            counterElement.title = tooltip.trim();
            counterElement.style.cursor = 'pointer';
        }

        function updateCommentsSummary(total, comentarios, postId) {
            const counterElement = document.getElementById(`comment_counter_${postId}`);
            
            if (total === 0) {
                counterElement.textContent = '';
                counterElement.title = '';
                return;
            }

            let tooltip = 'Comentarios de:\n';
            comentarios.slice(0, 5).forEach(comment => {
                tooltip += `${comment.usuario}: ${comment.comentario.substring(0, 50)}...\n`;
            });

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
