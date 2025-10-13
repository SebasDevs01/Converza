// ========================================
// SCRIPT DE EMERGENCIA: FORZAR TOOLTIPS
// ========================================
// Pega este código en la consola (F12) para activar tooltips inmediatamente

console.log('🔧 ACTIVANDO TOOLTIPS DE EMERGENCIA...');

// Función para crear tooltip dinámico
function createTooltip(element, text) {
    // Crear elemento del tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'dynamic-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        white-space: pre-line;
        z-index: 99999;
        pointer-events: none;
        max-width: 250px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        line-height: 1.4;
    `;
    
    // Posicionar tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - 10) + 'px';
    tooltip.style.transform = 'translateY(-100%)';
    
    document.body.appendChild(tooltip);
    return tooltip;
}

// Activar tooltips en contadores
document.querySelectorAll('.reaction-counter, .comment-counter').forEach(counter => {
    let tooltip = null;
    
    counter.addEventListener('mouseenter', function() {
        const tooltipText = this.getAttribute('data-tooltip');
        
        if (!tooltipText || tooltipText === 'Sin reacciones' || tooltipText === 'Sin comentarios') {
            return; // No mostrar tooltip vacío
        }
        
        console.log('👆 Hover en contador:', this.id, 'Texto:', tooltipText);
        tooltip = createTooltip(this, tooltipText);
    });
    
    counter.addEventListener('mouseleave', function() {
        if (tooltip) {
            tooltip.remove();
            tooltip = null;
        }
    });
    
    counter.addEventListener('mousemove', function(e) {
        if (tooltip) {
            tooltip.style.left = e.pageX + 'px';
            tooltip.style.top = (e.pageY - 10) + 'px';
            tooltip.style.transform = 'translateY(-100%)';
        }
    });
});

console.log('✅ TOOLTIPS ACTIVADOS!');
console.log('📊 Contadores encontrados:', document.querySelectorAll('.reaction-counter, .comment-counter').length);
console.log('🎯 Haz hover sobre los contadores (5), (2), etc.');
