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
// 🆕 Soporta tanto el formato nuevo (karma_pendiente) como el antiguo (karma_notification)
if (isset($_SESSION['karma_pendiente']) && !isset($_SESSION['karma_shown'])) {
    $puntos = $_SESSION['karma_pendiente'];
    $response['success'] = true;
    $response['data'] = [
        'puntos' => $puntos,
        'tipo' => $puntos > 0 ? 'positivo' : 'negativo',
        'mensaje' => $puntos > 0 ? '¡Has ganado karma!' : 'Has perdido karma'
    ];
    
    // Marcar como mostrada para que no se repita
    $_SESSION['karma_shown'] = true;
} else if (isset($_SESSION['karma_notification']) && !isset($_SESSION['karma_shown'])) {
    $response['success'] = true;
    $response['data'] = $_SESSION['karma_notification'];
    
    // Marcar como mostrada para que no se repita
    $_SESSION['karma_shown'] = true;
} else if (isset($_SESSION['karma_shown'])) {
    // Limpiar después de la segunda llamada
    unset($_SESSION['karma_notification']);
    unset($_SESSION['karma_pendiente']);
    unset($_SESSION['karma_shown']);
}

echo json_encode($response);
