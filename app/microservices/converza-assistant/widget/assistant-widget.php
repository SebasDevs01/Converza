<?php
/**
 * ✨ CONVERZA ASSISTANT - Widget PHP Wrapper
 * Este archivo permite incluir el widget HTML en páginas PHP
 */

// Prevenir acceso directo
if (!defined('ASSISTANT_WIDGET_LOADED')) {
    define('ASSISTANT_WIDGET_LOADED', true);
}

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Obtener datos del usuario de la sesión
$usuario_widget = null;
$foto_perfil_widget = '/Converza/public/avatars/defect.jpg'; // Foto por defecto
$nombre_usuario_widget = 'Usuario';
$id_usuario_widget = 0;

if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
    $id_usuario_widget = $_SESSION['id'];
    $nombre_usuario_widget = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario';
    
    // Obtener foto de perfil si existe en sesión
    if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
        $foto_perfil_widget = '/Converza/' . $_SESSION['foto_perfil'];
    }
}

// Cargar el contenido HTML del widget
$widget_path = __DIR__ . '/assistant-widget.html';

if (file_exists($widget_path)) {
    include $widget_path;
} else {
    // Fallback silencioso si no existe el archivo
    error_log('⚠️ Widget del asistente no encontrado en: ' . $widget_path);
}
?>
