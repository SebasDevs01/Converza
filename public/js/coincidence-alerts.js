/**
 * COINCIDENCE ALERTS - Sistema de Detección en Tiempo Real
 * Verifica cada 30 segundos si hay usuarios compatibles online
 */

class CoincidenceAlertsManager {
    constructor() {
        this.checkInterval = 30000; // 30 segundos
        this.counterInterval = 60000; // 1 minuto para contador
        this.isActive = true;
        this.lastCheck = 0;
        
        this.init();
    }
    
    init() {
        // Verificar inmediatamente al cargar
        this.checkCoincidences();
        
        // Luego verificar cada 30 segundos
        setInterval(() => {
            if (this.isActive && document.visibilityState === 'visible') {
                this.checkCoincidences();
            }
        }, this.checkInterval);
        
        // Actualizar contador cada minuto
        setInterval(() => {
            this.updateCounter();
        }, this.counterInterval);
        
        // Pausar cuando la pestaña no está visible
        document.addEventListener('visibilitychange', () => {
            this.isActive = document.visibilityState === 'visible';
        });
    }
    
    async checkCoincidences() {
        try {
            const formData = new FormData();
            formData.append('action', 'check');
            
            const response = await fetch('/Converza/app/presenters/check_coincidence_alerts.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.hay_coincidencias) {
                this.mostrarAlerta(data);
            }
            
            // Actualizar contador en navbar
            if (data.contador > 0) {
                this.actualizarBadge(data.contador);
            }
            
            this.lastCheck = Date.now();
            
        } catch (error) {
            console.error('Error verificando coincidencias:', error);
        }
    }
    
    async updateCounter() {
        try {
            const formData = new FormData();
            formData.append('action', 'contador');
            
            const response = await fetch('/Converza/app/presenters/check_coincidence_alerts.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.contador > 0) {
                this.actualizarBadge(data.contador);
            }
            
        } catch (error) {
            console.error('Error actualizando contador:', error);
        }
    }
    
    mostrarAlerta(data) {
        const coincidencia = data.coincidencias[0]; // La mejor coincidencia
        
        // Crear notificación visual
        const alertHTML = `
            <div class="coincidence-alert-popup" id="coincidenceAlert" style="
                position: fixed;
                top: 80px;
                right: 20px;
                width: 350px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 15px;
                padding: 20px;
                box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
                z-index: 99999;
                animation: slideInRight 0.5s ease-out;
            ">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <img src="${coincidencia.avatar || 'public/img/avatars/default.jpg'}" 
                         style="width: 60px; height: 60px; border-radius: 50%; border: 3px solid white;">
                    <div style="flex: 1;">
                        <div style="font-size: 12px; opacity: 0.9; margin-bottom: 5px;">
                            ✨ ¡Coincidencia Mística!
                        </div>
                        <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;">
                            ${coincidencia.usuario_nombre}
                        </div>
                        <div style="font-size: 14px; opacity: 0.95;">
                            ${coincidencia.compatibilidad}% compatible
                        </div>
                        <div style="font-size: 12px; opacity: 0.8; margin-top: 5px;">
                            ${coincidencia.razon}
                        </div>
                    </div>
                    <button onclick="coincidenceAlerts.cerrarAlerta()" style="
                        background: rgba(255,255,255,0.2);
                        border: none;
                        color: white;
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        cursor: pointer;
                        font-size: 18px;
                    ">×</button>
                </div>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <a href="perfil.php?usuario=${coincidencia.usuario_nombre}" 
                       style="flex: 1; background: white; color: #667eea; text-align: center; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: bold;">
                        Ver Perfil
                    </a>
                    <button onclick="coincidenceAlerts.cerrarAlerta()" style="
                        flex: 1; background: rgba(255,255,255,0.2); color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                        Después
                    </button>
                </div>
            </div>
            
            <style>
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
                
                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            </style>
        `;
        
        // Insertar alerta
        const existingAlert = document.getElementById('coincidenceAlert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        document.body.insertAdjacentHTML('beforeend', alertHTML);
        
        // Sonido de notificación (opcional)
        this.playNotificationSound();
        
        // Auto-cerrar después de 15 segundos
        setTimeout(() => {
            this.cerrarAlerta();
        }, 15000);
    }
    
    cerrarAlerta() {
        const alert = document.getElementById('coincidenceAlert');
        if (alert) {
            alert.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }
    }
    
    actualizarBadge(cantidad) {
        // Actualizar badge en navbar
        let badge = document.querySelector('.coincidence-badge');
        
        if (!badge) {
            // Crear badge si no existe
            const navItem = document.querySelector('#conexionesMisticasLink');
            if (navItem) {
                badge = document.createElement('span');
                badge.className = 'coincidence-badge';
                badge.style.cssText = `
                    position: absolute;
                    top: -5px;
                    right: -10px;
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    color: white;
                    font-size: 11px;
                    font-weight: bold;
                    padding: 3px 7px;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(245, 87, 108, 0.4);
                    animation: pulse 2s infinite;
                `;
                navItem.style.position = 'relative';
                navItem.appendChild(badge);
            }
        }
        
        if (badge) {
            badge.textContent = cantidad > 99 ? '99+' : cantidad;
            badge.style.display = cantidad > 0 ? 'block' : 'none';
        }
    }
    
    playNotificationSound() {
        try {
            const audio = new Audio('public/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {
                // Silenciar error si el navegador bloquea auto-play
            });
        } catch (error) {
            // No hacer nada si falla el sonido
        }
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        try {
            window.coincidenceAlerts = new CoincidenceAlertsManager();
        } catch (error) {
            console.error('Error inicializando Coincidence Alerts:', error);
        }
    });
} else {
    try {
        window.coincidenceAlerts = new CoincidenceAlertsManager();
    } catch (error) {
        console.error('Error inicializando Coincidence Alerts:', error);
    }
}
