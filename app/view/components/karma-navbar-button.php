<?php
/**
 * Botón de Karma en Navbar
 * Muestra karma actual con animación y enlace a perfil
 */

require_once __DIR__ . '/../../models/config.php';
require_once __DIR__ . '/../../models/karma-social-helper.php';

// Obtener karma del usuario actual
$usuario_actual = $_SESSION['id'] ?? 0;
$karma_total = 0;
$nivel_info = ['emoji' => '🌱', 'nivel' => 'Novato', 'color' => '#4ade80'];

if ($usuario_actual > 0) {
    try {
        $karmaHelper = new KarmaSocialHelper($conexion);
        $karma_data = $karmaHelper->obtenerKarmaTotal($usuario_actual);
        $karma_total = $karma_data['karma_total'] ?? 0;
        $nivel_info = $karmaHelper->obtenerNivelKarma($karma_total);
    } catch (Exception $e) {
        error_log("Error al obtener karma para navbar: " . $e->getMessage());
    }
}
?>

<!-- 🌟 KARMA BUTTON IN NAVBAR -->
<div class="karma-navbar-button" id="karmaNavbarButton" data-karma="<?php echo $karma_total; ?>">
    <div class="karma-icon-wrapper">
        <span class="karma-emoji"><?php echo $nivel_info['emoji']; ?></span>
        <div class="karma-pulse"></div>
    </div>
    
    <div class="karma-info">
        <span class="karma-label">Karma</span>
        <span class="karma-points" id="karmaPointsDisplay"><?php echo $karma_total; ?></span>
    </div>
    
    <div class="karma-level-indicator" style="background: <?php echo $nivel_info['color']; ?>"></div>
</div>

<style>
/* 🎨 Karma Navbar Button Styles */
.karma-navbar-button {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 15px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    margin: 0 10px;
}

.karma-navbar-button:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
    border-color: rgba(102, 126, 234, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.karma-icon-wrapper {
    position: relative;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.karma-emoji {
    font-size: 24px;
    position: relative;
    z-index: 2;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.karma-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.4) 0%, transparent 70%);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.5;
    }
    50% {
        transform: scale(1.3);
        opacity: 0;
    }
}

.karma-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.karma-label {
    font-size: 11px;
    color: #888;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.karma-points {
    font-size: 18px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.karma-level-indicator {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 0 0 30px 30px;
    animation: shimmer 2s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

/* Animación cuando cambia el karma */
.karma-points.animate {
    animation: pointsChange 0.6s ease-out;
}

@keyframes pointsChange {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); color: #4ade80; }
    100% { transform: scale(1); }
}

/* Responsive */
@media (max-width: 768px) {
    .karma-navbar-button {
        padding: 6px 10px;
        gap: 8px;
    }
    
    .karma-emoji {
        font-size: 20px;
    }
    
    .karma-label {
        font-size: 10px;
    }
    
    .karma-points {
        font-size: 16px;
    }
}
</style>

<script>
// 🌟 Actualizar karma en tiempo real
function updateKarmaDisplay(newKarma) {
    const karmaDisplay = document.getElementById('karmaPointsDisplay');
    const oldKarma = parseInt(karmaDisplay.textContent);
    
    if (newKarma !== oldKarma) {
        // Animar cambio
        karmaDisplay.classList.add('animate');
        
        // Contar desde el valor anterior al nuevo
        animateKarmaCounter(oldKarma, newKarma, 500);
        
        setTimeout(() => {
            karmaDisplay.classList.remove('animate');
        }, 600);
    }
}

function animateKarmaCounter(start, end, duration) {
    const karmaDisplay = document.getElementById('karmaPointsDisplay');
    const range = end - start;
    const increment = range / (duration / 16); // 60 FPS
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        karmaDisplay.textContent = Math.round(current);
    }, 16);
}

// Click en botón de karma
document.getElementById('karmaNavbarButton')?.addEventListener('click', function() {
    // Redirigir a perfil o abrir modal de karma
    window.location.href = '/Converza/app/view/perfil.php';
});
</script>
