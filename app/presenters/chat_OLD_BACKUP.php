<?php
session_start();
require_once __DIR__.'/../models/config.php';
require_once __DIR__.'/../models/socialnetwork-lib.php';

if(!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}

$user = isset($_GET['usuario']) ? (int)$_GET['usuario'] : 0;
$sess = $_SESSION['id'];

// ✅ Obtener solo amigos confirmados (estado = 1)
$stmtAmigos = $conexion->prepare("
    SELECT u.* 
    FROM usuarios u
    INNER JOIN amigos a 
        ON (
            (a.de = :sess1 AND a.para = u.id_use) 
            OR (a.para = :sess2 AND a.de = u.id_use)
        )
    WHERE a.estado = 1
    ORDER BY u.usuario ASC
");
$stmtAmigos->execute([
    ':sess1' => $sess,
    ':sess2' => $sess
]);
$amigos = $stmtAmigos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>REDSOCIAL - Chat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  
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
      background: #f8f9fa;
      color: #333;
      border: 1px solid #e9ecef;
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
      min-height: 20px; /* Asegurar que el área sea visible */
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
    

    
    /* Mensajes de voz */
    .voice-message {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
    }
    
    .voice-play-btn {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: none;
      background: rgba(255,255,255,0.2);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    
    .voice-waveform {
      flex: 1;
      height: 20px;
      background: rgba(255,255,255,0.2);
      border-radius: 10px;
      position: relative;
      overflow: hidden;
    }
    
    .voice-waveform::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 5px;
      width: calc(100% - 10px);
      height: 2px;
      background: rgba(255,255,255,0.5);
      transform: translateY(-50%);
      border-radius: 1px;
    }
    
    .voice-duration {
      font-size: 12px;
      color: rgba(255,255,255,0.8);
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
  </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <!-- 📌 Listado de amigos -->
      <div class="card shadow-lg mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between">
          <a href="/converza/app/view/index.php" class="btn btn-light btn-sm">
            <i class="fa fa-arrow-left"></i> Volver
          </a>
          <span><i class="bi bi-people-fill"></i> Tus amigos</span>
        </div>
        <div class="card-body">
          <?php if($amigos): ?>
            <ul class="list-group">
              <?php foreach($amigos as $am): ?>
                <li class="list-group-item d-flex align-items-center justify-content-between">
                  <div>
                    <img src="/converza/public/avatars/<?php echo $am['avatar']; ?>" 
                         width="32" height="32" class="rounded-circle me-2">
                    <?php echo htmlspecialchars($am['usuario']); ?>
                  </div>
                  <!-- ✅ Botón que inicia conversación si no existe -->
                  <a href="iniciar_chat.php?usuario=<?php echo $am['id_use']; ?>" 
                     class="btn btn-sm btn-primary">
                    <i class="bi bi-chat-dots"></i> Chatear
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted">No tienes amigos registrados todavía.</p>
          <?php endif; ?>
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
            // Marcar mensajes como leídos cuando el usuario entra al chat
            $stmtMarkRead = $conexion->prepare(
              "UPDATE chats SET leido = 1 
               WHERE de = :user_from AND para = :sess AND leido = 0"
            );
            $stmtMarkRead->execute([
              ':user_from' => $user,
              ':sess' => $sess
            ]);

            // Verificar si la tabla mensajes_eliminados existe, si no crearla
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
              // Si hay error creando la tabla, continuar sin filtrar mensajes eliminados
              error_log("Error creando tabla mensajes_eliminados: " . $e->getMessage());
            }

            $stmt = $conexion->prepare(
              "SELECT c.* FROM chats c
               WHERE ((c.de = ? AND c.para = ?) 
                  OR (c.de = ? AND c.para = ?))
                  -- Excluir mensajes eliminados para el usuario actual
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
                    
                    <!-- Reacciones debajo del mensaje -->
                    <div class="message-reactions" id="reactions_'.$ch['id_cha'].'">';
                
                // Mostrar reacciones existentes
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
                    </div>
                    
                    <!-- Reacciones debajo del mensaje -->
                    <div class="message-reactions text-end" id="reactions_'.$ch['id_cha'].'">';
                
                // Mostrar reacciones existentes
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
                        <button class="btn btn-sm btn-outline-danger reaction-btn" onclick="deleteMessage('.$ch['id_cha'].', '.strtotime($ch['fecha']).', true)" title="Eliminar mensaje"
                          <i class="bi bi-trash3"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>';
              }
            }
            ?>
          <?php endif; ?>
          </div>
        </div>

        <!-- 📌 Formulario de envío -->
        <?php if($user != 0): ?>
        <div class="card-footer bg-white">
          <!-- Formulario de mensaje de texto -->
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
          
          <!-- Grabadora de voz -->
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
          
          <!-- Vista previa del mensaje de voz -->
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
                  // Buscar conversación (placeholders únicos)
                  $stmtC = $conexion->prepare(
                    "SELECT id_cch FROM c_chats 
                    WHERE (de = :de1 AND para = :para1) 
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
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Variables para la nueva interfaz de voz tipo WhatsApp
let mediaRecorder = null;
let audioChunks = [];
let isRecording = false;
let recordingTimer = null;
let recordingStartTime = 0;
let currentAudioBlob = null;
let previewAudio = null;
let isPreviewPlaying = false;
let recordingCancelled = false;

// Auto-scroll al final del chat cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Inicializar sistema de reacciones
    initReactionSystem();
});

