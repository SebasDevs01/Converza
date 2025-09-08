<?php
// app/models/JwtHelper.php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper {
    private static function getSecret() {
        // Cargar .env si no está cargado
        if (!getenv('JWT_SECRET')) {
            if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
                require_once __DIR__ . '/../../vendor/autoload.php';
                $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->load();
            }
        }
        return getenv('JWT_SECRET');
    }

    public static function createToken($data, $exp = 3600) {
        $payload = [
            'iat' => time(),
            'exp' => time() + $exp,
            'data' => $data
        ];
        return JWT::encode($payload, self::getSecret(), 'HS256');
    }

    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecret(), 'HS256'));
            return $decoded->data;
        } catch (Exception $e) {
            return false;
        }
    }
}
