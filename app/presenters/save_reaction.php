<?php
// ═══════════════════════════════════════════════════════════════
// 🎯 GUARDAR REACCIÓN - ULTRA SEGURO CON KARMA
// ═══════════════════════════════════════════════════════════════

// 🔍 DEBUG MODE - Activar para ver errores
define('DEBUG_KARMA', true);

// Desactivar TODOS los errores para producción
@ini_set('display_errors', DEBUG_KARMA ? '1' : '0');
@ini_set('display_startup_errors', DEBUG_KARMA ? '1' : '0');
@error_reporting(DEBUG_KARMA ? E_ALL : 0);
@ini_set('log_errors', '1');

// Función de log debug
function debugLog($message, $data = null) {
    if (DEBUG_KARMA) {
        $logMsg = "🔥 KARMA DEBUG: " . $message;
        if ($data !== null) {
            $logMsg .= " | " . json_encode($data);
        }
        error_log($logMsg);
    }
}

// Buffer para capturar cualquier salida
ob_start();

// Función de limpieza para shutdown
function cleanShutdown() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Limpiar TODOS los buffers
        while (@ob_get_level()) {
            @ob_end_clean();
        }
        
        // Nuevo buffer limpio
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error crítico del servidor',
            'error_details' => [
                'message' => $error['message'],
                'file' => basename($error['file']),
                'line' => $error['line']
            ]
        ]);
        
        ob_end_flush();
        exit;
    }
}

register_shutdown_function('cleanShutdown');

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Sesión
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// ═══════════════════════════════════════════════════════════════
// 1. CARGAR CONFIG (CRÍTICO)
// ═══════════════════════════════════════════════════════════════
$conexion = null;

if (!file_exists(__DIR__.'/../models/config.php')) {
    ob_end_clean();
    die(json_encode(['success' => false, 'message' => 'config.php no encontrado']));
}

try {
    require_once(__DIR__.'/../models/config.php');
    
    if (!isset($conexion) || !$conexion) {
        throw new Exception('Variable $conexion no definida');
    }
} catch (Throwable $e) {
    ob_end_clean();
    die(json_encode([
        'success' => false, 
        'message' => 'Error cargando configuración',
        'error' => $e->getMessage()
    ]));
}

// ═══════════════════════════════════════════════════════════════
// 2. CARGAR SISTEMA DE KARMA (OPCIONAL PERO IMPORTANTE)
// ═══════════════════════════════════════════════════════════════
$karmaTriggers = null;
$karmaHelper = null;
$notificacionesTriggers = null;

// Cargar KarmaSocialTriggers
try {
    if (file_exists(__DIR__.'/../models/karma-social-triggers.php')) {
        require_once(__DIR__.'/../models/karma-social-triggers.php');
        
        if (class_exists('KarmaSocialTriggers')) {
            $karmaTriggers = new KarmaSocialTriggers($conexion);
        }
    }
} catch (Throwable $e) {
    @error_log("⚠️ KarmaSocialTriggers no cargado: " . $e->getMessage() . " en línea " . $e->getLine());
    // Continuar sin karma
}

// Cargar KarmaSocialHelper
try {
    if (file_exists(__DIR__.'/../models/karma-social-helper.php')) {
        require_once(__DIR__.'/../models/karma-social-helper.php');
        
        if (class_exists('KarmaSocialHelper')) {
            $karmaHelper = new KarmaSocialHelper($conexion);
        }
    }
} catch (Throwable $e) {
    @error_log("⚠️ KarmaSocialHelper no cargado: " . $e->getMessage() . " en línea " . $e->getLine());
    // Continuar sin helper
}

// Cargar NotificacionesTriggers
try {
    if (file_exists(__DIR__.'/../models/notificaciones-triggers.php')) {
        require_once(__DIR__.'/../models/notificaciones-triggers.php');
        
        if (class_exists('NotificacionesTriggers')) {
            $notificacionesTriggers = new NotificacionesTriggers($conexion);
        }
    }
} catch (Throwable $e) {
    @error_log("⚠️ NotificacionesTriggers no cargado: " . $e->getMessage() . " en línea " . $e->getLine());
    // Continuar sin notificaciones
}

