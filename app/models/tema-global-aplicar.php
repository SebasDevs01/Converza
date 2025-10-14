<?php
/**
 * APLICADOR GLOBAL DE TEMAS - Converza
 * Incluir este archivo en el <head> de todas las páginas para aplicar el tema equipado
 */

// Determinar qué tema aplicar
$temaClase = 'tema-default'; // Por defecto siempre es Converza azul

if (isset($_SESSION['id'])) {
    require_once __DIR__ . '/recompensas-aplicar-helper.php';
    
    try {
        $recompensasHelper = new RecompensasAplicarHelper($conexion);
        $temaEquipado = $recompensasHelper->getTemaClaseBody($_SESSION['id']);
        
        // Si hay tema equipado, usarlo; si no, mantener default
        if (!empty($temaEquipado)) {
            $temaClase = $temaEquipado;
        }
        
    } catch (Exception $e) {
        error_log("Error al aplicar tema global: " . $e->getMessage());
        $temaClase = 'tema-default'; // En caso de error, usar default
    }
}

// Aplicar tema INMEDIATAMENTE mediante JavaScript inline (antes de DOMContentLoaded)
echo "<script>
    // Aplicar tema INMEDIATAMENTE para evitar parpadeo
    (function() {
        document.documentElement.className += ' " . $temaClase . "';
        document.body.className = (document.body.className || '') + ' " . $temaClase . "';
    })();
    
    // Confirmar aplicación cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Limpiar clases de tema antiguas
        document.body.classList.remove('tema-default', 'tema-oscuro', 'tema-galaxy', 'tema-sunset', 'tema-neon');
        // Aplicar tema correcto
        document.body.classList.add('" . $temaClase . "');
        console.log('✨ Tema Converza aplicado: " . $temaClase . "');
    });
</script>\n";

// Enlazar el archivo CSS de temas
echo '<link rel="stylesheet" href="/Converza/public/css/temas-sistema.css">' . "\n";
?>