// Auto-scroll después de enviar un mensaje
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
    // Crear menú de reacciones
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
    
    // Ocultar menú al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.reaction-menu') && !e.target.closest('.reaction-btn')) {
            hideReactionMenu();
        }
    });
}

function toggleReactionMenu(messageId) {
    const menu = document.getElementById('reactionMenu');
    const button = event.target.closest('.reaction-btn');
    
    if (menu.style.display === 'block') {
        hideReactionMenu();
    } else {
        // Buscar la burbuja del mensaje para posicionar el menú MUY cerca encima
        const messageGroup = document.querySelector(`[data-message-id="${messageId}"]`);
        const messageBubble = messageGroup.querySelector('.message-bubble');
        const rect = messageBubble.getBoundingClientRect();
        
        // Mostrar menú temporalmente para obtener sus dimensiones
        menu.style.display = 'block';
        menu.style.visibility = 'hidden';
        
        // Calcular posición óptima
        let leftPos = rect.left;
        let topPos = rect.top - menu.offsetHeight - 3; // Solo 3px de separación
        
        // Ajustar si se sale por la derecha de la pantalla
        if (leftPos + menu.offsetWidth > window.innerWidth - 10) {
            leftPos = rect.right - menu.offsetWidth;
        }
        
        // Ajustar si se sale por arriba de la pantalla
        if (topPos < 10) {
            topPos = rect.bottom + 3; // Mostrar debajo del mensaje si no cabe arriba
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
    
    // Enviar reacción al servidor
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
            // Actualizar reacciones visuales
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

// Función para reaccionar directamente haciendo clic en una reacción existente
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
                // Si fue cancelada, limpiar y no hacer nada
                audioChunks = [];
                recordingCancelled = false;
            }
        };
        
        mediaRecorder.start();
        isRecording = true;
        recordingCancelled = false; // Resetear al iniciar nueva grabación
        
        // Mostrar interfaz de grabación
        document.getElementById('messageForm').style.display = 'none';
        document.getElementById('voiceRecorder').style.display = 'block';
        
        // Iniciar cronómetro
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
        
        // Ocultar interfaz de grabación
        document.getElementById('voiceRecorder').style.display = 'none';
        // No mostrar el form todavía, esperamos la vista previa
    }
}

function cancelRecording() {
    if (mediaRecorder && isRecording) {
        recordingCancelled = true; // Marcar como cancelada ANTES de parar
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
        isRecording = false;
        clearInterval(recordingTimer);
        audioChunks = [];
        
        // Ocultar interfaz de grabación y mostrar form normal
        document.getElementById('voiceRecorder').style.display = 'none';
        document.getElementById('messageForm').style.display = 'flex';
    }
}

