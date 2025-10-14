<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/socialnetwork-lib.php';
require_once __DIR__.'/../models/chat-permisos-helper.php';
require_once __DIR__.'/../models/recompensas-aplicar-helper.php'; // 🎁 Sistema de recompensas

if(!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}

// Acepta tanto 'usuario' como 'id' para compatibilidad
$user = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0);
$sess = $_SESSION['id'];

// 🎁 Inicializar sistema de recompensas
$recompensasHelper = new RecompensasAplicarHelper($conexion);
$temaCSS = $recompensasHelper->getTemaCSS($sess);

// ✅ Obtener usuarios con los que puede chatear (SIN DUPLICADOS)
// PRIORIDAD: Amigos > Seguidores mutuos > Solicitudes aceptadas
// Si son amigos, NO los muestra como seguidores
$stmtAmigos = $conexion->prepare("
    SELECT DISTINCT u.id_use, u.usuario, u.nombre, u.avatar, u.verificado,
    CASE
        WHEN EXISTS(
            SELECT 1 FROM amigos a 
            WHERE ((a.de = :sess1 AND a.para = u.id_use) OR (a.para = :sess2 AND a.de = u.id_use))
            AND a.estado = 1
        ) THEN 'amigo'
        WHEN EXISTS(
            SELECT 1 FROM seguidores s1
            INNER JOIN seguidores s2 ON s1.seguidor_id = s2.seguido_id AND s1.seguido_id = s2.seguidor_id
            WHERE s1.seguidor_id = :sess3 AND s1.seguido_id = u.id_use
        ) THEN 'seguidor_mutuo'
        WHEN EXISTS(
            SELECT 1 FROM solicitudes_mensaje sm
            WHERE ((sm.de = :sess4 AND sm.para = u.id_use) OR (sm.para = :sess5 AND sm.de = u.id_use))
            AND sm.estado = 'aceptada'
        ) THEN 'solicitud_aceptada'
    END as tipo_relacion,
    (
        SELECT COUNT(*) FROM chats c
        WHERE c.de = u.id_use AND c.para = :sess16 AND c.leido = 0
    ) as mensajes_no_leidos
    FROM usuarios u
    WHERE u.id_use != :sess6
    AND NOT EXISTS (
        SELECT 1 FROM chats_archivados ca 
        WHERE ca.usuario_id = :sess17 AND ca.chat_con_usuario_id = u.id_use
    )
    AND NOT EXISTS (
        SELECT 1 FROM bloqueos b 
        WHERE (b.bloqueador_id = :sess18 AND b.bloqueado_id = u.id_use)
           OR (b.bloqueador_id = u.id_use AND b.bloqueado_id = :sess19)
    )
    AND (
        -- Son amigos confirmados
        EXISTS(
            SELECT 1 FROM amigos a 
            WHERE ((a.de = :sess7 AND a.para = u.id_use) OR (a.para = :sess8 AND a.de = u.id_use))
            AND a.estado = 1
        )
        OR
        -- Son seguidores mutuos (pero NO amigos)
        (
            EXISTS(
                SELECT 1 FROM seguidores s1
                INNER JOIN seguidores s2 ON s1.seguidor_id = s2.seguido_id AND s1.seguido_id = s2.seguidor_id
                WHERE s1.seguidor_id = :sess9 AND s1.seguido_id = u.id_use
            )
            AND NOT EXISTS(
                SELECT 1 FROM amigos a 
                WHERE ((a.de = :sess10 AND a.para = u.id_use) OR (a.para = :sess11 AND a.de = u.id_use))
                AND a.estado = 1
            )
        )
        OR
        -- Tienen solicitud de mensaje aceptada (pero NO son amigos ni seguidores mutuos)
        (
            EXISTS(
                SELECT 1 FROM solicitudes_mensaje sm
                WHERE ((sm.de = :sess12 AND sm.para = u.id_use) OR (sm.para = :sess13 AND sm.de = u.id_use))
                AND sm.estado = 'aceptada'
            )
            AND NOT EXISTS(
                SELECT 1 FROM amigos a 
                WHERE ((a.de = :sess14 AND a.para = u.id_use) OR (a.para = :sess15 AND a.de = u.id_use))
                AND a.estado = 1
            )
        )
    )
    ORDER BY mensajes_no_leidos DESC, u.usuario ASC
");
$stmtAmigos->execute([
    ':sess1' => $sess, ':sess2' => $sess, ':sess3' => $sess,
    ':sess4' => $sess, ':sess5' => $sess, ':sess6' => $sess,
    ':sess7' => $sess, ':sess8' => $sess, ':sess9' => $sess,
    ':sess10' => $sess, ':sess11' => $sess, ':sess12' => $sess,
    ':sess13' => $sess, ':sess14' => $sess, ':sess15' => $sess,
    ':sess16' => $sess, ':sess17' => $sess, ':sess18' => $sess,
    ':sess19' => $sess
]);
$amigos = $stmtAmigos->fetchAll(PDO::FETCH_ASSOC);

// Obtener solicitudes de mensaje pendientes
$stmtSolicitudes = $conexion->prepare("
    SELECT sm.*, u.usuario, u.nombre, u.avatar, u.verificado
    FROM solicitudes_mensaje sm
    INNER JOIN usuarios u ON sm.de = u.id_use
    WHERE sm.para = :sess AND sm.estado = 'pendiente'
    ORDER BY sm.fecha_solicitud DESC
");
$stmtSolicitudes->execute([':sess' => $sess]);
$solicitudesMensaje = $stmtSolicitudes->fetchAll(PDO::FETCH_ASSOC);
$countSolicitudesMensaje = count($solicitudesMensaje);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Coverza - Chat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  
  <?php if ($temaCSS): ?>
  <!-- 🎨 Tema personalizado equipado -->
  <style><?php echo $temaCSS; ?></style>
  <?php endif; ?>
  
  <style>
    /* Estilos para burbujas de chat modernas */
    .message-container {
      max-width: 75%;
      margin-bottom: 2px;
    }
    
    .message-bubble {
      padding: 12px 16px;
      border-radius: 18px;
      max-width: 100%;
      word-wrap: break-word;
      margin-bottom: 4px;
      font-size: 14px;
      line-height: 1.4;
      display: flex;
      align-items: center;
    }
    
    .message-bubble.sent {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
      color: white;
      border-bottom-right-radius: 6px;
      margin-left: auto;
    }
    
    .message-bubble.received {
      background: linear-gradient(135deg, #e5e5ea 0%, #d1d1d6 100%);
      color: #333;
      border: 1px solid #c7c7cc;
      border-bottom-left-radius: 6px;
    }
    
    .message-time {
      font-size: 11px;
      padding: 0 8px;
    }
    
    .chat-container {
      background: #f8f9fa;
      background-image: 
        radial-gradient(circle at 20px 80px, #e9ecef 1px, transparent 1px),
        radial-gradient(circle at 80px 20px, #e9ecef 1px, transparent 1px);
      background-size: 100px 100px;
    }
    
    /* Mejorar el scroll del chat */
    .chat-messages {
      max-height: 500px;
      overflow-y: auto;
      padding: 20px;
      scroll-behavior: smooth;
    }
    
    /* Scroll personalizado */
    .chat-messages::-webkit-scrollbar {
      width: 6px;
    }
    
    .chat-messages::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    
    .chat-messages::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 10px;
    }
    
    .chat-messages::-webkit-scrollbar-thumb:hover {
      background: #a1a1a1;
    }
    
    .message-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 4px;
    }
    
    .reaction-btn {
      opacity: 0.7;
      transition: opacity 0.2s;
      border: 1px solid #ddd !important;
      background: rgba(255,255,255,0.8) !important;
      font-size: 12px;
      padding: 2px 6px;
      border-radius: 4px;
    }
    
    .message-group:hover .reaction-btn,
    .reaction-btn:hover {
      opacity: 1;
      background: rgba(255,255,255,1) !important;
    }
    
    /* Sistema de reacciones */
    .message-reactions {
      margin-top: 6px;
      margin-bottom: 2px;
      display: flex;
      gap: 4px;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
      min-height: 20px;
    }
    
    /* Alinear reacciones a la derecha para mensajes enviados */
    .message-reactions.text-end {
      justify-content: flex-end;
    }
    
    /* Nombres de usuario debajo del avatar */
    .user-name {
      font-size: 10px;
      text-align: center;
      white-space: nowrap;
      max-width: 50px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    /* Contador de mensajes no leídos en lista de chats */
    .list-group-item:has(.badge.bg-danger) {
      background-color: #f8f9ff;
      border-left: 3px solid #dc3545;
    }
    
    .list-group-item .badge.bg-danger {
      animation: pulse-badge 2s infinite;
      box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
    }
    
    @keyframes pulse-badge {
      0%, 100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
      }
      50% {
        box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
      }
    }
    
    /* Hover effect mejorado para items con mensajes no leídos */
    .list-group-item:hover {
      background-color: #e9ecef;
      transform: translateX(3px);
      transition: all 0.2s ease;
    }
    
    .reaction-item {
      background: rgba(0,0,0,0.05);
      border-radius: 12px;
      padding: 3px 8px;
      font-size: 12px;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      border: 1px solid transparent;
      cursor: pointer;
      transition: all 0.2s ease;
      margin: 1px;
    }
    
    .reaction-item:hover {
      background: rgba(0,0,0,0.1);
      transform: scale(1.05);
    }
    
    .reaction-item.user-reacted {
      background: rgba(0, 123, 255, 0.1);
      border: 1px solid rgba(0, 123, 255, 0.3);
      color: #007bff;
      font-weight: 600;
    }
    
    .reaction-menu {
      position: fixed;
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 25px;
      padding: 6px 10px;
      box-shadow: 0 2px 15px rgba(0,0,0,0.2);
      z-index: 2000;
      display: none;
      backdrop-filter: blur(5px);
    }
    
    .reaction-option {
      background: none;
      border: none;
      font-size: 22px;
      padding: 6px 8px;
      margin: 0 1px;
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }
    
    .reaction-option:hover {
      transform: scale(1.3);
      background: rgba(0,0,0,0.08);
    }
    
    .reaction-option:active {
      transform: scale(1.1);
    }
    
    /* Grabadora de voz */
    .voice-recorder {
      border-top: 1px solid #e9ecef;
      background: #f8f9fa;
    }
    
    .recording-animation {
      font-size: 24px;
      animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
    
    .recording-text {
      color: #dc3545;
      font-weight: 600;
    }
    
    /* Vista previa de mensaje de voz */
    .voice-preview {
      border-top: 1px solid #e9ecef;
      background: #f8f9fa;
    }
    
    .voice-preview .bg-light {
      background-color: #e9ecef !important;
      border: 1px solid #dee2e6 !important;
    }
    
    #previewPlayBtn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    #previewPlayBtn i {
      font-size: 18px;
    }
    
        /* Mensajes de voz - Estructura base */
    .voice-message {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      width: 100%;
    }

    /* Botón de play - Por defecto para mensajes recibidos */
    .voice-play-btn {
      width: 32px;
      height: 32px;
      min-width: 32px;
      border-radius: 50%;
      border: none;
      background: rgba(0,0,0,0.15);
      color: #333;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
    }

    /* Botón de play para mensajes enviados */
    .message-bubble.sent .voice-play-btn {
      background: rgba(255,255,255,0.3);
      color: white;
    }

    /* Barra de onda - Por defecto para mensajes recibidos */
    .voice-waveform {
      flex: 1;
      min-width: 60px;
      height: 24px;
      background: rgba(0,0,0,0.1);
      border-radius: 12px;
      position: relative;
      overflow: hidden;
    }

    .voice-waveform::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 6px;
      right: 6px;
      height: 3px;
      background: rgba(0,0,0,0.4);
      transform: translateY(-50%);
      border-radius: 2px;
    }

    /* Barra de onda para mensajes enviados */
    .message-bubble.sent .voice-waveform {
      background: rgba(255,255,255,0.25);
    }

    .message-bubble.sent .voice-waveform::after {
      background: rgba(255,255,255,0.6);
    }

    /* Duración - Por defecto para mensajes recibidos */
    .voice-duration {
      font-size: 11px;
      font-weight: 500;
      color: rgba(0,0,0,0.7);
      flex-shrink: 0;
      min-width: 35px;
      text-align: right;
    }

    /* Duración para mensajes enviados */
    .message-bubble.sent .voice-duration {
      color: rgba(255,255,255,0.9);
    }
    /* Botones de eliminar mensaje */
    .message-content {
      flex: 1;
    }
    
    .message-actions {
      display: flex;
      align-items: center;
      margin-left: 8px;
      opacity: 1;
      transition: opacity 0.2s;
    }
    
    .btn-delete-message {
      background: none;
      border: none;
      color: rgba(255,255,255,0.6);
      cursor: pointer;
      padding: 4px;
      border-radius: 50%;
      width: 28px;
      height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    
    .btn-delete-message:hover {
      background: rgba(255,255,255,0.1);
      color: #ff4757;
    }
    
    /* Modal de eliminación tipo WhatsApp */
    .delete-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    
    .delete-modal {
      background: white;
      border-radius: 8px;
      padding: 0;
      min-width: 300px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      animation: modalSlideIn 0.3s ease;
    }
    
    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: scale(0.8);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }
    
    @keyframes messageSlideOut {
      from {
        opacity: 1;
        transform: translateX(0);
        max-height: 100px;
      }
      to {
        opacity: 0;
        transform: translateX(-100%);
        max-height: 0;
      }
    }
    
    .delete-modal-header {
      padding: 20px 20px 10px;
      border-bottom: 1px solid #eee;
    }
    
    .delete-modal-header h4 {
      margin: 0;
      font-size: 18px;
      color: #333;
    }
    
    .delete-modal-content {
      padding: 10px 0;
    }
    
    .delete-option {
      width: 100%;
      padding: 15px 20px;
      border: none;
      background: white;
      text-align: left;
      cursor: pointer;
      font-size: 16px;
      color: #333;
      transition: background 0.2s;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .delete-option:hover {
      background: #f5f5f5;
    }
    
    .delete-option.cancel {
      color: #666;
      border-top: 1px solid #eee;
    }
    
    /* Context menu */
    .context-menu {
      position: fixed;
      background: white;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 8px 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 2000;
      display: none;
      min-width: 150px;
    }
    
    .context-menu-item {
      padding: 8px 16px;
      cursor: pointer;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      font-size: 14px;
    }
    
    .context-menu-item:hover {
      background: #f8f9fa;
    }
    
    /* Arreglar dropdown menu de 3 puntos - Solución definitiva */
    
    /* Asegurar overflow visible en todos los contenedores */
    #chats-panel,
    #solicitudes-panel,
    .tab-pane,
    .tab-content,
    .card,
    .card-body,
    #chats-panel ul,
    #solicitudes-panel ul,
    .list-group {
      overflow: visible !important;
    }
    
    /* Cada list-group-item debe tener overflow visible */
    .list-group-item {
      overflow: visible !important;
      position: relative;
    }
    
    /* Elevar z-index cuando el dropdown está abierto */
    .list-group-item:has(.dropdown.show) {
      z-index: 1060 !important;
    }
    
    /* Asegurar que el dropdown-menu tenga z-index alto */
    .dropdown-menu {
      z-index: 1070 !important;
      position: absolute !important;
    }
  </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <!-- 📌 Listado de amigos y solicitudes de mensaje -->
      <div class="card shadow-lg mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
          <a href="/converza/app/view/index.php" class="btn btn-light btn-sm">
            <i class="fa fa-arrow-left"></i> Volver
          </a>
          <span><i class="bi bi-chat-dots"></i> Mensajes</span>
          <div></div>
        </div>
        
        <!-- Tabs: Chats | Solicitudes | Archivados | Bloqueados -->
        <ul class="nav nav-tabs" id="chatTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="chats-tab" data-bs-toggle="tab" data-bs-target="#chats-panel" type="button" role="tab">
              <i class="bi bi-chat-dots"></i> Chats
              <span class="badge bg-primary ms-1"><?php echo count($amigos); ?></span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link position-relative" id="solicitudes-tab" data-bs-toggle="tab" data-bs-target="#solicitudes-panel" type="button" role="tab">
              <i class="bi bi-envelope"></i> Solicitudes
              <?php if ($countSolicitudesMensaje > 0): ?>
              <span class="badge bg-danger ms-1"><?php echo $countSolicitudesMensaje; ?></span>
              <?php endif; ?>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="archivados-tab" data-bs-toggle="tab" data-bs-target="#archivados-panel" type="button" role="tab">
              <i class="bi bi-archive"></i> Archivados
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="bloqueados-tab" data-bs-toggle="tab" data-bs-target="#bloqueados-panel" type="button" role="tab">
              <i class="bi bi-shield-x"></i> Bloqueados
            </button>
          </li>
        </ul>
        
        <div class="tab-content" id="chatTabsContent">
          <!-- Panel: Chats -->
          <div class="tab-pane fade show active p-3" id="chats-panel" role="tabpanel">
            <?php if($amigos): ?>
              <ul class="list-group">
                <?php foreach($amigos as $am): ?>
                  <li class="list-group-item d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                      <img src="/converza/public/avatars/<?php echo $am['avatar']; ?>" 
                           width="40" height="40" class="rounded-circle me-2">
                      <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                          <span class="fw-bold"><?php echo htmlspecialchars($am['usuario']); ?></span>
                          <?php if ($am['mensajes_no_leidos'] > 0): ?>
                            <span class="badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                              <?php echo $am['mensajes_no_leidos'] > 9 ? '9+' : $am['mensajes_no_leidos']; ?>
                            </span>
                          <?php endif; ?>
                        </div>
                        <small class="text-muted">
                          <?php if ($am['tipo_relacion'] === 'amigo'): ?>
                            <i class="bi bi-people-fill"></i> Amigo
                          <?php elseif ($am['tipo_relacion'] === 'seguidor_mutuo'): ?>
                            <i class="bi bi-arrow-left-right"></i> Seguidor mutuo
                          <?php else: ?>
                            <i class="bi bi-check-circle"></i> Solicitud aceptada
                          <?php endif; ?>
                        </small>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <a href="chat.php?id=<?php echo $am['id_use']; ?>" 
                         class="btn btn-sm btn-primary">
                        <i class="bi bi-chat-dots"></i> Chatear
                      </a>
                      <!-- Menú de opciones -->
                      <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item" href="perfil.php?id=<?php echo $am['id_use']; ?>">
                              <i class="bi bi-person-circle"></i> Ver perfil
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="#" onclick="archivarConversacion(<?php echo $am['id_use']; ?>); return false;">
                              <i class="bi bi-archive"></i> Archivar
                            </a>
                          </li>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <a class="dropdown-item text-danger" href="#" onclick="bloquearUsuarioDesdeChat(<?php echo $am['id_use']; ?>, '<?php echo htmlspecialchars($am['usuario']); ?>'); return false;">
                              <i class="bi bi-shield-x"></i> Bloquear
                            </a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> No tienes conversaciones activas todavía.
              </div>
            <?php endif; ?>
          </div>
          
          <!-- Panel: Solicitudes de Mensaje -->
          <div class="tab-pane fade p-3" id="solicitudes-panel" role="tabpanel">
            <?php if($solicitudesMensaje): ?>
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> 
                Tienes <strong><?php echo $countSolicitudesMensaje; ?></strong> solicitud<?php echo $countSolicitudesMensaje > 1 ? 'es' : ''; ?> de mensaje pendiente<?php echo $countSolicitudesMensaje > 1 ? 's' : ''; ?>.
              </div>
              <ul class="list-group">
                <?php foreach($solicitudesMensaje as $sol): ?>
                  <li class="list-group-item">
                    <div class="d-flex align-items-start mb-3">
                      <img src="/converza/public/avatars/<?php echo $sol['avatar']; ?>" 
                           width="48" height="48" class="rounded-circle me-3">
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                          <div>
                            <a href="/Converza/app/presenters/perfil.php?id=<?php echo $sol['de']; ?>" class="fw-bold text-decoration-none">
                              <?php echo htmlspecialchars($sol['usuario']); ?>
                              <?php if ($sol['verificado']): ?>
                                <i class="bi bi-patch-check-fill text-primary"></i>
                              <?php endif; ?>
                            </a>
                            <br>
                            <small class="text-muted">
                              <i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])); ?>
                            </small>
                          </div>
                        </div>
                        <div class="mt-2 p-3 bg-light rounded">
                          <i class="bi bi-chat-quote"></i> 
                          <em><?php echo htmlspecialchars($sol['primer_mensaje']); ?></em>
                        </div>
                      </div>
                    </div>
                    <div class="d-flex gap-2">
                      <button class="btn btn-success btn-sm flex-fill" onclick="gestionarSolicitudMensaje(<?php echo $sol['id']; ?>, 'aceptar', this)">
                        <i class="bi bi-check-circle"></i> Aceptar
                      </button>
                      <button class="btn btn-danger btn-sm flex-fill" onclick="gestionarSolicitudMensaje(<?php echo $sol['id']; ?>, 'rechazar', this)">
                        <i class="bi bi-x-circle"></i> Rechazar
                      </button>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="alert alert-info mb-0">
                <i class="bi bi-inbox"></i> No tienes solicitudes de mensaje pendientes.
              </div>
            <?php endif; ?>
          </div>
          
          <!-- Panel: Archivados -->
          <div class="tab-pane fade p-3" id="archivados-panel" role="tabpanel">
            <?php
            // Obtener chats archivados
            $stmtArchivados = $conexion->prepare("
              SELECT u.id_use, u.usuario, u.avatar, u.nombre,
                     ca.fecha_archivado,
                     (SELECT mensaje FROM chats 
                      WHERE (de = :sess1 AND para = u.id_use) 
                         OR (de = u.id_use AND para = :sess2)
                      ORDER BY fecha DESC LIMIT 1) as ultimo_mensaje,
                     (SELECT fecha FROM chats 
                      WHERE (de = :sess3 AND para = u.id_use) 
                         OR (de = u.id_use AND para = :sess4)
                      ORDER BY fecha DESC LIMIT 1) as fecha_ultimo_mensaje
              FROM chats_archivados ca
              INNER JOIN usuarios u ON ca.chat_con_usuario_id = u.id_use
              WHERE ca.usuario_id = :sess5
              ORDER BY ca.fecha_archivado DESC
            ");
            $stmtArchivados->execute([
              ':sess1' => $sess,
              ':sess2' => $sess,
              ':sess3' => $sess,
              ':sess4' => $sess,
              ':sess5' => $sess
            ]);
            $chatsArchivados = $stmtArchivados->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG: Verificar qué se está obteniendo
            ?>
            
            <!-- DEBUG: Total archivados = <?php echo count($chatsArchivados); ?> -->
            
            <?php if($chatsArchivados && count($chatsArchivados) > 0): ?>
              <ul class="list-group">
                <?php foreach($chatsArchivados as $arch): ?>
                  <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <?php
                        $avatarArch = htmlspecialchars($arch['avatar']);
                        $avatarPathArch = __DIR__.'/../../public/avatars/'.$avatarArch;
                        if ($avatarArch && $avatarArch !== 'default_avatar.svg' && file_exists($avatarPathArch)) {
                          echo '<img src="/converza/public/avatars/'.$avatarArch.'" class="rounded-circle" width="50" height="50" alt="Avatar">';
                        } else {
                          echo '<img src="/converza/public/avatars/defect.jpg" class="rounded-circle" width="50" height="50" alt="Avatar">';
                        }
                        ?>
                        <div class="flex-grow-1">
                          <div class="fw-bold"><?php echo htmlspecialchars($arch['usuario']); ?></div>
                          <?php if ($arch['ultimo_mensaje']): ?>
                            <small class="text-muted">
                              <?php 
                              $ultMsgArch = htmlspecialchars($arch['ultimo_mensaje']);
                              echo strlen($ultMsgArch) > 40 ? substr($ultMsgArch, 0, 40).'...' : $ultMsgArch;
                              ?>
                            </small>
                          <?php endif; ?>
                          <div class="text-muted small">
                            <i class="bi bi-archive-fill"></i> Archivado: 
                            <?php 
                            $fechaArch = new DateTime($arch['fecha_archivado']);
                            echo $fechaArch->format('d/m/Y H:i');
                            ?>
                          </div>
                        </div>
                      </div>
                      <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-success btn-sm" onclick="desarchivarConversacion(<?php echo $arch['id_use']; ?>); return false;" title="Desarchivar">
                          <i class="bi bi-arrow-up-circle"></i> Desarchivar
                        </button>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="alert alert-info mb-0">
                <i class="bi bi-archive"></i> No tienes conversaciones archivadas.
              </div>
            <?php endif; ?>
          </div>
          
          <!-- Panel: Bloqueados -->
          <div class="tab-pane fade p-3" id="bloqueados-panel" role="tabpanel">
            <?php
            // Obtener usuarios bloqueados
            $stmtBloqueados = $conexion->prepare("
              SELECT u.id_use, u.usuario, u.avatar, u.nombre, b.fecha_bloqueo
              FROM bloqueos b
              INNER JOIN usuarios u ON b.bloqueado_id = u.id_use
              WHERE b.bloqueador_id = :sess
              ORDER BY b.fecha_bloqueo DESC
            ");
            $stmtBloqueados->execute([':sess' => $sess]);
            $usuariosBloqueados = $stmtBloqueados->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if($usuariosBloqueados && count($usuariosBloqueados) > 0): ?>
              <ul class="list-group">
                <?php foreach($usuariosBloqueados as $bloq): ?>
                  <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-2 flex-grow-1">
                        <?php
                        $avatarBloq = htmlspecialchars($bloq['avatar']);
                        $avatarPathBloq = __DIR__.'/../../public/avatars/'.$avatarBloq;
                        if ($avatarBloq && $avatarBloq !== 'default_avatar.svg' && file_exists($avatarPathBloq)) {
                          echo '<img src="/converza/public/avatars/'.$avatarBloq.'" class="rounded-circle" width="50" height="50" alt="Avatar">';
                        } else {
                          echo '<img src="/converza/public/avatars/defect.jpg" class="rounded-circle" width="50" height="50" alt="Avatar">';
                        }
                        ?>
                        <div class="flex-grow-1">
                          <div class="fw-bold"><?php echo htmlspecialchars($bloq['usuario']); ?></div>
                          <small class="text-muted">
                            <?php echo htmlspecialchars($bloq['nombre']); ?>
                          </small>
                          <div class="text-muted small">
                            <i class="bi bi-shield-x"></i> Bloqueado: 
                            <?php 
                            $fechaBloq = new DateTime($bloq['fecha_bloqueo']);
                            echo $fechaBloq->format('d/m/Y H:i');
                            ?>
                          </div>
                        </div>
                      </div>
                      <div class="d-flex gap-2 align-items-center">
                        <a href="perfil.php?id=<?php echo $bloq['id_use']; ?>" class="btn btn-outline-primary btn-sm" title="Ver perfil">
                          <i class="bi bi-person-circle"></i>
                        </a>
                        <button class="btn btn-warning btn-sm" onclick="desbloquearUsuario(<?php echo $bloq['id_use']; ?>, '<?php echo htmlspecialchars($bloq['usuario']); ?>'); return false;" title="Desbloquear">
                          <i class="bi bi-unlock"></i> Desbloquear
                        </button>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="alert alert-info mb-0">
                <i class="bi bi-shield-check"></i> No tienes usuarios bloqueados.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- 📌 Ventana de chat -->
      <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
          <i class="bi bi-chat-dots"></i> Chat
        </div>
        <div class="card-body chat-container">
          <div class="chat-messages">
          <?php if($user == 0): ?>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Selecciona un amigo para iniciar un chat.
            </div>
          <?php else: ?>
            <?php
            // Verificar si el usuario está bloqueado (en cualquier dirección)
            $stmtCheckBloqueo = $conexion->prepare("
              SELECT 1 FROM bloqueos 
              WHERE (bloqueador_id = :sess1 AND bloqueado_id = :user1)
                 OR (bloqueador_id = :user2 AND bloqueado_id = :sess2)
            ");
            $stmtCheckBloqueo->execute([
              ':sess1' => $sess,
              ':user1' => $user,
              ':user2' => $user,
              ':sess2' => $sess
            ]);
            $estaBloqueado = $stmtCheckBloqueo->fetch();
            
            // Verificar si el usuario está archivado
            $stmtCheckArchivado = $conexion->prepare("
              SELECT 1 FROM chats_archivados 
              WHERE usuario_id = :sess AND chat_con_usuario_id = :user
            ");
            $stmtCheckArchivado->execute([':sess' => $sess, ':user' => $user]);
            $estaArchivado = $stmtCheckArchivado->fetch();
            
            if ($estaBloqueado): ?>
              <div class="alert alert-warning">
                <i class="bi bi-shield-x"></i> Este chat no está disponible. El usuario ha sido bloqueado.
              </div>
            <?php elseif ($estaArchivado): ?>
              <div class="alert alert-info">
                <i class="bi bi-archive"></i> Esta conversación está archivada. Ve a la sección "Archivados" para verla.
              </div>
            <?php else: ?>
            <?php
            // Marcar mensajes como leídos
            $stmtMarkRead = $conexion->prepare(
              "UPDATE chats SET leido = 1 
               WHERE de = :user_from AND para = :sess AND leido = 0"
            );
            $stmtMarkRead->execute([
              ':user_from' => $user,
              ':sess' => $sess
            ]);

            // Crear tabla mensajes_eliminados si no existe
            try {
              $conexion->exec("CREATE TABLE IF NOT EXISTS mensajes_eliminados (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  mensaje_id INT NOT NULL,
                  usuario_id INT NOT NULL,
                  fecha_eliminacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                  INDEX idx_mensaje (mensaje_id),
                  INDEX idx_usuario (usuario_id),
                  UNIQUE KEY unique_user_message (mensaje_id, usuario_id),
                  FOREIGN KEY (mensaje_id) REFERENCES chats(id_cha) ON DELETE CASCADE,
                  FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
              )");
            } catch (Exception $e) {
              error_log("Error creando tabla mensajes_eliminados: " . $e->getMessage());
            }

            $stmt = $conexion->prepare(
              "SELECT c.* FROM chats c
               WHERE ((c.de = ? AND c.para = ?) 
                  OR (c.de = ? AND c.para = ?))
                  AND NOT EXISTS (
                      SELECT 1 FROM mensajes_eliminados me 
                      WHERE me.mensaje_id = c.id_cha AND me.usuario_id = ?
                  )
               ORDER BY c.id_cha ASC"
            );
            $stmt->execute([$user, $sess, $sess, $user, $sess]);
            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($chats as $ch) {
              $var = ($ch['de'] == $user) ? $user : $sess;
              $stmtU = $conexion->prepare("SELECT * FROM usuarios WHERE id_use = :id");
              $stmtU->execute([':id' => $var]);
              $us = $stmtU->fetch(PDO::FETCH_ASSOC);
              
              // Obtener reacciones del mensaje
              $stmtReactions = $conexion->prepare("
                  SELECT 
                      cr.tipo_reaccion,
                      COUNT(*) as total,
                      GROUP_CONCAT(u.usuario SEPARATOR ', ') as usuarios,
                      MAX(CASE WHEN cr.usuario_id = :user_id THEN 1 ELSE 0 END) as user_reacted
                  FROM chat_reacciones cr
                  INNER JOIN usuarios u ON cr.usuario_id = u.id_use
                  WHERE cr.mensaje_id = :mensaje_id
                  GROUP BY cr.tipo_reaccion
                  ORDER BY total DESC
              ");
              $stmtReactions->execute([
                  ':mensaje_id' => $ch['id_cha'],
                  ':user_id' => $_SESSION['id']
              ]);
              $reactions = $stmtReactions->fetchAll(PDO::FETCH_ASSOC);

              if ($ch['de'] == $user) {
                // Mensaje del otro usuario (izquierda)
                $fechaCompleta = date('d/m/Y H:i', strtotime($ch['fecha']));
                echo '
                <div class="d-flex mb-3 align-items-end message-group" data-message-id="'.$ch['id_cha'].'">
                  <div class="d-flex flex-column align-items-center me-2">
                    <img src="/converza/public/avatars/'.$us['avatar'].'" 
                         class="rounded-circle" width="40" height="40">
                    <small class="text-muted mt-1 user-name">'.htmlspecialchars($us['usuario']).'</small>
                  </div>
                  <div class="message-container">
                    <div class="message-bubble received" oncontextmenu="showMessageMenu(event, '.$ch['id_cha'].', false)">';
                
                // Mostrar contenido según el tipo de mensaje
                if ($ch['tipo_mensaje'] == 'voz') {
                  echo '
                      <div class="voice-message">
                        <button class="voice-play-btn" onclick="playVoiceMessage(\'/Converza/public/voice_messages/'.$ch['archivo_audio'].'\', this)">
                          <i class="bi bi-play-fill"></i>
                        </button>
                        <div class="voice-waveform"></div>
                        <span class="voice-duration">'.($ch['duracion_audio'] ? gmdate("i:s", $ch['duracion_audio']) : "0:00").'</span>
                      </div>';
                } else {
                  echo htmlspecialchars($ch['mensaje']);
                }
                
                echo '
                    </div>
                    
                    <div class="message-reactions" id="reactions_'.$ch['id_cha'].'">';
                
                foreach($reactions as $reaction) {
                  $userReacted = isset($reaction['user_reacted']) && $reaction['user_reacted'] ? 'user-reacted' : '';
                  echo '<span class="reaction-item '.$userReacted.'" title="'.$reaction['usuarios'].'" onclick="toggleReactionDirect('.$ch['id_cha'].', \''.$reaction['tipo_reaccion'].'\')">'.$reaction['tipo_reaccion'].' '.$reaction['total'].'</span>';
                }
                
                echo '
                    </div>
                    <div class="message-actions">
                      <small class="text-muted message-time">'.$fechaCompleta.'</small>
                      <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary reaction-btn" onclick="toggleReactionMenu('.$ch['id_cha'].')" title="Reaccionar">
                          😊
                        </button>
                        <button class="btn btn-sm btn-outline-danger reaction-btn" onclick="deleteMessage('.$ch['id_cha'].', '.strtotime($ch['fecha']).', false)" title="Eliminar mensaje">
                          <i class="bi bi-trash3"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>';
              } else {
                // Mensaje propio (derecha)
                $estadoMensaje = $ch['leido'] == 1 ? 
                  '<i class="bi bi-check2-all text-info ms-1" title="Leído"></i>' : 
                  '<i class="bi bi-check2 text-muted ms-1" title="Enviado"></i>';
                
                $fechaCompleta = date('d/m/Y H:i', strtotime($ch['fecha']));
                echo '
                <div class="d-flex flex-row-reverse mb-3 align-items-end message-group" data-message-id="'.$ch['id_cha'].'">
                  <div class="d-flex flex-column align-items-center ms-2">
                    <img src="/converza/public/avatars/'.$us['avatar'].'" 
                         class="rounded-circle" width="40" height="40">
                    <small class="text-muted mt-1 user-name">'.htmlspecialchars($us['usuario']).'</small>
                  </div>
                  <div class="message-container">
                    <div class="message-bubble sent" oncontextmenu="showMessageMenu(event, '.$ch['id_cha'].', true)">
                      <div class="message-content">';
                
                if ($ch['tipo_mensaje'] == 'voz') {
                  echo '
                      <div class="voice-message">
                        <button class="voice-play-btn" onclick="playVoiceMessage(\'/Converza/public/voice_messages/'.$ch['archivo_audio'].'\', this)">
                          <i class="bi bi-play-fill"></i>
                        </button>
                        <div class="voice-waveform"></div>
                        <span class="voice-duration">'.($ch['duracion_audio'] ? gmdate("i:s", $ch['duracion_audio']) : "0:00").'</span>
                      </div>';
                } else {
                  echo htmlspecialchars($ch['mensaje']);
                }
                
                echo '
                      </div>
                    </div>
                    
                    <div class="message-reactions text-end" id="reactions_'.$ch['id_cha'].'">';
                
                foreach($reactions as $reaction) {
                  $userReacted = isset($reaction['user_reacted']) && $reaction['user_reacted'] ? 'user-reacted' : '';
                  echo '<span class="reaction-item '.$userReacted.'" title="'.$reaction['usuarios'].'" onclick="toggleReactionDirect('.$ch['id_cha'].', \''.$reaction['tipo_reaccion'].'\')">'.$reaction['tipo_reaccion'].' '.$reaction['total'].'</span>';
                }
                
                echo '
                    </div>
                    <div class="message-actions text-end">
                      <small class="text-muted message-time">'.$fechaCompleta.' '.$estadoMensaje.'</small>
                      <div class="d-flex gap-1 justify-content-end">
                        <button class="btn btn-sm btn-outline-secondary reaction-btn" onclick="toggleReactionMenu('.$ch['id_cha'].')" title="Reaccionar">
                          😊
                        </button>
                        <button class="btn btn-sm btn-outline-danger reaction-btn" onclick="deleteMessage('.$ch['id_cha'].', '.strtotime($ch['fecha']).', true)" title="Eliminar mensaje">
                          <i class="bi bi-trash3"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>';
              }
            }
            ?>
            <?php endif; // Cierre de: else (no bloqueado ni archivado) ?>
          <?php endif; // Cierre de: if($user == 0) ?>
          </div>
        </div>

        <?php if($user != 0 && !$estaBloqueado && !$estaArchivado): ?>
        <div class="card-footer bg-white">
          <form action="" method="post" class="d-flex align-items-center gap-2" id="messageForm">
            <button type="button" class="btn btn-outline-secondary" id="voiceBtn" onclick="toggleVoiceRecording()" title="Mensaje de voz">
              <i class="bi bi-mic"></i>
            </button>
            <input type="text" name="mensaje" id="messageInput" placeholder="Escribe un mensaje" 
                   class="form-control" required>
            <input type="hidden" name="tipo_mensaje" value="texto">
            <button type="submit" name="enviar" class="btn btn-primary">
              <i class="bi bi-send"></i>
            </button>
          </form>
          
          <div id="voiceRecorder" class="voice-recorder" style="display: none;">
            <div class="d-flex align-items-center justify-content-center gap-3 p-3">
              <div class="recording-animation">🎤</div>
              <span class="recording-text">Grabando... <span id="recordingTime">0:00</span></span>
              <button class="btn btn-danger" onclick="stopRecording()">
                <i class="bi bi-stop-circle"></i> Detener
              </button>
              <button class="btn btn-secondary" onclick="cancelRecording()">
                <i class="bi bi-x-circle"></i> Cancelar
              </button>
            </div>
          </div>
          
          <div id="voicePreview" class="voice-preview" style="display: none;">
            <div class="d-flex align-items-center justify-content-between p-3 bg-light border rounded">
              <div class="d-flex align-items-center gap-3">
                <button id="previewPlayBtn" class="btn btn-primary btn-sm" onclick="togglePreviewPlayback()">
                  <i class="bi bi-play-fill"></i>
                </button>
                <span class="text-muted">Mensaje de voz (<span id="previewDuration">0:00</span>)</span>
                <audio id="previewAudio" style="display: none;"></audio>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm" onclick="confirmSendVoiceMessage()">
                  <i class="bi bi-send"></i> Enviar
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteVoiceMessage()">
                  <i class="bi bi-trash"></i> Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <?php
          if(isset($_POST['enviar'])) {
              $mensaje = trim($_POST['mensaje']);
              $de = $_SESSION['id'];
              $para = $user;

              if(empty($mensaje) || !$de || !$para){
                echo "<div class='alert alert-danger mt-2'>⚠️ Error: faltan datos para enviar el mensaje.</div>";
              } else {
                $stmtC = $conexion->prepare(
                  "SELECT id_cch FROM c_chats WHERE (de = :de1 AND para = :para1) 
                      OR (de = :de2 AND para = :para2)"
                );
                $stmtC->execute([
                  ':de1'   => $de,
                  ':para1' => $para,
                  ':de2'   => $para,
                  ':para2' => $de
                ]);
                $com = $stmtC->fetch(PDO::FETCH_ASSOC);

                if($com && isset($com['id_cch'])) {
                  $id_cch = $com['id_cch'];

                  $stmtMsg = $conexion->prepare(
                    "INSERT INTO chats (id_cch,de,para,mensaje,fecha,leido) 
                    VALUES (:id_cch,:de,:para,:mensaje,NOW(),0)"
                  );
                  $stmtMsg->execute([
                    ':id_cch' => $id_cch,
                    ':de'     => $de,
                    ':para'   => $para,
                    ':mensaje'=> $mensaje
                  ]);

                  echo '<script>window.location="chat.php?usuario='.$para.'"</script>';
                } else {
                  echo "<div class='alert alert-danger mt-2'>⚠️ Error: no se encontró la conversación.</div>";
                }
              }
          }
        ?>  
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Variables para mensajes de voz
let mediaRecorder = null;
let audioChunks = [];
let isRecording = false;
let recordingTimer = null;
let recordingStartTime = 0;
let currentAudioBlob = null;
let previewAudio = null;
let isPreviewPlaying = false;
let recordingCancelled = false;

// Auto-scroll al final del chat
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    initReactionSystem();
    
    // Controlar visibilidad del chat según la pestaña activa
    const chatCards = document.querySelectorAll('.card.shadow-lg');
    let chatCard = null;
    chatCards.forEach(card => {
        if (card.querySelector('.chat-container')) {
            chatCard = card;
        }
    });
    
    const chatsTab = document.getElementById('chats-tab');
    const solicitudesTab = document.getElementById('solicitudes-tab');
    const archivadosTab = document.getElementById('archivados-tab');
    const bloqueadosTab = document.getElementById('bloqueados-tab');
    
    // Solo mostrar el chat si hay un usuario seleccionado
    const urlParams = new URLSearchParams(window.location.search);
    const hasUser = urlParams.has('id') || urlParams.has('usuario');
    
    function updateChatVisibility() {
        if (!chatCard) return;
        
        const chatsActive = chatsTab && chatsTab.classList.contains('active');
        
        // Mostrar chat solo si:
        // 1. La pestaña "Chats" está activa
        // 2. Y hay un usuario seleccionado
        if (chatsActive && hasUser) {
            chatCard.style.display = 'block';
        } else if (!chatsActive) {
            // Ocultar cuando no estamos en la pestaña Chats
            chatCard.style.display = 'none';
        } else if (!hasUser) {
            // Mostrar el placeholder si no hay usuario
            chatCard.style.display = 'block';
        }
    }
    
    // Ejecutar al cargar
    updateChatVisibility();
    
    // Escuchar cambios de pestaña
    if (chatsTab) {
        chatsTab.addEventListener('click', updateChatVisibility);
    }
    if (solicitudesTab) {
        solicitudesTab.addEventListener('click', updateChatVisibility);
    }
    if (archivadosTab) {
        archivadosTab.addEventListener('click', updateChatVisibility);
    }
    if (bloqueadosTab) {
        bloqueadosTab.addEventListener('click', updateChatVisibility);
    }
});

const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function() {
        setTimeout(() => {
            const chatMessages = document.querySelector('.chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }, 100);
    });
}

// Sistema de reacciones
function initReactionSystem() {
    const reactionMenu = document.createElement('div');
    reactionMenu.className = 'reaction-menu';
    reactionMenu.id = 'reactionMenu';
    reactionMenu.innerHTML = `
        <button class="reaction-option" onclick="addReaction('❤️')">❤️</button>
        <button class="reaction-option" onclick="addReaction('😂')">😂</button>
        <button class="reaction-option" onclick="addReaction('😮')">😮</button>
        <button class="reaction-option" onclick="addReaction('😢')">😢</button>
        <button class="reaction-option" onclick="addReaction('😡')">😡</button>
        <button class="reaction-option" onclick="addReaction('👍')">👍</button>
        <button class="reaction-option" onclick="addReaction('👎')">👎</button>
    `;
    document.body.appendChild(reactionMenu);
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.reaction-menu') && !e.target.closest('.reaction-btn')) {
            hideReactionMenu();
        }
    });
}

function toggleReactionMenu(messageId) {
    const menu = document.getElementById('reactionMenu');
    
    if (menu.style.display === 'block') {
        hideReactionMenu();
    } else {
        const messageGroup = document.querySelector(`[data-message-id="${messageId}"]`);
        const messageBubble = messageGroup.querySelector('.message-bubble');
        const rect = messageBubble.getBoundingClientRect();
        
        menu.style.display = 'block';
        menu.style.visibility = 'hidden';
        
        let leftPos = rect.left;
        let topPos = rect.top - menu.offsetHeight - 3;
        
        if (leftPos + menu.offsetWidth > window.innerWidth - 10) {
            leftPos = rect.right - menu.offsetWidth;
        }
        
        if (topPos < 10) {
            topPos = rect.bottom + 3;
        }
        
        menu.style.left = leftPos + 'px';
        menu.style.top = topPos + 'px';
        menu.style.visibility = 'visible';
        menu.dataset.messageId = messageId;
    }
}

function hideReactionMenu() {
    const menu = document.getElementById('reactionMenu');
    menu.style.display = 'none';
}

function addReaction(emoji) {
    const menu = document.getElementById('reactionMenu');
    const messageId = menu.dataset.messageId;
    
    fetch('/Converza/app/presenters/chat_reactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_reaction&mensaje_id=${messageId}&tipo_reaccion=${encodeURIComponent(emoji)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMessageReactions(messageId, data.reactions);
        } else {
            console.error('Error:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al agregar reacción:', error);
    });
    
    hideReactionMenu();
}

