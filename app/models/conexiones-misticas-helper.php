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
     * 🚀 GENERACIÓN AUTOMÁTICA DE CONEXIONES PARA UN USUARIO ESPECÍFICO
     * Se ejecuta automáticamente cuando el usuario carga la página
     * Solo genera si no tiene conexiones o si han pasado más de 6 horas
     */
    public function generarConexionesAutomaticas($usuario_id) {
        try {
            // Verificar si necesita actualización
            $necesitaActualizar = $this->necesitaActualizacion($usuario_id);
            
            if (!$necesitaActualizar) {
                return false; // Ya tiene conexiones recientes
            }
            
            // Generar conexiones solo para este usuario (rápido y eficiente)
            $this->detectarGustosCompartidosUsuario($usuario_id);
            $this->detectarInteresesComunesUsuario($usuario_id);
            $this->detectarAmigosDeAmigosUsuario($usuario_id);
            $this->detectarHorariosCoincidentesUsuario($usuario_id);
            
            // Actualizar timestamp de última generación
            $this->marcarActualizacion($usuario_id);
            
            return true;
        } catch (Exception $e) {
            error_log("Error generando conexiones para usuario {$usuario_id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si el usuario necesita actualización de conexiones
     */
    private function necesitaActualizacion($usuario_id) {
        // Verificar última actualización
        $stmt = $this->conexion->prepare("
            SELECT MAX(fecha_deteccion) as ultima
            FROM conexiones_misticas
            WHERE usuario1_id = ? OR usuario2_id = ?
        ");
        $stmt->execute([$usuario_id, $usuario_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no tiene conexiones, necesita actualización
        if (!$resultado['ultima']) {
            return true;
        }
        
        // Si han pasado más de 6 horas, necesita actualización
        $ultima = strtotime($resultado['ultima']);
        $ahora = time();
        $diferencia = ($ahora - $ultima) / 3600; // Horas
        
        return $diferencia >= 6;
    }
    
    /**
     * Marca la actualización en el sistema
     */
    private function marcarActualizacion($usuario_id) {
        $stmt = $this->conexion->prepare("
            INSERT INTO conexiones_misticas_contador 
            (usuario_id, ultima_actualizacion, total_conexiones, nuevas_conexiones)
            SELECT 
                ?,
                NOW(),
                COUNT(*),
                COUNT(*)
            FROM conexiones_misticas
            WHERE usuario1_id = ? OR usuario2_id = ?
            ON DUPLICATE KEY UPDATE
                ultima_actualizacion = NOW(),
                total_conexiones = VALUES(total_conexiones),
                nuevas_conexiones = VALUES(nuevas_conexiones)
        ");
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
    }
    
    /**
     * Actualizar conexiones automáticamente (para cron job)
     */
    public function actualizarConexionesAutomatico() {
        try {
            // Limpiar conexiones antiguas (más de 30 días)
            $this->limpiarConexionesAntiguas();
            
            // Detectar nuevas conexiones
            $this->detectarConexiones();
            
            // Actualizar contadores
            $this->actualizarContadores();
            
            return true;
        } catch (Exception $e) {
            error_log("Error en actualización automática: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar conexiones antiguas de un usuario específico
     */
    public function limpiarConexionesUsuario($usuario_id) {
        try {
            $stmt = $this->conexion->prepare("
                DELETE FROM conexiones_misticas
                WHERE (usuario1_id = ? OR usuario2_id = ?)
            ");
            $stmt->execute([$usuario_id, $usuario_id]);
            
            // Resetear contador
            $this->resetearContador($usuario_id);
            
            return [
                'success' => true,
                'mensaje' => 'Conexiones limpiadas correctamente',
                'eliminadas' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            error_log("Error limpiando conexiones: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error al limpiar conexiones'
            ];
        }
    }
    
    /**
     * Limpiar conexiones de todos los usuarios (más de 30 días)
     */
    private function limpiarConexionesAntiguas() {
        $stmt = $this->conexion->prepare("
            DELETE FROM conexiones_misticas
            WHERE fecha_deteccion < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        echo "🧹 Limpiadas " . $stmt->rowCount() . " conexiones antiguas\n";
    }
    
    /**
     * Actualizar contador de conexiones por usuario
     */
    private function actualizarContadores() {
        $sql = "
            INSERT INTO conexiones_misticas_contador (usuario_id, total_conexiones, nuevas_conexiones, ultima_actualizacion)
            SELECT 
                usuario_id,
                COUNT(*) as total,
                SUM(CASE WHEN fecha_deteccion >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as nuevas,
                NOW()
            FROM (
                SELECT usuario1_id as usuario_id, fecha_deteccion FROM conexiones_misticas
                UNION ALL
                SELECT usuario2_id as usuario_id, fecha_deteccion FROM conexiones_misticas
            ) as todas_conexiones
            GROUP BY usuario_id
            ON DUPLICATE KEY UPDATE
                total_conexiones = VALUES(total_conexiones),
                nuevas_conexiones = VALUES(nuevas_conexiones),
                ultima_actualizacion = NOW()
        ";
        
        $this->conexion->exec($sql);
        echo "📊 Contadores actualizados\n";
    }
    
    /**
     * Resetear contador de un usuario
     */
    private function resetearContador($usuario_id) {
        $stmt = $this->conexion->prepare("
            DELETE FROM conexiones_misticas_contador WHERE usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
    }
    
    /**
     * Obtener contador de conexiones de un usuario
     */
    public function obtenerContador($usuario_id) {
        $stmt = $this->conexion->prepare("
            SELECT 
                COALESCE(total_conexiones, 0) as total,
                COALESCE(nuevas_conexiones, 0) as nuevas,
                ultima_actualizacion
            FROM conexiones_misticas_contador
            WHERE usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no existe, calcular en tiempo real
        if (!$resultado) {
            $stmtCalc = $this->conexion->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN fecha_deteccion >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as nuevas
                FROM conexiones_misticas
                WHERE usuario1_id = ? OR usuario2_id = ?
            ");
            $stmtCalc->execute([$usuario_id, $usuario_id]);
            $resultado = $stmtCalc->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado) {
                return ['total' => 0, 'nuevas' => 0, 'ultima_actualizacion' => null];
            }
        }
        
        return $resultado;
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
    
    // ========================================
    // 🚀 MÉTODOS OPTIMIZADOS PARA UN SOLO USUARIO
    // ========================================
    
    /**
     * Detecta gustos compartidos para un usuario específico
     */
    private function detectarGustosCompartidosUsuario($usuario_id) {
        $sql = "
            SELECT 
                r2.id_usuario as otro_usuario,
                COUNT(DISTINCT r1.id_publicacion) as publicaciones_comunes
            FROM reacciones r1
            JOIN reacciones r2 ON r1.id_publicacion = r2.id_publicacion
            WHERE r1.id_usuario = ? 
            AND r2.id_usuario != ?
            AND r2.id_usuario NOT IN (
                SELECT usuario2_id FROM conexiones_misticas 
                WHERE usuario1_id = ? AND tipo_conexion = 'gustos_compartidos'
            )
            GROUP BY r2.id_usuario
            HAVING publicaciones_comunes >= 2
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $usuario_id,
                $row['otro_usuario'],
                'gustos_compartidos',
                "¡Ambos reaccionaron a {$row['publicaciones_comunes']} publicaciones similares! 💫",
                min(100, $row['publicaciones_comunes'] * 20)
            );
        }
    }
    
    /**
     * Detecta intereses comunes para un usuario específico
     */
    private function detectarInteresesComunesUsuario($usuario_id) {
        $sql = "
            SELECT 
                c2.id_usuario as otro_usuario,
                COUNT(DISTINCT c1.id_publicacion) as publicaciones_comunes
            FROM comentarios c1
            JOIN comentarios c2 ON c1.id_publicacion = c2.id_publicacion
            WHERE c1.id_usuario = ?
            AND c2.id_usuario != ?
            AND c2.id_usuario NOT IN (
                SELECT usuario2_id FROM conexiones_misticas 
                WHERE usuario1_id = ? AND tipo_conexion = 'intereses_comunes'
            )
            GROUP BY c2.id_usuario
            HAVING publicaciones_comunes >= 2
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $usuario_id,
                $row['otro_usuario'],
                'intereses_comunes',
                "¡Ambos comentaron en {$row['publicaciones_comunes']} temas similares! 💬",
                min(100, $row['publicaciones_comunes'] * 25)
            );
        }
    }
    
    /**
     * Detecta amigos de amigos para un usuario específico
     */
    private function detectarAmigosDeAmigosUsuario($usuario_id) {
        $sql = "
            SELECT 
                a2.id_amigo as otro_usuario,
                COUNT(*) as amigos_comunes
            FROM amigos a1
            JOIN amigos a2 ON a1.id_amigo = a2.id_usuario
            WHERE a1.id_usuario = ?
            AND a2.id_amigo != ?
            AND a2.id_amigo NOT IN (
                SELECT id_amigo FROM amigos WHERE id_usuario = ?
            )
            AND a2.id_amigo NOT IN (
                SELECT usuario2_id FROM conexiones_misticas 
                WHERE usuario1_id = ? AND tipo_conexion = 'amigos_de_amigos'
            )
            AND a1.estado = 'aceptada'
            AND a2.estado = 'aceptada'
            GROUP BY a2.id_amigo
            HAVING amigos_comunes >= 1
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id, $usuario_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $this->guardarConexion(
                $usuario_id,
                $row['otro_usuario'],
                'amigos_de_amigos',
                "¡Tienen {$row['amigos_comunes']} amigos en común! 👥",
                min(100, $row['amigos_comunes'] * 20)
            );
        }
    }
    
    /**
     * Detecta horarios coincidentes para un usuario específico
     */
    private function detectarHorariosCoincidentesUsuario($usuario_id) {
        $sql = "
            SELECT 
                p2.id_usuario as otro_usuario,
                COUNT(*) as coincidencias,
                HOUR(p1.fecha) as hora_comun
            FROM publicaciones p1
            JOIN publicaciones p2 ON HOUR(p1.fecha) = HOUR(p2.fecha)
            WHERE p1.id_usuario = ?
            AND p2.id_usuario != ?
            AND p2.id_usuario NOT IN (
                SELECT usuario2_id FROM conexiones_misticas 
                WHERE usuario1_id = ? AND tipo_conexion = 'horarios_coincidentes'
            )
            AND p1.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p2.id_usuario, HOUR(p1.fecha)
            HAVING coincidencias >= 3
        ";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as $row) {
            $hora = $row['hora_comun'];
            $periodo = ($hora < 12) ? "mañana" : (($hora < 18) ? "tarde" : "noche");
            
            $this->guardarConexion(
                $usuario_id,
                $row['otro_usuario'],
                'horarios_coincidentes',
                "¡Ambos suelen estar activos en la {$periodo}! 🌙",
                40
            );
        }
    }
}
