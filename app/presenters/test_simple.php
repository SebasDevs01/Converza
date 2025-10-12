<?php
session_start();
require_once __DIR__.'/../models/config.php';

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../app/view/index.php");
    exit;
}

// Test simple
echo "✅ El sistema funciona correctamente";
echo "<br>Usuario: " . $_SESSION['usuario'];
echo "<br>Timestamp: " . date('Y-m-d H:i:s');
?>