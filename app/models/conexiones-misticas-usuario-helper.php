<?php
/**
 * Detecta conexiones místicas solo para un usuario específico
 * Más rápido que detectar para todos los usuarios
 */

class ConexionesMisticasUsuario {
    private $conexion;
    private $usuarioId;
    
    public function __construct($conexion, $usuarioId) {
        $this->conexion = $conexion;
        $this->usuarioId = $usuarioId;
    }
    
    /**
     * Detecta todas las conexiones para este usuario específico
     */
    public function detectarConexionesUsuario() {
        $this->detectarGustosCompartidos();
        $this->detectarInteresesComunes();
        $this->detectarAmigosDeAmigos();
        $this->detectarHorariosCoincidentes();
    }
    
    /**
     * Detecta gustos compartidos solo para este usuario
     */
    private function detectarGustosCompartidos() {
        $sql = "
            SELECT 
                r2.id_usuario as otro_usuario,
                COUNT(DISTINCT r1.id_publicacion) as publicaciones_comunes
            FROM reacciones r1
            JOIN reacciones r2 ON r1.id_publicacion = r2.id_publicacion
            WHERE r1.id_usuario = ? 
            AND r2.id_usuario != ?
            GROUP BY r2.id_usuario
            HAVING publicaciones_comunes >= 2
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$this->usuarioId, $this->usuarioId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $row['otro_usuario'],
                'gustos_compartidos',
                "¡Ambos reaccionaron a {$row['publicaciones_comunes']} publicaciones similares! 💫",
                min(100, $row['publicaciones_comunes'] * 20)
            );
        }
    }
    
    /**
     * Detecta intereses comunes solo para este usuario
     */
    private function detectarInteresesComunes() {
        $sql = "
            SELECT 
                c2.usuario as otro_usuario,
                COUNT(DISTINCT c1.publicacion) as publicaciones_comunes
            FROM comentarios c1
            JOIN comentarios c2 ON c1.publicacion = c2.publicacion
            WHERE c1.usuario = ? 
            AND c2.usuario != ?
            GROUP BY c2.usuario
            HAVING publicaciones_comunes >= 2
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$this->usuarioId, $this->usuarioId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $row['otro_usuario'],
                'intereses_comunes',
                "¡Ambos comentaron en {$row['publicaciones_comunes']} publicaciones! Tienen intereses similares 🎯",
                min(100, $row['publicaciones_comunes'] * 25)
            );
        }
    }
    
    /**
     * Detecta amigos de amigos solo para este usuario
     */
    private function detectarAmigosDeAmigos() {
        $sql = "
            SELECT DISTINCT
                a2.para as otro_usuario,
                u_comun.usuario as amigo_comun
            FROM amigos a1
            JOIN amigos a2 ON a1.para = a2.de
            JOIN usuarios u_comun ON a1.para = u_comun.id_use
            WHERE a1.de = ?
            AND a1.estado = 1 
            AND a2.estado = 1
            AND a2.para != ?
            AND NOT EXISTS (
                SELECT 1 FROM amigos 
                WHERE ((de = ? AND para = a2.para) OR (de = a2.para AND para = ?))
            )
            LIMIT 20
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$this->usuarioId, $this->usuarioId, $this->usuarioId, $this->usuarioId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $row['otro_usuario'],
                'amigos_de_amigos',
                "¡Ambos son amigos de {$row['amigo_comun']}! 🌟",
                60
            );
        }
    }
    
    /**
     * Detecta horarios coincidentes solo para este usuario
     */
    private function detectarHorariosCoincidentes() {
        $sql = "
            SELECT 
                p2.usuario as otro_usuario,
                HOUR(p1.fecha) as hora,
                COUNT(*) as coincidencias
            FROM publicaciones p1
            JOIN publicaciones p2 ON HOUR(p1.fecha) = HOUR(p2.fecha)
            WHERE p1.usuario = ?
            AND p2.usuario != ?
            AND p1.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p2.usuario, HOUR(p1.fecha)
            HAVING coincidencias >= 3
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$this->usuarioId, $this->usuarioId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $hora = $row['hora'];
            $periodo = ($hora < 12) ? "mañana" : (($hora < 18) ? "tarde" : "noche");
            
            $this->guardarConexion(
                $row['otro_usuario'],
                'horarios_coincidentes',
                "¡Ambos suelen estar activos en la {$periodo}! 🌙",
                40
            );
        }
    }
    
    /**
     * Guarda una conexión para este usuario
     */
    private function guardarConexion($otroUsuarioId, $tipo, $descripcion, $puntuacion) {
        try {
            // Verificar si la conexión ya existe
            $sql_check = "
                SELECT id, puntuacion 
                FROM conexiones_misticas 
                WHERE usuario1_id = ? 
                AND usuario2_id = ? 
                AND tipo_conexion = ?
            ";
            
            $stmt_check = $this->conexion->prepare($sql_check);
            $stmt_check->execute([
                min($this->usuarioId, $otroUsuarioId),
                max($this->usuarioId, $otroUsuarioId),
                $tipo
            ]);
            
            $conexion_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);
            $es_nueva_conexion = !$conexion_existente;
            $puntuacion_anterior = $conexion_existente ? $conexion_existente['puntuacion'] : 0;
            
            // Guardar o actualizar la conexión
            $sql = "
                INSERT INTO conexiones_misticas 
                (usuario1_id, usuario2_id, tipo_conexion, descripcion, puntuacion)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    descripcion = VALUES(descripcion),
                    puntuacion = VALUES(puntuacion),
                    fecha_deteccion = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                min($this->usuarioId, $otroUsuarioId),
                max($this->usuarioId, $otroUsuarioId),
                $tipo,
                $descripcion,
                $puntuacion
            ]);
            
            // Enviar notificación solo si la conexión es significativa (>=80)
            // Y solo si es nueva o mejoró significativamente (incremento de al menos 20 puntos)
            if ($puntuacion >= 80 && ($es_nueva_conexion || ($puntuacion - $puntuacion_anterior) >= 20)) {
                $this->enviarNotificacionCoincidencia($otroUsuarioId, $tipo, $descripcion, $puntuacion);
            }
            
        } catch (Exception $e) {
            // Silenciar errores
        }
    }
    
    /**
     * Envía notificación de coincidencia significativa a ambos usuarios
     */
    private function enviarNotificacionCoincidencia($otroUsuarioId, $tipo, $descripcion, $puntuacion) {
        try {
            // Obtener nombres de ambos usuarios
            $sql = "SELECT id_use, usuario FROM usuarios WHERE id_use IN (?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$this->usuarioId, $otroUsuarioId]);
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $nombre_usuario1 = '';
            $nombre_usuario2 = '';
            
            foreach ($usuarios as $usuario) {
                if ($usuario['id_use'] == $this->usuarioId) {
                    $nombre_usuario1 = $usuario['usuario'];
                } else {
                    $nombre_usuario2 = $usuario['usuario'];
                }
            }
            
            // Crear trigger de notificaciones
            require_once __DIR__ . '/notificaciones-triggers.php';
            $triggers = new NotificacionesTriggers($this->conexion);
            
            // Enviar notificación de coincidencia significativa
            $triggers->coincidenciaSignificativa(
                $this->usuarioId,
                $otroUsuarioId,
                $tipo,
                $descripcion,
                $puntuacion,
                $nombre_usuario1,
                $nombre_usuario2
            );
            
        } catch (Exception $e) {
            // Silenciar errores de notificación
            error_log("Error al enviar notificación de coincidencia: " . $e->getMessage());
        }
    }
}
?>
