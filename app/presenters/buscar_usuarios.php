<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/bloqueos-helper.php';


$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    exit;
}

// Buscar usuarios en línea excluyendo usuarios bloqueados
$filtroBloqueos = generarFiltroBloqueos($conexion, $_SESSION['id'], 'id_use');
$stmt = $conexion->prepare("SELECT id_use, usuario, avatar, sexo FROM usuarios WHERE (usuario LIKE :usuario OR nombre LIKE :nombre) AND id_use != :id AND ($filtroBloqueos) LIMIT 10");
$like = "%$q%";
$stmt->bindParam(':usuario', $like, PDO::PARAM_STR);
$stmt->bindParam(':nombre', $like, PDO::PARAM_STR);
$stmt->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$usuarios) {
    echo '<div class="text-muted small">No se encontraron usuarios en línea.</div>';
    exit;
}

// Función para verificar si pueden chatear directamente
// Pueden chatear si: son amigos, tienen historial de chat, o tiene solicitud de mensaje aceptada
function puedenChatear($conexion, $userId1, $userId2) {
    // 1. Verificar si son amigos (estado = 1 significa aceptado)
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total FROM amigos 
        WHERE ((de = ? AND para = ?) OR (de = ? AND para = ?)) 
        AND estado = 1
    ");
    $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['total'] > 0) return true;
    
    // 2. Verificar si tienen historial de chat
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total FROM chats 
        WHERE (de = ? AND para = ?) OR (de = ? AND para = ?)
        LIMIT 1
    ");
    $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['total'] > 0) return true;
    
    // 3. Verificar si tiene solicitud de mensaje aceptada
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total FROM solicitudes_mensaje 
        WHERE (de = ? AND para = ? AND estado = 'aceptada')
        LIMIT 1
    ");
    $stmt->execute([$userId1, $userId2]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['total'] > 0) return true;
    
    return false;
}

foreach ($usuarios as $u) {
    $avatar = htmlspecialchars($u['avatar']);
    $avatarPath = __DIR__.'/../../public/avatars/'.$avatar;
    if ($avatar && file_exists($avatarPath)) {
        $src = '/Converza/public/avatars/'.$avatar;
    } else {
        $src = '/Converza/public/avatars/defect.jpg';
    }
    
    // Verificar si pueden chatear (son amigos, tienen historial, o solicitud aceptada)
    $puedeChatear = puedenChatear($conexion, $_SESSION['id'], $u['id_use']);
    
    // ✅ Verificar si YA SON AMIGOS (estado = 1)
    $stmtAmigo = $conexion->prepare("
        SELECT COUNT(*) as es_amigo FROM amigos 
        WHERE ((de = ? AND para = ?) OR (de = ? AND para = ?)) 
        AND estado = 1
    ");
    $stmtAmigo->execute([$_SESSION['id'], $u['id_use'], $u['id_use'], $_SESSION['id']]);
    $esAmigo = $stmtAmigo->fetch(PDO::FETCH_ASSOC)['es_amigo'] > 0;
    
    echo '<div class="d-flex align-items-center mb-2">';
    echo '<img src="'.$src.'" class="rounded-circle me-2" width="32" height="32" alt="Avatar">';
    echo '<div class="flex-grow-1">';
    echo '<a class="fw-bold text-decoration-none" href="/Converza/app/presenters/perfil.php?id='.urlencode($u['id_use']).'">'.htmlspecialchars($u['usuario']).'</a> ';
    echo '<small class="text-muted">'.htmlspecialchars($u['sexo']).'</small>';
    echo '</div>';
    
    // Botón de chatear: solo si pueden chatear va al chat, sino al perfil con modal de solicitud
    if ($puedeChatear) {
        // Si pueden chatear → botón "Chatear" que va al chat
        echo '<a href="/Converza/app/presenters/chat.php?user='.urlencode($u['id_use']).'" class="btn btn-primary btn-sm ms-2"><i class="bi bi-chat"></i> Chatear</a>';
    } else {
        // Si NO pueden chatear → botón "Mensaje" que va al perfil con parámetro para abrir modal
        echo '<a href="/Converza/app/presenters/perfil.php?id='.urlencode($u['id_use']).'&solicitar_mensaje=1" class="btn btn-outline-primary btn-sm ms-2"><i class="bi bi-chat"></i> Mensaje</a>';
    }
    
    // ✅ BOTÓN DE AMISTAD INTELIGENTE
    if ($esAmigo) {
        // YA SON AMIGOS → Mostrar badge "Amigos" (deshabilitado)
        echo '<button class="btn btn-success btn-sm ms-1" disabled><i class="bi bi-check-circle-fill"></i> Amigos</button>';
    } else {
        // NO SON AMIGOS → Mostrar botón "Agregar"
        echo '<a href="/Converza/app/presenters/solicitud.php?action=agregar&id='.urlencode($u['id_use']).'" class="btn btn-outline-success btn-sm ms-1"><i class="bi bi-person-plus"></i> Agregar</a>';
    }
    
    echo '</div>';
}
