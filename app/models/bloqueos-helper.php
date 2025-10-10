<?php
/**
 * Funciones helper para el sistema de bloqueos
 */

/**
 * Verifica si un usuario ha bloqueado a otro usuario
 * @param PDO $conexion - Conexión a la base de datos
 * @param int $bloqueador_id - ID del usuario que bloquea
 * @param int $bloqueado_id - ID del usuario bloqueado
 * @return bool - true si está bloqueado, false si no
 */
function usuarioBloqueado($conexion, $bloqueador_id, $bloqueado_id) {
    try {
        $stmt = $conexion->prepare('
            SELECT COUNT(*) as count 
            FROM bloqueos 
            WHERE bloqueador_id = :bloqueador AND bloqueado_id = :bloqueado
        ');
        $stmt->bindParam(':bloqueador', $bloqueador_id, PDO::PARAM_INT);
        $stmt->bindParam(':bloqueado', $bloqueado_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    } catch (Exception $e) {
        error_log("Error verificando bloqueo: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica si existe un bloqueo mutuo entre dos usuarios (en cualquier dirección)
 * @param PDO $conexion - Conexión a la base de datos
 * @param int $usuario1_id - ID del primer usuario
 * @param int $usuario2_id - ID del segundo usuario
 * @return array - Array con información del bloqueo: ['bloqueado' => bool, 'direccion' => string|null]
 */
function verificarBloqueoMutuo($conexion, $usuario1_id, $usuario2_id) {
    try {
        $stmt = $conexion->prepare('
            SELECT bloqueador_id, bloqueado_id 
            FROM bloqueos 
            WHERE (bloqueador_id = :user1 AND bloqueado_id = :user2)
               OR (bloqueador_id = :user2_check AND bloqueado_id = :user1_check)
        ');
        $stmt->bindParam(':user1', $usuario1_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2', $usuario2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user2_check', $usuario2_id, PDO::PARAM_INT);
        $stmt->bindParam(':user1_check', $usuario1_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $bloqueo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bloqueo) {
            if ($bloqueo['bloqueador_id'] == $usuario1_id) {
                return ['bloqueado' => true, 'direccion' => 'yo_bloquee'];
            } else {
                return ['bloqueado' => true, 'direccion' => 'me_bloquearon'];
            }
        }
        
        return ['bloqueado' => false, 'direccion' => null];
    } catch (Exception $e) {
        error_log("Error verificando bloqueo mutuo: " . $e->getMessage());
        return ['bloqueado' => false, 'direccion' => null];
    }
}

/**
 * Obtiene la lista de usuarios bloqueados por un usuario
 * @param PDO $conexion - Conexión a la base de datos
 * @param int $usuario_id - ID del usuario
 * @return array - Array de IDs de usuarios bloqueados
 */
function obtenerUsuariosBloqueados($conexion, $usuario_id) {
    try {
        $stmt = $conexion->prepare('
            SELECT bloqueado_id 
            FROM bloqueos 
            WHERE bloqueador_id = :usuario_id
        ');
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $bloqueados = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bloqueados[] = $row['bloqueado_id'];
        }
        
        return $bloqueados;
    } catch (Exception $e) {
        error_log("Error obteniendo usuarios bloqueados: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene la lista de usuarios que han bloqueado a un usuario
 * @param PDO $conexion - Conexión a la base de datos
 * @param int $usuario_id - ID del usuario
 * @return array - Array de IDs de usuarios que lo han bloqueado
 */
function obtenerUsuariosQueMeBloquearon($conexion, $usuario_id) {
    try {
        $stmt = $conexion->prepare('
            SELECT bloqueador_id 
            FROM bloqueos 
            WHERE bloqueado_id = :usuario_id
        ');
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $bloqueadores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bloqueadores[] = $row['bloqueador_id'];
        }
        
        return $bloqueadores;
    } catch (Exception $e) {
        error_log("Error obteniendo usuarios que me bloquearon: " . $e->getMessage());
        return [];
    }
}

/**
 * Genera una cláusula WHERE SQL para excluir usuarios bloqueados
 * @param PDO $conexion - Conexión a la base de datos
 * @param int $usuario_id - ID del usuario actual
 * @param string $campo_usuario - Nombre del campo que contiene el ID del usuario a filtrar
 * @return string - Cláusula WHERE SQL
 */
function generarFiltroBloqueos($conexion, $usuario_id, $campo_usuario = 'id_use') {
    $bloqueados = obtenerUsuariosBloqueados($conexion, $usuario_id);
    $bloqueadores = obtenerUsuariosQueMeBloquearon($conexion, $usuario_id);
    
    $todos_excluidos = array_merge($bloqueados, $bloqueadores);
    
    if (empty($todos_excluidos)) {
        return '1=1'; // No hay usuarios bloqueados
    }
    
    $ids_escaped = array_map('intval', $todos_excluidos);
    return $campo_usuario . ' NOT IN (' . implode(',', $ids_escaped) . ')';
}
?>