function updateMessageReactions(messageId, reactions) {
    const container = document.getElementById('reactions_' + messageId);
    container.innerHTML = '';
    
    reactions.forEach(reaction => {
        const reactionItem = document.createElement('span');
        reactionItem.className = 'reaction-item' + (reaction.user_reacted ? ' user-reacted' : '');
        reactionItem.title = reaction.usuarios;
        reactionItem.onclick = () => toggleReactionDirect(messageId, reaction.tipo_reaccion);
        reactionItem.innerHTML = `${reaction.tipo_reaccion} ${reaction.total}`;
        container.appendChild(reactionItem);
    });
}

function toggleReactionDirect(messageId, emoji) {
    fetch('/Converza/app/presenters/chat_reactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_reaction&mensaje_id=${messageId}&tipo_reaccion=${encodeURIComponent(emoji)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMessageReactions(messageId, data.reactions);
        } else {
            console.error('Error:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al cambiar reacción:', error);
    });
}

// Sistema de mensajes de voz
async function toggleVoiceRecording() {
    if (!isRecording) {
        await startRecording();
    } else {
        stopRecording();
    }
}

async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        
        mediaRecorder.ondataavailable = (event) => {
            audioChunks.push(event.data);
        };
        
        mediaRecorder.onstop = () => {
            if (!recordingCancelled) {
                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                showVoicePreview(audioBlob);
            } else {
                audioChunks = [];
                recordingCancelled = false;
            }
        };
        
        mediaRecorder.start();
        isRecording = true;
        recordingCancelled = false;
        
        document.getElementById('messageForm').style.display = 'none';
        document.getElementById('voiceRecorder').style.display = 'block';
        
        recordingStartTime = Date.now();
        recordingTimer = setInterval(updateRecordingTime, 1000);
        
    } catch (error) {
        console.error('Error al acceder al micrófono:', error);
        alert('No se pudo acceder al micrófono. Verifica los permisos.');
    }
}

