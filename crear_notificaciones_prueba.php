<?php
session_start();
require_once __DIR__ . '/app/models/config.php';
require_once __DIR__ . '/app/models/notificaciones-helper.php';

if (!isset($_SESSION['id'])) {
    die("Debes estar logueado para usar este script");
}

$helper = new NotificacionesHelper($conexion);
$miId = $_SESSION['id'];
$miUsuario = $_SESSION['usuario'];

echo "<h1>🔔 Crear Notificaciones de Prueba</h1>";
echo "<p>Usuario actual: <strong>$miUsuario</strong> (ID: $miId)</p>";

// Crear varias notificaciones de prueba para el usuario actual
$notificacionesCreadas = [];

// 1. Notificación de comentario
$resultado1 = $helper->crear(
    $miId,
    'nuevo_comentario',
    '<strong>admin</strong> comentó tu publicación: "¡Excelente foto! 📸"',
    10, // de usuario admin
    1,  // publicación ID 1
    'publicacion',
    '/Converza/app/view/index.php#publicacion-1'
);
$notificacionesCreadas[] = $resultado1 ? "✅ Notificación de comentario" : "❌ Error en comentario";

// 2. Notificación de like
$resultado2 = $helper->crear(
    $miId,
    'reaccion_publicacion',
    '<strong>admin</strong> reaccionó ❤️ a tu publicación',
    10,
    1,
    'publicacion',
    '/Converza/app/view/index.php#publicacion-1'
);
$notificacionesCreadas[] = $resultado2 ? "✅ Notificación de like" : "❌ Error en like";

// 3. Notificación de nuevo mensaje
$resultado3 = $helper->crear(
    $miId,
    'nuevo_mensaje',
    '<strong>admin</strong> te envió un mensaje: "Hola, ¿cómo estás?"',
    10,
    null,
    'mensaje',
    '/Converza/app/presenters/chat.php?usuario=10'
);
$notificacionesCreadas[] = $resultado3 ? "✅ Notificación de mensaje" : "❌ Error en mensaje";

// 4. Notificación de solicitud de amistad
$resultado4 = $helper->crear(
    $miId,
    'solicitud_amistad',
    '<strong>admin</strong> te envió una solicitud de amistad',
    10,
    null,
    'solicitud_amistad',
    '/Converza/app/view/index.php'
);
$notificacionesCreadas[] = $resultado4 ? "✅ Notificación de solicitud de amistad" : "❌ Error en solicitud";

// 5. Notificación de nueva publicación
$resultado5 = $helper->crear(
    $miId,
    'nueva_publicacion',
    '<strong>admin</strong> publicó algo nuevo: "¡Miren esta foto increíble!"',
    10,
    1,
    'publicacion',
    '/Converza/app/view/index.php#publicacion-1'
);
$notificacionesCreadas[] = $resultado5 ? "✅ Notificación de nueva publicación" : "❌ Error en publicación";

echo "<h2>Resultados:</h2>";
echo "<ul>";
foreach ($notificacionesCreadas as $resultado) {
    echo "<li>$resultado</li>";
}
echo "</ul>";

// Contar notificaciones
$total = $helper->contarNoLeidas($miId);
echo "<h2>Total de notificaciones no leídas: <span style='color: green; font-size: 24px;'>$total</span></h2>";

// Mostrar las notificaciones
$notificaciones = $helper->obtenerNoLeidas($miId);
echo "<h2>Tus notificaciones:</h2>";
if (count($notificaciones) > 0) {
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
    foreach ($notificaciones as $noti) {
        $icono = '🔔';
        switch ($noti['tipo']) {
            case 'nuevo_comentario': $icono = '💬'; break;
            case 'reaccion_publicacion': $icono = '❤️'; break;
            case 'nuevo_mensaje': $icono = '✉️'; break;
            case 'solicitud_amistad': $icono = '👥'; break;
            case 'nueva_publicacion': $icono = '📝'; break;
        }
        
        echo "<div style='background: white; padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid #007bff;'>";
        echo "<div style='font-size: 20px; margin-bottom: 5px;'>$icono</div>";
        echo "<div>{$noti['mensaje']}</div>";
        echo "<div style='color: #6c757d; font-size: 12px; margin-top: 5px;'>{$noti['fecha_creacion']}</div>";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p>No hay notificaciones</p>";
}

echo "<hr>";
echo "<h2>Ahora haz lo siguiente:</h2>";
echo "<ol>";
echo "<li>Ve a tu página de inicio: <a href='/Converza/app/view/index.php' target='_blank'><strong>Ir al inicio</strong></a></li>";
echo "<li>Busca la campana 🔔 en la esquina superior derecha</li>";
echo "<li>Deberías ver un badge rojo con el número <strong>$total</strong></li>";
echo "<li>Haz click en la campana para ver tus notificaciones</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='/Converza/app/view/index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🚀 IR A VER MIS NOTIFICACIONES</a></p>";
?>
