<?php
// ==========================================
// IMPORTANTE: No debe haber NINGÚN espacio o texto antes de <?php
// ==========================================

// Deshabilitar TODOS los errores para JSON limpio
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../comentarios_debug.log');

// Limpiar CUALQUIER salida antes de enviar JSON
ob_start();

// Iniciar sesión PRIMERO (si no está iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require(__DIR__.'/../models/config.php'); // Aquí tienes tu conexión PDO en $conexion
require_once(__DIR__.'/../models/bloqueos-helper.php');
require_once(__DIR__.'/../models/notificaciones-triggers.php');

// 🌟 Sistema de Karma Social (opcional - no romper si no existe)
$karmaTriggers = null;
if (file_exists(__DIR__.'/../models/karma-social-triggers.php')) {
    require_once(__DIR__.'/../models/karma-social-triggers.php');
    $karmaTriggers = new KarmaSocialTriggers($conexion);
}

// Instanciar sistema de notificaciones
$notificacionesTriggers = new NotificacionesTriggers($conexion);

// Verificar si el usuario está bloqueado antes de permitir comentarios
if (isset($_SESSION['id']) && isUserBlocked($_SESSION['id'], $conexion)) {
    ob_end_clean(); // Limpiar buffer
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Usuario bloqueado. No puedes realizar esta acción.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos
    if (!empty($_POST['usuario']) && !empty($_POST['comentario']) && !empty($_POST['publicacion'])) {
        // Sanear y asignar variables
        $usuario     = (int)trim($_POST['usuario']);
        $comentario  = trim($_POST['comentario']);
        $publicacion = (int)$_POST['publicacion'];

        // Validar que los IDs sean válidos
        if ($usuario > 0 && $publicacion > 0 && strlen($comentario) > 0) {
            try {
                // Verificar que la publicación existe y obtener su autor
                $stmt_check = $conexion->prepare("SELECT id_pub, usuario FROM publicaciones WHERE id_pub = :id_pub");
                $stmt_check->execute([':id_pub' => $publicacion]);
                $pub_data = $stmt_check->fetch();
                
                if (!$pub_data) {
                    throw new Exception("La publicación no existe.");
                }
                
                // Verificar bloqueo con el autor de la publicación
                $bloqueoInfo = verificarBloqueoMutuo($conexion, $usuario, $pub_data['usuario']);
                if ($bloqueoInfo['bloqueado']) {
                    throw new Exception("No puedes comentar en esta publicación.");
                }

                // Insertar comentario
                $stmt = $conexion->prepare("INSERT INTO comentarios (usuario, comentario, publicacion) 
                                            VALUES (:usuario, :comentario, :publicacion)");
                $stmt->execute([
                    ':usuario' => $usuario,
                    ':comentario' => $comentario,
                    ':publicacion' => $publicacion
                ]);
                
                // Obtener el ID del comentario recién insertado
                $comentarioId = (int)$conexion->lastInsertId();
                
                if ($comentarioId === 0) {
                    // Fallback: buscar el último comentario insertado
                    $stmtLastId = $conexion->prepare("
                        SELECT id_com FROM comentarios 
                        WHERE usuario = :usuario AND publicacion = :publicacion 
                        ORDER BY id_com DESC LIMIT 1
                    ");
                    $stmtLastId->execute([
                        ':usuario' => $usuario,
                        ':publicacion' => $publicacion
                    ]);
                    $lastComment = $stmtLastId->fetch(PDO::FETCH_ASSOC);
                    $comentarioId = (int)($lastComment['id_com'] ?? 0);
                }

                // Obtener usuario de la publicación para notificación
                $stmt2 = $conexion->prepare("SELECT usuario FROM publicaciones WHERE id_pub = :id_pub");
                $stmt2->execute([':id_pub' => $publicacion]);
                $ll = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($ll) {
                    $usuario2 = (int)$ll['usuario'];

                    // Solo crear notificación si el comentario no es del mismo usuario que hizo la publicación
                    if ($usuario !== $usuario2) {
                        // Obtener nombre del comentador
                        $stmtNombre = $conexion->prepare("SELECT usuario FROM usuarios WHERE id_use = :id");
                        $stmtNombre->execute([':id' => $usuario]);
                        $datosComentador = $stmtNombre->fetch(PDO::FETCH_ASSOC);
                        $nombreComentador = $datosComentador['usuario'] ?? 'Usuario';
                        
                        // Enviar notificación usando el sistema de triggers
                        $notificacionesTriggers->nuevoComentario($usuario, $usuario2, $nombreComentador, $publicacion, $comentario);
                        
                        // 🌟 REGISTRAR KARMA SOCIAL AUTOMÁTICAMENTE (si está disponible)
                        if ($karmaTriggers !== null) {
                            $karmaTriggers->nuevoComentario($usuario, $comentarioId, $comentario);
                        }
                        
                        // NOTA: No insertamos en tabla notificaciones porque el sistema de triggers YA lo hace
                        // Si tu tabla usa la estructura VIEJA (user1, user2), descomentar:
                        /*
                        $stmt3 = $conexion->prepare("INSERT INTO notificaciones (user1, user2, tipo, leido, fecha, id_pub) 
                                                    VALUES (:user1, :user2, 'ha comentado', 0, NOW(), :id_pub)");
                        $stmt3->execute([
                            ':user1' => $usuario,
                            ':user2' => $usuario2,
                            ':id_pub' => $publicacion
                        ]);
                        */
                    }
                }

                // Respuesta exitosa con datos del comentario
                // Obtener datos del usuario que comentó
                $stmtUser = $conexion->prepare("SELECT usuario, avatar FROM usuarios WHERE id_use = :id");
                $stmtUser->execute([':id' => $usuario]);
                $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
                
                // 🚀 OPTIMIZACIÓN: Incluir karma actualizado en la respuesta
                $karmaActualizado = null;
                $karmaNotificacion = null;
                
                if (isset($_SESSION['id'])) {
                    try {
                        require_once(__DIR__.'/../models/karma-social-helper.php');
                        $karmaHelper = new KarmaSocialHelper($conexion);
                        
                        // Obtener karma ANTES del comentario
                        $stmtKarmaAntes = $conexion->prepare("
                            SELECT karma_total 
                            FROM karma_total_usuarios 
                            WHERE usuario_id = ?
                        ");
                        $stmtKarmaAntes->execute([$_SESSION['id']]);
                        $karmaAntesData = $stmtKarmaAntes->fetch(PDO::FETCH_ASSOC);
                        $karmaAntes = intval($karmaAntesData['karma_total'] ?? 0);
                        
                        // 🧠 SISTEMA INTELIGENTE DE ANÁLISIS SEMÁNTICO Y DE TONO
                        $puntosGanados = 0;
                        $tipoNotificacion = 'neutro';
                        $mensajeNotificacion = 'Comentario publicado';
                        $otorgarKarma = false;
                        $categoria = 'neutro';
                        
                        // Analizar contenido del comentario
                        $comentarioLower = mb_strtolower($comentario, 'UTF-8');
                        $comentarioOriginal = $comentario; // Mantener original para análisis
                        
                        // ═══════════════════════════════════════════════════════════
                        // 1️⃣ ANÁLISIS DE CONTENIDO OBSCENO/MORBOSO (Mayor prioridad)
                        // ═══════════════════════════════════════════════════════════
                        $palabrasObscenas = [
                            // Contenido sexual explícito
                            'sexo', 'porno', 'xxx', 'desnud', 'sexual', 'erótic', 'cachond',
                            // Insultos fuertes
                            'puta', 'puto', 'mierda', 'coño', 'verga', 'carajo', 'chingad',
                            'pendejo', 'idiota', 'imbécil', 'estúpido', 'gilipollas',
                            // Contenido morboso
                            'morbo', 'morbos', 'pervert', 'enferm', 'asco', 'asqueros',
                            // Drogas/violencia
                            'drogas', 'matar', 'muerto', 'sangre', 'violencia', 'golpear'
                        ];
                        
                        foreach ($palabrasObscenas as $palabra) {
                            if (mb_strpos($comentarioLower, $palabra) !== false) {
                                $puntosGanados = -10;
                                $otorgarKarma = true;
                                $tipoNotificacion = 'negativo';
                                $categoria = 'obsceno/morboso';
                                $mensajeNotificacion = '⚠️ Contenido inapropiado detectado';
                                break;
                            }
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 2️⃣ ANÁLISIS DE TONO OFENSIVO/AGRESIVO
                        // ═══════════════════════════════════════════════════════════
                        if ($puntosGanados === 0) { // Solo si no es obsceno
                            $palabrasOfensivas = [
                                // Insultos directos
                                'odio', 'horrible', 'basura', 'pésimo', 'malo', 'fatal',
                                // Agresión
                                'cállate', 'callate', 'idiota', 'tonto', 'estúpid', 'imbécil',
                                // Desprecio
                                'patético', 'ridículo', 'vergüenza', 'vergonzos', 'lamentable',
                                // Amenazas
                                'vas a ver', 'te vas a arrepentir', 'cuidado'
                            ];
                            
                            foreach ($palabrasOfensivas as $palabra) {
                                if (mb_strpos($comentarioLower, $palabra) !== false) {
                                    $puntosGanados = -7;
                                    $otorgarKarma = true;
                                    $tipoNotificacion = 'negativo';
                                    $categoria = 'ofensivo';
                                    $mensajeNotificacion = '⛔ Comentario ofensivo detectado';
                                    break;
                                }
                            }
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 3️⃣ ANÁLISIS DE NEGATIVIDAD SUAVE
                        // ═══════════════════════════════════════════════════════════
                        if ($puntosGanados === 0) { // Solo si no es ofensivo ni obsceno
                            $palabrasNegativas = [
                                'no me gusta', 'aburrido', 'feo', 'desagradable', 'molesto',
                                'fastidioso', 'irritante', 'tedioso', 'cansado', 'pesado',
                                'decepcion', 'mal hecho', 'podrían mejorar', 'no funciona',
                                'error', 'problema', 'falla'
                            ];
                            
                            foreach ($palabrasNegativas as $palabra) {
                                if (mb_strpos($comentarioLower, $palabra) !== false) {
                                    $puntosGanados = -3;
                                    $otorgarKarma = true;
                                    $tipoNotificacion = 'negativo';
                                    $categoria = 'crítica negativa';
                                    $mensajeNotificacion = '😕 Comentario negativo';
                                    break;
                                }
                            }
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 4️⃣ ANÁLISIS DE POSITIVIDAD EXTREMA (Máximo entusiasmo + VARIACIONES)
                        // ═══════════════════════════════════════════════════════════
                        if ($puntosGanados === 0) {
                            $palabrasEntusiastas = [
                                'me encanta', 'encanta', 'amo esto', 'amo', 'lo mejor', 'increíble', 'increible',
                                'espectacular', 'maravilloso', 'extraordinario', 'fantástico', 'fantastico',
                                'brutal', 'épico', 'epico', 'alucinante', 'impresionante',
                                'wow', 'guau', 'genial al máximo', 'genial al maximo',
                                'perfecto', 'excelente', 'magnífico', 'magnifico',
                                'hermoso', 'hermosa', 'bellísimo', 'bellisimo', 'precioso', 'preciosa',
                                'súper bueno', 'super bueno', 'superbueno', 'lo máximo', 'lo maximo',
                                'la mejor', 'el mejor', 'la más', 'la mas', 'el más', 'el mas',
                                'te amo', 'te adoro', 'eres increíble', 'eres increible'
                            ];
                            
                            foreach ($palabrasEntusiastas as $palabra) {
                                if (mb_strpos($comentarioLower, $palabra) !== false) {
                                    $puntosGanados = 12;
                                    $otorgarKarma = true;
                                    $tipoNotificacion = 'positivo';
                                    $categoria = 'muy positivo';
                                    $mensajeNotificacion = '⭐ ¡Comentario muy positivo!';
                                    break;
                                }
                            }
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 5️⃣ ANÁLISIS DE POSITIVIDAD MODERADA (CON VARIACIONES Y TYPOS)
                        // ═══════════════════════════════════════════════════════════
                        if ($puntosGanados === 0) {
                            $palabrasPositivas = [
                                'me gusta', 'me gust', 'gusta', 'gusto', // Incluye typos comunes
                                'bueno', 'bien', 'genial', 'cool', 'nice',
                                'interesante', 'útil', 'util', 'agradable', 'bonito', 'lindo',
                                'gracias', 'aprecio', 'valoro', 'admiro', 'felicito',
                                'buen trabajo', 'bien hecho', 'sigue así', 'sigue asi', 'continúa', 'continua',
                                'apoyo', 'comparto', 'de acuerdo', 'concuerdo',
                                'chido', 'chévere', 'chevere', 'bacano', 'piola' // Variaciones regionales
                            ];
                            
                            foreach ($palabrasPositivas as $palabra) {
                                if (mb_strpos($comentarioLower, $palabra) !== false) {
                                    $puntosGanados = 8;
                                    $otorgarKarma = true;
                                    $tipoNotificacion = 'positivo';
                                    $categoria = 'positivo';
                                    $mensajeNotificacion = '😊 Comentario positivo';
                                    break;
                                }
                            }
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 6️⃣ ANÁLISIS DE EMOJIS (ACUMULABLE - NO hace break)
                        // ═══════════════════════════════════════════════════════════
                        // Emojis muy positivos (acumulables con categoría base)
                        $emojisPositivos = ['😍', '🥰', '❤️', '💖', '💕', '💗', '🔥', '✨', '⭐', '🌟', '👏', '🎉', '🙌'];
                        $puntosEmojisPositivos = 0;
                        foreach ($emojisPositivos as $emoji) {
                            // Contar cuántas veces aparece cada emoji
                            $count = mb_substr_count($comentarioOriginal, $emoji);
                            if ($count > 0) {
                                $puntosEmojisPositivos += ($count * 6); // 6 puntos por cada emoji positivo
                                $otorgarKarma = true;
                                $tipoNotificacion = 'positivo';
                                // Solo cambiar categoría si aún es neutro
                                if ($puntosGanados === 0 && $categoria === 'neutro') {
                                    $categoria = 'emoji positivo';
                                    $mensajeNotificacion = '💖 Emoji positivo detectado';
                                }
                                $puntosEmoji = $count * 6;
                                error_log("✨ Emoji positivo detectado: {$emoji} (x{$count}) = +{$puntosEmoji} pts");
                            }
                        }
                        $puntosGanados += $puntosEmojisPositivos;
                        if ($puntosEmojisPositivos > 0) {
                            error_log("✨ Total puntos emojis positivos: +{$puntosEmojisPositivos} | Puntos acumulados: {$puntosGanados}");
                        }
                        
                        // Emojis negativos (acumulables)
                        if ($puntosGanados === 0) { // Solo si no hay puntos positivos previos
                            $emojisNegativos = ['😠', '😡', '🤬', '💩', '👎', '😤', '😒', '🙄'];
                            $puntosEmojisNegativos = 0;
                            foreach ($emojisNegativos as $emoji) {
                                $count = mb_substr_count($comentarioOriginal, $emoji);
                                if ($count > 0) {
                                    $puntosEmojisNegativos += ($count * 4); // 4 puntos negativos por cada emoji
                                    $otorgarKarma = true;
                                    $tipoNotificacion = 'negativo';
                                    if ($categoria === 'neutro') {
                                        $categoria = 'emoji negativo';
                                        $mensajeNotificacion = '😤 Emoji negativo detectado';
                                    }
                                }
                            }
                            $puntosGanados -= $puntosEmojisNegativos;
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 7️⃣ BONIFICACIONES ADICIONALES
                        // ═══════════════════════════════════════════════════════════
                        
                        // Bonus por comentario largo y detallado (solo si es positivo)
                        if ($puntosGanados > 0 && strlen($comentario) > 150) {
                            $puntosGanados += 3;
                            $mensajeNotificacion .= ' y detallado (+3)';
                        }
                        
                        // Bonus por uso de signos de exclamación múltiples (entusiasmo)
                        if ($puntosGanados > 0 && (substr_count($comentario, '!') >= 2 || substr_count($comentario, '!!!') >= 1)) {
                            $puntosGanados += 2;
                            $mensajeNotificacion .= ' ¡Con entusiasmo! (+2)';
                        }
                        
                        // Penalización adicional por MAYÚSCULAS EXCESIVAS (gritar)
                        $palabrasMayusculas = preg_match_all('/[A-ZÁÉÍÓÚÑ]{4,}/', $comentario);
                        if ($palabrasMayusculas > 0 && strlen($comentario) > 20) {
                            $puntosGanados -= 2;
                            $mensajeNotificacion .= ' (MAYÚSCULAS -2)';
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 8️⃣ ANÁLISIS DE PREGUNTAS (Engagement constructivo)
                        // ═══════════════════════════════════════════════════════════
                        if ($puntosGanados === 0) {
                            $patronesPreguntas = ['¿', '?', 'cómo', 'por qué', 'cuál', 'cuánd', 'dónde', 'quién'];
                            $esPregunta = false;
                            foreach ($patronesPreguntas as $patron) {
                                if (mb_strpos($comentarioLower, $patron) !== false) {
                                    $esPregunta = true;
                                    break;
                                }
                            }
                            
                            if ($esPregunta && strlen($comentario) > 10) {
                                $puntosGanados = 4;
                                $otorgarKarma = true;
                                $tipoNotificacion = 'positivo';
                                $categoria = 'pregunta constructiva';
                                $mensajeNotificacion = '❓ Pregunta constructiva';
                            }
                        }
                        
                        // ═══════════════════════════════════════════════════════════
                        // 9️⃣ CONSTRUCCIÓN DEL MENSAJE FINAL
                        // ═══════════════════════════════════════════════════════════
                        
                        error_log("🎯 PUNTOS FINALES: {$puntosGanados} | Categoría: {$categoria} | Comentario: " . mb_substr($comentario, 0, 50));
                        
                        $karmaNotificacion = [
                            'mostrar' => $otorgarKarma,
                            'puntos' => $puntosGanados,
                            'tipo' => $tipoNotificacion,
                            'mensaje' => $mensajeNotificacion,
                            'categoria' => $categoria,
                            'analisis' => [
                                'longitud' => strlen($comentario),
                                'palabras' => str_word_count($comentarioLower),
                                'tono' => $categoria
                            ]
                        ];
                        
                        // ═══════════════════════════════════════════════════════════
                        // 🔥 ACTUALIZACIÓN CRÍTICA: PERSISTIR KARMA EN BASE DE DATOS
                        // Sistema de karma robusto con karma_social
                        // ═══════════════════════════════════════════════════════════
                        if ($otorgarKarma && $puntosGanados != 0) {
                            try {
                                // REGISTRAR ACCIÓN EN karma_social (el trigger actualizará karma_total_usuarios)
                                $stmtInsertKarma = $conexion->prepare("
                                    INSERT INTO karma_social 
                                    (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion, fecha_accion)
                                    VALUES 
                                    (:usuario_id, :tipo_accion, :puntos, :referencia_id, :referencia_tipo, :descripcion, NOW())
                                ");
                                
                                $resultado = $stmtInsertKarma->execute([
                                    ':usuario_id' => $_SESSION['id'],
                                    ':tipo_accion' => 'comentario_' . $categoria,
                                    ':puntos' => $puntosGanados,
                                    ':referencia_id' => $comentarioId,
                                    ':referencia_tipo' => 'comentario',
                                    ':descripcion' => $mensajeNotificacion
                                ]);
                                
                                if ($resultado) {
                                    // Obtener karma actualizado DESPUÉS de la inserción (el trigger ya lo actualizó)
                                    $stmtKarmaFinal = $conexion->prepare("
                                        SELECT karma_total, acciones_totales 
                                        FROM karma_total_usuarios 
                                        WHERE usuario_id = ?
                                    ");
                                    $stmtKarmaFinal->execute([$_SESSION['id']]);
                                    $karmaFinalData = $stmtKarmaFinal->fetch(PDO::FETCH_ASSOC);
                                    $karmaFinal = intval($karmaFinalData['karma_total'] ?? 0);
                                    $accionesTotales = intval($karmaFinalData['acciones_totales'] ?? 0);
                                    
                                    // Recalcular nivel con el karma actualizado
                                    $nivelActualizado = $karmaHelper->obtenerNivelKarma($karmaFinal);
                                    
                                    $karmaActualizado = [
                                        'karma' => (string)$karmaFinal,
                                        'nivel' => $nivelActualizado['nivel'] ?? 1,
                                        'nivel_titulo' => $nivelActualizado['titulo'] ?? 'Novato',
                                        'nivel_emoji' => $nivelActualizado['emoji'] ?? '🌱',
                                        'acciones_totales' => $accionesTotales
                                    ];
                                    
                                    error_log("✅ Karma comentario registrado: Usuario {$_SESSION['id']} | Puntos: {$puntosGanados} | Karma total: {$karmaFinal}");
                                } else {
                                    error_log("⚠️ No se pudo registrar karma del comentario");
                                }
                                
                            } catch (PDOException $e) {
                                error_log("❌ Error actualizando karma comentario: " . $e->getMessage());
                                // Continuar sin karma si falla
                            }
                        } else {
                            // Si no se otorga karma, obtener el karma actual sin modificar
                            try {
                                $stmtKarmaActual = $conexion->prepare("
                                    SELECT karma_total, acciones_totales 
                                    FROM karma_total_usuarios 
                                    WHERE usuario_id = ?
                                ");
                                $stmtKarmaActual->execute([$_SESSION['id']]);
                                $karmaActualData = $stmtKarmaActual->fetch(PDO::FETCH_ASSOC);
                                
                                if ($karmaActualData) {
                                    $karmaTotal = intval($karmaActualData['karma_total'] ?? 0);
                                    $nivelData = $karmaHelper->obtenerNivelKarma($karmaTotal);
                                    
                                    $karmaActualizado = [
                                        'karma' => (string)$karmaTotal,
                                        'nivel' => $nivelData['nivel'] ?? 1,
                                        'nivel_titulo' => $nivelData['titulo'] ?? 'Novato',
                                        'nivel_emoji' => $nivelData['emoji'] ?? '🌱',
                                        'acciones_totales' => intval($karmaActualData['acciones_totales'] ?? 0)
                                    ];
                                }
                            } catch (PDOException $e) {
                                error_log("❌ Error obteniendo karma actual: " . $e->getMessage());
                            }
                        }
                        
                        // 🔔 CREAR NOTIFICACIÓN EN EL SISTEMA (campanita)
                        if ($otorgarKarma && $puntosGanados != 0) {
                            try {
                                $signo = $puntosGanados > 0 ? '+' : '';
                                $notifMensaje = "{$signo}{$puntosGanados} Karma: {$mensajeNotificacion}";
                                
                                $notificacionesTriggers->crearNotificacion(
                                    $_SESSION['id'],       // Para quién es la notificación
                                    'karma',               // Tipo
                                    $notifMensaje,         // Mensaje
                                    null,                  // De usuario (sistema)
                                    $comentarioId,         // Referencia
                                    'comentario',          // Tipo de referencia
                                    null                   // URL
                                );
                                
                                error_log("🔔 Notificación de karma por comentario creada");
                                
                            } catch (Exception $e) {
                                error_log("⚠️ Error al crear notificación karma: " . $e->getMessage());
                            }
                        }
                        
                    } catch (Exception $e) {
                        error_log("Error obteniendo karma actualizado: " . $e->getMessage());
                    }
                }
                
                $response = [
                    'status' => 'success',
                    'message' => 'Tu comentario ha sido publicado.',
                    'comentario' => [
                        'id' => $comentarioId, // Usar el ID capturado
                        'usuario' => $userData['usuario'] ?? 'Usuario',
                        'avatar' => $userData['avatar'] ?? 'defect.jpg',
                        'comentario' => htmlspecialchars($comentario),
                        'fecha' => date('Y-m-d H:i:s')
                    ],
                    'karma_actualizado' => $karmaActualizado, // 🚀 Karma incluido en la respuesta
                    'karma_notificacion' => $karmaNotificacion // 🎯 Notificación de karma
                ];

            } catch (PDOException $e) {
                error_log("ERROR PDO: " . $e->getMessage());
                $response = [
                    'status' => 'error',
                    'message' => 'Ocurrió un problema al guardar el comentario. Por favor, inténtalo de nuevo.'
                ];
            } catch (Exception $e) {
                error_log("ERROR Exception: " . $e->getMessage());
                $response = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }

        } else {
            $response = [
                'status' => 'error',
                'message' => 'Datos no válidos.'
            ];
        }
    } else {
        $response = [
            'status' => 'warning',
            'message' => 'Por favor, complete todos los campos.'
        ];
    }

    // Siempre devolver JSON para compatibilidad con AJAX
    ob_end_clean(); // Limpiar cualquier salida accidental
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}
?>
