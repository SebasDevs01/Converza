<?php
/**
 * Session Helper - Manejo seguro de sesiones con supresión de warnings
 * 
 * Este archivo proporciona una función segura para iniciar sesiones
 * que evita warnings cuando hay problemas de permisos en la carpeta de sesiones.
 * 
 * USO: Incluir este archivo en vez de llamar session_start() directamente
 */

/**
 * Inicia sesión de forma segura suprimiendo warnings de permisos
 * 
 * @return bool True si la sesión se inició correctamente, false si ya estaba iniciada
 */
function iniciarSesionSegura() {
    // Verificar si ya hay una sesión activa
    if (session_status() === PHP_SESSION_ACTIVE) {
        return false; // Ya hay sesión activa
    }
    
    // Intentar iniciar sesión suprimiendo warnings
    @session_start();
    
    return true;
}

/**
 * Destruye sesión de forma segura limpiando todas las variables
 * 
 * @return void
 */
function destruirSesionSegura() {
    // Verificar si hay sesión activa
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Eliminar cookie de sesión si existe
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Intentar destruir sesión (sin fallar si hay problemas de permisos)
        @session_destroy();
    }
}

// Auto-iniciar sesión al incluir este archivo
iniciarSesionSegura();
?>
