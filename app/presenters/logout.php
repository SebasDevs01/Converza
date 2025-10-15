<?php
// ⭐ Usar helper de sesiones para manejo seguro
require_once __DIR__.'/../models/session-helper.php';

// Destruir sesión de forma segura
destruirSesionSegura();

// ⭐ Redirigir al login con parámetro para forzar limpieza de campos
header("Location: login.php?logout=1");
exit();
?>