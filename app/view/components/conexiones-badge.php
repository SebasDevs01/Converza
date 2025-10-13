<!-- Badge de Conexiones Místicas Nuevas -->
<?php
// Contar conexiones nuevas (no vistas) del usuario
$usuarioId = $_SESSION['id'] ?? 0;

if ($usuarioId > 0) {
    try {
        $stmtConexiones = $conexion->prepare("
            SELECT COUNT(*) as nuevas
            FROM conexiones_misticas
            WHERE ((usuario1_id = ? AND visto_usuario1 = 0) 
                OR (usuario2_id = ? AND visto_usuario2 = 0))
            AND fecha_deteccion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmtConexiones->execute([$usuarioId, $usuarioId]);
        $conexionesNuevas = $stmtConexiones->fetch(PDO::FETCH_ASSOC)['nuevas'];
    } catch (Exception $e) {
        $conexionesNuevas = 0;
    }
} else {
    $conexionesNuevas = 0;
}
?>

<style>
.conexiones-badge {
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
    z-index: 10;
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

.conexiones-container {
    position: relative;
    display: inline-block;
}
</style>

<div class="conexiones-container">
    <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasConexiones" title="Conexiones Místicas">
        <i class="bi bi-stars"></i> Místicas
        <?php if ($conexionesNuevas > 0): ?>
            <span class="conexiones-badge" id="conexiones-badge-count">
                <?php echo $conexionesNuevas; ?>
            </span>
        <?php endif; ?>
    </a>
</div>
