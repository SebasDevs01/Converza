<?php
session_start();
require_once __DIR__.'/../models/config.php';

header('Content-Type: application/json');

// Log para debugging
error_log("🔍 verificar_conversacion_existente.php - Inicio");
error_log("POST data: " . print_r($_POST, true));
error_log("Session ID: " . ($_SESSION['id'] ?? 'NO SESSION'));

// Verificar sesión
if (!isset($_SESSION['id'])) {
    error_log("❌ Error: No hay sesión activa");
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Obtener el ID del usuario destino
$usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;

error_log("Usuario destino: " . $usuario_id);
error_log("Usuario actual (sesión): " . $_SESSION['id']);

if ($usuario_id <= 0) {
    error_log("❌ Error: ID de usuario inválido");
    echo json_encode(['error' => 'ID de usuario inválido']);
    exit();
}

try {
    error_log("🔍 Iniciando verificaciones...");
    
    // 1️⃣ Verificar si son AMIGOS confirmados (estado = 1)
    error_log("1️⃣ Verificando si son amigos...");
    $stmtAmigos = $conexion->prepare("
        SELECT id_ami 
        FROM amigos 
        WHERE ((de = :yo1 AND para = :otro1) OR (de = :otro2 AND para = :yo2))
        AND estado = 1
        LIMIT 1
    ");
    $stmtAmigos->bindParam(':yo1', $_SESSION['id'], PDO::PARAM_INT);
    $stmtAmigos->bindParam(':otro1', $usuario_id, PDO::PARAM_INT);
    $stmtAmigos->bindParam(':otro2', $usuario_id, PDO::PARAM_INT);
    $stmtAmigos->bindParam(':yo2', $_SESSION['id'], PDO::PARAM_INT);
    
    error_log("Ejecutando consulta de amistad...");
    $stmtAmigos->execute();
    error_log("Consulta ejecutada, obteniendo resultado...");
    
    $amistad = $stmtAmigos->fetch(PDO::FETCH_ASSOC);
    error_log("Resultado amistad RAW: " . print_r($amistad, true));
    error_log("Resultado amistad: " . ($amistad ? "SÍ SON AMIGOS" : "No son amigos"));
    
    if ($amistad) {
        // Son amigos confirmados, redirigir al chat
        error_log("✅ SON AMIGOS CONFIRMADOS - Redirigiendo al chat");
        echo json_encode([
            'existe_conversacion' => true,
            'tipo' => 'amigos',
            'usuario_id' => $usuario_id
        ]);
        exit();
    }
    
    error_log("No son amigos, continuando con siguiente verificación...");
    
    // 2️⃣ Verificar si son SEGUIDORES MUTUOS
    error_log("2️⃣ Verificando si son seguidores mutuos...");
    $stmtSeguidores = $conexion->prepare("
        SELECT COUNT(*) as total
        FROM seguidores s1
        INNER JOIN seguidores s2 
            ON s1.seguidor_id = s2.seguido_id 
            AND s1.seguido_id = s2.seguidor_id
        WHERE s1.seguidor_id = :yo 
        AND s1.seguido_id = :otro
    ");
    $stmtSeguidores->bindParam(':yo', $_SESSION['id'], PDO::PARAM_INT);
    $stmtSeguidores->bindParam(':otro', $usuario_id, PDO::PARAM_INT);
    $stmtSeguidores->execute();
    
    $seguidoresMutuos = $stmtSeguidores->fetch(PDO::FETCH_ASSOC);
    error_log("Resultado seguidores mutuos: " . ($seguidoresMutuos['total'] > 0 ? "SÍ SON SEGUIDORES MUTUOS" : "No son seguidores mutuos"));
    
    if ($seguidoresMutuos['total'] > 0) {
        // Son seguidores mutuos, redirigir al chat
        error_log("✅ SON SEGUIDORES MUTUOS - Redirigiendo al chat");
        echo json_encode([
            'existe_conversacion' => true,
            'tipo' => 'seguidores_mutuos',
            'usuario_id' => $usuario_id
        ]);
        exit();
    }
    
    // 3️⃣ Verificar si existe una solicitud de mensaje ACEPTADA
    error_log("3️⃣ Verificando si tienen solicitud de mensaje aceptada...");
    $stmtSolicitud = $conexion->prepare("
        SELECT id, estado 
        FROM solicitudes_mensaje 
        WHERE ((de = :yo3 AND para = :otro3) OR (de = :otro4 AND para = :yo4))
        AND estado = 'aceptada'
        LIMIT 1
    ");
    $stmtSolicitud->bindParam(':yo3', $_SESSION['id'], PDO::PARAM_INT);
    $stmtSolicitud->bindParam(':otro3', $usuario_id, PDO::PARAM_INT);
    $stmtSolicitud->bindParam(':otro4', $usuario_id, PDO::PARAM_INT);
    $stmtSolicitud->bindParam(':yo4', $_SESSION['id'], PDO::PARAM_INT);
    $stmtSolicitud->execute();
    
    $solicitud = $stmtSolicitud->fetch();
    error_log("Resultado solicitud mensaje: " . ($solicitud ? "SÍ TIENEN SOLICITUD ACEPTADA" : "No tienen solicitud aceptada"));
    
    if ($solicitud) {
        // Solicitud de mensaje aceptada, redirigir al chat
        error_log("✅ TIENEN SOLICITUD DE MENSAJE ACEPTADA - Redirigiendo al chat");
        echo json_encode([
            'existe_conversacion' => true,
            'tipo' => 'solicitud_aceptada',
            'usuario_id' => $usuario_id
        ]);
        exit();
    }
    
    // ❌ No hay ninguna relación que permita chatear libremente
    error_log("❌ NO HAY RELACIÓN - Mostrando modal de solicitud");
    echo json_encode([
        'existe_conversacion' => false
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al verificar conversación',
        'detalle' => $e->getMessage()
    ]);
}
?>
