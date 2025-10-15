// ═══════════════════════════════════════════════════════════════
// 🎯 SISTEMA INTELIGENTE DE KARMA EN TIEMPO REAL (SILENCIOSO)
// ═══════════════════════════════════════════════════════════════

/**
 * Actualizar contador de karma en navbar (SILENCIOSO)
 */
function actualizarContadorKarma(karmaData) {
    console.log('🔄 Actualizando contador karma:', karmaData);
    
    if (!karmaData) return;
    
    // Buscar todos los posibles selectores del contador
    const selectores = [
        '#karma-points-display',     // Navbar badge (index.php)
        '#karmaPointsDisplay',       // Navbar button (otras páginas)
        '.karma-navbar-points',      // Clase del span de puntos
        '.karma-points',             // Clase genérica
        '[data-karma-display]',      // Atributo data
        '.karma-display',
        '#karma-counter',
        '.karma-counter',
        '[data-karma]',
        '#karmaDisplay',
        '#karma-display'
    ];
    
    let karmaDisplay = null;
    for (const selector of selectores) {
        karmaDisplay = document.querySelector(selector);
        if (karmaDisplay) {
            console.log('✅ Encontrado contador con selector:', selector);
            break;
        }
    }
    
    if (karmaDisplay) {
        const { karma, nivel_emoji, nivel_titulo } = karmaData;
        
        // Actualizar solo el número (sin emoji ni "pts")
        karmaDisplay.textContent = karma;
        
        // Actualizar emoji si existe elemento aparte
        const emojiElement = document.querySelector('.karma-emoji');
        if (emojiElement && nivel_emoji) {
            emojiElement.textContent = nivel_emoji;
        }
        
        // Actualizar nivel si existe
        const nivelElement = document.querySelector('.karma-level-title, [data-karma-nivel]');
        if (nivelElement && nivel_titulo) {
            nivelElement.textContent = nivel_titulo;
        }
        
        // Animación discreta
        karmaDisplay.style.transform = 'scale(1.3)';
        karmaDisplay.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
        karmaDisplay.style.fontWeight = '700';
        karmaDisplay.style.color = '#667eea';
        
        setTimeout(() => {
            karmaDisplay.style.transform = 'scale(1)';
            karmaDisplay.style.fontWeight = '';
            karmaDisplay.style.color = '';
        }, 400);
        
        console.log('✅ Contador actualizado:', karma, 'pts |', nivel_emoji, nivel_titulo);
    } else {
        console.warn('⚠️ No se encontró elemento de karma en navbar');
    }
}

/**
 * Procesar respuesta de karma (SOLO ACTUALIZAR CONTADOR)
 */
function procesarRespuestaKarma(response) {
    console.log('📦 Procesando respuesta karma:', response);
    
    if (!response) {
        console.warn('⚠️ Respuesta vacía');
        return;
    }
    
    // Solo actualizar contador si hay karma_actualizado
    if (response.karma_actualizado) {
        console.log('🔄 Karma actualizado detectado:', response.karma_actualizado);
        actualizarContadorKarma(response.karma_actualizado);
    }
    
    // Mostrar info en consola si hay análisis detallado
    if (response.karma_notificacion && response.karma_notificacion.mostrar) {
        const { puntos, categoria, mensaje, analisis } = response.karma_notificacion;
        const signo = puntos > 0 ? '+' : '';
        const color = puntos > 0 ? 'color: #10b981; font-weight: bold;' : 'color: #ef4444; font-weight: bold;';
        
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #667eea');
        console.log(`%c🧠 ANÁLISIS INTELIGENTE DE COMENTARIO`, 'font-size: 14px; font-weight: bold; color: #667eea');
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #667eea');
        console.log(`%c📊 Puntos: ${signo}${puntos}`, color);
        console.log(`%c🎯 Categoría: ${categoria}`, 'color: #8b5cf6; font-weight: bold;');
        console.log(`%c💬 Mensaje: ${mensaje}`, 'color: #3b82f6;');
        
        if (analisis) {
            console.log(`%c📏 Análisis Detallado:`, 'color: #10b981; font-weight: bold;');
            console.log(`   ├─ Longitud: ${analisis.longitud} caracteres`);
            console.log(`   ├─ Palabras: ${analisis.palabras}`);
            console.log(`   └─ Tono: ${analisis.tono}`);
        }
        
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #667eea');
    }
}

/**
 * Inicializar sistema inteligente
 */
(function() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKarmaSystem);
    } else {
        initKarmaSystem();
    }
})();

function initKarmaSystem() {
    console.log('🚀 Sistema de Karma Inteligente inicializado (modo silencioso)');
    
    // Interceptar TODOS los fetch para agregar procesamiento de karma
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        console.log('🌐 Fetch interceptado:', args[0]);
        
        return originalFetch.apply(this, args)
            .then(response => {
                // Clonar response para poder leerlo
                const clonedResponse = response.clone();
                
                // Si es JSON, intentar procesar karma
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    clonedResponse.json().then(data => {
                        console.log('📥 Respuesta JSON recibida:', data);
                        
                        // Verificar si tiene información de karma
                        if (data.karma_actualizado || data.karma_notificacion) {
                            console.log('✅ Karma detectado en respuesta');
                            procesarRespuestaKarma(data);
                        } else {
                            console.log('ℹ️ Sin información de karma en respuesta');
                        }
                    }).catch(() => {
                        // Si falla el parse, no hacer nada
                    });
                }
                
                return response;
            });
    };
    
    console.log('✅ Fetch interceptado para karma automático inteligente (silencioso)');
}

// Exportar funciones globalmente
window.actualizarContadorKarma = actualizarContadorKarma;
window.procesarRespuestaKarma = procesarRespuestaKarma;

console.log('✅ Sistema de karma inteligente cargado (sin notificaciones flotantes)');
