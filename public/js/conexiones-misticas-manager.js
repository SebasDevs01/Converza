/**
 * CONEXIONES MÍSTICAS - Contador y Gestión
 * Muestra contador en navbar y permite limpiar/actualizar
 */

class ConexionesMisticasManager {
    constructor() {
        this.updateInterval = 300000; // 5 minutos
        this.init();
    }
    
    init() {
        // Actualizar contador inmediatamente
        this.actualizarContador();
        
        // Actualizar cada 5 minutos
        setInterval(() => {
            this.actualizarContador();
        }, this.updateInterval);
        
        // Agregar botón de limpiar si estamos en la página de conexiones
        if (window.location.pathname.includes('conexiones_misticas')) {
            this.agregarBotonesGestion();
        }
    }
    
    async actualizarContador() {
        try {
            const formData = new FormData();
            formData.append('action', 'contador');
            
            const response = await fetch('/Converza/app/presenters/manage_conexiones.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarContador(data.contador);
            }
            
        } catch (error) {
            console.error('Error actualizando contador:', error);
        }
    }
    
    mostrarContador(contador) {
        const total = contador.total || 0;
        const nuevas = contador.nuevas || 0;
        
        // Buscar elemento del menú de conexiones místicas
        let badge = document.querySelector('.conexiones-counter-badge');
        const navLink = document.querySelector('a[href*="conexiones_misticas"]');
        
        if (!badge && navLink) {
            // Crear badge si no existe
            badge = document.createElement('span');
            badge.className = 'conexiones-counter-badge';
            badge.style.cssText = `
                position: absolute;
                top: -5px;
                right: -10px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-size: 11px;
                font-weight: bold;
                padding: 3px 7px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
            `;
            
            navLink.style.position = 'relative';
            navLink.appendChild(badge);
        }
        
        if (badge) {
            if (nuevas > 0) {
                badge.textContent = nuevas > 99 ? '99+' : nuevas;
                badge.style.display = 'block';
                badge.title = `${nuevas} conexiones nuevas de ${total} totales`;
            } else if (total > 0) {
                badge.textContent = total > 99 ? '99+' : total;
                badge.style.display = 'block';
                badge.style.background = 'linear-gradient(135deg, #a8a8a8 0%, #828282 100%)';
                badge.title = `${total} conexiones místicas`;
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Si estamos en la página de conexiones, actualizar también el header
        const headerCounter = document.querySelector('.conexiones-header-counter');
        if (headerCounter) {
            headerCounter.innerHTML = `
                <span style="font-size: 24px; font-weight: bold; color: #667eea;">${total}</span>
                <span style="font-size: 14px; color: #888;"> conexiones místicas</span>
                ${nuevas > 0 ? `<span style="font-size: 12px; color: #f5576c; margin-left: 10px;">✨ ${nuevas} nuevas</span>` : ''}
            `;
        }
    }
    
    agregarBotonesGestion() {
        // Buscar el contenedor principal
        const mainContainer = document.querySelector('.conexiones-container') || document.querySelector('main');
        
        if (!mainContainer) return;
        
        // Crear panel de gestión
        const gestionHTML = `
            <div class="conexiones-gestion-panel" style="
                background: white;
                border-radius: 15px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            ">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <h3 style="margin: 0; color: #333;">🔮 Mis Conexiones Místicas</h3>
                        <p style="margin: 5px 0 0 0; color: #888; font-size: 14px;">
                            Gestiona tus conexiones y descubre nuevas coincidencias
                        </p>
                    </div>
                    <div class="conexiones-header-counter"></div>
                </div>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button onclick="conexionesManager.actualizarConexiones()" class="btn-actualizar" style="
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 10px;
                        cursor: pointer;
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        transition: transform 0.2s;
                    ">
                        <span>🔄</span>
                        <span>Actualizar Conexiones</span>
                    </button>
                    
                    <button onclick="conexionesManager.limpiarConexiones()" class="btn-limpiar" style="
                        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 10px;
                        cursor: pointer;
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        transition: transform 0.2s;
                    ">
                        <span>🧹</span>
                        <span>Limpiar y Renovar</span>
                    </button>
                    
                    <button onclick="conexionesManager.verAyuda()" style="
                        background: #f0f0f0;
                        color: #666;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 10px;
                        cursor: pointer;
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        transition: transform 0.2s;
                    ">
                        <span>❓</span>
                        <span>Ayuda</span>
                    </button>
                </div>
                
                <div id="gestionStatus" style="
                    margin-top: 15px;
                    padding: 12px;
                    border-radius: 8px;
                    display: none;
                "></div>
            </div>
            
            <style>
                .btn-actualizar:hover, .btn-limpiar:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
                }
            </style>
        `;
        
        mainContainer.insertAdjacentHTML('afterbegin', gestionHTML);
        
        // Actualizar contador en el header
        this.actualizarContador();
    }
    
    async actualizarConexiones() {
        const btn = document.querySelector('.btn-actualizar');
        const status = document.getElementById('gestionStatus');
        
        if (btn) btn.disabled = true;
        if (btn) btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Actualizando...';
        
        try {
            const formData = new FormData();
            formData.append('action', 'actualizar');
            
            const response = await fetch('/Converza/app/presenters/manage_conexiones.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarMensaje('✅ ¡Conexiones actualizadas correctamente!', 'success');
                this.mostrarContador(data.contador);
                
                // Recargar página después de 2 segundos
                setTimeout(() => location.reload(), 2000);
            } else {
                this.mostrarMensaje('❌ Error al actualizar conexiones', 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.mostrarMensaje('❌ Error de conexión', 'error');
        } finally {
            if (btn) btn.disabled = false;
            if (btn) btn.innerHTML = '<span>🔄</span><span>Actualizar Conexiones</span>';
        }
    }
    
    async limpiarConexiones() {
        if (!confirm('¿Estás seguro de limpiar todas tus conexiones actuales? Se buscarán nuevas conexiones inmediatamente.')) {
            return;
        }
        
        const btn = document.querySelector('.btn-limpiar');
        if (btn) btn.disabled = true;
        if (btn) btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Limpiando...';
        
        try {
            const formData = new FormData();
            formData.append('action', 'limpiar_y_actualizar');
            
            const response = await fetch('/Converza/app/presenters/manage_conexiones.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarMensaje(`✅ Eliminadas ${data.eliminadas} conexiones antiguas. Encontradas ${data.contador.nuevas} nuevas conexiones!`, 'success');
                this.mostrarContador(data.contador);
                
                // Recargar página después de 2 segundos
                setTimeout(() => location.reload(), 2000);
            } else {
                this.mostrarMensaje('❌ Error al limpiar conexiones', 'error');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.mostrarMensaje('❌ Error de conexión', 'error');
        } finally {
            if (btn) btn.disabled = false;
            if (btn) btn.innerHTML = '<span>🧹</span><span>Limpiar y Renovar</span>';
        }
    }
    
    mostrarMensaje(mensaje, tipo) {
        const status = document.getElementById('gestionStatus');
        if (!status) return;
        
        const colores = {
            success: { bg: '#d4edda', text: '#155724', border: '#c3e6cb' },
            error: { bg: '#f8d7da', text: '#721c24', border: '#f5c6cb' },
            info: { bg: '#d1ecf1', text: '#0c5460', border: '#bee5eb' }
        };
        
        const color = colores[tipo] || colores.info;
        
        status.style.display = 'block';
        status.style.backgroundColor = color.bg;
        status.style.color = color.text;
        status.style.border = `1px solid ${color.border}`;
        status.textContent = mensaje;
        
        setTimeout(() => {
            status.style.display = 'none';
        }, 5000);
    }
    
    verAyuda() {
        alert(`🔮 CONEXIONES MÍSTICAS - AYUDA

📊 ¿Qué son las Conexiones Místicas?
Son usuarios con los que tienes coincidencias especiales basadas en:
• Gustos compartidos (reacciones a las mismas publicaciones)
• Intereses comunes (comentan en los mismos temas)
• Amigos en común
• Horarios de actividad similares

🔄 Actualizar Conexiones:
Busca nuevas conexiones basadas en tu actividad reciente.

🧹 Limpiar y Renovar:
Elimina tus conexiones actuales y busca completamente nuevas.

⏰ Actualización Automática:
El sistema se actualiza automáticamente cada 6 horas.

✨ Coincidence Alerts:
Recibirás notificaciones cuando usuarios muy compatibles estén online.`);
    }
}

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.conexionesManager = new ConexionesMisticasManager();
    });
} else {
    window.conexionesManager = new ConexionesMisticasManager();
}