function stopRecording() {
    if (mediaRecorder && isRecording) {
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
        isRecording = false;
        clearInterval(recordingTimer);
        
        document.getElementById('voiceRecorder').style.display = 'none';
    }
}

function cancelRecording() {
    if (mediaRecorder && isRecording) {
        recordingCancelled = true;
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
        isRecording = false;
        clearInterval(recordingTimer);
        audioChunks = [];
        
        document.getElementById('voiceRecorder').style.display = 'none';
        document.getElementById('messageForm').style.display = 'flex';
    }
}

function showVoicePreview(audioBlob) {
    currentAudioBlob = audioBlob;
    
    const audioUrl = URL.createObjectURL(audioBlob);
    previewAudio = document.getElementById('previewAudio');
    previewAudio.src = audioUrl;
    
    const duration = recordingStartTime ? Math.floor((Date.now() - recordingStartTime) / 1000) : 0;
    const minutes = Math.floor(duration / 60);
    const seconds = duration % 60;
    document.getElementById('previewDuration').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    document.getElementById('voicePreview').style.display = 'block';
    
    const playBtn = document.getElementById('previewPlayBtn');
    playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
    isPreviewPlaying = false;
}

function confirmSendVoiceMessage() {
    if (currentAudioBlob) {
        document.getElementById('voicePreview').style.display = 'none';
        document.getElementById('messageForm').style.display = 'flex';
        
        sendVoiceMessage(currentAudioBlob);
    }
}

