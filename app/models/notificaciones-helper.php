<?php
/**
 * Helper para el Sistema de Notificaciones en Tiempo Real
 * Gestiona la creación, lectura y eliminación de notificaciones
 */

class NotificacionesHelper {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Crear una nueva notificación
     */
    public function crear($usuario_id, $tipo, $mensaje, $de_usuario_id = null, $referencia_id = null, $referencia_tipo = null, $url_redireccion = null) {
        try {
            // No crear notificación si el usuario se notifica a sí mismo
            if ($usuario_id == $de_usuario_id) {
                return false;
            }
            
            $stmt = $this->conexion->prepare("
                INSERT INTO notificaciones 
                (usuario_id, tipo, mensaje, de_usuario_id, referencia_id, referencia_tipo, url_redireccion)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $usuario_id,
                $tipo,
                $mensaje,
                $de_usuario_id,
                $referencia_id,
                $referencia_tipo,
                $url_redireccion
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener notificaciones no leídas de un usuario
     */
    public function obtenerNoLeidas($usuario_id, $limite = 50) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT n.*, 
                       u.usuario as de_usuario_nombre, 
                       u.avatar as de_usuario_avatar
                FROM notificaciones n
                LEFT JOIN usuarios u ON n.de_usuario_id = u.id_use
                WHERE n.usuario_id = ? AND n.leida = 0
                ORDER BY n.fecha_creacion DESC
                LIMIT ?
            ");
            $stmt->execute([$usuario_id, $limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener notificaciones no leídas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todas las notificaciones de un usuario (leídas y no leídas)
     */
    public function obtenerTodas($usuario_id, $limite = 50) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT n.*, 
                       u.usuario as de_usuario_nombre, 
                       u.avatar as de_usuario_avatar
                FROM notificaciones n
                LEFT JOIN usuarios u ON n.de_usuario_id = u.id_use
                WHERE n.usuario_id = ?
                ORDER BY n.fecha_creacion DESC
                LIMIT ?
            ");
            $stmt->execute([$usuario_id, $limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todas las notificaciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas($usuario_id) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT COUNT(*) as total 
                FROM notificaciones 
                WHERE usuario_id = ? AND leida = 0
            ");
            $stmt->execute([$usuario_id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error al contar notificaciones no leídas: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Marcar una notificación como leída
     */
    public function marcarComoLeida($notificacion_id) {
        try {
            $stmt = $this->conexion->prepare("
                UPDATE notificaciones 
                SET leida = 1, fecha_leida = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$notificacion_id]);
        } catch (PDOException $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public function marcarTodasComoLeidas($usuario_id) {
        try {
            $stmt = $this->conexion->prepare("
                UPDATE notificaciones 
                SET leida = 1, fecha_leida = NOW() 
                WHERE usuario_id = ? AND leida = 0
            ");
            return $stmt->execute([$usuario_id]);
        } catch (PDOException $e) {
            error_log("Error al marcar todas como leídas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una notificación específica
     */
    public function eliminar($notificacion_id) {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM notificaciones WHERE id = ?");
            return $stmt->execute([$notificacion_id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar notificación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar todas las notificaciones de un usuario
     */
    public function eliminarTodas($usuario_id) {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM notificaciones WHERE usuario_id = ?");
            return $stmt->execute([$usuario_id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar todas las notificaciones: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar notificaciones antiguas (más de 30 días y leídas)
     */
    public function limpiarAntiguas() {
        try {
            $stmt = $this->conexion->prepare("
                DELETE FROM notificaciones 
                WHERE leida = 1 
                AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al limpiar notificaciones antiguas: " . $e->getMessage());
            return false;
        }
    }
}
?>
