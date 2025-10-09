<?php
$host = 'localhost';
$db   = 'converza';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Muestra errores claros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch asociativo por defecto
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Más seguro
];

try {
    $conexion = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Agregar conexión mysqli
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Error de conexión mysqli: " . mysqli_connect_error());
}