function deleteVoiceMessage() {
    if (previewAudio) {
        previewAudio.pause();
        URL.revokeObjectURL(previewAudio.src);
        previewAudio.src = '';
    }
    
    currentAudioBlob = null;
    
    document.getElementById('voicePreview').style.display = 'none';
    document.getElementById('messageForm').style.display = 'flex';
    
    isPreviewPlaying = false;
    audioChunks = [];
}

function updateRecordingTime() {
    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    const timeText = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('recordingTime').textContent = timeText;
}

function togglePreviewPlayback() {
    if (!previewAudio || !previewAudio.src) {
        alert('No hay audio grabado para reproducir');
        return;
    }
    
    const playBtn = document.getElementById('previewPlayBtn');
    
    if (!isPreviewPlaying) {
        previewAudio.play().then(() => {
            playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
            isPreviewPlaying = true;
        }).catch(error => {
            console.error('Error al reproducir audio:', error);
            alert('No se puede reproducir el audio: ' + error.message);
        });
        
        previewAudio.onended = () => {
            playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            isPreviewPlaying = false;
        };
    } else {
        previewAudio.pause();
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        isPreviewPlaying = false;
    }
}

function sendVoiceMessage(audioBlob) {
    const paraUsuario = new URLSearchParams(window.location.search).get('usuario');
    
    if (!paraUsuario) {
        alert('Error: No se puede enviar el mensaje (usuario no encontrado)');
        return;
    }
    
    const formData = new FormData();
    formData.append('audio', audioBlob, 'voice_message.wav');
    formData.append('action', 'upload_voice');
    formData.append('para', paraUsuario);
    const duracion = recordingStartTime ? Math.floor((Date.now() - recordingStartTime) / 1000) : 0;
    formData.append('duracion', duracion);
    
    fetch('./upload_voice_new.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('El servidor no devolvió JSON válido');
            }
        });
    })
    .then(data => {
        if (data.success) {
            document.getElementById('voicePreview').style.display = 'none';
            document.getElementById('messageForm').style.display = 'flex';
            
            currentAudioBlob = null;
            if (previewAudio) {
                previewAudio.src = '';
            }
            
            setTimeout(() => {
                location.reload();
            }, 500);
            
        } else {
            alert('Error al enviar mensaje de voz: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar mensaje de voz: ' + error.message);
    });
}