// Funciones para vista previa de voz
function showVoicePreview(audioBlob) {
    currentAudioBlob = audioBlob;
    
    // Crear URL para el audio
    const audioUrl = URL.createObjectURL(audioBlob);
    previewAudio = document.getElementById('previewAudio');
    previewAudio.src = audioUrl;
    
    // Calcular y mostrar duración
    const duration = recordingStartTime ? Math.floor((Date.now() - recordingStartTime) / 1000) : 0;
    const minutes = Math.floor(duration / 60);
    const seconds = duration % 60;
    document.getElementById('previewDuration').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    // Mostrar vista previa
    document.getElementById('voicePreview').style.display = 'block';
    
    // Resetear botón de play
    const playBtn = document.getElementById('previewPlayBtn');
    playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
    isPreviewPlaying = false;
}

function confirmSendVoiceMessage() {
    if (currentAudioBlob) {
        // Ocultar vista previa
        document.getElementById('voicePreview').style.display = 'none';
        document.getElementById('messageForm').style.display = 'flex';
        
        // Enviar el mensaje (versión simplificada sin errores SQL)
        sendVoiceMessageSimple();
    }
}

function deleteVoiceMessage() {
    // Limpiar audio
    if (previewAudio) {
        previewAudio.pause();
        URL.revokeObjectURL(previewAudio.src);
        previewAudio.src = '';
    }
    
    currentAudioBlob = null;
    
    // Ocultar vista previa y mostrar form
    document.getElementById('voicePreview').style.display = 'none';
    document.getElementById('messageForm').style.display = 'flex';
    
    // Resetear estado
    isPreviewPlaying = false;
    audioChunks = [];
}

// Función para enviar mensaje de voz real
function sendVoiceMessageSimple() {
    if (!currentAudioBlob) {
        alert('No hay audio para enviar');
        return;
    }
    
    // Usar la función completa que ya funciona
    sendVoiceMessage(currentAudioBlob);
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
        console.log('No hay audio para reproducir');
        alert('No hay audio grabado para reproducir');
        return;
    }
    
    const playBtn = document.getElementById('previewPlayBtn');
    
    if (!isPreviewPlaying) {
        console.log('Iniciando reproducción');
        previewAudio.play().then(() => {
            playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
            isPreviewPlaying = true;
            console.log('Reproducción iniciada correctamente');
        }).catch(error => {
            console.error('Error al reproducir audio:', error);
            alert('No se puede reproducir el audio: ' + error.message);
        });
        
        previewAudio.onended = () => {
            playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            isPreviewPlaying = false;
            console.log('Reproducción terminada');
        };
    } else {
        console.log('Pausando reproducción');
        previewAudio.pause();
        playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        isPreviewPlaying = false;
        // NO resetear currentTime para mantener la posición
    }
}

// Context menu para mensajes
function showMessageMenu(event, messageId, isOwn) {
    event.preventDefault();
    
    // Crear o mostrar menú contextual
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
    
    // Ocultar menú al hacer clic fuera
    setTimeout(() => {
        document.addEventListener('click', function hideContextMenu() {
            contextMenu.style.display = 'none';
            document.removeEventListener('click', hideContextMenu);
        });
    }, 10);
}

function copyMessage(messageId) {
    // Implementar copia del mensaje
    console.log('Copiando mensaje:', messageId);
}

function replyToMessage(messageId) {
    // Implementar respuesta al mensaje
    console.log('Respondiendo a mensaje:', messageId);
}

function deleteMessage(messageId) {
    // Implementar eliminación del mensaje
    console.log('Eliminando mensaje:', messageId);
}

// Las funciones de vista previa ya están definidas arriba

