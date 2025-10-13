<!-- Sistema de Notificaciones en Tiempo Real -->
<style>
/* Campana de notificaciones */
.notificaciones-container {
    position: relative;
    display: inline-block;
}

.notificaciones-btn {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
}

.notificaciones-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.notificaciones-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    padding: 0 5px;
    animation: pulse-badge 2s infinite;
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
}

@keyframes pulse-badge {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
        transform: scale(1.05);
    }
}

.notificaciones-panel {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 10px;
    width: 400px;
    max-height: 600px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    display: none;
    z-index: 9999;
    overflow: hidden;
}

.notificaciones-panel.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.notificaciones-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.notificaciones-header h6 {
    margin: 0;
    font-weight: 600;
    color: #333;
}

.notificaciones-actions {
    display: flex;
    gap: 10px;
}

.notificaciones-lista {
    max-height: 450px;
    overflow-y: auto;
}

.notificacion-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    gap: 12px;
    align-items: start;
}

.notificacion-item:hover {
    background: #f8f9fa;
}

.notificacion-item.no-leida {
    background: #e7f3ff;
}

.notificacion-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
}

.notificacion-contenido {
    flex: 1;
}

.notificacion-mensaje {
    font-size: 14px;
    color: #333;
    margin-bottom: 4px;
    line-height: 1.4;
}

.notificacion-tiempo {
    font-size: 12px;
    color: #6c757d;
}