// Reproducir mensajes de voz
let currentAudio = null;

function playVoiceMessage(audioUrl, buttonElement) {
    if (!buttonElement && window.event) {
        buttonElement = window.event.target.closest('.voice-play-btn');
    }
    
    if (currentAudio && currentAudio.src.includes(audioUrl) && !currentAudio.paused) {
        currentAudio.pause();
        if (buttonElement) {
            buttonElement.innerHTML = '<i class="bi bi-play-fill"></i>';
        }
        return;
    }
    
    if (currentAudio && !currentAudio.paused) {
        currentAudio.pause();
        document.querySelectorAll('.voice-play-btn').forEach(btn => {
            if (btn !== buttonElement) {
                btn.innerHTML = '<i class="bi bi-play-fill"></i>';
            }
        });
    }
    
    if (!currentAudio || !currentAudio.src.includes(audioUrl)) {
        currentAudio = new Audio(audioUrl);
    }
    
    currentAudio.play().then(() => {
        if (buttonElement) {
            buttonElement.innerHTML = '<i class="bi bi-pause-fill"></i>';
        }
    }).catch(error => {
        console.error('Error al reproducir audio:', error);
        alert('Error al reproducir mensaje de voz: ' + error.message);
    });
    
    currentAudio.onended = () => {
        if (buttonElement) {
            buttonElement.innerHTML = '<i class="bi bi-play-fill"></i>';
        }
    };
}

