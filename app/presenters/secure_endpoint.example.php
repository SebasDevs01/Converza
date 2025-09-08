<?php
// app/presenters/secure_endpoint.example.php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../models/JwtHelper.php';
// ...existing code...
// Obtener el token del header Authorization: Bearer <token>
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = str_replace('Bearer ', '', $authHeader);

$userData = JwtHelper::validateToken($token);
if (!$userData) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
    exit;
}
// $userData contiene los datos del usuario autenticado
// ...existing code...
