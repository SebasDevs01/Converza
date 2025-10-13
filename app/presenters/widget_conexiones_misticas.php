<?php
/**
 * Widget de Conexiones Místicas
 * Muestra coincidencias curiosas con otros usuarios
 */

if (!isset($_SESSION['id'])) {
    return;
}

require_once(__DIR__ . '/../models/conexiones-misticas-helper.php');

$motor = new ConexionesMisticas($conexion);
$conexiones = $motor->obtenerConexionesUsuario($_SESSION['id'], 3);

if (empty($conexiones)) {
    return; // No mostrar si no hay conexiones
}
?>

<style>
    .conexiones-misticas-widget {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 20px;
        color: white;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        margin-bottom: 24px;
    }
    
    .conexiones-misticas-widget h3 {
        font-size: 1.3rem;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .conexiones-misticas-widget .subtitle {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 16px;
    }
    
    .conexion-item {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .conexion-item:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }
    
    .conexion-item:last-child {
        margin-bottom: 0;
    }
    
    .conexion-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    
    .conexion-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.6);
        object-fit: cover;
    }
    
    .conexion-username {
        font-weight: 600;
        font-size: 1rem;
    }
    
    .conexion-badge {
        background: rgba(255, 255, 255, 0.3);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        margin-left: auto;
    }
    
    .conexion-descripcion {
        font-size: 0.9rem;
        line-height: 1.4;
        opacity: 0.95;
    }
    
    .ver-mas-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        width: 100%;
        margin-top: 12px;
        transition: all 0.3s ease;
    }
    
    .ver-mas-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
    }
</style>

<div class="conexiones-misticas-widget">
    <h3>
        <span>🔮</span>
        <span>Conexiones Místicas</span>
    </h3>
    <p class="subtitle">Descubre coincidencias curiosas con otros usuarios</p>
    
    <?php foreach ($conexiones as $conexion): 
        $avatarPath = $conexion['otro_avatar'] 
            ? "/Converza/public/avatars/{$conexion['otro_avatar']}" 
            : "/Converza/public/avatars/defect.jpg";
        
        // Iconos según tipo de conexión
        $iconos = [
            'gustos_compartidos' => '💖',
            'intereses_comunes' => '💬',
            'amigos_de_amigos' => '👥',
            'horarios_coincidentes' => '🕐'
        ];
        $icono = $iconos[$conexion['tipo_conexion']] ?? '✨';
    ?>
    
    <div class="conexion-item" onclick="location.href='../presenters/perfil.php?id=<?php echo $conexion['otro_id']; ?>'">
        <div class="conexion-header">
            <img src="<?php echo $avatarPath; ?>" alt="Avatar" class="conexion-avatar">
            <span class="conexion-username"><?php echo htmlspecialchars($conexion['otro_usuario']); ?></span>
            <span class="conexion-badge"><?php echo $icono; ?> <?php echo $conexion['puntuacion']; ?>%</span>
        </div>
        <div class="conexion-descripcion">
            <?php echo htmlspecialchars($conexion['descripcion']); ?>
        </div>
    </div>
    
    <?php endforeach; ?>
    
    <button class="btn ver-mas-btn" onclick="location.href='../presenters/conexiones_misticas.php'">
        Ver todas las conexiones ✨
    </button>
</div>