// ═══════════════════════════════════════════════════════════════
// 3. OBTENER Y VALIDAR DATOS
// ═══════════════════════════════════════════════════════════════
$id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
$id_publicacion = isset($_POST['id_publicacion']) ? (int)$_POST['id_publicacion'] : null;
$tipo_reaccion = isset($_POST['tipo_reaccion']) ? trim($_POST['tipo_reaccion']) : null;

debugLog("📥 Datos recibidos", [
    'id_usuario' => $id_usuario,
    'id_publicacion' => $id_publicacion,
    'tipo_reaccion' => $tipo_reaccion
]);

if (!$id_usuario || !$id_publicacion || !$tipo_reaccion) {
    ob_end_clean();
    die(json_encode(['success' => false, 'message' => 'Datos incompletos']));
}

$validReactions = ['me_gusta', 'me_encanta', 'me_divierte', 'me_asombra', 'me_entristece', 'me_enoja'];
if (!in_array($tipo_reaccion, $validReactions, true)) {
    ob_end_clean();
    die(json_encode(['success' => false, 'message' => 'Tipo de reacción no válido']));
}

// ═══════════════════════════════════════════════════════════════
// 4. OBTENER AUTOR DE LA PUBLICACIÓN
// ═══════════════════════════════════════════════════════════════
$publicacion = null;
try {
    $stmtAutor = $conexion->prepare("SELECT usuario FROM publicaciones WHERE id_pub = ?");
    $stmtAutor->execute([$id_publicacion]);
    $publicacion = $stmtAutor->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Continuar aunque falle
}

// ═══════════════════════════════════════════════════════════════
// 5. VERIFICAR/CREAR TABLA REACCIONES
// ═══════════════════════════════════════════════════════════════
try {
    $conexion->query("SELECT 1 FROM reacciones LIMIT 1");
} catch (PDOException $e) {
    try {
        $conexion->exec("
            CREATE TABLE IF NOT EXISTS reacciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                id_publicacion INT NOT NULL,
                tipo_reaccion ENUM('me_gusta', 'me_encanta', 'me_divierte', 'me_asombra', 'me_entristece', 'me_enoja') NOT NULL,
                fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_post_reaction (id_usuario, id_publicacion)
            )
        ");
    } catch (PDOException $e2) {
        ob_end_clean();
        die(json_encode(['success' => false, 'message' => 'Error creando tabla']));
    }
}

