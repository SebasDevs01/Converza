<?php
/**
 * KARMA NAVBAR BUTTON - BADGE INTERACTIVO
 * Botón compacto para navbar que muestra karma actual y nivel
 * Incluir después del logo y antes de otros botones
 */

require_once(__DIR__.'/../../models/config.php');
require_once(__DIR__.'/../../models/karma-social-helper.php');

// Obtener karma del usuario actual
$karmaHelper = new KarmaSocialHelper($conexion);
$karmaData = $karmaHelper->obtenerKarmaUsuario($_SESSION['id']);
$karma = $karmaData['karma_total'];
$nivel_nombre = $karmaData['nivel'];
$emoji = $karmaData['nivel_emoji'];

// Determinar número de nivel (1-6) para el badge
$niveles_numericos = [
    'Novato' => 1,
    'Intermedio' => 2,
    'Avanzado' => 3,
    'Experto' => 4,
    'Maestro' => 5,
    'Legendario' => 6
];
$nivel = $niveles_numericos[$nivel_nombre] ?? 1;
?>

<style>
.karma-navbar-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
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
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.4), rgba(118, 75, 162, 0.4));
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white;
    text-decoration: none;
}

.karma-navbar-emoji {
    font-size: 1.2rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.karma-navbar-points {
    font-weight: 700;
    background: linear-gradient(135deg, #ffffff, #e0e7ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.karma-navbar-level {
    font-size: 0.7rem;
    opacity: 0.9;
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 6px;
    border-radius: 10px;
}

/* Animación de pulso suave */
@keyframes karma-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.karma-navbar-btn.has-new {
    animation: karma-pulse 2s infinite;
}

/* 🎯 ANIMACIÓN DE PUNTOS FLOTANTES */
.karma-points-popup {
    position: absolute;
    top: -30px;
    right: 0;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    animation: float-up-karma 2s ease-out forwards;
    pointer-events: none;
    z-index: 10000;
    white-space: nowrap;
}

.karma-points-popup.negative {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

@keyframes float-up-karma {
    0% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    50% {
        opacity: 1;
        transform: translateY(-15px) scale(1.2);
    }
    100% {
        opacity: 0;
        transform: translateY(-40px) scale(0.8);
    }
}

/* Efecto de brillo cuando gana karma */
.karma-navbar-btn.karma-gained {
    animation: karma-glow 1s ease-out;
}

@keyframes karma-glow {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    50% {
        box-shadow: 0 0 20px 10px rgba(16, 185, 129, 0.4);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

/* Responsive */
@media (max-width: 991px) {
    .karma-navbar-btn {
        padding: 8px 14px;
        margin: 5px 0;
    }
    
    .karma-points-popup {
        top: -25px;
        font-size: 0.75rem;
    }
}
</style>

<a href="../presenters/karma_tienda.php" class="karma-navbar-btn" id="karmaNavbarBtn" title="Ver mi Karma y Recompensas">
    <span class="karma-navbar-emoji" id="karmaNavbarEmoji"><?php echo $emoji; ?></span>
    <span class="karma-navbar-points" id="karmaNavbarPoints"><?php echo $karma; ?></span>
    <span class="karma-navbar-level">Nv. <?php echo $nivel; ?></span>
</a>

<script>
// 🎯 Función para mostrar popup de puntos ganados/perdidos
function mostrarPuntosKarma(puntos) {
    const btn = document.getElementById('karmaNavbarBtn');
    if (!btn) return;
    
    // Crear popup
    const popup = document.createElement('div');
    popup.className = 'karma-points-popup' + (puntos < 0 ? ' negative' : '');
    popup.textContent = (puntos > 0 ? '+' : '') + puntos;
    
    // Agregar al botón
    btn.style.position = 'relative';
    btn.appendChild(popup);
    
    // Agregar efecto de brillo
    btn.classList.add('karma-gained');
    
    // Eliminar después de la animación
    setTimeout(() => {
        popup.remove();
        btn.classList.remove('karma-gained');
    }, 2000);
}

// Función para actualizar el botón de karma en tiempo real
function actualizarKarmaNavbar(nuevoKarma, nuevoNivel, puntosGanados = null) {
    const btn = document.getElementById('karmaNavbarBtn');
    const points = document.getElementById('karmaNavbarPoints');
    const emoji = document.getElementById('karmaNavbarEmoji');
    
    if (points) {
        // Si hay puntos ganados, mostrar popup
        if (puntosGanados !== null && puntosGanados !== 0) {
            mostrarPuntosKarma(puntosGanados);
        }
        
        // Animar el cambio
        btn.classList.add('has-new');
        
        // Actualizar puntos con animación
        const karmaActual = parseInt(points.textContent);
        animarContador(karmaActual, nuevoKarma, 1000, (val) => {
            points.textContent = val;
        });
        
        // Actualizar emoji si cambió de nivel
        const nivelesEmoji = {
            1: '🌱',
            2: '⭐',
            3: '✨',
            4: '💫',
            5: '🌟',
            6: '👑'
        };
        if (nuevoNivel && emoji) {
            const emojiNuevo = nivelesEmoji[nuevoNivel] || '🌱';
            if (emoji.textContent !== emojiNuevo) {
                emoji.textContent = emojiNuevo;
                // Animación especial al subir de nivel
                emoji.style.animation = 'karma-pulse 0.5s ease-in-out 3';
            }
        }
        
        // Quitar animación después de 3 segundos
        setTimeout(() => {
            btn.classList.remove('has-new');
        }, 3000);
    }
}

// Función auxiliar para animar contador
function animarContador(inicio, fin, duracion, callback) {
    const diferencia = fin - inicio;
    const incremento = diferencia / (duracion / 16);
    let actual = inicio;
    
    const timer = setInterval(() => {
        actual += incremento;
        if ((incremento > 0 && actual >= fin) || (incremento < 0 && actual <= fin)) {
            actual = fin;
            clearInterval(timer);
        }
        callback(Math.round(actual));
    }, 16);
}

// 🎯 NUEVO: Verificar si hay notificación de karma pendiente (después de comentar, etc)
function verificarKarmaPendiente() {
    console.log('🔍 Verificando karma pendiente...');
    fetch('/converza/app/presenters/check_karma_notification.php')
        .then(response => response.json())
        .then(data => {
            console.log('📨 Respuesta de check_karma_notification:', data);
            if (data.success && data.data) {
                const { puntos, tipo, mensaje } = data.data;
                console.log('🎉 ¡Karma detectado!', { puntos, tipo, mensaje });
                
                // Mostrar popup inmediato
                mostrarPuntosKarma(puntos);
                
                // Recargar karma actual
                fetch('/converza/app/presenters/get_karma.php')
                    .then(res => res.json())
                    .then(karmaData => {
                        if (karmaData.success) {
                            actualizarKarmaNavbar(
                                karmaData.karma,
                                karmaData.nivel,
                                puntos
                            );
                        }
                    });
                
                // Actualizar el badge de notificaciones (campana 🔔)
                if (typeof cargarNotificaciones === 'function') {
                    cargarNotificaciones();
                }
            } else {
                console.log('ℹ️ No hay karma pendiente');
            }
        })
        .catch(error => console.error('❌ Error al verificar karma:', error));
}

// Verificar karma pendiente al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    verificarKarmaPendiente();
});

// Exponer función global para llamarla después de comentar
window.verificarKarmaPendiente = verificarKarmaPendiente;

// Hacer funciones globales
window.actualizarKarmaNavbar = actualizarKarmaNavbar;
window.mostrarPuntosKarma = mostrarPuntosKarma;
</script>
