// 🔥 SOLUCIÓN RÁPIDA - Pegar en Consola (F12)

console.clear();
console.log('%c🚀 ACTIVANDO TOOLTIPS MANUALMENTE', 'background: #FF5722; color: white; font-size: 16px; padding: 10px;');

// Forzar tooltips CSS con JavaScript
document.querySelectorAll('.reaction-counter, .comment-counter').forEach(counter => {
    // Asegurar estilos base
    counter.style.position = 'relative';
    counter.style.display = 'inline-block';
    
    // Crear tooltip dinámico al hacer hover
    counter.addEventListener('mouseenter', function(e) {
        const tooltipText = this.getAttribute('data-tooltip');
        
        // Ignorar si no hay tooltip o es placeholder
        if (!tooltipText || tooltipText === 'Sin reacciones' || tooltipText === 'Sin comentarios') {
            return;
        }
        
        // Eliminar tooltip anterior si existe
        const oldTooltip = this.querySelector('.js-tooltip');
        if (oldTooltip) oldTooltip.remove();
        
        // Crear nuevo tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'js-tooltip';
        tooltip.textContent = tooltipText;
        tooltip.style.cssText = `
            position: absolute;
            top: 50%;
            left: calc(100% + 8px);
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: pre;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            pointer-events: none;
            max-width: 200px;
            line-height: 1.3;
        `;
        
        // Agregar flecha
        const arrow = document.createElement('div');
        arrow.style.cssText = `
            position: absolute;
            top: 50%;
            left: -6px;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-right: 6px solid #333;
        `;
        tooltip.appendChild(arrow);
        
        this.appendChild(tooltip);
        
        console.log('✅ Tooltip mostrado:', this.id, '→', tooltipText);
    });
    
    counter.addEventListener('mouseleave', function(e) {
        const tooltip = this.querySelector('.js-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    });
});

console.log('✅ Tooltips activados para', document.querySelectorAll('.reaction-counter, .comment-counter').length, 'contadores');
console.log('Ahora haz hover sobre los contadores (1), (2), etc.');

// Verificar que todos tengan data-tooltip
let sinTooltip = 0;
document.querySelectorAll('.reaction-counter, .comment-counter').forEach(counter => {
    const tooltip = counter.getAttribute('data-tooltip');
    if (!tooltip || tooltip === 'Sin reacciones' || tooltip === 'Sin comentarios') {
        sinTooltip++;
        console.warn('⚠️ Sin tooltip:', counter.id);
    }
});

if (sinTooltip > 0) {
    console.warn(`⚠️ ${sinTooltip} contadores sin datos. Recargando...`);
    
    // Recargar datos si es necesario
    if (typeof loadReactionsData === 'function') {
        document.querySelectorAll('[id^="reaction_counter_"]').forEach((counter, index) => {
            const postId = counter.id.replace('reaction_counter_', '');
            setTimeout(() => {
                console.log(`🔄 Recargando post ${postId}...`);
                loadReactionsData(postId);
            }, index * 300);
        });
    } else {
        console.error('❌ loadReactionsData no está definida. Recarga la página.');
    }
}
