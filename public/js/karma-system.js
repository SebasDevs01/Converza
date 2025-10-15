// ═══════════════════════════════════════════════════════════════
// 🎯 SISTEMA INTELIGENTE DE KARMA EN TIEMPO REAL (SILENCIOSO)
// ═══════════════════════════════════════════════════════════════

/**
 * Actualizar contador de karma en navbar (SILENCIOSO)
 */
function actualizarContadorKarma(karmaData) {
    console.log('🔄 Actualizando contador karma:', karmaData);
    
    if (!karmaData) return;
    
    // 🎯 CAMBIO CRÍTICO: Parsear el valor de karma como número
    const karmaActual = typeof karmaData.karma === 'string' ? parseInt(karmaData.karma) : karmaData.karma || 0;
    const nivel = karmaData.nivel || 1;
    const nivelTitulo = karmaData.nivel_titulo || 'Novato';
    const nivelEmoji = karmaData.nivel_emoji || '🌱';
    
    console.log(`✅ Karma parseado correctamente: ${karmaActual}`);
    console.log(`📊 Valores parseados:`, {
        karmaActual,
        nivel,
        nivelTitulo,
        nivelEmoji
    });
    
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
        // 🎯 ACTUALIZAR EL CONTENIDO CON EL VALOR REAL
        karmaDisplay.textContent = karmaActual.toLocaleString();
        
        // También actualizar atributos data si existen
        if (karmaDisplay.dataset) {
            karmaDisplay.dataset.karma = karmaActual;
            karmaDisplay.dataset.nivel = nivel;
            karmaDisplay.dataset.nivelTitulo = nivelTitulo;
        }
        
        // Actualizar emoji si existe elemento aparte
        const emojiElement = document.querySelector('.karma-emoji');
        if (emojiElement && nivelEmoji) {
            emojiElement.textContent = nivelEmoji;
        }
        
        // Actualizar nivel si existe
        const nivelElement = document.querySelector('.karma-level-title, [data-karma-nivel]');
        if (nivelElement && nivelTitulo) {
            nivelElement.textContent = nivelTitulo;
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
        
        console.log(`✅ Contador actualizado: ${karmaActual.toLocaleString()} pts | ${nivelEmoji} ${nivelTitulo}`);
        
        // 🎯 NUEVO: Disparar evento personalizado para otros componentes
        window.dispatchEvent(new CustomEvent('karmaUpdated', {
            detail: {
                karma: karmaActual,
                nivel: nivel,
                nivelTitulo: nivelTitulo,
                nivelEmoji: nivelEmoji
            }
        }));
    } else {
        console.warn('⚠️ No se encontró elemento de karma en navbar');
    }
}

/**
 * Procesar respuesta de karma (ACTUALIZAR CONTADOR SOLAMENTE)
 */
function procesarRespuestaKarma(response) {
    console.log('📦 Procesando respuesta karma:', response);
    
    if (!response) {
        console.warn('⚠️ Respuesta vacía');
        return;
    }
    
    // Actualizar contador si hay karma_actualizado
    if (response.karma_actualizado) {
        console.log('🔄 Karma actualizado detectado:', response.karma_actualizado);
        actualizarContadorKarma(response.karma_actualizado);
    }
    
    // Mostrar info en consola si hay análisis detallado (SIN notificación flotante)
    if (response.karma_notificacion && response.karma_notificacion.mostrar) {
        const { puntos, categoria, mensaje, analisis, tipo } = response.karma_notificacion;
        const signo = puntos > 0 ? '+' : '';
        const color = puntos > 0 ? 'color: #10b981; font-weight: bold;' : 'color: #ef4444; font-weight: bold;';
        
        // Log en consola
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #667eea');
        console.log(`%c🎯 KARMA ${puntos > 0 ? 'GANADO' : 'PERDIDO'}`, 'font-size: 14px; font-weight: bold; color: #667eea');
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #667eea');
        console.log(`%c📊 Puntos: ${signo}${puntos}`, color);
        console.log(`%c🎯 Categoría: ${categoria}`, 'color: #8b5cf6; font-weight: bold;');
        console.log(`%c💬 Mensaje: ${mensaje}`, 'color: #3b82f6;');
        console.log('%c🔔 Notificación enviada al sistema de campanita', 'color: #f59e0b; font-weight: bold;');
        
        if (analisis) {
            console.log(`%c📏 Análisis Detallado:`, 'color: #10b981; font-weight: bold;');
            console.log(`   ├─ Longitud: ${analisis.longitud} caracteres`);
            console.log(`   ├─ Palabras: ${analisis.palabras}`);
            console.log(`   └─ Tono: ${analisis.tono}`);
        }
        
        console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #667eea');
        
        // 🔔 Ya NO mostrar notificación flotante
        // La notificación se envía al sistema de campanita en el backend
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

/**
 * 🎯 Función para forzar actualización desde el servidor
 */
async function sincronizarKarmaDesdeServidor() {
    try {
        const response = await fetch('/Converza/app/presenters/get_karma.php');
        const data = await response.json();
        
        if (data.success && data.karma_actualizado) {
            actualizarContadorKarma(data.karma_actualizado);
        }
    } catch (error) {
        console.error('❌ Error al sincronizar karma:', error);
    }
}

// Exportar funciones globalmente
window.actualizarContadorKarma = actualizarContadorKarma;
window.procesarRespuestaKarma = procesarRespuestaKarma;
window.sincronizarKarmaDesdeServidor = sincronizarKarmaDesdeServidor;

// 🎯 Sincronizar karma al cargar la página
document.addEventListener('DOMContentLoaded', sincronizarKarmaDesdeServidor);

console.log('✅ Sistema de karma inteligente cargado (sin notificaciones flotantes)');