// Sistema de eliminación de mensajes
function deleteMessage(messageId, messageTimestamp, isOwner) {
    const ahoraTimestamp = Math.floor(Date.now() / 1000);
    const puedeEliminarParaTodos = isOwner;
    
    if (messageTimestamp <= 0) {
        alert('Error: Timestamp del mensaje inválido');
        return;
    }
    
    showDeleteModal(messageId, puedeEliminarParaTodos);
}

function showDeleteModal(messageId, canDeleteForEveryone) {
    const modal = document.createElement('div');
    modal.className = 'delete-modal-overlay';
    
    const deleteForEveryoneButton = canDeleteForEveryone ? 
        '<button class="delete-option" onclick="confirmDelete(' + messageId + ', \'for_everyone\')"><i class="bi bi-trash3"></i> Eliminar para todos</button>' : 
        '';
    
    modal.innerHTML = `
        <div class="delete-modal">
            <div class="delete-modal-header">
                <h4>Eliminar mensaje</h4>
            </div>
            <div class="delete-modal-content">
                ${deleteForEveryoneButton}
                <button class="delete-option" onclick="confirmDelete(${messageId}, 'for_me')"><i class="bi bi-eye-slash"></i> Eliminar para mí</button>
                <button class="delete-option cancel" onclick="closeDeleteModal()"><i class="bi bi-x-circle"></i> Cancelar</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeDeleteModal();
        }
    });
}

function closeDeleteModal() {
    const modal = document.querySelector('.delete-modal-overlay');
    if (modal) {
        document.body.removeChild(modal);
    }
}

function confirmDelete(messageId, deleteType) {
    closeDeleteModal();
    
    const confirmMsg = deleteType === 'for_everyone' ? 
        '¿Estás seguro de eliminar este mensaje para todos?' :
        '¿Estás seguro de eliminar este mensaje para ti?';
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    fetch('./delete_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            messageId: messageId,
            deleteType: deleteType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (deleteType === 'for_everyone') {
                location.reload();
            } else {
                const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                if (messageElement) {
                    messageElement.style.animation = 'messageSlideOut 0.3s ease';
                    setTimeout(() => {
                        messageElement.remove();
                    }, 300);
                }
            }
        } else {
            alert('Error al eliminar mensaje: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al eliminar mensaje');
    });
}

// Context menu
function showMessageMenu(event, messageId, isOwn) {
    event.preventDefault();
    
    let contextMenu = document.getElementById('contextMenu');
    if (!contextMenu) {
        contextMenu = document.createElement('div');
        contextMenu.className = 'context-menu';
        contextMenu.id = 'contextMenu';
        document.body.appendChild(contextMenu);
    }
    
    let menuItems = `
        <button class="context-menu-item" onclick="copyMessage(${messageId})">
            <i class="bi bi-clipboard"></i> Copiar mensaje
        </button>
        <button class="context-menu-item" onclick="replyToMessage(${messageId})">
            <i class="bi bi-reply"></i> Responder
        </button>
    `;
    
    if (isOwn) {
        menuItems += `
            <button class="context-menu-item" onclick="deleteMessage(${messageId})">
                <i class="bi bi-trash"></i> Eliminar
            </button>
        `;
    }
    
    contextMenu.innerHTML = menuItems;
    contextMenu.style.display = 'block';
    contextMenu.style.left = event.pageX + 'px';
    contextMenu.style.top = event.pageY + 'px';
    
    setTimeout(() => {
        document.addEventListener('click', function hideContextMenu() {
            contextMenu.style.display = 'none';
            document.removeEventListener('click', hideContextMenu);
        });
    }, 10);
}

function copyMessage(messageId) {
    console.log('Copiando mensaje:', messageId);
}

function replyToMessage(messageId) {
    console.log('Respondiendo a mensaje:', messageId);
}
</script>

<div data-current-user="<?php echo htmlspecialchars($_SESSION['usuario']); ?>" style="display: none;"></div>

<script src="/converza/app/presenters/chat_sync.js"></script>

<script>
// Sistema de sincronización
let ultimoMensajeId = 0;
let ultimaActualizacion = new Date().toISOString().slice(0, 19).replace('T', ' ');
let sincronizacionActiva = true;
let sesionUsuario = '<?php echo addslashes($_SESSION["usuario"]); ?>';

function obtenerUsuarioSesion() {
    const userElement = document.querySelector('[data-current-user]');
    if (userElement) {
        return userElement.getAttribute('data-current-user');
    }
    return sesionUsuario;
}

document.addEventListener('DOMContentLoaded', function() {
    sesionUsuario = obtenerUsuarioSesion();
    
    const mensajes = document.querySelectorAll('[data-message-id]');
    if (mensajes.length > 0) {
        const ids = Array.from(mensajes).map(msg => parseInt(msg.getAttribute('data-message-id')));
        ultimoMensajeId = Math.max(...ids);
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    let userId = urlParams.get('usuario');
    
    if (userId && sesionUsuario) {
        setInterval(sincronizarChat, 3000);
        setTimeout(sincronizarChat, 1000);
    }
});

function sincronizarChat() {
    if (!sincronizacionActiva) return;
    
    const urlParams = new URLSearchParams(window.location.search);
    let userId = urlParams.get('usuario');
    
    if (!userId) return;
    
    const url = `verificar_nuevos_mensajes.php?usuario=${userId}&ultimo_id=${ultimoMensajeId}&ultima_actualizacion=${encodeURIComponent(ultimaActualizacion)}`;
    
    fetch(url, { 
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            
            if (data.error) return;
            
            if (data.cambios) {
                if (data.nuevos_mensajes && data.nuevos_mensajes.length > 0) {
                    data.nuevos_mensajes.forEach(mensaje => {
                        if (document.querySelector(`[data-message-id="${mensaje.id_cha}"]`)) {
                            return;
                        }
                        
                        const mensajeHtml = crearHtmlMensajeCompleto(mensaje);
                        document.querySelector('.chat-messages').insertAdjacentHTML('beforeend', mensajeHtml);
                        
                        ultimoMensajeId = Math.max(ultimoMensajeId, parseInt(mensaje.id_cha));
                    });
                    
                    const chatContainer = document.querySelector('.chat-messages');
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            }
        } catch (e) {
            console.error('Error parseando JSON:', e);
        }
    })
    .catch(error => {
        console.error('Error en sincronización:', error);
    });
}

function crearHtmlMensajeCompleto(mensaje) {
    const fechaCompleta = new Date(mensaje.fecha).toLocaleDateString() + ' ' + 
                         new Date(mensaje.fecha).toLocaleTimeString().slice(0, 5);
    const esPropio = mensaje.de_usuario === sesionUsuario;
    
    if (esPropio) {
        const estadoMensaje = mensaje.leido == 1 ? 
            '<i class="bi bi-check2-all text-info ms-1" title="Leído"></i>' : 
            '<i class="bi bi-check2 text-muted ms-1" title="Enviado"></i>';
            
        return `
            <div class="d-flex flex-row-reverse mb-3 align-items-end message-group" data-message-id="${mensaje.id_cha}">
                <div class="d-flex flex-column align-items-center ms-2">
                    <img src="/converza/public/avatars/${mensaje.de_avatar}" class="rounded-circle" width="40" height="40">
                    <small class="text-muted mt-1 user-name">${mensaje.de_usuario}</small>
                </div>
                <div class="message-container">
                    <div class="message-bubble sent">
                        ${mensaje.mensaje}
                    </div>
                    <div class="message-reactions text-end" id="reactions_${mensaje.id_cha}"></div>
                    <div class="message-actions text-end">
                        <small class="text-muted message-time">${fechaCompleta} ${estadoMensaje}</small>
                        <div class="d-flex gap-1 justify-content-end">
                            <button class="btn btn-sm btn-outline-secondary reaction-btn" onclick="toggleReactionMenu(${mensaje.id_cha})" title="Reaccionar">😊</button>
                            <button class="btn btn-sm btn-outline-danger reaction-btn" onclick="deleteMessage(${mensaje.id_cha}, ${Math.floor(new Date(mensaje.fecha).getTime()/1000)}, true)" title="Eliminar mensaje"><i class="bi bi-trash3"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        return `
            <div class="d-flex mb-3 align-items-end message-group" data-message-id="${mensaje.id_cha}">
                <div class="d-flex flex-column align-items-center me-2">
                    <img src="/converza/public/avatars/${mensaje.de_avatar}" class="rounded-circle" width="40" height="40">
                    <small class="text-muted mt-1 user-name">${mensaje.de_usuario}</small>
                </div>
                <div class="message-container">
                    <div class="message-bubble received">
                        ${mensaje.mensaje}
                    </div>
                    <div class="message-reactions" id="reactions_${mensaje.id_cha}"></div>
                    <div class="message-actions">
                        <small class="text-muted message-time">${fechaCompleta}</small>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary reaction-btn" onclick="toggleReactionMenu(${mensaje.id_cha})" title="Reaccionar">😊</button>
                            <button class="btn btn-sm btn-outline-danger reaction-btn" onclick="deleteMessage(${mensaje.id_cha}, ${Math.floor(new Date(mensaje.fecha).getTime()/1000)}, false)" title="Eliminar mensaje"><i class="bi bi-trash3"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

// 📩 Gestionar solicitud de mensaje (Aceptar/Rechazar)
function gestionarSolicitudMensaje(idSolicitud, accion, buttonElement) {
    const textoAccion = accion === 'aceptar' ? 'aceptar' : 'rechazar';
    if (!confirm(`¿Estás seguro de que quieres ${textoAccion} esta solicitud de mensaje?`)) {
        return;
    }
    
    console.log('🔧 buttonElement recibido:', buttonElement);
    
    fetch('gestionar_solicitud_mensaje.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `solicitud_id=${idSolicitud}&accion=${accion}`
    })
    .then(r => r.json())
    .then(data => {
        console.log('📥 Respuesta completa del servidor:', data);
        console.log('📊 data.success:', data.success);
        console.log('👤 data.usuario:', data.usuario);
        
        if (data.success) {
            // Remover la solicitud de la lista visualmente usando el botón recibido
            const solicitudElement = buttonElement ? buttonElement.closest('.list-group-item') : null;
            console.log('📋 Elemento de solicitud encontrado:', solicitudElement);
            if (solicitudElement) {
                solicitudElement.style.transition = 'opacity 0.3s ease';
                solicitudElement.style.opacity = '0';
                setTimeout(() => {
                    solicitudElement.remove();
                    
                    // Actualizar contador de solicitudes
                    const badge = document.querySelector('#solicitudes-tab .badge');
                    if (badge) {
                        let count = parseInt(badge.textContent) || 0;
                        count--;
                        if (count > 0) {
                            badge.textContent = count;
                        } else {
                            badge.remove();
                        }
                    }
                    
                    // Verificar si quedan solicitudes
                    const solicitudesRestantes = document.querySelectorAll('#solicitudes-panel .list-group-item').length;
                    if (solicitudesRestantes === 0) {
                        document.querySelector('#solicitudes-panel ul').innerHTML = `
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-inbox"></i> No tienes solicitudes de mensaje pendientes.
                            </div>
                        `;
                    }
                    
                    // Si se aceptó, agregar a la lista y luego redirigir
                    if (accion === 'aceptar') {
                        console.log('✅ Solicitud aceptada');
                        
                        // Validar que tenemos la información del usuario
                        if (!data.usuario) {
                            console.error('⚠️ No se recibió información del usuario, redirigiendo directamente...');
                            // Obtener el ID del usuario desde el elemento de la solicitud
                            if (solicitudElement) {
                                const enlacePerfil = solicitudElement.querySelector('a[href*="perfil.php?id="]');
                                if (enlacePerfil) {
                                    const usuarioId = enlacePerfil.href.match(/id=(\d+)/)[1];
                                    console.log('🔍 ID de usuario extraído del DOM:', usuarioId);
                                    setTimeout(() => {
                                        window.location.href = 'chat.php?id=' + usuarioId;
                                    }, 500);
                                    return;
                                }
                            }
                            mostrarNotificacion('⚠️ Solicitud aceptada, pero recarga la página', 'warning');
                            setTimeout(() => location.reload(), 2000);
                            return;
                        }
                        
                        console.log('👤 Agregando usuario a lista:', data.usuario);
                        
                        // 1. Agregar el usuario a la lista de chats
                        agregarUsuarioAListaChats(data.usuario);
                        
                        // 2. Cambiar al tab de chats
                        setTimeout(() => {
                            const chatsTab = document.querySelector('button[data-bs-target="#chats-panel"]');
                            if (chatsTab) {
                                const tab = new bootstrap.Tab(chatsTab);
                                tab.show();
                            }
                            
                            // 3. Redirigir después de un breve delay para que el usuario vea el cambio
                            setTimeout(() => {
                                console.log('🚀 Redirigiendo a chat con usuario:', data.usuario.id_use);
                                window.location.href = 'chat.php?id=' + data.usuario.id_use;
                            }, 800);
                        }, 300);
                    } else if (accion === 'rechazar') {
                        // Solo mostrar notificación si se rechazó
                        mostrarNotificacion('✓ Solicitud rechazada correctamente.', 'success');
                    }
                }, 300);
            }
        } else {
            console.error('❌ Error del servidor:', data);
            mostrarNotificacion('❌ Error: ' + (data.error || data.mensaje || 'Error desconocido'), 'error');
        }
    })
    .catch(err => {
        console.error('💥 Error de red o parsing:', err);
        console.error('Stack trace:', err.stack);
        mostrarNotificacion('❌ Error al procesar la solicitud: ' + err.message, 'error');
    });
}

// Función para agregar un usuario a la lista de chats dinámicamente
function agregarUsuarioAListaChats(usuario) {
    console.log('🔧 agregarUsuarioAListaChats() llamada con:', usuario);
    
    if (!usuario || !usuario.id_use) {
        console.error('❌ Usuario inválido o sin id_use:', usuario);
        return;
    }
    
    const chatsPanel = document.querySelector('#chats-panel ul');
    console.log('📋 Panel de chats encontrado:', chatsPanel);
    
    // Si no existe la lista (estaba vacía), crearla
    if (!chatsPanel) {
        const panelDiv = document.querySelector('#chats-panel');
        const alertInfo = panelDiv.querySelector('.alert-info');
        if (alertInfo) {
            alertInfo.remove();
        }
        const newList = document.createElement('ul');
        newList.className = 'list-group';
        panelDiv.appendChild(newList);
    }
    
    // Obtener el icono según el tipo de relación
    let iconoRelacion = '<i class="bi bi-check-circle"></i> Solicitud aceptada';
    if (usuario.tipo_relacion === 'amigo') {
        iconoRelacion = '<i class="bi bi-people-fill"></i> Amigo';
    } else if (usuario.tipo_relacion === 'seguidor_mutuo') {
        iconoRelacion = '<i class="bi bi-arrow-left-right"></i> Seguidor mutuo';
    }
    
    // Crear el elemento del nuevo usuario
    const nuevoItem = document.createElement('li');
    nuevoItem.className = 'list-group-item d-flex align-items-center justify-content-between';
    nuevoItem.style.opacity = '0';
    nuevoItem.innerHTML = `
        <div class="d-flex align-items-center flex-grow-1">
            <img src="/converza/public/avatars/${usuario.avatar}" 
                 width="40" height="40" class="rounded-circle me-2">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold">${usuario.usuario}</span>
                </div>
                <small class="text-muted">
                    ${iconoRelacion}
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="chat.php?id=${usuario.id_use}" 
               class="btn btn-sm btn-primary">
                <i class="bi bi-chat-dots"></i> Chatear
            </a>
            <!-- Menú de opciones -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="perfil.php?id=${usuario.id_use}">
                            <i class="bi bi-person-circle"></i> Ver perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="archivarConversacion(${usuario.id_use}); return false;">
                            <i class="bi bi-archive"></i> Archivar
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="#" onclick="bloquearUsuarioDesdeChat(${usuario.id_use}, '${usuario.usuario}'); return false;">
                            <i class="bi bi-shield-x"></i> Bloquear
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    `;
    
    // Agregar al inicio de la lista con animación
    const listaChats = document.querySelector('#chats-panel ul');
    listaChats.insertBefore(nuevoItem, listaChats.firstChild);
    
    // Animar la aparición
    setTimeout(() => {
        nuevoItem.style.transition = 'opacity 0.5s ease';
        nuevoItem.style.opacity = '1';
    }, 100);
    
    // Actualizar el contador del tab de chats
    const badgeChats = document.querySelector('#chats-tab .badge');
    if (badgeChats) {
        let count = parseInt(badgeChats.textContent) || 0;
        badgeChats.textContent = count + 1;
    }
    
    // Remover el badge "Nuevo" después de 5 segundos
    setTimeout(() => {
        const badgeNuevo = nuevoItem.querySelector('.badge-success');
        if (badgeNuevo) {
            badgeNuevo.style.transition = 'opacity 0.3s ease';
            badgeNuevo.style.opacity = '0';
            setTimeout(() => badgeNuevo.remove(), 300);
        }
    }, 5000);
}

// Función para bloquear usuario desde el chat
function bloquearUsuarioDesdeChat(usuarioId, nombreUsuario) {
    if (!confirm(`¿Estás seguro de que quieres bloquear a ${nombreUsuario}?\n\nAl bloquear a este usuario:\n• No podrá ver tu perfil\n• No podrá enviarte mensajes\n• No podrá interactuar contigo de ninguna forma\n• Solo podrás desbloquearlo desde su perfil`)) {
        return;
    }
    
    fetch('bloquear_usuario.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `usuario_id=${usuarioId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(`✓ Usuario bloqueado correctamente. ${nombreUsuario} ya no podrá interactuar contigo.`, 'success');
            
            // Remover al usuario de la lista de chats visualmente
            const listItems = document.querySelectorAll('#chats-panel .list-group-item');
            listItems.forEach(item => {
                const nombreEnItem = item.querySelector('.fw-bold');
                if (nombreEnItem && nombreEnItem.textContent.trim() === nombreUsuario) {
                    item.style.transition = 'opacity 0.3s ease';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                        
                        // Verificar si quedan usuarios en la lista
                        const itemsRestantes = document.querySelectorAll('#chats-panel .list-group-item').length;
                        if (itemsRestantes === 0) {
                            const listaChats = document.querySelector('#chats-panel ul');
                            if (listaChats) {
                                listaChats.outerHTML = `
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle"></i> No tienes conversaciones activas todavía.
                                    </div>
                                `;
                            }
                        }
                    }, 300);
                }
            });
            
            // Actualizar el contador del tab de chats
            const badgeChats = document.querySelector('#chats-tab .badge');
            if (badgeChats) {
                let count = parseInt(badgeChats.textContent) || 0;
                count--;
                if (count > 0) {
                    badgeChats.textContent = count;
                } else {
                    badgeChats.remove();
                }
            }
            
            // Recargar página para mostrar cambios
            setTimeout(() => {
                window.location.href = 'chat.php';
            }, 800);
        } else {
            mostrarNotificacion('❌ Error al bloquear usuario: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        mostrarNotificacion('❌ Error al procesar la solicitud de bloqueo', 'error');
    });
}