.notificacion-icono {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.notificacion-icono.solicitud { background: #e3f2fd; color: #1976d2; }
.notificacion-icono.aceptada { background: #e8f5e9; color: #388e3c; }
.notificacion-icono.rechazada { background: #ffebee; color: #d32f2f; }
.notificacion-icono.seguidor { background: #f3e5f5; color: #7b1fa2; }
.notificacion-icono.mensaje { background: #fff3e0; color: #f57c00; }
.notificacion-icono.comentario { background: #e0f2f1; color: #00796b; }
.notificacion-icono.publicacion { background: #fce4ec; color: #c2185b; }

.notificaciones-vacio {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.notificaciones-vacio i {
    font-size: 48px;
    color: #dee2e6;
    margin-bottom: 10px;
}

.btn-limpiar-notis {
    background: none;
    border: 1px solid #dc3545;
    color: #dc3545;
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-limpiar-notis:hover {
    background: #dc3545;
    color: white;
}

.btn-marcar-leidas {
    background: none;
    border: 1px solid #007bff;
    color: #007bff;
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-marcar-leidas:hover {
    background: #007bff;
    color: white;
}
</style>

<div class="notificaciones-container">
    <button class="nav-link notificaciones-btn" id="btnNotificaciones" title="Notificaciones">
        <i class="bi bi-bell-fill"></i>
        <span class="notificaciones-badge" id="notificacionesBadge" style="display: none;">0</span>
    </button>
    
        <div class="notificaciones-panel" id="notificacionesPanel">
            <div class="notificaciones-header">
                <h6><i class="bi bi-bell"></i> Notificaciones</h6>
                <div class="notificaciones-actions">
                    <button class="btn-marcar-leidas" id="btnMarcarTodasLeidas" title="Marcar todas como leídas">
                        <i class="bi bi-check-all"></i>
                    </button>
                    <button class="btn-limpiar-notis" id="btnLimpiarNotis" title="Limpiar todas">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="notificaciones-lista" id="notificacionesLista">
                <div class="notificaciones-vacio">
                    <i class="bi bi-bell-slash"></i>
                    <p>No tienes notificaciones</p>
                </div>
            </div>
        </div>
</div>

<script>
// Sistema de Notificaciones en Tiempo Real
const NotificacionesSystem = {
    panel: null,
    badge: null,
    lista: null,
    botonCampana: null,
    intervalo: null,
    
    init() {
        this.panel = document.getElementById('notificacionesPanel');
        this.badge = document.getElementById('notificacionesBadge');
        this.lista = document.getElementById('notificacionesLista');
        this.botonCampana = document.getElementById('btnNotificaciones');
        
        // Event listeners
        this.botonCampana.addEventListener('click', (e) => {
            e.stopPropagation();
            this.togglePanel();
        });
        
        // Cerrar panel al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!this.panel.contains(e.target) && e.target !== this.botonCampana) {
                this.cerrarPanel();
            }
        });
        
        // Marcar todas como leídas
        document.getElementById('btnMarcarTodasLeidas').addEventListener('click', () => {
            this.marcarTodasLeidas();
        });
        
        // Limpiar todas
        document.getElementById('btnLimpiarNotis').addEventListener('click', () => {
            if (confirm('¿Estás seguro de que quieres eliminar todas las notificaciones?')) {
                this.limpiarTodas();
            }
        });
        
        // Cargar notificaciones iniciales
        this.cargarNotificaciones();
        
        // Actualizar cada 10 segundos
        this.intervalo = setInterval(() => {
            this.cargarNotificaciones();
        }, 10000);
    },
    
    togglePanel() {
        this.panel.classList.toggle('show');
        if (this.panel.classList.contains('show')) {
            this.cargarNotificaciones();
        }
    },
    
    cerrarPanel() {
        this.panel.classList.remove('show');
    },
    
    async cargarNotificaciones() {
        try {
            const response = await fetch('/Converza/app/presenters/notificaciones_api.php?accion=obtener');
            const data = await response.json();
            
            if (data.success) {
                this.actualizarBadge(data.total);
                this.renderizarNotificaciones(data.notificaciones);
            }
        } catch (error) {
            console.error('Error al cargar notificaciones:', error);
        }
    },
    
    actualizarBadge(total) {
        if (total > 0) {
            this.badge.textContent = total > 99 ? '99+' : total;
            this.badge.style.display = 'flex';
        } else {
            this.badge.style.display = 'none';
        }
    },
    
    renderizarNotificaciones(notificaciones) {
        if (notificaciones.length === 0) {
            this.lista.innerHTML = `
                <div class="notificaciones-vacio">
                    <i class="bi bi-bell-slash"></i>
                    <p>No tienes notificaciones</p>
                </div>
            `;
            return;
        }
        
        this.lista.innerHTML = notificaciones.map(noti => this.crearNotificacionHTML(noti)).join('');
        
        // Agregar event listeners a las notificaciones
        this.lista.querySelectorAll('.notificacion-item').forEach(item => {
            item.addEventListener('click', () => {
                const notiId = item.dataset.id;
                const url = item.dataset.url;
                this.marcarLeida(notiId, url);
            });
        });
    },
    
    crearNotificacionHTML(noti) {
        const iconoClass = this.obtenerClaseIcono(noti.tipo);
        const icono = this.obtenerIcono(noti.tipo);
        const tiempo = this.obtenerTiempoRelativo(noti.fecha_creacion);
        const claseLeida = noti.leida == 0 ? 'no-leida' : '';
        
        let avatar = '';
        if (noti.de_usuario_avatar) {
            avatar = `<img src="/converza/public/avatars/${noti.de_usuario_avatar}" class="notificacion-avatar" alt="">`;
        } else {
            avatar = `<div class="notificacion-icono ${iconoClass}"><i class="${icono}"></i></div>`;
        }
        
        return `
            <div class="notificacion-item ${claseLeida}" data-id="${noti.id}" data-url="${noti.url_redireccion || '#'}">
                ${avatar}
                <div class="notificacion-contenido">
                    <div class="notificacion-mensaje">${noti.mensaje}</div>
                    <div class="notificacion-tiempo"><i class="bi bi-clock"></i> ${tiempo}</div>
                </div>
            </div>
        `;
    },
    
    obtenerClaseIcono(tipo) {
        const clases = {
            'solicitud_amistad': 'solicitud',
            'amistad_aceptada': 'aceptada',
            'amistad_rechazada': 'rechazada',
            'nuevo_seguidor': 'seguidor',
            'solicitud_mensaje': 'mensaje',
            'mensaje_aceptado': 'aceptada',
            'nuevo_mensaje': 'mensaje',
            'nuevo_comentario': 'comentario',
            'nueva_publicacion': 'publicacion'
        };
        return clases[tipo] || 'solicitud';
    },
    
    obtenerIcono(tipo) {
        const iconos = {
            'solicitud_amistad': 'bi bi-person-plus',
            'amistad_aceptada': 'bi bi-check-circle',
            'amistad_rechazada': 'bi bi-x-circle',
            'nuevo_seguidor': 'bi bi-heart',
            'solicitud_mensaje': 'bi bi-envelope',
            'mensaje_aceptado': 'bi bi-check-circle',
            'nuevo_mensaje': 'bi bi-chat-dots',
            'nuevo_comentario': 'bi bi-chat-left-text',
            'nueva_publicacion': 'bi bi-file-post'
        };
        return iconos[tipo] || 'bi bi-bell';
    },
    
    obtenerTiempoRelativo(fecha) {
        const ahora = new Date();
        const fechaNoti = new Date(fecha);
        const diff = Math.floor((ahora - fechaNoti) / 1000);
        
        if (diff < 60) return 'Ahora mismo';
        if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
        if (diff < 604800) return `Hace ${Math.floor(diff / 86400)} días`;
        return fechaNoti.toLocaleDateString();
    },
    
    async marcarLeida(notiId, url) {
        try {
            const formData = new FormData();
            formData.append('notificacion_id', notiId);
            
            const response = await fetch('/Converza/app/presenters/notificaciones_api.php?accion=marcar_leida', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                this.actualizarBadge(data.total);
                if (url && url !== '#' && url !== 'null') {
                    window.location.href = url;
                } else {
                    this.cargarNotificaciones();
                }
            }
        } catch (error) {
            console.error('Error al marcar como leída:', error);
        }
    },
    
    async marcarTodasLeidas() {
        try {
            const response = await fetch('/Converza/app/presenters/notificaciones_api.php?accion=marcar_todas_leidas', {
                method: 'POST'
            });
            
            const data = await response.json();
            if (data.success) {
                this.actualizarBadge(0);
                this.cargarNotificaciones();
            }
        } catch (error) {
            console.error('Error al marcar todas como leídas:', error);
        }
    },
    
    async limpiarTodas() {
        try {
            const response = await fetch('/Converza/app/presenters/notificaciones_api.php?accion=eliminar_todas', {
                method: 'POST'
            });
            
            const data = await response.json();
            if (data.success) {
                this.actualizarBadge(0);
                this.lista.innerHTML = `
                    <div class="notificaciones-vacio">
                        <i class="bi bi-bell-slash"></i>
                        <p>No tienes notificaciones</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error al limpiar notificaciones:', error);
        }
    }
};

// Inicializar sistema de notificaciones cuando cargue la página
document.addEventListener('DOMContentLoaded', () => {
    NotificacionesSystem.init();
});
</script>
