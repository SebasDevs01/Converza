<!-- Badge de Solicitudes de Amistad con actualización automática -->
<style>
.solicitudes-badge-container {
    position: relative;
    display: inline-block;
}

.solicitudes-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    display: none;
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
</style>

<div class="solicitudes-badge-container">
    <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSolicitudes" title="Solicitudes de amistad">
        <i class="bi bi-person-plus"></i>
        <span class="solicitudes-badge" id="solicitudes-badge">0</span>
    </a>
</div>

<script>
class SolicitudesBadge {
    constructor() {
        this.badge = document.getElementById('solicitudes-badge');
        this.intervalId = null;
        this.init();
    }

    init() {
        this.actualizar();
        // Actualizar cada 10 segundos
        this.intervalId = setInterval(() => this.actualizar(), 10000);
    }

    async actualizar() {
        try {
            const response = await fetch('/Converza/app/presenters/solicitudes_api.php?action=contar_pendientes', {
                method: 'GET',
                credentials: 'same-origin'
            });

            if (!response.ok) throw new Error('Error al obtener solicitudes');

            const data = await response.json();
            
            if (data.success) {
                this.actualizarBadge(data.total);
            }
        } catch (error) {
            console.error('Error al actualizar badge de solicitudes:', error);
        }
    }

    actualizarBadge(total) {
        if (total > 0) {
            this.badge.textContent = total > 99 ? '99+' : total;
            this.badge.style.display = 'flex';
        } else {
            this.badge.style.display = 'none';
        }
    }

    destruir() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.solicitudesBadge = new SolicitudesBadge();
    });
} else {
    window.solicitudesBadge = new SolicitudesBadge();
}
</script>
