<?php
/**
 * Componente de visualización de Karma Social
 * Muestra el karma del usuario en su perfil
 */

require_once __DIR__ . '/../../models/config.php';
require_once __DIR__ . '/../../models/karma-social-helper.php';

// Si no se especifica usuario, usar el de la sesión
$usuario_id_karma = isset($usuario_id_perfil) ? $usuario_id_perfil : (isset($_SESSION['id_use']) ? $_SESSION['id_use'] : null);

if ($usuario_id_karma) {
    $karmaHelper = new KarmaSocialHelper($conexion);
    $karma_data = $karmaHelper->obtenerKarmaTotal($usuario_id_karma);
    $karma_total = $karma_data['karma_total'];
    $nivel = $karmaHelper->obtenerNivelKarma($karma_total);
?>

<div class="karma-social-widget" style="background: linear-gradient(135deg, <?php echo $nivel['color']; ?>20, <?php echo $nivel['color']; ?>10); border-left: 4px solid <?php echo $nivel['color']; ?>; padding: 15px; border-radius: 10px; margin: 15px 0;">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 32px;"><?php echo $nivel['emoji']; ?></span>
                <div>
                    <h5 style="margin: 0; color: #333; font-weight: bold;">
                        Karma Social
                    </h5>
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        Nivel: <strong style="color: <?php echo $nivel['color']; ?>;"><?php echo $nivel['nivel']; ?></strong>
                    </p>
                </div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 28px; font-weight: bold; color: <?php echo $nivel['color']; ?>;">
                <?php echo number_format($karma_total); ?>
            </div>
            <div style="font-size: 12px; color: #999;">
                <?php echo $karma_data['acciones_totales']; ?> acciones
            </div>
        </div>
    </div>
    
    <div style="margin-top: 10px; font-size: 12px; color: #666;">
        <strong>💫 Buenas acciones registradas</strong><br>
        Comentarios positivos • Apoyo • Respeto • Ayuda
    </div>
</div>

<style>
.karma-social-widget {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.karma-social-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>

<?php
}
?>
