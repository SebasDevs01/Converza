<?php
session_start();
echo "<h1>Debug de Sesión</h1>";
echo "<h2>Datos de la sesión actual:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>ID de usuario:</h2>";
$userId = $_SESSION['id'] ?? null;
echo "Usuario ID: " . ($userId ? $userId : "NO ENCONTRADO") . "<br>";

if (!$userId) {
    echo "<p style='color: red;'>❌ ERROR: No hay sesión de usuario activa</p>";
    echo "<p>Inicia sesión primero en <a href='/Converza/app/view/index.php'>Converza</a></p>";
} else {
    echo "<p style='color: green;'>✅ Usuario logueado correctamente</p>";
}
?>