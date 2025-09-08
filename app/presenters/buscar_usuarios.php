<?php
session_start();
require_once __DIR__.'/../models/config.php';


$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    exit;
}

// Buscar usuarios en línea (simulación: usuarios con sesión activa y nombre/usuario que coincida)
$stmt = $conexion->prepare("SELECT id_use, usuario, avatar, sexo FROM usuarios WHERE (usuario LIKE :usuario OR nombre LIKE :nombre) AND id_use != :id LIMIT 10");
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
foreach ($usuarios as $u) {
    $avatar = htmlspecialchars($u['avatar']);
    $avatarPath = __DIR__.'/../../public/avatars/'.$avatar;
    if ($avatar && file_exists($avatarPath)) {
        $src = '/TrabajoRedSocial/public/avatars/'.$avatar;
    } else {
        $src = '/TrabajoRedSocial/public/avatars/defect.jpg';
    }
    echo '<div class="d-flex align-items-center mb-2">';
    echo '<img src="'.$src.'" class="rounded-circle me-2" width="32" height="32" alt="Avatar">';
    echo '<div class="flex-grow-1">';
    echo '<a class="fw-bold text-decoration-none" href="../presenters/perfil.php?id='.urlencode($u['id_use']).'">'.htmlspecialchars($u['usuario']).'</a> ';
    echo '<small class="text-muted">'.htmlspecialchars($u['sexo']).'</small>';
    echo '</div>';
    echo '<a href="../presenters/chat.php?user='.urlencode($u['id_use']).'" class="btn btn-primary btn-sm ms-2"><i class="bi bi-chat"></i> Chatear</a>';
    echo '<a href="../presenters/solicitud.php?action=agregar&id='.urlencode($u['id_use']).'" class="btn btn-success btn-sm ms-1"><i class="bi bi-person-plus"></i></a>';
    echo '</div>';
}
