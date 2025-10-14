<?php
/**
 * COINCIDENCE ALERTS - Sistema de Detección en Tiempo Real
 * Detecta usuarios online con alta compatibilidad y envía alertas instantáneas
 */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/conexiones-misticas-usuario-helper.php');

class CoincidenceAlertsHelper {
    private $conexion;
    private $conexionesMisticas;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->conexionesMisticas = new ConexionesMisticasUsuarioHelper($conexion);
    }
    
    /**
     * 🔴 DETECTAR USUARIOS ONLINE CON ALTA COMPATIBILIDAD
     * Se ejecuta cada vez que un usuario está activo
     */
    public function detectarCoincidenciasEnTiempoReal($usuario_id) {
        try {
            // 1. Obtener usuarios activos en los últimos 5 minutos
            $usuariosOnline = $this->obtenerUsuariosOnline($usuario_id);
            
            if (empty($usuariosOnline)) {
                return [
                    'hay_coincidencias' => false,
                    'mensaje' => 'No hay usuarios online en este momento'
                ];
            }
            
            // 2. Calcular compatibilidad con cada usuario online
            $coincidencias = [];
            foreach ($usuariosOnline as $usuario_online) {
                $compatibilidad = $this->calcularCompatibilidadRapida(
                    $usuario_id, 
                    $usuario_online['id_use']
                );
                
                // Solo alertar si compatibilidad > 70%
                if ($compatibilidad['score'] >= 70) {
                    $coincidencias[] = [
                        'usuario_id' => $usuario_online['id_use'],
                        'usuario_nombre' => $usuario_online['usuario'],
                        'avatar' => $usuario_online['avatar'],
                        'compatibilidad' => $compatibilidad['score'],
                        'razon' => $compatibilidad['razon'],
                        'tiempo_online' => $usuario_online['ultima_actividad']
                    ];
                }
            }
            
            // 3. Ordenar por compatibilidad (mayor primero)
            usort($coincidencias, function($a, $b) {
                return $b['compatibilidad'] - $a['compatibilidad'];
            });
            
            // 4. Guardar alerta en base de datos
            if (!empty($coincidencias)) {
                $this->registrarAlerta($usuario_id, $coincidencias[0]); // Solo la mejor coincidencia
            }
            
            return [
                'hay_coincidencias' => !empty($coincidencias),
                'total' => count($coincidencias),
                'coincidencias' => array_slice($coincidencias, 0, 3), // Máximo 3
                'mensaje' => !empty($coincidencias) 
                    ? "¡{$coincidencias[0]['compatibilidad']}% compatible con {$coincidencias[0]['usuario_nombre']}!" 
                    : 'No hay coincidencias significativas'
            ];
            
        } catch (Exception $e) {
            error_log("Error en Coincidence Alerts: " . $e->getMessage());
            return [
                'hay_coincidencias' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener usuarios activos en los últimos 5 minutos
     */
    private function obtenerUsuariosOnline($usuario_id_excluir) {
        $sql = "
            SELECT DISTINCT u.id_use, u.usuario, u.avatar, u.ultima_actividad
            FROM usuarios u
            WHERE u.id_use != ?
            AND u.ultima_actividad >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            AND u.id_use NOT IN (
                SELECT bloqueador_id FROM bloqueos WHERE bloqueado_id = ?
                UNION
                SELECT bloqueado_id FROM bloqueos WHERE bloqueador_id = ?
            )
            ORDER BY u.ultima_actividad DESC
            LIMIT 20
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario_id_excluir, $usuario_id_excluir, $usuario_id_excluir]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular compatibilidad rápida (optimizado para tiempo real)
     */
    private function calcularCompatibilidadRapida($usuario1, $usuario2) {
        $score = 0;
        $razones = [];
        
        // 1. Karma de ambos usuarios
        $stmtKarma = $this->conexion->prepare("
            SELECT usuario_id, SUM(puntos) as total
            FROM karma_social
            WHERE usuario_id IN (?, ?)
            GROUP BY usuario_id
        ");
        $stmtKarma->execute([$usuario1, $usuario2]);
        $karmas = $stmtKarma->fetchAll(PDO::FETCH_ASSOC);
        
        $karma1 = 0;
        $karma2 = 0;
        foreach ($karmas as $k) {
            if ($k['usuario_id'] == $usuario1) $karma1 = $k['total'];
            if ($k['usuario_id'] == $usuario2) $karma2 = $k['total'];
        }
        
        if ($karma1 > 100 && $karma2 > 100) {
            $score += 20;
            $razones[] = "Ambos tienen buen karma";
        }
        
        // 2. Reacciones a las mismas publicaciones
        $stmtReacciones = $this->conexion->prepare("
            SELECT COUNT(DISTINCT r1.id_publicacion) as comunes
            FROM reacciones r1
            JOIN reacciones r2 ON r1.id_publicacion = r2.id_publicacion
            WHERE r1.id_usuario = ? AND r2.id_usuario = ?
            AND r1.fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmtReacciones->execute([$usuario1, $usuario2]);
        $reacciones = $stmtReacciones->fetch(PDO::FETCH_ASSOC);
        
        if ($reacciones['comunes'] > 0) {
            $score += min($reacciones['comunes'] * 15, 40);
            $razones[] = "{$reacciones['comunes']} publicaciones en común";
        }
        
        // 3. Amigos en común
        $stmtAmigos = $this->conexion->prepare("
            SELECT COUNT(*) as comunes
            FROM amigos a1
            JOIN amigos a2 ON a1.id_amigo = a2.id_amigo
            WHERE a1.id_usuario = ? AND a2.id_usuario = ?
        ");
        $stmtAmigos->execute([$usuario1, $usuario2]);
        $amigos = $stmtAmigos->fetch(PDO::FETCH_ASSOC);
        
        if ($amigos['comunes'] > 0) {
            $score += min($amigos['comunes'] * 10, 30);
            $razones[] = "{$amigos['comunes']} amigos en común";
        }
        
        // 4. Actividad en horarios similares
        $stmtHorarios = $this->conexion->prepare("
            SELECT 
                HOUR(u1.ultima_actividad) as hora1,
                HOUR(u2.ultima_actividad) as hora2
            FROM usuarios u1, usuarios u2
            WHERE u1.id_use = ? AND u2.id_use = ?
        ");
        $stmtHorarios->execute([$usuario1, $usuario2]);
        $horarios = $stmtHorarios->fetch(PDO::FETCH_ASSOC);
        
        if ($horarios && abs($horarios['hora1'] - $horarios['hora2']) <= 2) {
            $score += 10;
            $razones[] = "Activos en horarios similares";
        }
        
        return [
            'score' => min($score, 100),
            'razon' => implode(", ", $razones)
        ];
    }
    
    /**
     * Registrar alerta en base de datos
     */
    private function registrarAlerta($usuario_id, $coincidencia) {
        try {
            // Verificar si ya existe una alerta reciente (últimos 30 minutos)
            $stmtCheck = $this->conexion->prepare("
                SELECT id FROM coincidence_alerts
                WHERE usuario_id = ?
                AND usuario_coincidente_id = ?
                AND fecha_alerta >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ");
            $stmtCheck->execute([$usuario_id, $coincidencia['usuario_id']]);
            
            if ($stmtCheck->rowCount() > 0) {
                return false; // Ya existe alerta reciente
            }
            
            // Insertar nueva alerta
            $stmt = $this->conexion->prepare("
                INSERT INTO coincidence_alerts 
                (usuario_id, usuario_coincidente_id, compatibilidad, razon, fecha_alerta)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $usuario_id,
                $coincidencia['usuario_id'],
                $coincidencia['compatibilidad'],
                $coincidencia['razon']
            ]);
            
        } catch (PDOException $e) {
            error_log("Error registrando alerta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener alertas no leídas del usuario
     */
    public function obtenerAlertasNoLeidas($usuario_id) {
        $sql = "
            SELECT 
                ca.*,
                u.usuario,
                u.avatar,
                u.ultima_actividad,
                TIMESTAMPDIFF(MINUTE, ca.fecha_alerta, NOW()) as minutos_transcurridos
            FROM coincidence_alerts ca
            JOIN usuarios u ON ca.usuario_coincidente_id = u.id_use
            WHERE ca.usuario_id = ?
            AND ca.leida = FALSE
            AND ca.fecha_alerta >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY ca.compatibilidad DESC, ca.fecha_alerta DESC
            LIMIT 5
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Marcar alerta como leída
     */
    public function marcarComoLeida($alerta_id) {
        $stmt = $this->conexion->prepare("
            UPDATE coincidence_alerts 
            SET leida = TRUE 
            WHERE id = ?
        ");
        return $stmt->execute([$alerta_id]);
    }
    
    /**
     * Contar alertas no leídas
     */
    public function contarAlertasNoLeidas($usuario_id) {
        $stmt = $this->conexion->prepare("
            SELECT COUNT(*) as total
            FROM coincidence_alerts
            WHERE usuario_id = ?
            AND leida = FALSE
            AND fecha_alerta >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>
