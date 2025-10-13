<?php
/**
 * CONEXIONES MÍSTICAS - Motor de Análisis
 * Detecta patrones y coincidencias curiosas entre usuarios
 */

require_once(__DIR__ . '/config.php');

class ConexionesMisticas {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Detecta todas las conexiones místicas disponibles
     */
    public function detectarConexiones() {
        echo "🔮 Iniciando detección de conexiones místicas...\n\n";
        
        $this->detectarGustosCompartidos();
        $this->detectarInteresesComunes();
        $this->detectarAmigosDeAmigos();
        $this->detectarHorariosCoincidentes();
        
        echo "\n✅ Detección completada!\n";
    }
    
    /**
     * Detecta usuarios que reaccionan a las mismas publicaciones
     */
    private function detectarGustosCompartidos() {
        echo "💖 Detectando gustos compartidos...\n";
        
        // Encontrar usuarios que han reaccionado a las mismas publicaciones
        $sql = "
            SELECT 
                r1.id_usuario as usuario1,
                r2.id_usuario as usuario2,
                COUNT(DISTINCT r1.id_publicacion) as publicaciones_comunes
            FROM reacciones r1
            JOIN reacciones r2 ON r1.id_publicacion = r2.id_publicacion
            WHERE r1.id_usuario < r2.id_usuario
            GROUP BY r1.id_usuario, r2.id_usuario
            HAVING publicaciones_comunes >= 2
        ";
        
        $stmt = $this->conexion->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $row['usuario1'],
                $row['usuario2'],
                'gustos_compartidos',
                "¡Ambos reaccionaron a {$row['publicaciones_comunes']} publicaciones similares! 💫",
                min(100, $row['publicaciones_comunes'] * 20)
            );
        }
        
        echo "   Encontradas: " . count($resultados) . " conexiones\n";
    }
    
    /**
     * Detecta usuarios que comentan en las mismas publicaciones
     */
    private function detectarInteresesComunes() {
        echo "💬 Detectando intereses comunes...\n";
        
        $sql = "
            SELECT 
                c1.usuario as usuario1,
                c2.usuario as usuario2,
                COUNT(DISTINCT c1.publicacion) as publicaciones_comunes
            FROM comentarios c1
            JOIN comentarios c2 ON c1.publicacion = c2.publicacion
            WHERE c1.usuario < c2.usuario
            GROUP BY c1.usuario, c2.usuario
            HAVING publicaciones_comunes >= 2
        ";
        
        $stmt = $this->conexion->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $row['usuario1'],
                $row['usuario2'],
                'intereses_comunes',
                "¡Ambos comentaron en {$row['publicaciones_comunes']} publicaciones! Tienen intereses similares 🎯",
                min(100, $row['publicaciones_comunes'] * 25)
            );
        }
        
        echo "   Encontradas: " . count($resultados) . " conexiones\n";
    }
    
    /**
     * Detecta amigos de amigos con intereses comunes
     */
    private function detectarAmigosDeAmigos() {
        echo "👥 Detectando amigos de amigos...\n";
        
        $sql = "
            SELECT DISTINCT
                a1.de as usuario1,
                a2.para as usuario2,
                u1.usuario as nombre1,
                u2.usuario as nombre2,
                u_comun.usuario as amigo_comun
            FROM amigos a1
            JOIN amigos a2 ON a1.para = a2.de
            JOIN usuarios u1 ON a1.de = u1.id_use
            JOIN usuarios u2 ON a2.para = u2.id_use
            JOIN usuarios u_comun ON a1.para = u_comun.id_use
            WHERE a1.estado = 1
            AND a2.estado = 1
            AND a1.de < a2.para
            AND NOT EXISTS (
                SELECT 1 FROM amigos 
                WHERE (de = a1.de AND para = a2.para) 
                OR (de = a2.para AND para = a1.de)
            )
            LIMIT 50
        ";
        
        $stmt = $this->conexion->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $row['usuario1'],
                $row['usuario2'],
                'amigos_de_amigos',
                "¡Ambos son amigos de {$row['amigo_comun']}! 🌟",
                60
            );
        }
        
        echo "   Encontradas: " . count($resultados) . " conexiones\n";
    }
    
    /**
     * Detecta usuarios activos en horarios similares
     */
    private function detectarHorariosCoincidentes() {
        echo "🕐 Detectando patrones de actividad...\n";
        
        $sql = "
            SELECT 
                p1.usuario as usuario1,
                p2.usuario as usuario2,
                HOUR(p1.fecha) as hora,
                COUNT(*) as coincidencias
            FROM publicaciones p1
            JOIN publicaciones p2 ON HOUR(p1.fecha) = HOUR(p2.fecha)
            WHERE p1.usuario < p2.usuario
            AND p1.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p1.usuario, p2.usuario, HOUR(p1.fecha)
            HAVING coincidencias >= 3
        ";
        
        $stmt = $this->conexion->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $hora = $row['hora'];
            $periodo = ($hora < 12) ? "mañana" : (($hora < 18) ? "tarde" : "noche");
            
            $this->guardarConexion(
                $row['usuario1'],
                $row['usuario2'],
                'horarios_coincidentes',
                "¡Ambos suelen estar activos en la {$periodo}! 🌙",
                40
            );
        }
        
        echo "   Encontradas: " . count($resultados) . " conexiones\n";
    }
    
    /**
     * Guarda una conexión mística en la base de datos
     */
    private function guardarConexion($usuario1, $usuario2, $tipo, $descripcion, $puntuacion) {
        try {
            $sql = "
                INSERT INTO conexiones_misticas 
                (usuario1_id, usuario2_id, tipo_conexion, descripcion, puntuacion)
                VALUES (:u1, :u2, :tipo, :desc, :punt)
                ON DUPLICATE KEY UPDATE 
                    descripcion = :desc2,
                    puntuacion = :punt2,
                    fecha_deteccion = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':u1' => min($usuario1, $usuario2),
                ':u2' => max($usuario1, $usuario2),
                ':tipo' => $tipo,
                ':desc' => $descripcion,
                ':punt' => $puntuacion,
                ':desc2' => $descripcion,
                ':punt2' => $puntuacion
            ]);
        } catch (Exception $e) {
            // Silenciar errores de duplicados
        }
    }
    
    /**
     * Obtiene las conexiones místicas de un usuario
     */
    public function obtenerConexionesUsuario($usuarioId, $limit = 5) {
        $sql = "
            SELECT 
                cm.*,
                CASE 
                    WHEN cm.usuario1_id = ? THEN u2.usuario
                    ELSE u1.usuario
                END as otro_usuario,
                CASE 
                    WHEN cm.usuario1_id = ? THEN u2.avatar
                    ELSE u1.avatar
                END as otro_avatar,
                CASE 
                    WHEN cm.usuario1_id = ? THEN cm.usuario2_id
                    ELSE cm.usuario1_id
                END as otro_id
            FROM conexiones_misticas cm
            JOIN usuarios u1 ON cm.usuario1_id = u1.id_use
            JOIN usuarios u2 ON cm.usuario2_id = u2.id_use
            WHERE (cm.usuario1_id = ? OR cm.usuario2_id = ?)
            ORDER BY cm.puntuacion DESC, cm.fecha_deteccion DESC
            LIMIT ?
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuarioId, $usuarioId, $usuarioId, $usuarioId, $usuarioId, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
