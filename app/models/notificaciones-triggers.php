<?php
/**
 * Triggers para generar notificaciones automáticas
 * Este archivo contiene funciones que deben llamarse cuando ocurren eventos
 */

require_once __DIR__ . '/notificaciones-helper.php';

class NotificacionesTriggers {
    private $notificacionesHelper;
    
    public function __construct($conexion) {
        $this->notificacionesHelper = new NotificacionesHelper($conexion);
    }
    
    /**
     * SOLICITUDES DE AMISTAD
     */
    public function solicitudAmistadEnviada($de_usuario_id, $para_usuario_id, $nombre_usuario) {
        $mensaje = "<strong>{$nombre_usuario}</strong> te envió una solicitud de amistad";
        $url = "/Converza/app/view/index.php"; // Abre el offcanvas de solicitudes
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'solicitud_amistad',
            $mensaje,
            $de_usuario_id,
            null,
            'solicitud_amistad',
            $url
        );
    }
    
    public function solicitudAmistadAceptada($de_usuario_id, $para_usuario_id, $nombre_usuario) {
        $mensaje = "<strong>{$nombre_usuario}</strong> aceptó tu solicitud de amistad 🎉";
        $url = "/Converza/app/presenters/perfil.php?id={$de_usuario_id}";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'amistad_aceptada',
            $mensaje,
            $de_usuario_id,
            null,
            'amistad',
            $url
        );
    }
    
    public function solicitudAmistadRechazada($de_usuario_id, $para_usuario_id, $nombre_usuario) {
        $mensaje = "<strong>{$nombre_usuario}</strong> rechazó tu solicitud de amistad";
        $url = "/Converza/app/presenters/perfil.php?id={$de_usuario_id}";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'amistad_rechazada',
            $mensaje,
            $de_usuario_id,
            null,
            'amistad',
            $url
        );
    }
    
    /**
     * SEGUIDORES
     */
    public function nuevoSeguidor($seguidor_id, $seguido_id, $nombre_seguidor) {
        $mensaje = "<strong>{$nombre_seguidor}</strong> comenzó a seguirte ❤️";
        $url = "/Converza/app/presenters/perfil.php?id={$seguidor_id}";
        
        return $this->notificacionesHelper->crear(
            $seguido_id,
            'nuevo_seguidor',
            $mensaje,
            $seguidor_id,
            null,
            'seguidor',
            $url
        );
    }
    
    /**
     * SOLICITUDES DE MENSAJE
     */
    public function solicitudMensajeEnviada($de_usuario_id, $para_usuario_id, $nombre_usuario, $primer_mensaje) {
        $mensaje = "<strong>{$nombre_usuario}</strong> te envió una solicitud de mensaje: \"{$primer_mensaje}\"";
        $url = "/Converza/app/presenters/chat.php";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'solicitud_mensaje',
            $mensaje,
            $de_usuario_id,
            null,
            'solicitud_mensaje',
            $url
        );
    }
    
    public function solicitudMensajeAceptada($de_usuario_id, $para_usuario_id, $nombre_usuario) {
        $mensaje = "<strong>{$nombre_usuario}</strong> aceptó tu solicitud de mensaje 💬";
        $url = "/Converza/app/presenters/chat.php?id={$de_usuario_id}";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'mensaje_aceptado',
            $mensaje,
            $de_usuario_id,
            null,
            'mensaje',
            $url
        );
    }
    
    public function solicitudMensajeRechazada($de_usuario_id, $para_usuario_id, $nombre_usuario) {
        $mensaje = "<strong>{$nombre_usuario}</strong> rechazó tu solicitud de mensaje";
        $url = "/Converza/app/presenters/perfil.php?id={$de_usuario_id}";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'mensaje_rechazado',
            $mensaje,
            $de_usuario_id,
            null,
            'mensaje',
            $url
        );
    }
    
    /**
     * MENSAJES
     */
    public function nuevoMensaje($de_usuario_id, $para_usuario_id, $nombre_usuario, $mensaje_preview) {
        // Limitar preview a 50 caracteres
        $preview = mb_strlen($mensaje_preview) > 50 
            ? mb_substr($mensaje_preview, 0, 50) . '...' 
            : $mensaje_preview;
            
        $mensaje = "<strong>{$nombre_usuario}</strong> te envió un mensaje: \"{$preview}\"";
        $url = "/Converza/app/presenters/chat.php?id={$de_usuario_id}";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'nuevo_mensaje',
            $mensaje,
            $de_usuario_id,
            null,
            'mensaje',
            $url
        );
    }
    
    /**
     * COMENTARIOS
     */
    public function nuevoComentario($de_usuario_id, $para_usuario_id, $nombre_usuario, $publicacion_id, $comentario_preview) {
        // Limitar preview a 50 caracteres
        $preview = mb_strlen($comentario_preview) > 50 
            ? mb_substr($comentario_preview, 0, 50) . '...' 
            : $comentario_preview;
            
        $mensaje = "<strong>{$nombre_usuario}</strong> comentó tu publicación: \"{$preview}\"";
        $url = "/Converza/app/view/index.php#publicacion-{$publicacion_id}";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'nuevo_comentario',
            $mensaje,
            $de_usuario_id,
            $publicacion_id,
            'publicacion',
            $url
        );
    }
    
    /**
     * PUBLICACIONES
     */
    public function nuevaPublicacion($autor_id, $nombre_autor, $publicacion_id, $contenido_preview) {
        // No implementar aquí - debe llamarse para cada seguidor/amigo
        // Ver función notificarNuevaPublicacion()
    }
    
    /**
     * Notificar a todos los seguidores y amigos sobre una nueva publicación
     */
    public function notificarNuevaPublicacion($conexion, $autor_id, $nombre_autor, $publicacion_id, $contenido_preview) {
        // Limitar preview a 60 caracteres
        $preview = mb_strlen($contenido_preview) > 60 
            ? mb_substr($contenido_preview, 0, 60) . '...' 
            : $contenido_preview;
            
        $mensaje = "<strong>{$nombre_autor}</strong> publicó algo nuevo: \"{$preview}\"";
        $url = "/Converza/app/view/index.php#publicacion-{$publicacion_id}";
        
        try {
            // Obtener todos los seguidores y amigos
            $stmt = $conexion->prepare("
                SELECT DISTINCT u.id_use
                FROM usuarios u
                WHERE u.id_use != :autor_id
                AND (
                    -- Es seguidor
                    EXISTS (
                        SELECT 1 FROM seguidores s 
                        WHERE s.seguidor_id = u.id_use 
                        AND s.seguido_id = :autor_id2
                    )
                    OR
                    -- Es amigo
                    EXISTS (
                        SELECT 1 FROM amigos a 
                        WHERE ((a.de = u.id_use AND a.para = :autor_id3) 
                            OR (a.de = :autor_id4 AND a.para = u.id_use))
                        AND a.estado = 1
                    )
                )
            ");
            
            $stmt->execute([
                ':autor_id' => $autor_id,
                ':autor_id2' => $autor_id,
                ':autor_id3' => $autor_id,
                ':autor_id4' => $autor_id
            ]);
            
            $destinatarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Crear notificación para cada destinatario
            foreach ($destinatarios as $usuario_id) {
                $this->notificacionesHelper->crear(
                    $usuario_id,
                    'nueva_publicacion',
                    $mensaje,
                    $autor_id,
                    $publicacion_id,
                    'publicacion',
                    $url
                );
            }
            
            return count($destinatarios);
            
        } catch (Exception $e) {
            error_log("Error al notificar nueva publicación: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * REACCIONES
     */
    public function nuevaReaccion($de_usuario_id, $para_usuario_id, $nombre_usuario, $publicacion_id, $tipo_reaccion) {
        $emojis = [
            'like' => '👍',
            'love' => '❤️',
            'haha' => '😂',
            'wow' => '😮',
            'sad' => '😢',
            'angry' => '😠'
        ];
        
        $emoji = $emojis[$tipo_reaccion] ?? '👍';
        $mensaje = "<strong>{$nombre_usuario}</strong> reaccionó {$emoji} a tu publicación";
        $url = "/Converza/app/view/index.php#publicacion-{$publicacion_id}";
        
        return $this->notificacionesHelper->crear(
            $para_usuario_id,
            'reaccion_publicacion',
            $mensaje,
            $de_usuario_id,
            $publicacion_id,
            'publicacion',
            $url
        );
    }
}
?>
