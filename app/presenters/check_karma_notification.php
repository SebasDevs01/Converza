<?php
/**
 * API para verificar si hay una notificación de karma pendiente
 * Devuelve los datos del popup para mostrar en tiempo real
 */
session_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'data' => null
];

// Verificar si hay notificación de karma en sesión
if (isset($_SESSION['karma_notification']) && !isset($_SESSION['karma_shown'])) {
    $response['success'] = true;
    $response['data'] = $_SESSION['karma_notification'];
    
    // Marcar como mostrada para que no se repita
    $_SESSION['karma_shown'] = true;
} else if (isset($_SESSION['karma_shown'])) {
    // Limpiar después de la segunda llamada
    unset($_SESSION['karma_notification']);
    unset($_SESSION['karma_shown']);
}

echo json_encode($response);
