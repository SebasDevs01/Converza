<?php
/**
 * Helper para verificar permisos de chat entre usuarios
 * Sistema flexible: Amigos confirmados, Seguidores mutuos, o Solicitud de mensaje pendiente
 */

/**
 * Verifica si dos usuarios pueden chatear libremente
 * @return array ['puede_chatear' => bool, 'motivo' => string, 'tipo_relacion' => string]
 */
function verificarPermisoChat($conexion, $usuario1, $usuario2) {
    $resultado = [
        'puede_chatear' => false,
        'motivo' => 'sin_relacion',
        'tipo_relacion' => null,
        'necesita_solicitud' => false
    ];
    
    // 1. PRIORIDAD MÁXIMA: Verificar si son amigos confirmados (estado = 1)
    // Si son amigos, ignora si son seguidores (amigos > seguidores)
    $stmtAmigos = $conexion->prepare("
        SELECT estado FROM amigos 
        WHERE ((de = :u1 AND para = :u2) OR (de = :u2_alt AND para = :u1_alt))
        LIMIT 1
    ");
    $stmtAmigos->execute([
        ':u1' => $usuario1,
        ':u2' => $usuario2,
        ':u2_alt' => $usuario2,
        ':u1_alt' => $usuario1
    ]);
    $amistad = $stmtAmigos->fetch(PDO::FETCH_ASSOC);
    
    if ($amistad && $amistad['estado'] == 1) {
        $resultado['puede_chatear'] = true;
        $resultado['motivo'] = 'amigos_confirmados';
        $resultado['tipo_relacion'] = 'amigos';
        return $resultado; // RETORNA AQUÍ - No verifica seguidores si son amigos
    }
    
    // 2. PRIORIDAD MEDIA: Verificar si se siguen mutuamente (SOLO si NO son amigos)
    $stmtSeguidoresMutuos = $conexion->prepare("
        SELECT COUNT(*) as total FROM seguidores s1
        INNER JOIN seguidores s2 
            ON s1.seguidor_id = s2.seguido_id 
            AND s1.seguido_id = s2.seguidor_id
        WHERE s1.seguidor_id = :u1 AND s1.seguido_id = :u2
    ");
    $stmtSeguidoresMutuos->execute([
        ':u1' => $usuario1,
        ':u2' => $usuario2
    ]);
    $seguidoresMutuos = $stmtSeguidoresMutuos->fetch(PDO::FETCH_ASSOC);
    
    if ($seguidoresMutuos && $seguidoresMutuos['total'] > 0) {
        $resultado['puede_chatear'] = true;
        $resultado['motivo'] = 'seguidores_mutuos';
        $resultado['tipo_relacion'] = 'seguidores_mutuos';
        return $resultado;
    }
    
    // 3. Verificar si hay solicitud de mensaje aceptada
    $stmtSolicitud = $conexion->prepare("
        SELECT estado FROM solicitudes_mensaje 
        WHERE ((de = :u1 AND para = :u2) OR (de = :u2_alt AND para = :u1_alt))
        AND estado = 'aceptada'
        LIMIT 1
    ");
    $stmtSolicitud->execute([
        ':u1' => $usuario1,
        ':u2' => $usuario2,
        ':u2_alt' => $usuario2,
        ':u1_alt' => $usuario1
    ]);
    $solicitud = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);
    
    if ($solicitud) {
        $resultado['puede_chatear'] = true;
        $resultado['motivo'] = 'solicitud_mensaje_aceptada';
        $resultado['tipo_relacion'] = 'solicitud_aceptada';
        return $resultado;
    }
    
    // 4. Si no cumple ninguna condición, necesita enviar solicitud de mensaje
    $resultado['puede_chatear'] = false;
    $resultado['necesita_solicitud'] = true;
    $resultado['motivo'] = 'necesita_solicitud_mensaje';
    
    return $resultado;
}

/**
 * Verifica si ya existe una solicitud de mensaje pendiente
 * @return array|false Datos de la solicitud o false si no existe
 */
function tieneSolicitudMensajePendiente($conexion, $de, $para) {
    $stmt = $conexion->prepare("
        SELECT id, estado, primer_mensaje, fecha_solicitud FROM solicitudes_mensaje 
        WHERE de = :de AND para = :para
        LIMIT 1
    ");
    $stmt->execute([':de' => $de, ':para' => $para]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Crea una solicitud de mensaje
 */
function crearSolicitudMensaje($conexion, $de, $para, $primerMensaje = null) {
    try {
        $stmt = $conexion->prepare("
            INSERT INTO solicitudes_mensaje (de, para, estado, primer_mensaje) 
            VALUES (:de, :para, 'pendiente', :mensaje)
        ");
        return $stmt->execute([
            ':de' => $de,
            ':para' => $para,
            ':mensaje' => $primerMensaje
        ]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Acepta una solicitud de mensaje
 */
function aceptarSolicitudMensaje($conexion, $solicitudId, $usuarioActual) {
    try {
        $stmt = $conexion->prepare("
            UPDATE solicitudes_mensaje 
            SET estado = 'aceptada', fecha_respuesta = CURRENT_TIMESTAMP 
            WHERE id = :id AND para = :usuario
        ");
        return $stmt->execute([
            ':id' => $solicitudId,
            ':usuario' => $usuarioActual
        ]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Rechaza una solicitud de mensaje
 */
function rechazarSolicitudMensaje($conexion, $solicitudId, $usuarioActual) {
    try {
        $stmt = $conexion->prepare("
            UPDATE solicitudes_mensaje 
            SET estado = 'rechazada', fecha_respuesta = CURRENT_TIMESTAMP 
            WHERE id = :id AND para = :usuario
        ");
        return $stmt->execute([
            ':id' => $solicitudId,
            ':usuario' => $usuarioActual
        ]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Obtiene solicitudes de mensaje pendientes para un usuario
 */
function obtenerSolicitudesMensajePendientes($conexion, $usuarioId) {
    $stmt = $conexion->prepare("
        SELECT sm.*, u.usuario, u.nombre, u.avatar 
        FROM solicitudes_mensaje sm
        INNER JOIN usuarios u ON sm.de = u.id_use
        WHERE sm.para = :usuario AND sm.estado = 'pendiente'
        ORDER BY sm.fecha_solicitud DESC
    ");
    $stmt->execute([':usuario' => $usuarioId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
