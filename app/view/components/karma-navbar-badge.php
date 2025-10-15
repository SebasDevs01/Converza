<?php
/**
 * KARMA NAVBAR BUTTON CON BADGE DE CONTADOR
 * Similar al badge de notificaciones pero para karma
 */

require_once(__DIR__.'/../../models/config.php');
require_once(__DIR__.'/../../models/karma-social-helper.php');

// Obtener karma del usuario actual
$karmaHelper = new KarmaSocialHelper($conexion);
$karmaData = $karmaHelper->obtenerKarmaUsuario($_SESSION['id']);
$karma = $karmaData['karma_total'];
$nivelData = $karmaData['nivel_data'] ?? ['nivel' => 1, 'emoji' => '🌱'];
$nivel = $nivelData['nivel'];
$emoji = $nivelData['emoji'];

// Verificar si hay puntos pendientes en sesión (para mostrar badge)
$puntos_pendientes = $_SESSION['karma_pendiente'] ?? 0;

// ⚠️ LIMPIAR INMEDIATAMENTE para evitar que se muestre en próxima recarga
if ($puntos_pendientes != 0) {
    unset($_SESSION['karma_pendiente']); // ✅ Limpiar sesión después de leer
}
?>

<style>
.karma-navbar-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.karma-navbar-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
}

.karma-navbar-emoji {
    font-size: 1.3rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

.karma-navbar-points {
    font-weight: 700;
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.karma-navbar-level {
    font-size: 0.75rem;
    opacity: 0.95;
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 7px;
    border-radius: 10px;
}

/* 🔴 BADGE DE CONTADOR CON FLECHA (como notificaciones) */
.karma-badge-counter {
    position: absolute;
    top: -10px;
    right: -10px;
    min-width: 45px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    font-size: 0.8rem;
    font-weight: 700;
    border-radius: 14px;
    padding: 4px 8px;
    box-shadow: 0 3px 12px rgba(16, 185, 129, 0.6), 0 0 0 3px rgba(255, 255, 255, 0.4);
    animation: badge-appear 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 10;
    letter-spacing: 0.5px;
}

.karma-badge-counter.negative {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 3px 12px rgba(239, 68, 68, 0.6), 0 0 0 3px rgba(255, 255, 255, 0.4);
}

.karma-badge-counter .arrow {
    font-size: 1rem;
    font-weight: 900;
    animation: arrow-bounce 0.6s ease-in-out infinite;
}

.karma-badge-counter.negative .arrow {
    animation: arrow-bounce-down 0.6s ease-in-out infinite;
}

@keyframes badge-appear {
    0% {
        transform: scale(0) rotate(-10deg);
        opacity: 0;
    }
    50% {
        transform: scale(1.3) rotate(5deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

@keyframes badge-pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 3px 12px rgba(16, 185, 129, 0.6), 0 0 0 3px rgba(255, 255, 255, 0.4);
    }
    50% {
        transform: scale(1.08);
        box-shadow: 0 5px 18px rgba(16, 185, 129, 0.8), 0 0 0 4px rgba(255, 255, 255, 0.5);
    }
}

.karma-badge-counter.pulse {
    animation: badge-pulse 1.2s ease-in-out infinite;
}

/* Animación de flecha subiendo */
@keyframes arrow-bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-3px);
    }
}

/* Animación de flecha bajando */
@keyframes arrow-bounce-down {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(3px);
    }
}

/* Animación de actualización de puntos */
@keyframes points-update {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); color: #fbbf24; }
}

.karma-navbar-points.updating {
    animation: points-update 0.6s ease;
}
</style>

<a href="/Converza/app/presenters/karma_tienda.php" class="karma-navbar-btn" id="karma-btn">
    <span class="karma-navbar-emoji"><?php echo $emoji; ?></span>
    <div class="d-flex flex-column align-items-start" style="line-height: 1.1;">
        <span class="karma-navbar-points" id="karma-points-display"><?php echo $karma; ?></span>
        <span class="karma-navbar-level">Nv.<?php echo $nivel; ?></span>
    </div>
    
    <?php if ($puntos_pendientes != 0): ?>
        <span class="karma-badge-counter <?php echo $puntos_pendientes < 0 ? 'negative' : ''; ?> pulse" id="karma-badge">
            <span class="arrow"><?php echo $puntos_pendientes > 0 ? '↑' : '↓'; ?></span>
            <span><?php echo $puntos_pendientes > 0 ? '+' : ''; ?><?php echo $puntos_pendientes; ?></span>
        </span>
    <?php endif; ?>
</a>

