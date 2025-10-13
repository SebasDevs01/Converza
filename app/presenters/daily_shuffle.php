<?php
session_start();
require_once __DIR__.'/../models/config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$usuario_id = $_SESSION['id'];
$fecha_hoy = date('Y-m-d');

try {
    // Limpiar shuffle de días anteriores
    $stmtClean = $conexion->prepare("DELETE FROM daily_shuffle WHERE fecha_shuffle < ?");
    $stmtClean->execute([$fecha_hoy]);
    
    // Verificar si ya existe shuffle para hoy
    $stmtCheck = $conexion->prepare("
        SELECT COUNT(*) as count 
        FROM daily_shuffle 
        WHERE usuario_id = ? AND fecha_shuffle = ?
    ");
    $stmtCheck->execute([$usuario_id, $fecha_hoy]);
    $existeHoy = $stmtCheck->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    if (!$existeHoy) {
        // Crear nuevo shuffle para hoy
        
        // Obtener usuarios disponibles (que no sean yo, ni mis amigos actuales, ni bloqueados)
        $stmtUsuarios = $conexion->prepare("
            SELECT u.* 
            FROM usuarios u
            WHERE u.id_use != :usuario_id
            AND u.id_use NOT IN (
                -- Excluir amigos actuales
                SELECT 
                    CASE 
                        WHEN a.de = :usuario_id2 THEN a.para
                        ELSE a.de 
                    END as amigo_id
                FROM amigos a 
                WHERE (a.de = :usuario_id3 OR a.para = :usuario_id4)
                AND a.estado IN (0, 1) -- Pendientes y confirmados
            )
            AND u.id_use NOT IN (
                -- Excluir usuarios bloqueados
                SELECT bloqueado_id FROM bloqueos WHERE bloqueador_id = :usuario_id5
                UNION
                SELECT bloqueador_id FROM bloqueos WHERE bloqueado_id = :usuario_id6
            )
            ORDER BY RAND()
            LIMIT 10
        ");
        
        $stmtUsuarios->execute([
            ':usuario_id' => $usuario_id,
            ':usuario_id2' => $usuario_id,
            ':usuario_id3' => $usuario_id,
            ':usuario_id4' => $usuario_id,
            ':usuario_id5' => $usuario_id,
            ':usuario_id6' => $usuario_id
        ]);
        
        $usuariosDisponibles = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
        
        // Insertar en daily_shuffle
        $stmtInsert = $conexion->prepare("
            INSERT INTO daily_shuffle (usuario_id, usuario_mostrado_id, fecha_shuffle)
            VALUES (?, ?, ?)
        ");
        
        foreach($usuariosDisponibles as $usuarioMostrado) {
            $stmtInsert->execute([$usuario_id, $usuarioMostrado['id_use'], $fecha_hoy]);
        }
    }
    
    // Obtener el shuffle del día
    $stmtShuffle = $conexion->prepare("
        SELECT 
            ds.*,
            u.usuario,
            u.nombre,
            u.avatar,
            u.descripcion,
            u.sexo,
            ds.ya_contactado
        FROM daily_shuffle ds
        INNER JOIN usuarios u ON ds.usuario_mostrado_id = u.id_use
        WHERE ds.usuario_id = ? AND ds.fecha_shuffle = ?
        ORDER BY ds.created_at ASC
    ");
    
    $stmtShuffle->execute([$usuario_id, $fecha_hoy]);
    $shuffle = $stmtShuffle->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'shuffle' => $shuffle,
        'fecha' => $fecha_hoy,
        'total' => count($shuffle),
        'nuevo_shuffle' => !$existeHoy
    ]);

} catch(Exception $e) {
    echo json_encode([
        'error' => 'Error al generar Daily Shuffle: ' . $e->getMessage()
    ]);
}
?>