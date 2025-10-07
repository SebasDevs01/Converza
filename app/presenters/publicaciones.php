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
                        $src = '/TrabajoRedSocial/public/avatars/'.$avatar;
                        echo '<img src="'.$src.'" class="rounded-circle me-2" width="48" height="48" alt="Avatar">';
                    } else {
                        echo '<img src="/TrabajoRedSocial/public/avatars/defect.jpg" class="rounded-circle me-2" width="48" height="48" alt="Avatar por defecto">';
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
                        echo '<img src="/TrabajoRedSocial/public/publicaciones/'.htmlspecialchars($img).'" class="rounded-3 mb-2" style="max-width:180px;max-height:180px;object-fit:cover;">';
                    }
                    echo '</div>';
                }
                ?>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <div class="like-button-container" data-id="<?php echo (int)$pub['id_pub']; ?>">
                        <button class="like-button" id="like_button_<?php echo (int)$pub['id_pub']; ?>">👍 Me gusta</button>
                        <div class="reactions-menu" style="display: none;">
                            <span class="reaction" data-reaction="like">👍</span>
                            <span class="reaction" data-reaction="love">❤️</span>
                            <span class="reaction" data-reaction="haha">😂</span>
                            <span class="reaction" data-reaction="wow">😮</span>
                            <span class="reaction" data-reaction="sad">😢</span>
                            <span class="reaction" data-reaction="angry">😡</span>
                        </div>
                    </div>
                    <span id="reaction_count_<?php echo (int)$pub['id_pub']; ?>" class="text-muted small" style="display: none; cursor: pointer;" title="Usuarios que reaccionaron">
                        <!-- Contador de reacciones -->
                    </span>
                    <div id="reaction_tooltip_<?php echo (int)$pub['id_pub']; ?>" class="reaction-tooltip" style="display: none;">
                        <!-- Tooltip con nombres de usuarios y reacciones -->
                    </div>
                    <span class="text-muted small ms-auto">
                        <i class="bi bi-chat-dots"></i> Comentarios
                    </span>
                    <a href="#" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-share"></i> Compartir
                    </a>
                </div>

            </div>
            <div class="ps-5 mt-2">
                <?php
                $stmtComentarios = $conexion->prepare("SELECT c.*, u.usuario, u.avatar FROM comentarios c JOIN usuarios u ON c.usuario = u.id_use WHERE c.publicacion = :publicacion ORDER BY c.id_com DESC LIMIT 2");
                $stmtComentarios->bindParam(':publicacion', $pub['id_pub'], PDO::PARAM_INT);
                $stmtComentarios->execute();
                $comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
                foreach ($comentarios as $com):
                    $avatarc = htmlspecialchars($com['avatar']);
                    $avatarcPath = __DIR__.'/../../public/avatars/'.$avatarc;
                    if ($avatarc && file_exists($avatarcPath)) {
                        $srcC = '/TrabajoRedSocial/public/avatars/'.$avatarc;
                        $imgC = '<img class="rounded-circle me-2" src="'.$srcC.'" alt="Avatar" width="32" height="32">';
                    } else {
                        $imgC = '<img class="rounded-circle me-2" src="/TrabajoRedSocial/public/avatars/defect.jpg" alt="Avatar por defecto" width="32" height="32">';
                    }
                ?>
                <div class="d-flex align-items-center mb-2">
                    <?php echo $imgC; ?>
                    <div class="bg-light rounded-4 p-2" style="display:inline-block;max-width:80%;">
                        <span class="fw-bold text-primary"> <?php echo htmlspecialchars($com['usuario']);?> </span>
                        <span class="text-muted small ms-2"> <?php echo htmlspecialchars($com['fecha']);?> </span><br>
                        <?php echo nl2br(htmlspecialchars($com['comentario']));?>
                    </div>
                </div>
                <?php endforeach; ?>
                <form action="/TrabajoRedSocial/app/presenters/agregarcomentario.php" method="POST">
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

    $.post('/TrabajoRedSocial/app/presenters/megusta.php', { id: postId }, function(res) {
        if (res.error) {
            alert(res.error);
        } else {
            $('#likes_' + postId).text(res.likes);
            $btn.html(res.text);
        }
    }, 'json');
});

// Manejar reacciones para múltiples publicaciones
const likeButtons = document.querySelectorAll('.like-button-container');

