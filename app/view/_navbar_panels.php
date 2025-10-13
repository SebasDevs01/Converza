<?php
// Paneles emergentes para la navbar (offcanvas Bootstrap)
?>
<!-- Offcanvas: Buscar usuarios -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSearch" aria-labelledby="offcanvasSearchLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasSearchLabel"><i class="bi bi-search"></i> Buscar usuarios</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <input type="text" id="buscador-usuarios" class="form-control mb-2" placeholder="Buscar usuario...">
    <div id="resultados-busqueda"></div>
  </div>
</div>
<!-- Offcanvas: Solicitudes de amistad -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSolicitudes" aria-labelledby="offcanvasSolicitudesLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasSolicitudesLabel"><i class="bi bi-person-plus"></i> Solicitudes de amistad</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <?php
    $stmtAmigos = $conexion->prepare("SELECT * FROM amigos WHERE para = :usuario_id AND estado = 0 ORDER BY id_ami DESC LIMIT 4");
    $stmtAmigos->bindParam(':usuario_id', $_SESSION['id'], PDO::PARAM_INT);
    $stmtAmigos->execute();
    $resAmigos = $stmtAmigos->fetchAll(PDO::FETCH_ASSOC);
    if ($resAmigos):
        foreach ($resAmigos as $am):
            $stmtUse = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id_use");
            $stmtUse->bindParam(':id_use', $am['de'], PDO::PARAM_INT);
            $stmtUse->execute();
            $us = $stmtUse->fetch(PDO::FETCH_ASSOC);
            $avatarU = htmlspecialchars($us['avatar']);
            $avatarUPath = realpath(__DIR__.'/../../public/avatars/'.$avatarU);
            $avatarUWeb = '/Converza/public/avatars/'.$avatarU;
            if ($avatarU && $avatarU !== 'default_avatar.svg' && $avatarUPath && file_exists($avatarUPath)) {
                $imgU = '<img src="'.$avatarUWeb.'" class="rounded-circle me-2" width="40" height="40" alt="Avatar" loading="lazy">';
            } else {
                $imgU = '<img src="/Converza/public/avatars/defect.jpg" class="rounded-circle me-2" width="40" height="40" alt="Avatar por defecto" loading="lazy">';
            }
    ?>
    <div class="d-flex align-items-center mb-2">
        <?php echo $imgU; ?>
        <a class="me-auto fw-bold text-decoration-none" href="/Converza/app/presenters/perfil.php?id=<?php echo (int)$us['id_use']; ?>"><?php echo htmlspecialchars($us['usuario']); ?></a>
        <button onclick="manejarSolicitud('aceptar', <?php echo (int)$am['de']; ?>)" class="btn btn-success btn-sm me-1"><i class="bi bi-check"></i></button>
        <button onclick="manejarSolicitud('rechazar', <?php echo (int)$am['de']; ?>)" class="btn btn-danger btn-sm"><i class="bi bi-x"></i></button>
    </div>
    <?php
        endforeach;
    else:
        echo '<div class="text-muted">No tienes solicitudes pendientes.</div>';
    endif;
    ?>
  </div>
</div>
<!-- Offcanvas: Nuevos usuarios -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNuevos" aria-labelledby="offcanvasNuevosLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasNuevosLabel"><i class="bi bi-people"></i> Nuevos usuarios</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <div class="row g-2">
      <?php
      $stmtReg = $conexion->prepare("SELECT id_use, avatar, usuario, fecha_reg FROM usuarios WHERE id_use != :current_user ORDER BY id_use DESC LIMIT 6");
      $stmtReg->bindParam(':current_user', $_SESSION['id'], PDO::PARAM_INT);
      $stmtReg->execute();
      $resReg = $stmtReg->fetchAll(PDO::FETCH_ASSOC);
      foreach ($resReg as $reg):
          $avatarR = htmlspecialchars($reg['avatar']);
          $avatarRPath = realpath(__DIR__.'/../../public/avatars/'.$avatarR);
          $avatarRWeb = '/Converza/public/avatars/'.$avatarR;
          if ($avatarR && $avatarR !== 'default_avatar.svg' && $avatarRPath && file_exists($avatarRPath)) {
              $imgR = '<img src="'.$avatarRWeb.'" class="card-img-top rounded-top" style="height:80px;object-fit:cover;" loading="lazy" title="Avatar de usuario">';
          } else {
              $imgR = '<img src="/Converza/public/avatars/defect.jpg" class="card-img-top rounded-top" style="height:80px;object-fit:cover;" width="100%" height="80" loading="lazy" title="Avatar por defecto">';
          }
      ?>
      <div class="col-6">
        <div class="card h-100 text-center border-0 bg-light">
          <?php echo $imgR; ?>
          <div class="card-body p-2">
            <a class="fw-bold text-decoration-none" href="/Converza/app/presenters/perfil.php?id=<?php echo (int)$reg['id_use']; ?>"><?php echo htmlspecialchars($reg['usuario']); ?></a>
            <div class="text-muted small"><?php echo htmlspecialchars($reg['fecha_reg']); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