// Función para archivar conversación
function archivarConversacion(usuarioId) {
    if (!confirm('¿Estás seguro de que quieres archivar esta conversación?\n\nLa conversación no se eliminará, solo se moverá a la sección de Archivados.')) {
        return;
    }
    
    fetch('gestionar_archivo_chat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `accion=archivar&usuario_id=${usuarioId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('✓ Conversación archivada correctamente', 'success');
            
            // Remover de la lista de chats activos
            const listItems = document.querySelectorAll('#chats-panel .list-group-item');
            listItems.forEach(item => {
                const chatearBtn = item.querySelector(`a[href*="id=${usuarioId}"]`);
                if (chatearBtn) {
                    item.style.transition = 'opacity 0.3s ease';
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 300);
                }
            });
            
            // Actualizar contador de chats
            const badgeChats = document.querySelector('#chats-tab .badge');
            if (badgeChats) {
                let count = parseInt(badgeChats.textContent) || 0;
                count--;
                if (count > 0) {
                    badgeChats.textContent = count;
                } else {
                    badgeChats.remove();
                }
            }
            
            // Recargar página para mostrar cambios
            setTimeout(() => {
                window.location.href = 'chat.php';
            }, 800);
        } else {
            mostrarNotificacion('❌ Error al archivar conversación: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        mostrarNotificacion('❌ Error al archivar conversación', 'error');
    });
}