likeButtons.forEach(container => {
    const likeButton = container.querySelector('.like-button');
    const reactionsMenu = container.querySelector('.reactions-menu');
    const reactionCountElement = document.getElementById(`reaction_count_${container.dataset.id}`);
    const reactionTooltip = document.getElementById(`reaction_tooltip_${container.dataset.id}`);
    const postId = container.dataset.id;
    let selectedReaction = null;

    // Cargar reacción seleccionada y contador al iniciar
    fetch(`/TrabajoRedSocial/app/presenters/get_reactions.php?postId=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar reacción del usuario
                const userReaction = data.userReaction;
                if (userReaction) {
                    selectedReaction = userReaction.tipo_reaccion;
                    const reactionText = {
                        like: 'Me gusta',
                        love: 'Me encanta',
                        haha: 'Me divierte',
                        wow: 'Me asombra',
                        sad: 'Me entristece',
                        angry: 'Me enoja'
                    };
                    likeButton.innerHTML = `${selectedReaction} <span style="margin-left: 8px;">${reactionText[selectedReaction]}</span>`;
                }

                // Actualizar contador
                if (data.reactions.length > 0) {
                    reactionCountElement.style.display = 'inline';
                    reactionCountElement.textContent = `(${data.reactions.length})`;
                    reactionTooltip.innerHTML = data.reactions.map(r => `<div>${r.usuario}: ${r.tipo_reaccion}</div>`).join('');
                } else {
                    reactionCountElement.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar las reacciones:', error);
        });

    // Mostrar/ocultar menú de reacciones
    likeButton.addEventListener('click', () => {
        reactionsMenu.style.display = reactionsMenu.style.display === 'none' ? 'block' : 'none';
    });

    likeButton.addEventListener('mouseleave', () => {
        setTimeout(() => {
            if (!reactionsMenu.matches(':hover')) {
                reactionsMenu.style.display = 'none';
            }
        }, 200);
    });

    reactionsMenu.addEventListener('mouseleave', () => {
        reactionsMenu.style.display = 'none';
    });

    // Manejar selección de reacción
    reactionsMenu.addEventListener('click', (event) => {
        const reaction = event.target.dataset.reaction;

        if (reaction) {
            const reactionText = {
                like: 'Me gusta',
                love: 'Me encanta',
                haha: 'Me divierte',
                wow: 'Me asombra',
                sad: 'Me entristece',
                angry: 'Me enoja'
            };

            if (selectedReaction === reaction) {
                selectedReaction = null;
                likeButton.textContent = '👍 Me gusta';
            } else {
                selectedReaction = reaction;
                likeButton.innerHTML = `${event.target.outerHTML} <span style="margin-left: 8px;">${reactionText[reaction]}</span>`;
            }

            // Enviar la reacción al servidor
            fetch('/TrabajoRedSocial/app/presenters/save_reaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reaction, postId }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reactionCountElement.style.display = 'inline';
                        reactionCountElement.textContent = `(${data.reactions.length})`;
                        reactionTooltip.innerHTML = data.reactions.map(r => `<div>${r.usuario}: ${r.tipo_reaccion}</div>`).join('');
                    }
                })
                .catch(error => {
                    console.error('Error al guardar la reacción:', error);
                });
        }

        // Cerrar el menú después de seleccionar
        reactionsMenu.style.display = 'none';
    });

    // Mostrar tooltip al pasar el cursor sobre el contador
    reactionCountElement.addEventListener('mouseenter', () => {
        reactionTooltip.style.display = 'block';
        reactionTooltip.style.opacity = '1';
        reactionTooltip.style.transform = 'translateY(0)';
    });

    reactionCountElement.addEventListener('mouseleave', () => {
        reactionTooltip.style.opacity = '0';
        reactionTooltip.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            reactionTooltip.style.display = 'none';
        }, 200);
    });
});
</script>

<style>
/* Estilo del menú de reacciones */
.reactions-menu {
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 10px;
    display: flex;
    gap: 10px;
    z-index: 1000;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.reaction {
    font-size: 24px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.reaction:hover {
    transform: scale(1.2);
}

/* Estilo del tooltip de reacciones */
.reaction-tooltip {
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 10px;
    z-index: 1000;
    transition: opacity 0.2s ease, transform 0.2s ease;
    opacity: 0;
    transform: translateY(-10px);
}
</style>

