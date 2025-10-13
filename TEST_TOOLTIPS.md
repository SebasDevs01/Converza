# 🚀 Test Rápido - Tooltips

## Paso 1: Abrir Consola (F12)

## Paso 2: Copiar y Pegar

```javascript
// ===================================
// TEST COMPLETO DE TOOLTIPS
// ===================================

console.clear();
console.log('%c🔍 INICIANDO TEST DE TOOLTIPS', 'background: #222; color: #bada55; font-size: 16px; padding: 10px;');

// 1. Verificar que los contadores existen
const reactionCounters = document.querySelectorAll('.reaction-counter');
const commentCounters = document.querySelectorAll('.comment-counter');

console.log(`\n📊 CONTADORES ENCONTRADOS:`);
console.log(`  • Reacciones: ${reactionCounters.length}`);
console.log(`  • Comentarios: ${commentCounters.length}`);

if (reactionCounters.length === 0) {
    console.error('❌ NO se encontraron contadores de reacciones!');
} else {
    console.log('✅ Contadores de reacciones encontrados');
}

// 2. Verificar atributos data-tooltip
console.log(`\n🏷️ ATRIBUTOS DATA-TOOLTIP:`);
reactionCounters.forEach((counter, index) => {
    const tooltip = counter.getAttribute('data-tooltip');
    const postId = counter.id.replace('reaction_counter_', '');
    console.log(`  [${index + 1}] Post ${postId}:`, tooltip || '❌ VACÍO');
});

commentCounters.forEach((counter, index) => {
    const tooltip = counter.getAttribute('data-tooltip');
    const postId = counter.id.replace('comment_counter_', '');
    console.log(`  [${index + 1}] Post ${postId}:`, tooltip || '❌ VACÍO');
});

// 3. Verificar CSS
console.log(`\n🎨 VERIFICANDO CSS:`);
const testCounter = reactionCounters[0];
if (testCounter) {
    const computedStyle = window.getComputedStyle(testCounter);
    const afterStyle = window.getComputedStyle(testCounter, '::after');
    
    console.log(`  • position: ${computedStyle.position}`);
    console.log(`  • display: ${computedStyle.display}`);
    console.log(`  • ::after content: ${afterStyle.content}`);
    console.log(`  • ::after z-index: ${afterStyle.zIndex}`);
    
    if (afterStyle.content === 'none' || afterStyle.content === '') {
        console.error('❌ CSS del tooltip NO está cargado!');
    } else {
        console.log('✅ CSS del tooltip está cargado');
    }
}

// 4. Test de hover
console.log(`\n🖱️ AGREGANDO EVENT LISTENERS DE TEST:`);
let hoverDetected = false;
reactionCounters.forEach((counter, index) => {
    counter.addEventListener('mouseenter', function() {
        if (!hoverDetected) {
            hoverDetected = true;
            console.log(`%c✨ HOVER DETECTADO en contador ${index + 1}`, 'background: #4CAF50; color: white; padding: 5px;');
            console.log(`  • ID: ${this.id}`);
            console.log(`  • Tooltip: ${this.getAttribute('data-tooltip')}`);
        }
    });
});

console.log('✅ Event listeners agregados. Haz hover sobre un contador...');

// 5. Verificar funciones globales
console.log(`\n🔧 VERIFICANDO FUNCIONES:`);
console.log(`  • loadReactionsData: ${typeof loadReactionsData === 'function' ? '✅' : '❌ NO EXISTE'}`);
console.log(`  • updateReactionsSummary: ${typeof updateReactionsSummary === 'function' ? '✅' : '❌ NO EXISTE'}`);
console.log(`  • updateCommentsSummary: ${typeof updateCommentsSummary === 'function' ? '✅' : '❌ NO EXISTE'}`);

// 6. Test manual de API
console.log(`\n🌐 TESTEANDO APIS:`);
if (reactionCounters.length > 0) {
    const postId = reactionCounters[0].id.replace('reaction_counter_', '');
    
    console.log(`Probando APIs para post ${postId}...`);
    
    fetch(`/Converza/app/presenters/get_reactions.php?postId=${postId}`)
        .then(r => r.json())
        .then(data => {
            console.log('%c📊 API de reacciones:', 'color: #2196F3; font-weight: bold');
            console.log(data);
            
            if (data.success && data.reactions && data.reactions.length > 0) {
                console.log('✅ API retorna datos correctamente');
                console.log(`  • Total reacciones: ${data.reactions.length}`);
                data.reactions.forEach(r => {
                    console.log(`    - ${r.tipo_reaccion}: ${r.total} (${r.usuarios})`);
                });
            } else {
                console.log('⚠️ Sin reacciones en este post');
            }
        })
        .catch(e => console.error('❌ Error en API de reacciones:', e));
    
    fetch(`/Converza/app/presenters/get_comentarios.php?postId=${postId}`)
        .then(r => r.json())
        .then(data => {
            console.log('%c💬 API de comentarios:', 'color: #FF9800; font-weight: bold');
            console.log(data);
            
            if (data.success && data.total > 0) {
                console.log('✅ API retorna datos correctamente');
                console.log(`  • Total comentarios: ${data.total}`);
                data.comentarios.forEach(c => {
                    console.log(`    - ${c.usuario}: ${c.comentario.substring(0, 30)}...`);
                });
            } else {
                console.log('⚠️ Sin comentarios en este post');
            }
        })
        .catch(e => console.error('❌ Error en API de comentarios:', e));
}

// 7. Forzar recarga de datos
console.log(`\n🔄 FORZANDO RECARGA DE DATOS:`);
if (typeof loadReactionsData === 'function') {
    reactionCounters.forEach((counter, index) => {
        const postId = counter.id.replace('reaction_counter_', '');
        console.log(`  [${index + 1}] Recargando post ${postId}...`);
        setTimeout(() => {
            loadReactionsData(postId);
        }, index * 500); // Delay progresivo para no saturar
    });
} else {
    console.error('❌ loadReactionsData NO está definida!');
}

console.log(`\n%c✅ TEST COMPLETADO`, 'background: #4CAF50; color: white; font-size: 14px; padding: 8px;');
console.log('Ahora haz hover sobre los contadores (1), (2), etc.');
console.log('Si no aparecen tooltips, copia el siguiente código:\n');

// 8. Solución de emergencia
const emergencyFix = `
// ⚡ SOLUCIÓN DE EMERGENCIA
document.querySelectorAll('.reaction-counter, .comment-counter').forEach(counter => {
    counter.addEventListener('mouseenter', function(e) {
        const tooltipText = this.getAttribute('data-tooltip');
        if (!tooltipText || tooltipText === 'Sin reacciones' || tooltipText === 'Sin comentarios') {
            return;
        }
        
        // Crear tooltip manual
        const tooltip = document.createElement('div');
        tooltip.className = 'emergency-tooltip';
        tooltip.textContent = tooltipText;
        tooltip.style.cssText = \`
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
        \`;
        this.style.position = 'relative';
        this.appendChild(tooltip);
    });
    
    counter.addEventListener('mouseleave', function(e) {
        const tooltip = this.querySelector('.emergency-tooltip');
        if (tooltip) tooltip.remove();
    });
});
console.log('✅ Tooltips de emergencia activados');
`;

console.log(emergencyFix);
```

## Paso 3: Hacer Hover

Después de ejecutar el script, haz hover sobre los contadores `(1)` o `(2)` en cualquier publicación.

## Resultado Esperado

Deberías ver en consola:
```
🔍 INICIANDO TEST DE TOOLTIPS

📊 CONTADORES ENCONTRADOS:
  • Reacciones: 3
  • Comentarios: 3
✅ Contadores de reacciones encontrados

🏷️ ATRIBUTOS DATA-TOOLTIP:
  [1] Post 123: ❤️ vane15
  [2] Post 456: Sin reacciones
  [3] Post 789: 👍 meliodas
💬 santi12

🎨 VERIFICANDO CSS:
  • position: relative
  • display: inline-block
  • ::after content: attr(data-tooltip)
  • ::after z-index: 9999
✅ CSS del tooltip está cargado

🖱️ AGREGANDO EVENT LISTENERS DE TEST:
✅ Event listeners agregados. Haz hover sobre un contador...

(Al hacer hover)
✨ HOVER DETECTADO en contador 1
  • ID: reaction_counter_123
  • Tooltip: ❤️ vane15
```

## Si NO funciona

Si después del test los tooltips siguen sin aparecer, ejecuta la "SOLUCIÓN DE EMERGENCIA" que aparece al final del test.

---

**Fecha**: 2025-10-13  
**Propósito**: Diagnosticar problemas de tooltips
