<?php
/**
 * ⚠️ WIDGET DESACTIVADO - Sistema Automático Implementado
 * 
 * Este archivo ya NO se usa porque el sistema de karma ahora es 100% automático.
 * 
 * NUEVO SISTEMA:
 * - Badge contador en navbar (como notificaciones)
 * - Flechas animadas ↑ verde / ↓ roja
 * - Actualización automática sin popups
 * - Ver: karma-navbar-badge.php
 * 
 * Este archivo se mantiene para referencia histórica pero NO se debe incluir.
 */

// ❌ YA NO SE USA - Sistema automático con badge contador
return;
?>

<!-- 🌟 KARMA NOTIFICATION WIDGET -->
<div id="karmaNotificationWidget" class="karma-notification-widget" style="display: none;">
    <div class="karma-notification-content">
        <div class="karma-icon-container">
            <div class="karma-pulse-ring"></div>
            <div class="karma-icon" id="karmaNotificationIcon">
                <i class="fas fa-star"></i>
            </div>
        </div>
        
        <div class="karma-message">
            <h3 id="karmaNotificationTitle">¡Karma Ganado!</h3>
            <p id="karmaNotificationMessage">Has recibido puntos por tu buen comportamiento</p>
            <div class="karma-points">
                <span id="karmaNotificationPoints" class="karma-points-badge">+8</span>
            </div>
        </div>
        
        <button class="karma-notification-close" onclick="closeKarmaNotification()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="karma-progress-bar">
        <div class="karma-progress-fill" id="karmaProgressFill"></div>
    </div>
</div>

<style>
/* 🎨 Karma Notification Widget Styles */
.karma-notification-widget {
    position: fixed;
    top: 80px;
    right: 20px;
    width: 350px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
    z-index: 10000;
    animation: slideInRight 0.5s ease-out;
    backdrop-filter: blur(10px);
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.karma-notification-widget.negative {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
}

.karma-notification-content {
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
}

.karma-icon-container {
    position: relative;
    width: 60px;
    height: 60px;
}

.karma-pulse-ring {
    position: absolute;
    width: 60px;
    height: 60px;
    border: 3px solid rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    animation: pulsate 1.5s ease-out infinite;
}

@keyframes pulsate {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.karma-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    animation: bounce 0.6s ease-out;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.karma-message {
    flex: 1;
    color: white;
}

.karma-message h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
    font-weight: 700;
}

.karma-message p {
    margin: 0 0 10px 0;
    font-size: 13px;
    opacity: 0.9;
}

.karma-points-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.3);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 20px;
    font-weight: bold;
    color: white;
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
    }
    to {
        transform: scale(1);
    }
}

.karma-notification-close {
    position: absolute;
    top: -10px;
    right: -10px;
    width: 30px;
    height: 30px;
    background: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s;
}

.karma-notification-close:hover {
    transform: scale(1.1);
}

.karma-progress-bar {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    margin-top: 15px;
    overflow: hidden;
}

.karma-progress-fill {
    height: 100%;
    background: white;
    border-radius: 2px;
    animation: progressFill 3s linear;
}

@keyframes progressFill {
    from { width: 100%; }
    to { width: 0%; }
}

/* Responsive */
@media (max-width: 768px) {
    .karma-notification-widget {
        width: calc(100% - 40px);
        right: 20px;
        left: 20px;
    }
}
</style>

<script>
// 🌟 Mostrar notificación de karma
function showKarmaNotification(puntos, tipo, mensaje) {
    const widget = document.getElementById('karmaNotificationWidget');
    const icon = document.getElementById('karmaNotificationIcon');
    const title = document.getElementById('karmaNotificationTitle');
    const message = document.getElementById('karmaNotificationMessage');
    const pointsBadge = document.getElementById('karmaNotificationPoints');
    
    // Configurar según tipo
    if (puntos > 0) {
        widget.classList.remove('negative');
        icon.innerHTML = '<i class="fas fa-star"></i>';
        title.textContent = '¡Karma Ganado! 🎉';
        pointsBadge.textContent = '+' + puntos;
        pointsBadge.style.color = '#4ade80';
    } else {
        widget.classList.add('negative');
        icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        title.textContent = '⚠️ Karma Reducido';
        pointsBadge.textContent = puntos;
        pointsBadge.style.color = '#ff6b6b';
    }
    
    message.textContent = mensaje || (puntos > 0 
        ? 'Has recibido puntos por tu buen comportamiento e interacciones positivas' 
        : 'Tu karma ha disminuido. Mantén un comportamiento positivo');
    
    // Mostrar widget
    widget.style.display = 'block';
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        closeKarmaNotification();
    }, 5000);
    
    // Reproducir sonido (opcional)
    playKarmaSound(puntos > 0);
}

function closeKarmaNotification() {
    const widget = document.getElementById('karmaNotificationWidget');
    widget.style.animation = 'slideOutRight 0.5s ease-out';
    
    setTimeout(() => {
        widget.style.display = 'none';
        widget.style.animation = 'slideInRight 0.5s ease-out';
    }, 500);
}

function playKarmaSound(positive) {
    // Crear audio context para sonido
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    if (positive) {
        // Sonido alegre para karma positivo
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
    } else {
        // Sonido de advertencia para karma negativo
        oscillator.frequency.value = 200;
        oscillator.type = 'square';
    }
    
    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
    
    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
}

// Auto-mostrar notificación si hay datos de sesión
<?php if ($mostrar_notificacion_karma && $karma_notif_data): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const puntosGanados = <?php echo (int)$karma_notif_data['puntos']; ?>;
        
        // Mostrar notificación grande
        showKarmaNotification(
            puntosGanados,
            '<?php echo htmlspecialchars($karma_notif_data['tipo']); ?>',
            '<?php echo htmlspecialchars($karma_notif_data['mensaje']); ?>'
        );
        
        // 🎯 Mostrar popup de puntos en el botón del navbar
        if (typeof window.mostrarPuntosKarma === 'function') {
            setTimeout(() => {
                window.mostrarPuntosKarma(puntosGanados);
            }, 300);
        }
        
        // Actualizar el botón del navbar si existe
        if (typeof window.actualizarKarmaNavbar === 'function') {
            // Obtener nuevo karma via AJAX
            fetch('/Converza/app/presenters/get_karma.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Calcular nivel numérico
                        const nivelesMap = {
                            'Novato': 1,
                            'Intermedio': 2,
                            'Avanzado': 3,
                            'Experto': 4,
                            'Maestro': 5,
                            'Legendario': 6
                        };
                        const nivelNumerico = nivelesMap[data.nivel] || 1;
                        
                        // Actualizar con puntos ganados
                        window.actualizarKarmaNavbar(data.karma, nivelNumerico, puntosGanados);
                    }
                })
                .catch(err => console.error('Error actualizando karma navbar:', err));
        }
    }, 500);
});
<?php endif; ?>
</script>