// Función para enviar mensaje de voz
function sendVoiceMessage(audioBlob) {
    const paraUsuario = new URLSearchParams(window.location.search).get('usuario');
    
    if (!paraUsuario) {
        console.error('No se encontró el parámetro usuario en la URL');
        alert('Error: No se puede enviar el mensaje (usuario no encontrado)');
        return;
    }
    
    const formData = new FormData();
    formData.append('audio', audioBlob, 'voice_message.wav');
    formData.append('action', 'upload_voice');
    formData.append('para', paraUsuario);
    const duracion = recordingStartTime ? Math.floor((Date.now() - recordingStartTime) / 1000) : 0;
    formData.append('duracion', duracion);
    
    console.log('Enviando mensaje de voz a usuario:', paraUsuario);
    
    fetch('./upload_voice_new.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Respuesta del servidor:', response.status);
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        return response.text().then(text => {
            console.log('Texto de respuesta:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parseando JSON:', e);
                console.error('Respuesta del servidor:', text);
                throw new Error('El servidor no devolvió JSON válido: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if (data.success) {
            console.log('Mensaje de voz enviado correctamente');
            
            // Ocultar vista previa y mostrar formulario
            document.getElementById('voicePreview').style.display = 'none';
            document.getElementById('messageForm').style.display = 'flex';
            
            // Limpiar audio
            currentAudioBlob = null;
            if (previewAudio) {
                previewAudio.src = '';
            }
            
            // Actualizar chat dinámicamente en lugar de recargar
            setTimeout(() => {
                location.reload(); // Por ahora recargamos, pero se puede mejorar con AJAX
            }, 500);
            
        } else {
            console.error('Error al enviar mensaje de voz:', data.error);
            alert('Error al enviar mensaje de voz: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al enviar mensaje de voz: ' + error.message);
    });
}

// Función para reproducir mensajes de voz
let currentAudio = null;

function playVoiceMessage(audioUrl, buttonElement) {
    // Si no se pasa el botón, intentar encontrarlo por el event
    if (!buttonElement && window.event) {
        buttonElement = window.event.target.closest('.voice-play-btn');
    }
    
    // Verificar si este audio ya está reproduciéndose
    if (currentAudio && currentAudio.src.includes(audioUrl) && !currentAudio.paused) {
        // Pausar el audio actual
        currentAudio.pause();
        if (buttonElement) {
            buttonElement.innerHTML = '<i class="bi bi-play-fill"></i>';
        }
        return;
    }
    
    // Detener cualquier otro audio que esté reproduciéndose
    if (currentAudio && !currentAudio.paused) {
        currentAudio.pause();
        // Resetear todos los otros botones de play
        document.querySelectorAll('.voice-play-btn').forEach(btn => {
            if (btn !== buttonElement) {
                btn.innerHTML = '<i class="bi bi-play-fill"></i>';
            }
        });
    }
    
    // Crear o reutilizar audio
    if (!currentAudio || !currentAudio.src.includes(audioUrl)) {
        currentAudio = new Audio(audioUrl);
    }
    
    // Reproducir desde donde se pausó
    currentAudio.play().then(() => {
        // Cambiar icono a pausa
        if (buttonElement) {
            buttonElement.innerHTML = '<i class="bi bi-pause-fill"></i>';
        }
        console.log('Reproduciendo mensaje de voz:', audioUrl);
    }).catch(error => {
        console.error('Error al reproducir audio:', error);
        alert('Error al reproducir mensaje de voz: ' + error.message);
    });
    
    // Cuando termine, volver al icono de play
    currentAudio.onended = () => {
        if (buttonElement) {
            buttonElement.innerHTML = '<i class="bi bi-play-fill"></i>';
        }
        console.log('Reproducción terminada');
    };
}

function updateRecordingTime() {
    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    const timeText = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('recordingTime').textContent = timeText;
}

// Función para mostrar menú de eliminación tipo WhatsApp
function deleteMessage(messageId, messageTimestamp, isOwner) {
    // Calcular tiempo transcurrido desde el envío del mensaje
    const ahoraTimestamp = Math.floor(Date.now() / 1000); // Timestamp actual en segundos
    const tiempoTranscurrido = ahoraTimestamp - messageTimestamp; // Diferencia en segundos
    const minutosTranscurridos = tiempoTranscurrido / 60; // Convertir a minutos
    
    // Debug completo
    console.log('=== DEBUG ELIMINAR MENSAJE ===');
    console.log('Mensaje ID:', messageId);
    console.log('Timestamp del mensaje:', messageTimestamp);
    console.log('Timestamp actual:', ahoraTimestamp);
    console.log('Segundos transcurridos:', tiempoTranscurrido);
    console.log('Minutos transcurridos:', minutosTranscurridos);
    console.log('Es el remitente:', isOwner);
    
    // REGLA SIMPLE: Si es el remitente = puede eliminar para todos (SIN límite de tiempo)
    const puedeEliminarParaTodos = isOwner;
    
    console.log('¿Puede eliminar para todos?:', puedeEliminarParaTodos);
    console.log('Condiciones: Es remitente =', isOwner, '(Sin límite de tiempo)');
    
    // Verificar que el timestamp sea válido
    if (messageTimestamp <= 0) {
        console.error('ERROR: Timestamp del mensaje inválido:', messageTimestamp);
        alert('Error: Timestamp del mensaje inválido');
        return;
    }
    
    // Mostrar modal con las opciones
    showDeleteModal(messageId, puedeEliminarParaTodos, isOwner, minutosTranscurridos);
}

function showDeleteModal(messageId, canDeleteForEveryone, isOwner, minutesElapsed) {
    // Crear modal
    const modal = document.createElement('div');
    modal.className = 'delete-modal-overlay';
    
    const deleteForEveryoneButton = canDeleteForEveryone ? 
        '<button class="delete-option" onclick="confirmDelete(' + messageId + ', \'for_everyone\')"><i class="bi bi-trash3"></i> Eliminar para todos</button>' : 
        '';
    
    console.log('Modal eliminación - Es propietario:', isOwner, '| Puede eliminar para todos:', canDeleteForEveryone);
    
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
    
    // Cerrar al hacer clic fuera
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
    
    // Enviar solicitud de eliminación
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
                // Recargar para mostrar cambios para ambos
                location.reload();
            } else {
                // Eliminar completamente el mensaje de la vista (tipo WhatsApp)
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

// Sistema de sincronización automática - Se carga desde archivo externo
</script>

<!-- Variable para el usuario actual -->
<div data-current-user="<?php echo htmlspecialchars($_SESSION['usuario']); ?>" style="display: none;"></div>

<!-- Incluir script de sincronización automática -->
<script src="/converza/app/presenters/chat_sync.js"></script>

<!-- Sistema de sincronización embebido -->
<script>
console.log('🟢 Sistema de sincronización iniciando...');

// Variables globales
let ultimoMensajeId = 0;
let ultimaActualizacion = new Date().toISOString().slice(0, 19).replace('T', ' ');
let sincronizacionActiva = true;
let sesionUsuario = '<?php echo addslashes($_SESSION["usuario"]); ?>';

// Función para obtener el usuario actual de la sesión
function obtenerUsuarioSesion() {
    const userElement = document.querySelector('[data-current-user]');
    if (userElement) {
        const usuario = userElement.getAttribute('data-current-user');
        console.log('👤 Usuario encontrado en DOM:', usuario);
        return usuario;
    }
    console.log('👤 Usuario desde PHP:', sesionUsuario);
    return sesionUsuario;
}

// Inicializar sistema de sincronización
document.addEventListener('DOMContentLoaded', function() {
    console.log('🟢 Sistema de sincronización iniciado');
    
    sesionUsuario = obtenerUsuarioSesion();
    
    // Obtener el ID del último mensaje al cargar la página
    const mensajes = document.querySelectorAll('[data-message-id]');
    if (mensajes.length > 0) {
        const ids = Array.from(mensajes).map(msg => parseInt(msg.getAttribute('data-message-id')));
        ultimoMensajeId = Math.max(...ids);
    }
    
    console.log('🔄 Sistema de sincronización iniciado - Último mensaje ID:', ultimoMensajeId);
    console.log('👤 Usuario de sesión:', sesionUsuario);
    
    // Verificar que tenemos los datos necesarios
    const urlParams = new URLSearchParams(window.location.search);
    let userId = urlParams.get('usuario'); // Usar 'usuario' que es lo que usa el PHP
    console.log('🎯 Chat con usuario ID:', userId);
    console.log('🔍 URL params completos:', Object.fromEntries(urlParams));
    
    if (!userId || !sesionUsuario) {
        console.error('❌ Faltan datos necesarios para sincronización');
        console.error('   - userId:', userId);
        console.error('   - sesionUsuario:', sesionUsuario);
        console.error('   - URL actual:', window.location.href);
        return;
    }
    
    // Iniciar sincronización completa cada 3 segundos
    console.log('⏰ Iniciando polling cada 3 segundos...');
    setInterval(sincronizarChat, 3000);
    
    // Primera verificación inmediata
    setTimeout(sincronizarChat, 1000);
});

function sincronizarChat() {
    if (!sincronizacionActiva) {
        console.log('⏸️ Sincronización pausada');
        return;
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    let userId = urlParams.get('usuario'); // Usar 'usuario' que es lo que usa el PHP
    
    if (!userId) {
        console.error('❌ No hay userId en la URL');
        console.error('   - URL params:', Object.fromEntries(urlParams));
        console.error('   - URL completa:', window.location.href);
        return;
    }
    
    const url = `verificar_nuevos_mensajes.php?usuario=${userId}&ultimo_id=${ultimoMensajeId}&ultima_actualizacion=${encodeURIComponent(ultimaActualizacion)}`;
    console.log('🔍 Verificando cambios...', { userId, ultimoMensajeId, ultimaActualizacion });
    console.log('🌐 URL completa:', window.location.origin + window.location.pathname.replace('chat.php', '') + url);
    
    fetch(url, { 
        method: 'GET',
        credentials: 'same-origin', // Incluir cookies de sesión
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('📡 Respuesta recibida:', response.status, response.statusText);
        console.log('📡 URL solicitada:', response.url);
        
        // Obtener el texto crudo para debugging
        return response.text().then(text => {
            console.log('📄 Respuesta cruda:', text);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${text}`);
            }
            
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('❌ Error parseando JSON:', e);
                console.error('📄 Texto recibido:', text);
                throw new Error('Respuesta no es JSON válido: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('📄 Datos parseados correctamente:', data);
        
        // Verificar si hay errores en la respuesta
        if (data.error) {
            console.error('❌ Error del servidor:', data.error);
            return;
        }
        
        if (data.cambios) {
            console.log('🔄 ¡CAMBIOS DETECTADOS!', data);
            
            // 1. AGREGAR MENSAJES NUEVOS
            if (data.nuevos_mensajes && data.nuevos_mensajes.length > 0) {
                console.log('📨 Mensajes nuevos:', data.nuevos_mensajes.length);
                
                data.nuevos_mensajes.forEach(mensaje => {
                    // Verificar que el mensaje no exista ya
                    if (document.querySelector(`[data-message-id="${mensaje.id_cha}"]`)) {
                        console.log('⚠️ Mensaje ya existe:', mensaje.id_cha);
                        return;
                    }
                    
                    console.log('➕ Agregando nuevo mensaje:', mensaje.id_cha, mensaje.mensaje);
                    const mensajeHtml = crearHtmlMensajeCompleto(mensaje);
                    document.querySelector('.chat-messages').insertAdjacentHTML('beforeend', mensajeHtml);
                    
                    // Actualizar último ID
                    ultimoMensajeId = Math.max(ultimoMensajeId, parseInt(mensaje.id_cha));
                });
                
                // Scroll automático hacia abajo
                const chatContainer = document.querySelector('.chat-messages');
                chatContainer.scrollTop = chatContainer.scrollHeight;
                
                console.log('✅ Nuevo ultimoMensajeId:', ultimoMensajeId);
            }
            
            // 2. ACTUALIZAR REACCIONES
            if (data.reacciones_actualizadas && data.reacciones_actualizadas.length > 0) {
                console.log('😊 Reacciones actualizadas:', data.reacciones_actualizadas.length);
                actualizarReaccionesEnTiempoReal(data.reacciones_actualizadas);
            }
            
            // 3. ELIMINAR MENSAJES
            if (data.mensajes_eliminados && data.mensajes_eliminados.length > 0) {
                console.log('🗑️ Mensajes eliminados:', data.mensajes_eliminados.length);
                eliminarMensajesEnTiempoReal(data.mensajes_eliminados);
            }
            
        } else {
            console.log('💤 Sin cambios detectados');
        }
    })
    .catch(error => {
        console.error('❌ Error en sincronización:', error);
    });
}

// Función agregarNuevosMensajes integrada arriba

function crearHtmlMensajeCompleto(mensaje) {
    const fechaCompleta = new Date(mensaje.fecha).toLocaleDateString() + ' ' + 
                         new Date(mensaje.fecha).toLocaleTimeString().slice(0, 5);
    const esPropio = mensaje.de_usuario === sesionUsuario;
    
    console.log('🏗️ Creando mensaje HTML:', {
        esPropio: esPropio,
        mensaje_usuario: mensaje.de_usuario,
        sesion_usuario: sesionUsuario,
        mensaje: mensaje.mensaje
    });
    
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

</script>

</body>
</html>
