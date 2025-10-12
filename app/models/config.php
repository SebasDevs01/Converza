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
    $pdo = $conexion; // Alias para compatibilidad
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Agregar conexión mysqli
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Error de conexión mysqli: " . mysqli_connect_error());
}

// Función para verificar si el usuario está bloqueado
function isUserBlocked($userId, $conexion) {
    try {
        $stmt = $conexion->prepare("SELECT tipo FROM usuarios WHERE id_use = :id");
        $stmt->execute([':id' => $userId]);
        $tipo = $stmt->fetchColumn();
        return $tipo === 'blocked';
    } catch (Exception $e) {
        return false;
    }
}

// Función para verificar permisos del usuario
function checkUserPermissions($userId, $conexion) {
    if (!isset($_SESSION['id']) || $_SESSION['id'] != $userId) {
        return false;
    }
    
    return !isUserBlocked($userId, $conexion);
}
