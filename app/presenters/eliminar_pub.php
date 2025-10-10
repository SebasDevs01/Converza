<?php
include(__DIR__.'/../models/socialnetwork-lib.php');
include(__DIR__.'/../models/config.php');

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_pub = intval($_GET['id']);

    // Validar permisos - dueño o admin pueden eliminar
    $isAdmin = isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
    
    if ($isAdmin) {
        // Admin puede eliminar cualquier publicación
        $stmt = $conexion->prepare("DELETE FROM publicaciones WHERE id_pub = ?");
        $stmt->execute([$id_pub]);
    } else {
        // Usuario normal solo puede eliminar sus publicaciones
        $stmt = $conexion->prepare("DELETE FROM publicaciones WHERE id_pub = ? AND usuario = ?");
        $stmt->execute([$id_pub, $_SESSION['id']]);
    }


    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