// ═══════════════════════════════════════════════════════════════
// 6. PROCESAR REACCIÓN
// ═══════════════════════════════════════════════════════════════
try {
    // Verificar reacción existente
    $stmt = $conexion->prepare("SELECT id, tipo_reaccion FROM reacciones WHERE id_publicacion = ? AND id_usuario = ?");
    $stmt->execute([$id_publicacion, $id_usuario]);
    $existingReaction = $stmt->fetch(PDO::FETCH_ASSOC);

    $action = '';

    if ($existingReaction) {
        if ($existingReaction['tipo_reaccion'] === $tipo_reaccion) {
            // ═══════════════════════════════════════════════════
            // CASO 1: ELIMINAR REACCIÓN (TOGGLE)
            // ═══════════════════════════════════════════════════
            $stmt = $conexion->prepare("DELETE FROM reacciones WHERE id_usuario = ? AND id_publicacion = ?");
            $stmt->execute([$id_usuario, $id_publicacion]);
            $action = 'removed';
            
            // Revertir karma si está disponible (AL AUTOR DE LA PUBLICACIÓN, NO AL QUE REACCIONA)
            if ($karmaTriggers && $publicacion && $publicacion['usuario'] != $id_usuario) {
                $autorPublicacion = $publicacion['usuario'];
                try {
                    if (method_exists($karmaTriggers, 'revertirReaccion')) {
                        $karmaTriggers->revertirReaccion($autorPublicacion, $id_publicacion, $existingReaction['tipo_reaccion']);
                    }
                } catch (Throwable $e) {
                    @error_log("Error revirtiendo karma: " . $e->getMessage());
                }
            }
        } else {
            // ═══════════════════════════════════════════════════
            // CASO 2: CAMBIAR REACCIÓN
            // ═══════════════════════════════════════════════════
            
            // Revertir karma anterior (AL AUTOR DE LA PUBLICACIÓN, NO AL QUE REACCIONA)
            if ($karmaTriggers && $publicacion && $publicacion['usuario'] != $id_usuario) {
                $autorPublicacion = $publicacion['usuario'];
                try {
                    if (method_exists($karmaTriggers, 'revertirReaccion')) {
                        $karmaTriggers->revertirReaccion($autorPublicacion, $id_publicacion, $existingReaction['tipo_reaccion']);
                    }
                } catch (Throwable $e) {
                    @error_log("Error revirtiendo karma anterior: " . $e->getMessage());
                }
            }
            
            // Actualizar reacción
            $stmt = $conexion->prepare("UPDATE reacciones SET tipo_reaccion = ?, fecha = NOW() WHERE id_usuario = ? AND id_publicacion = ?");
            $stmt->execute([$tipo_reaccion, $id_usuario, $id_publicacion]);
            $action = 'updated';
            
            // Aplicar nuevo karma (AL AUTOR DE LA PUBLICACIÓN, NO AL QUE REACCIONA)
            if ($karmaTriggers && $publicacion && $publicacion['usuario'] != $id_usuario) {
                $autorPublicacion = $publicacion['usuario'];
                try {
                    if (method_exists($karmaTriggers, 'registrarReaccionPositiva')) {
                        $karmaTriggers->registrarReaccionPositiva($autorPublicacion, $id_publicacion, $tipo_reaccion);
                    }
                } catch (Throwable $e) {
                    @error_log("Error aplicando nuevo karma: " . $e->getMessage());
                }
            }
        }
    } else {
        // ═══════════════════════════════════════════════════
        // CASO 3: NUEVA REACCIÓN
        // ═══════════════════════════════════════════════════
        $stmt = $conexion->prepare("INSERT INTO reacciones (id_usuario, id_publicacion, tipo_reaccion, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$id_usuario, $id_publicacion, $tipo_reaccion]);
        $action = 'added';
        
        // Procesar notificación y karma solo si no es el mismo usuario
        if ($publicacion && $publicacion['usuario'] != $id_usuario) {
            $autorPublicacion = $publicacion['usuario'];
            
            // Obtener nombre del usuario
            $nombreUsuario = 'Usuario';
            try {
                $stmtNombre = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = ?");
                $stmtNombre->execute([$id_usuario]);
                $datosUsuario = $stmtNombre->fetch(PDO::FETCH_ASSOC);
                if ($datosUsuario) {
                    $nombreUsuario = $datosUsuario['usuario'];
                }
            } catch (Throwable $e) {
                // Usar valor por defecto
            }
            
            // Enviar notificación
            if ($notificacionesTriggers) {
                try {
                    if (method_exists($notificacionesTriggers, 'nuevaReaccion')) {
                        $notificacionesTriggers->nuevaReaccion($id_usuario, $autorPublicacion, $nombreUsuario, $id_publicacion, $tipo_reaccion);
                    }
                } catch (Throwable $e) {
                    @error_log("Error enviando notificación: " . $e->getMessage());
                }
            }
            
            // Aplicar karma (AL AUTOR DE LA PUBLICACIÓN, NO AL QUE REACCIONA)
            if ($karmaTriggers) {
                try {
                    if (method_exists($karmaTriggers, 'registrarReaccionPositiva')) {
                        $karmaTriggers->registrarReaccionPositiva($autorPublicacion, $id_publicacion, $tipo_reaccion);
                    }
                } catch (Throwable $e) {
                    @error_log("Error aplicando karma: " . $e->getMessage());
                }
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // 7. OBTENER KARMA ACTUALIZADO Y CALCULAR PUNTOS
    // ═══════════════════════════════════════════════════════════════
    $karmaActualizado = null;
    $karmaNotificacion = null;

    // 🎯 IMPORTANTE: Obtener karma del USUARIO QUE REACCIONA, no del autor
    if ($id_usuario && $karmaHelper) {
        try {
            if (method_exists($karmaHelper, 'obtenerKarmaUsuario')) {
                // Obtener karma del usuario que está reaccionando
                $karmaData = $karmaHelper->obtenerKarmaUsuario($id_usuario);
                
                $karmaActualizado = [
                    'karma' => (string)($karmaData['karma_total'] ?? 0), // ⭐ STRING
                    'nivel' => $karmaData['nivel_data']['nivel'] ?? 1,
                    'nivel_titulo' => $karmaData['nivel_data']['titulo'] ?? 'Novato',
                    'nivel_emoji' => $karmaData['nivel_emoji'] ?? '🌱'
                ];
                
                // 🎯 CALCULAR PUNTOS DE LA REACCIÓN
                if ($action === 'added' || $action === 'updated') {
                    $puntosGanados = 0;
                    $mensajeNotificacion = '';
                    $tipoNotificacion = 'positivo';
                    
                    // ⭐ MAPEO DE PUNTOS POR REACCIÓN
                    switch ($tipo_reaccion) {
                        case 'me_gusta':
                            $puntosGanados = 5;
                            $mensajeNotificacion = '👍 ¡Me gusta!';
                            $tipoNotificacion = 'positivo';
                            break;
                        case 'me_encanta':
                            $puntosGanados = 10;
                            $mensajeNotificacion = '❤️ ¡Me encanta!';
                            $tipoNotificacion = 'positivo';
                            break;
                        case 'me_divierte':
                            $puntosGanados = 7;
                            $mensajeNotificacion = '😂 ¡Me divierte!';
                            $tipoNotificacion = 'positivo';
                            break;
                        case 'me_asombra':
                            $puntosGanados = 8;
                            $mensajeNotificacion = '😮 ¡Me asombra!';
                            $tipoNotificacion = 'positivo';
                            break;
                        case 'me_entristece':
                            $puntosGanados = -3;
                            $mensajeNotificacion = '😢 Me entristece';
                            $tipoNotificacion = 'negativo';
                            break;
                        case 'me_enoja':
                            $puntosGanados = -5;
                            $mensajeNotificacion = '� Me enoja';
                            $tipoNotificacion = 'negativo';
                            break;
                        default:
                            $puntosGanados = 5;
                            $mensajeNotificacion = 'Reacción registrada';
                            $tipoNotificacion = 'positivo';
                    }
                    
                    debugLog("🎯 Puntos calculados", [
                        'tipo_reaccion' => $tipo_reaccion,
                        'puntos' => $puntosGanados,
                        'mensaje' => $mensajeNotificacion,
                        'tipo' => $tipoNotificacion
                    ]);
                    
                    // 🎯 ACTUALIZAR KARMA EN LA BASE DE DATOS DEL USUARIO QUE REACCIONA
                    // Usando sistema de tablas karma_social + karma_total_usuarios
                    try {
                        // Obtener karma ANTES de actualizar (desde karma_total_usuarios)
                        $stmtKarmaAntes = $conexion->prepare("
                            SELECT karma_total 
                            FROM karma_total_usuarios 
                            WHERE usuario_id = ?
                        ");
                        $stmtKarmaAntes->execute([$id_usuario]);
                        $karmaAntesData = $stmtKarmaAntes->fetch(PDO::FETCH_ASSOC);
                        $karmaAntes = intval($karmaAntesData['karma_total'] ?? 0);
                        
                        debugLog("📊 Karma ANTES de actualizar", [
                            'usuario_id' => $id_usuario,
                            'karma_antes' => $karmaAntes,
                            'tabla' => 'karma_total_usuarios'
                        ]);
                        
                        // REGISTRAR ACCIÓN EN karma_social (el trigger actualizará karma_total_usuarios)
                        $stmtInsertKarma = $conexion->prepare("
                            INSERT INTO karma_social 
                            (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion, fecha_accion)
                            VALUES 
                            (:usuario_id, :tipo_accion, :puntos, :referencia_id, :referencia_tipo, :descripcion, NOW())
                        ");
                        
                        $resultado = $stmtInsertKarma->execute([
                            ':usuario_id' => $id_usuario,
                            ':tipo_accion' => 'reaccion_' . $tipo_reaccion,
                            ':puntos' => $puntosGanados,
                            ':referencia_id' => $id_publicacion,
                            ':referencia_tipo' => 'publicacion',
                            ':descripcion' => $mensajeNotificacion
                        ]);
                        
                        debugLog("💾 INSERT en karma_social ejecutado", [
                            'resultado' => $resultado,
                            'rows_affected' => $stmtInsertKarma->rowCount(),
                            'tipo_accion' => 'reaccion_' . $tipo_reaccion,
                            'puntos' => $puntosGanados
                        ]);
                        
                        // Obtener karma actualizado DESPUÉS de la inserción (el trigger ya lo actualizó)
                        $stmtKarmaFinal = $conexion->prepare("
                            SELECT karma_total, acciones_totales 
                            FROM karma_total_usuarios 
                            WHERE usuario_id = ?
                        ");
                        $stmtKarmaFinal->execute([$id_usuario]);
                        $karmaFinalData = $stmtKarmaFinal->fetch(PDO::FETCH_ASSOC);
                        $karmaFinal = intval($karmaFinalData['karma_total'] ?? 0);
                        $accionesTotales = intval($karmaFinalData['acciones_totales'] ?? 0);
                        
                        debugLog("📊 Karma DESPUÉS de actualizar", [
                            'karma_despues' => $karmaFinal,
                            'acciones_totales' => $accionesTotales,
                            'diferencia' => ($karmaFinal - $karmaAntes),
                            'esperado' => $puntosGanados,
                            'trigger_funciono' => ($karmaFinal - $karmaAntes) === $puntosGanados
                        ]);
                        
                        // Recalcular nivel con el karma actualizado
                        $nivelActualizado = $karmaHelper->obtenerNivelKarma($karmaFinal);
                        
                        debugLog("🏆 Nivel recalculado", $nivelActualizado);
                        
                        // Actualizar respuesta con valores reales
                        $karmaActualizado = [
                            'karma' => (string)$karmaFinal, // ⭐ STRING
                            'nivel' => $nivelActualizado['nivel'] ?? 1,
                            'nivel_titulo' => $nivelActualizado['titulo'] ?? 'Novato',
                            'nivel_emoji' => $nivelActualizado['emoji'] ?? '🌱',
                            'acciones_totales' => $accionesTotales
                        ];
                        
                        debugLog("✅ karma_actualizado final", $karmaActualizado);
                        
                        // 🔔 CREAR NOTIFICACIÓN EN EL SISTEMA (campanita)
                        if ($puntosGanados != 0) {
                            try {
                                $notificacionesTriggers = new NotificacionesTriggers($conexion);
                                
                                $signo = $puntosGanados > 0 ? '+' : '';
                                $notifMensaje = "{$signo}{$puntosGanados} Karma: {$mensajeNotificacion}";
                                
                                $notificacionesTriggers->crearNotificacion(
                                    $id_usuario,           // Para quién es la notificación
                                    'karma',               // Tipo
                                    $notifMensaje,         // Mensaje
                                    null,                  // De usuario (sistema)
                                    $id_publicacion,       // Referencia
                                    'reaccion',            // Tipo de referencia
                                    null                   // URL
                                );
                                
                                debugLog("🔔 Notificación de karma creada en sistema");
                                
                            } catch (Exception $e) {
                                debugLog("⚠️ Error al crear notificación karma", ['error' => $e->getMessage()]);
                            }
                        }
                        
                    } catch (PDOException $e) {
                        debugLog("❌ ERROR en sistema karma", [
                            'error' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'nota' => 'Verifica que el trigger after_karma_social_insert exista'
                        ]);
                        @error_log("Error actualizando karma en BD: " . $e->getMessage());
                    }
                    
                    $karmaNotificacion = [
                        'mostrar' => true,
                        'puntos' => $puntosGanados,
                        'tipo' => $tipoNotificacion,
                        'mensaje' => $mensajeNotificacion,
                        'categoria' => $tipo_reaccion
                    ];
                }
            }
        } catch (Throwable $e) {
            @error_log("Error obteniendo karma actualizado: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // 8. RESPUESTA EXITOSA
    // ═══════════════════════════════════════════════════
    
    $respuestaFinal = [
        'success' => true, 
        'message' => 'Reacción procesada correctamente',
        'action' => $action,
        'tipo_reaccion' => $action === 'removed' ? null : $tipo_reaccion,
        'karma_actualizado' => $karmaActualizado, // ⭐ Karma del usuario que reacciona
        'karma_notificacion' => $karmaNotificacion,
        'karma_system_active' => ($karmaTriggers !== null)
    ];
    
    debugLog("🚀 RESPUESTA FINAL", $respuestaFinal);
    
    ob_end_clean();
    echo json_encode($respuestaFinal, JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Error en la base de datos',
        'error' => $e->getMessage()
    ]);
    exit;
} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Error procesando reacción',
        'error' => $e->getMessage()
    ]);
    exit;
}
?>