function manejarSolicitud(accion, userId) {
    fetch('/Converza/app/presenters/solicitud.php?action=' + accion + '&id=' + userId)
        .then(response => response.text())
        .then(data => {
            // Actualizar contador de notificaciones
            const badges = document.querySelectorAll('.notification-badge');
            badges.forEach(badge => {
                const currentCount = parseInt(badge.textContent.replace('+', ''));
                const newCount = Math.max(0, currentCount - 1);
                if (newCount === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.textContent = newCount > 9 ? '9+' : newCount;
                }
            });
            
            // Recargar el contenido del panel de solicitudes
            location.reload(); // Por ahora recargamos la página
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
}
</script>

<!-- Offcanvas: Daily Shuffle -->
<div class="offcanvas offcanvas-end offcanvas-daily-shuffle" tabindex="-1" id="offcanvasDailyShuffle" aria-labelledby="offcanvasDailyShuffleLabel">
  <div class="offcanvas-header bg-gradient-shuffle text-white">
    <h5 class="offcanvas-title" id="offcanvasDailyShuffleLabel">
        <i class="bi bi-shuffle"></i> Daily Shuffle
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body p-0">
    <!-- Loading spinner -->
    <div id="shuffle-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-3 text-muted">Preparando tu shuffle diario...</p>
    </div>
    
    <!-- Mensaje de bienvenida -->
    <div id="shuffle-welcome" class="text-center p-4" style="display: none;">
        <i class="bi bi-shuffle display-1 text-primary"></i>
        <h4 class="mt-3">🎲 ¡Descubre gente nueva!</h4>
        <p class="text-muted">Cada día te mostramos hasta 10 personas nuevas para conectar</p>
        <div class="shuffle-stats mt-3">
            <span class="badge bg-primary" id="shuffle-total-count" style="font-size: 1rem; padding: 8px 15px;">
                <i class="bi bi-people-fill"></i> 0 usuarios hoy
            </span>
        </div>
    </div>
    
    <!-- Contenedor de cards -->
    <div id="shuffle-cards-container" class="shuffle-container" style="display: none;">
        <!-- Cards se insertan aquí dinámicamente -->
    </div>
    
    <!-- Mensaje cuando no hay más usuarios -->
    <div id="shuffle-empty" class="text-center p-4" style="display: none;">
        <i class="bi bi-emoji-smile display-1 text-muted"></i>
        <h5 class="mt-3">¡Eso es todo por hoy!</h5>
        <p class="text-muted">Vuelve mañana para descubrir más personas</p>
        <small class="text-muted">Usuarios contactados: <span id="contacted-count">0</span></small>
    </div>
  </div>
</div>

<style>
/* Estilos para Daily Shuffle */
.offcanvas-daily-shuffle {
    width: 450px !important;
    max-width: 90vw;
}

.bg-gradient-shuffle {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    /* Azul de Bootstrap/Converza */
}

.shuffle-container {
    position: relative;
    height: calc(100vh - 120px);
    overflow-y: auto;
    padding: 20px;
}

.shuffle-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: slideIn 0.5s ease;
}

.shuffle-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.shuffle-card-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.shuffle-card-body {
    padding: 20px;
}

.shuffle-card-username {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 8px;
}

.shuffle-card-bio {
    color: #718096;
    margin-bottom: 15px;
    font-size: 0.95rem;
    line-height: 1.5;
}

.shuffle-actions {
    display: flex;
    gap: 10px;
    justify-content: space-between;
}

.shuffle-btn {
    flex: 1;
    padding: 12px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.shuffle-btn:hover {
    transform: scale(1.05);
}

.shuffle-btn-profile {
    background: #e2e8f0;
    color: #4a5568;
}

.shuffle-btn-profile:hover {
    background: #cbd5e0;
}

.shuffle-btn-follow {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
}

.shuffle-btn-follow:hover {
    background: linear-gradient(135deg, #0b5ed7 0%, #0d6efd 100%);
}

.shuffle-btn-friend {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    /* Azul de Converza para botón Agregar */
    color: white;
}

.shuffle-btn-friend:hover {
    background: linear-gradient(135deg, #0b5ed7 0%, #0d6efd 100%);
    transform: scale(1.08);
    box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
}

.shuffle-card-contacted {
    opacity: 0.6;
    position: relative;
}

.shuffle-card-contacted::after {
    content: "✓ Ya contactado";
    position: absolute;
    top: 15px;
    right: 15px;
    background: #48bb78;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.shuffle-stats {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 768px) {
    .offcanvas-daily-shuffle {
        max-width: 100%;
    }
    
    .shuffle-card-image {
        height: 200px;
    }
}
</style>

<script>
// JavaScript para Daily Shuffle
let shuffleData = [];
let currentShuffleIndex = 0;

// Abrir el offcanvas y cargar datos
document.getElementById('offcanvasDailyShuffle')?.addEventListener('show.bs.offcanvas', function () {
    cargarDailyShuffle();
});

function cargarDailyShuffle() {
    const loading = document.getElementById('shuffle-loading');
    const welcome = document.getElementById('shuffle-welcome');
    const container = document.getElementById('shuffle-cards-container');
    const empty = document.getElementById('shuffle-empty');
    
    loading.style.display = 'block';
    welcome.style.display = 'none';
    container.style.display = 'none';
    empty.style.display = 'none';
    
    fetch('/Converza/app/presenters/daily_shuffle.php')
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            
            shuffleData = data.shuffle || [];
            
            if (shuffleData.length === 0) {
                empty.style.display = 'block';
                document.getElementById('contacted-count').textContent = '0';
            } else {
                welcome.style.display = 'block';
                container.style.display = 'block';
                document.getElementById('shuffle-total-count').textContent = `${shuffleData.length} usuarios hoy`;
                renderShuffleCards();
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            console.error('Error cargando shuffle:', error);
            alert('Error al cargar Daily Shuffle');
        });
}

function renderShuffleCards() {
    const container = document.getElementById('shuffle-cards-container');
    container.innerHTML = '';
    
    let contactedCount = 0;
    
    shuffleData.forEach((usuario, index) => {
        const isContacted = usuario.ya_contactado == 1;
        if (isContacted) contactedCount++;
        
        const avatarUrl = usuario.avatar && usuario.avatar !== 'defect.jpg' 
            ? `/Converza/public/avatars/${usuario.avatar}` 
            : '/Converza/public/avatars/defect.jpg';
        
        const card = document.createElement('div');
        card.className = `shuffle-card ${isContacted ? 'shuffle-card-contacted' : ''}`;
        card.innerHTML = `
            <img src="${avatarUrl}" 
                 alt="${escapeHtml(usuario.nombre || usuario.usuario)}" 
                 class="shuffle-card-image"
                 onerror="this.src='/Converza/public/avatars/defect.jpg'">
            <div class="shuffle-card-body">
                <h3 class="shuffle-card-username">
                    ${escapeHtml(usuario.usuario)}
                </h3>
                <p class="shuffle-card-bio">
                    ${escapeHtml(usuario.descripcion || 'Usuario de Converza 👋')}
                </p>
                <div class="shuffle-actions">
                    <button class="shuffle-btn shuffle-btn-profile" 
                            onclick="window.location.href='/Converza/app/presenters/perfil.php?id=${usuario.usuario_mostrado_id}'">
                        <i class="bi bi-person"></i> Ver perfil
                    </button>
                    <button class="shuffle-btn shuffle-btn-friend" 
                            onclick="enviarSolicitudAmistad(${usuario.usuario_mostrado_id}, this)"
                            ${isContacted ? 'disabled' : ''}>
                        <i class="bi bi-person-plus"></i> Agregar
                    </button>
                </div>
            </div>
        `;
        
        container.appendChild(card);
    });
    
    // Actualizar contador de contactados
    document.getElementById('contacted-count').textContent = contactedCount;
}

function enviarSolicitudAmistad(userId, button) {
    if (button.disabled) return;
    
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
    
    // Enviar solicitud de amistad
    fetch('/Converza/app/presenters/enviar_solicitud_shuffle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${userId}`
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            throw new Error(result.error || 'Error al enviar solicitud');
        }
        // Marcar como contactado en Daily Shuffle
        return fetch('/Converza/app/presenters/marcar_contacto_shuffle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `usuario_contactado_id=${userId}`
        });
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = '<i class="bi bi-check"></i> ¡Enviado!';
        button.classList.remove('shuffle-btn-friend');
        button.classList.add('shuffle-btn-profile');
        
        // Marcar la card como contactada
        const card = button.closest('.shuffle-card');
        card.classList.add('shuffle-card-contacted');
        
        // Actualizar contador
        const contactedCount = parseInt(document.getElementById('contacted-count').textContent);
        document.getElementById('contacted-count').textContent = contactedCount + 1;
        
        setTimeout(() => {
            button.innerHTML = '<i class="bi bi-check"></i> Contactado';
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-person-plus"></i> Reintentar';
        alert('Error: ' + error.message);
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>