// Función para desarchivar conversación
function desarchivarConversacion(usuarioId) {
    fetch('gestionar_archivo_chat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `accion=desarchivar&usuario_id=${usuarioId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('✓ Conversación desarchivada correctamente', 'success');
            
            // Remover de la lista de archivados
            const listItems = document.querySelectorAll('#archivados-panel .list-group-item');
            listItems.forEach(item => {
                const desarchivarBtn = item.querySelector(`button[onclick*="${usuarioId}"]`);
                if (desarchivarBtn) {
                    item.style.transition = 'opacity 0.3s ease';
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 300);
                }
            });
            
            // Redirigir a chats después de 1 segundo
            setTimeout(() => {
                window.location.href = 'chat.php';
            }, 1000);
        } else {
            mostrarNotificacion('❌ Error al desarchivar conversación: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        mostrarNotificacion('❌ Error al desarchivar conversación', 'error');
    });
}

// Función para desbloquear usuario
function desbloquearUsuario(usuarioId, nombreUsuario) {
    if (!confirm(`¿Estás seguro de que quieres desbloquear a ${nombreUsuario}?\n\nEste usuario podrá volver a interactuar contigo.`)) {
        return;
    }
    
    fetch('desbloquear_usuario.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `usuario_id=${usuarioId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Mensaje de éxito
            if (data.data && data.data.tiene_conversacion) {
                mostrarNotificacion(`✓ ${nombreUsuario} desbloqueado. Conversación restaurada (${data.data.mensajes_previos} mensajes).`, 'success');
            } else {
                mostrarNotificacion(`✓ ${nombreUsuario} ha sido desbloqueado correctamente.`, 'success');
            }
            
            // Recargar la página después de 800ms para mostrar los cambios
            setTimeout(() => {
                window.location.href = 'chat.php';
            }, 800);
        } else {
            mostrarNotificacion('❌ Error al desbloquear usuario: ' + (data.message || 'Error desconocido'), 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        mostrarNotificacion('❌ Error al procesar la solicitud', 'error');
    });
}

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo) {
    const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    const alerta = document.createElement('div');
    alerta.className = `alert ${alertClass} alert-dismissible fade show position-fixed shadow-lg`;
    alerta.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    alerta.setAttribute('role', 'alert');
    alerta.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${iconClass} me-2 fs-5"></i>
            <div class="flex-grow-1">${mensaje}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alerta);
    
    // Auto-ocultar después de 4 segundos
    setTimeout(function() {
        alerta.style.transition = 'opacity 0.3s ease';
        alerta.style.opacity = '0';
        setTimeout(function() {
            if (alerta.parentNode) {
                alerta.parentNode.removeChild(alerta);
            }
        }, 300);
    }, 4000);
}

// Manejar z-index de dropdowns para que aparezcan correctamente
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dropdown').forEach(function(dropdown) {
        const button = dropdown.querySelector('[data-bs-toggle="dropdown"]');
        
        if (button) {
            button.addEventListener('show.bs.dropdown', function() {
                const listItem = dropdown.closest('.list-group-item');
                if (listItem) {
                    listItem.style.zIndex = '1060';
                }
            });
            
            button.addEventListener('hidden.bs.dropdown', function() {
                const listItem = dropdown.closest('.list-group-item');
                if (listItem) {
                    listItem.style.zIndex = '';
                }
            });
        }
    });
});
</script>

</body>
</html>