<script>
// 🔄 Función para actualizar el badge de karma
function actualizarKarmaBadge(karma, nivel, puntosDelta) {
    const btn = document.getElementById('karma-btn');
    const pointsDisplay = document.getElementById('karma-points-display');
    const levelDisplay = btn.querySelector('.karma-navbar-level');
    
    if (!btn || !pointsDisplay) return;
    
    // Actualizar puntos con animación
    if (pointsDisplay.textContent !== String(karma)) {
        pointsDisplay.classList.add('updating');
        setTimeout(() => {
            pointsDisplay.textContent = karma;
            setTimeout(() => {
                pointsDisplay.classList.remove('updating');
            }, 600);
        }, 100);
    }
    
    // Actualizar nivel
    if (levelDisplay) {
        levelDisplay.textContent = 'Nv.' + nivel;
    }
    
    // Mostrar/actualizar badge de contador CON FLECHA
    if (puntosDelta !== 0) {
        let badge = document.getElementById('karma-badge');
        
        if (!badge) {
            // Crear badge si no existe
            badge = document.createElement('span');
            badge.id = 'karma-badge';
            badge.className = 'karma-badge-counter pulse';
            btn.appendChild(badge);
        }
        
        // Crear contenido con flecha
        const arrow = puntosDelta > 0 ? '↑' : '↓';
        const signo = puntosDelta > 0 ? '+' : '';
        
        badge.innerHTML = `
            <span class="arrow">${arrow}</span>
            <span>${signo}${puntosDelta}</span>
        `;
        
        // Actualizar estilo
        badge.className = 'karma-badge-counter pulse' + (puntosDelta < 0 ? ' negative' : '');
        
        // Quitar el badge después de 6 segundos
        setTimeout(() => {
            if (badge && badge.parentNode) {
                badge.style.transition = 'all 0.4s ease';
                badge.style.transform = 'scale(0) rotate(180deg)';
                badge.style.opacity = '0';
                setTimeout(() => {
                    if (badge.parentNode) badge.remove();
                }, 400);
            }
        }, 6000);
    }
}

// ⚡ NUEVA FUNCIÓN: Procesar karma instantáneo (sin fetch adicionales)
function procesarKarmaInstantaneo(karmaData, puntosGanados = 0) {
    console.log('⚡ procesarKarmaInstantaneo:', { karmaData, puntosGanados });
    
    if (!karmaData) return;
    
    // Actualizar badge con los datos recibidos
    const karma = parseInt(karmaData.karma) || 0;
    const nivel = parseInt(karmaData.nivel) || 1;
    
    actualizarKarmaBadge(karma, nivel, puntosGanados);
    
    // Marcar como actualizado para evitar fetch redundante
    const karmaDisplay = document.querySelector('#karma-points-display, .karma-navbar-points');
    if (karmaDisplay) {
        karmaDisplay.dataset.updated = 'true';
        setTimeout(() => {
            karmaDisplay.dataset.updated = 'false';
        }, 2000); // Reset después de 2 segundos
    }
}

// 🔍 Verificar karma pendiente SOLO cuando se solicita explícitamente (NO en DOMContentLoaded)
function verificarKarmaPendiente() {
    // ⚡ OPTIMIZADO: Usar fetch interceptor que ya tiene los datos
    // Solo hacer la petición si es la primera carga o si no hay interceptor
    
    fetch('/converza/app/presenters/check_karma_notification.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const { puntos } = data.data;
                
                // Solo recargar karma si no se actualizó automáticamente
                const karmaDisplay = document.querySelector('#karma-points-display, .karma-navbar-points');
                if (!karmaDisplay || karmaDisplay.dataset.updated !== 'true') {
                    fetch('/converza/app/presenters/get_karma.php')
                        .then(res => res.json())
                        .then(karmaData => {
                            if (karmaData.success) {
                                actualizarKarmaBadge(karmaData.karma, karmaData.nivel, puntos);
                            }
                        });
                }
                
                // Actualizar notificaciones de campana
                if (typeof cargarNotificaciones === 'function') {
                    cargarNotificaciones();
                }
            }
        })
        .catch(error => console.error('Error al verificar karma:', error));
}

// ⚠️ NO verificar automáticamente al cargar (evita animaciones no solicitadas)
// Solo verificar cuando el usuario interactúa (comentarios, reacciones, etc.)
// document.addEventListener('DOMContentLoaded', verificarKarmaPendiente); // ❌ DESHABILITADO

// Exponer funciones globalmente
window.verificarKarmaPendiente = verificarKarmaPendiente;
window.procesarKarmaInstantaneo = procesarKarmaInstantaneo; // ⚡ Nueva función instantánea
window.actualizarKarmaBadge = actualizarKarmaBadge;
</script>
