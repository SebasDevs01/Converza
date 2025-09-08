<?php
// app/presenters/login.php (fragmento de ejemplo para uso de JWT)
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../models/JwtHelper.php';
// ...existing code...
// Después de validar usuario y contraseña:
if ($usuario_valido) {
    $token = JwtHelper::createToken(['user_id' => $user_id, 'username' => $username]);
    // Puedes devolver el token en la respuesta o guardarlo en sesión/cookie
    echo json_encode(['token' => $token]);
    exit;
}
// ...existing code...
