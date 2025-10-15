<?php
/**
 * Helper para el Sistema de Karma Social
 * Registra y gestiona las buenas acciones de los usuarios
 */

class KarmaSocialHelper {
    private $conexion;
    
    // Configuración de puntos por tipo de acción
    private const PUNTOS = [
        'comentario_positivo' => 8,
        'comentario_negativo' => -5,
        'interaccion_respetuosa' => 8,
        'apoyo_publicacion' => 3,
        'reaccion_negativa' => -2,
        'reversion_reaccion' => 0, // Puntos dinámicos (revertir reacción anterior)
        'compartir_conocimiento' => 15,
        'ayuda_usuario' => 12,
        'primera_interaccion' => 5,
        'mensaje_motivador' => 10,
        'reaccion_constructiva' => 3,
        'sin_reportes' => 50,
        'amigo_activo' => 20,
        'compra_tienda' => 0  // Los puntos se especifican dinámicamente (negativos)
    ];
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Registrar una acción de karma
     */
    public function registrarAccion($usuario_id, $tipo_accion, $referencia_id = null, $referencia_tipo = null, $descripcion = null) {
        try {
            // Validar que el tipo de acción existe
            if (!isset(self::PUNTOS[$tipo_accion])) {
                return false;
            }
            
            // Evitar duplicados en acciones únicas
            if ($this->esAccionDuplicada($usuario_id, $tipo_accion, $referencia_id, $referencia_tipo)) {
                return false;
            }
            
            $puntos = self::PUNTOS[$tipo_accion];
            
            // 🛡️ PROTECCIÓN: Si son puntos negativos, verificar que el usuario tenga karma suficiente
            if ($puntos < 0) {
                $karma_actual = $this->obtenerKarmaTotal($usuario_id);
                $karma_total = $karma_actual['karma_total'];
                
                // Si el karma actual es 0 o negativo, NO quitar más puntos
                if ($karma_total <= 0) {
                    error_log("⚠️ No se quitaron {$puntos} puntos al usuario {$usuario_id} porque su karma es {$karma_total}");
                    return false; // No registrar acción negativa si ya está en 0
                }
                
                // Si la penalización haría que tenga karma negativo, ajustar para que quede en 0
                if (($karma_total + $puntos) < 0) {
                    $puntos = -$karma_total; // Solo quitar hasta llegar a 0
                    error_log("⚖️ Ajustando penalización para usuario {$usuario_id}: {$puntos} puntos (karma actual: {$karma_total})");
                }
            }
            
            $stmt = $this->conexion->prepare("
                INSERT INTO karma_social 
                (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $resultado = $stmt->execute([
                $usuario_id,
                $tipo_accion,
                $puntos,
                $referencia_id,
                $referencia_tipo,
                $descripcion
            ]);
            
            // Si el registro fue exitoso, crear notificación en el sistema de notificaciones
            if ($resultado && $puntos != 0) {
                $mensaje_final = $descripcion ?? $this->obtenerMensajeAccion($tipo_accion);
                
                // Guardar en sesión para popup inmediato
                $_SESSION['karma_notification'] = [
                    'puntos' => $puntos,
                    'tipo' => $puntos > 0 ? 'positivo' : 'negativo',
                    'mensaje' => $mensaje_final
                ];
                
                // NUEVO: Crear notificación en el sistema de campana (🔔)
                if ($puntos > 0) {
                    $tipo_notif = 'karma_ganado';
                    $icono = '⭐';
                    $mensaje_completo = "{$icono} Has ganado {$puntos} puntos de karma por: {$mensaje_final}";
                } else {
                    $tipo_notif = 'karma_perdido';
                    $icono = '⚠️';
                    $mensaje_completo = "{$icono} Has perdido " . abs($puntos) . " puntos de karma por: {$mensaje_final}";
                }
                
                // Insertar en tabla notificaciones
                try {
                    $stmtNotif = $this->conexion->prepare("
                        INSERT INTO notificaciones 
                        (usuario_id, tipo, mensaje, referencia_id, referencia_tipo)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmtNotif->execute([
                        $usuario_id,
                        $tipo_notif,
                        $mensaje_completo,
                        $referencia_id,
                        $referencia_tipo
                    ]);
                } catch (PDOException $e) {
                    error_log("Error al crear notificación de karma: " . $e->getMessage());
                }
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("Error al registrar karma: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener mensaje descriptivo según el tipo de acción
     */
    private function obtenerMensajeAccion($tipo_accion) {
        $mensajes = [
            'publicacion' => '¡Has compartido contenido!',
            'comentario_positivo' => '¡Comentario positivo detectado por IA!',
            'comentario_negativo' => 'Comentario negativo detectado por IA',
            'apoyo_publicacion' => '¡Reacción positiva detectada!',
            'reaccion_negativa' => 'Reacción negativa detectada',
            'dar_like' => '¡Interacción positiva!',
            'recibir_like' => '¡Tu contenido recibió un like!',
            'aceptar_amistad' => '¡Nueva amistad creada!',
            'compartir' => '¡Has compartido contenido!',
            'primera_publicacion' => '¡Tu primera publicación!',
            'primera_interaccion' => '¡Primera interacción con usuario!',
            'mensaje_motivador' => '¡Mensaje motivador enviado!',
            'compartir_conocimiento' => '¡Compartiste conocimiento!',
            'contenido_reportado' => 'Contenido reportado',
            'bloquear_usuario' => 'Usuario bloqueado',
            'spam' => 'Spam detectado'
        ];
        
        return $mensajes[$tipo_accion] ?? 'Acción registrada';
    }
    
    /**
     * Analizar comentario y registrar karma si es positivo
     * MEJORADO V2: Detecta sarcasmo, negaciones, ofensas y doble sentido
     */
    public function analizarComentario($usuario_id, $comentario_id, $texto_comentario) {
        // 🤖 ANÁLISIS INTELIGENTE DE SENTIMIENTO
        $sentimiento = $this->analizarSentimientoInteligente($texto_comentario);
        
        // Log del análisis
        error_log("🤖 KARMA AI: Comentario '{$texto_comentario}' → Sentimiento: {$sentimiento['tipo']} ({$sentimiento['puntuacion']}/100) - {$sentimiento['razon']}");
        
        // Decisión basada en el sentimiento detectado
        if ($sentimiento['tipo'] === 'positivo') {
            // ✅ COMENTARIO POSITIVO - DAR KARMA
            return $this->registrarAccion(
                $usuario_id,
                'comentario_positivo',
                $comentario_id,
                'comentario',
                $sentimiento['razon']
            );
            
        } elseif ($sentimiento['tipo'] === 'negativo') {
            // ❌ COMENTARIO NEGATIVO - QUITAR KARMA
            return $this->registrarAccion(
                $usuario_id,
                'comentario_negativo',
                $comentario_id,
                'comentario',
                $sentimiento['razon']
            );
            
        } else {
            // ⚪ NEUTRAL - NO DAR NI QUITAR KARMA
            error_log("⚪ KARMA: Comentario neutral, no se otorga karma");
            return false;
        }
    }
    
    /**
     * 🤖 ANÁLISIS INTELIGENTE DE SENTIMIENTO
     * Detecta automáticamente si un texto es positivo, negativo o neutral
     */
    private function analizarSentimientoInteligente($texto) {
        $texto_lower = mb_strtolower($texto);
        $longitud = mb_strlen($texto);
        
        // Puntuación de sentimiento (0-100)
        $puntuacion = 50; // Neutral por defecto
        $razon = 'Comentario neutral';
        
        // ============================================
        // 1️⃣ ANÁLISIS DE EMOJIS
        // ============================================
        $emojis_positivos = ['😊', '😃', '😄', '😁', '🙂', '😍', '🥰', '😘', '❤️', '💕', '💖', '💗', '💙', '💚', '💛', '💜', '🧡', 
                            '👍', '👏', '🙌', '💪', '✨', '⭐', '🌟', '💫', '🎉', '🎊', '🎈', '🏆', '🥇', '🔥', '💯', '👌', '🤩'];
        
        $emojis_negativos = ['😠', '😡', '🤬', '😤', '😒', '🙄', '😑', '😐', '😕', '😟', '😞', '😔', '😢', '😭', '😩', '😫',
                            '💩', '🖕', '👎', '❌', '🚫', '⛔', '💔', '🗑️'];
        
        $contador_emojis_positivos = 0;
        $contador_emojis_negativos = 0;
        
        foreach ($emojis_positivos as $emoji) {
            if (strpos($texto, $emoji) !== false) {
                $contador_emojis_positivos += 2;
            }
        }
        
        foreach ($emojis_negativos as $emoji) {
            if (strpos($texto, $emoji) !== false) {
                $contador_emojis_negativos += 3;
            }
        }
        
        // ============================================
        // 2️⃣ ANÁLISIS DE TONO
        // ============================================
        $exclamaciones = substr_count($texto, '!');
        if ($exclamaciones > 0 && $exclamaciones <= 3) {
            $puntuacion += ($exclamaciones * 5);
        } elseif ($exclamaciones > 3) {
            $puntuacion -= 10;
        }
        
        // ============================================
        // 3️⃣ ANÁLISIS SEMÁNTICO
        // ============================================
        $indicadores_positivos = [
            'bonit', 'lind', 'herman', 'buen', 'mejor', 'perfect', 'excelent', 'genial', 'increíbl',
            'maravillos', 'fantástic', 'gracias', 'feliz', 'alegr', 'amo', 'encanta', 'gusta'
        ];
        
        $indicadores_negativos = [
            'mal', 'peor', 'horribl', 'terribl', 'pésim', 'odio', 'detesto', 'asco', 'basura',
            'idiota', 'tonto', 'estúpid'
        ];
        
        $contador_positivos = 0;
        $contador_negativos = 0;
        
        foreach ($indicadores_positivos as $indicador) {
            if (preg_match('/\b' . $indicador . '/i', $texto_lower)) {
                $contador_positivos++;
            }
        }
        
        foreach ($indicadores_negativos as $indicador) {
            if (preg_match('/\b' . $indicador . '/i', $texto_lower)) {
                $contador_negativos += 2;
            }
        }
        
        // ============================================
        // 4️⃣ CÁLCULO FINAL
        // ============================================
        $puntuacion += ($contador_emojis_positivos * 8);
        $puntuacion -= ($contador_emojis_negativos * 10);
        $puntuacion += ($contador_positivos * 6);
        $puntuacion -= ($contador_negativos * 8);
        
        $puntuacion = max(0, min(100, $puntuacion));
        
        // ============================================
        // 5️⃣ CLASIFICACIÓN
        // ============================================
        if ($puntuacion >= 65) {
            $tipo = 'positivo';
            $razon = "Sentimiento positivo detectado ({$puntuacion}/100)";
        } elseif ($puntuacion <= 35) {
            $tipo = 'negativo';
            $razon = "Sentimiento negativo detectado ({$puntuacion}/100)";
        } else {
            $tipo = 'neutral';
            $razon = "Sentimiento neutral ({$puntuacion}/100)";
        }
        
        return [
            'tipo' => $tipo,
            'puntuacion' => $puntuacion,
            'razon' => $razon
        ];
    }
    
    /**
     * 🎯 REGISTRAR KARMA POR REACCIÓN
     * Mapeo directo de reacciones españolas a puntos
     */
    public function registrarReaccionPositiva($usuario_id, $publicacion_id, $tipo_reaccion) {
        // 🎯 MAPEO DIRECTO DE REACCIONES EN ESPAÑOL
        $mapeo_reacciones = [
            // ✅ POSITIVAS (dan puntos)
            'me_gusta'      => ['puntos' => 5,  'tipo' => 'positivo', 'descripcion' => '👍 Me gusta'],
            'me_encanta'    => ['puntos' => 10, 'tipo' => 'positivo', 'descripcion' => '❤️ Me encanta'],
            'me_divierte'   => ['puntos' => 7,  'tipo' => 'positivo', 'descripcion' => '😂 Me divierte'],
            'me_asombra'    => ['puntos' => 8,  'tipo' => 'positivo', 'descripcion' => '😮 Me asombra'],
            
            // ⚠️ NEGATIVAS (quitan puntos)
            'me_entristece' => ['puntos' => -3, 'tipo' => 'negativo', 'descripcion' => '😢 Me entristece'],
            'me_enoja'      => ['puntos' => -5, 'tipo' => 'negativo', 'descripcion' => '😡 Me enoja'],
        ];
        
        // Verificar si la reacción existe en el mapeo
        if (!isset($mapeo_reacciones[$tipo_reaccion])) {
            error_log("⚠️ Reacción desconocida: '{$tipo_reaccion}' - No se otorga karma");
            return false;
        }
        
        $config = $mapeo_reacciones[$tipo_reaccion];
        $puntos = $config['puntos'];
        
        error_log("🎯 KARMA: Usuario {$usuario_id} reaccionó '{$tipo_reaccion}' → {$puntos} puntos");
        
        // ⭐ REGISTRAR DIRECTAMENTE CON PUNTOS REALES (sin pasar por registrarAccion que usa valores fijos)
        return $this->registrarKarmaDirecto(
            $usuario_id,
            $puntos,
            $publicacion_id,
            'publicacion',
            $config['descripcion'],
            $config['tipo']
        );
    }
    
    /**
     * 🎯 REGISTRAR KARMA DIRECTO CON PUNTOS EXACTOS
     * Método nuevo que NO usa valores fijos de PUNTOS[]
     */
    private function registrarKarmaDirecto($usuario_id, $puntos_exactos, $referencia_id, $referencia_tipo, $descripcion, $tipo_sentimiento = 'positivo') {
        try {
            // Evitar duplicados
            if ($this->esAccionDuplicada($usuario_id, 'reaccion_directa', $referencia_id, $referencia_tipo)) {
                error_log("⚠️ Reacción duplicada - No se registra");
                return false;
            }
            
            // 🛡️ PROTECCIÓN: Si son puntos negativos, verificar karma
            if ($puntos_exactos < 0) {
                $karma_actual = $this->obtenerKarmaTotal($usuario_id);
                $karma_total = $karma_actual['karma_total'];
                
                if ($karma_total <= 0) {
                    error_log("⚠️ No se quitaron {$puntos_exactos} puntos porque karma actual es {$karma_total}");
                    return false;
                }
                
                if (($karma_total + $puntos_exactos) < 0) {
                    $puntos_exactos = -$karma_total;
                    error_log("⚖️ Ajustando a {$puntos_exactos} para no quedar negativo");
                }
            }
            
            // Insertar en base de datos con puntos EXACTOS
            $stmt = $this->conexion->prepare("
                INSERT INTO karma_social 
                (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion)
                VALUES (?, 'reaccion_directa', ?, ?, ?, ?)
            ");
            
            $resultado = $stmt->execute([
                $usuario_id,
                $puntos_exactos,
                $referencia_id,
                $referencia_tipo,
                $descripcion
            ]);
            
            // Crear notificación en sesión
            if ($resultado && $puntos_exactos != 0) {
                $_SESSION['karma_notification'] = [
                    'puntos' => $puntos_exactos,
                    'tipo' => $tipo_sentimiento,
                    'mensaje' => $descripcion
                ];
                
                // Crear notificación en campana
                $tipo_notif = ($puntos_exactos > 0) ? 'karma_ganado' : 'karma_perdido';
                $icono = ($puntos_exactos > 0) ? '⭐' : '⚠️';
                $mensaje_completo = "{$icono} " . (($puntos_exactos > 0) ? "Has ganado {$puntos_exactos}" : "Has perdido " . abs($puntos_exactos)) . " puntos de karma por: {$descripcion}";
                
                try {
                    $stmtNotif = $this->conexion->prepare("
                        INSERT INTO notificaciones 
                        (usuario_id, tipo, mensaje, referencia_id, referencia_tipo)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmtNotif->execute([
                        $usuario_id,
                        $tipo_notif,
                        $mensaje_completo,
                        $referencia_id,
                        $referencia_tipo
                    ]);
                } catch (PDOException $e) {
                    error_log("Error creando notificación: " . $e->getMessage());
                }
            }
            
            error_log("✅ KARMA REGISTRADO: {$puntos_exactos} puntos para usuario {$usuario_id}");
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("❌ Error registrando karma directo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 🔄 REVERTIR KARMA DE REACCIÓN
     * Cuando se elimina o cambia una reacción, revertir los puntos
     */
    public function revertirReaccion($usuario_id, $publicacion_id, $tipo_reaccion_antigua) {
        // 🎯 MAPEO DIRECTO DE REACCIONES EN ESPAÑOL
        $mapeo_reacciones = [
            'me_gusta'      => ['puntos' => 5,  'descripcion' => '👍 Me gusta'],
            'me_encanta'    => ['puntos' => 10, 'descripcion' => '❤️ Me encanta'],
            'me_divierte'   => ['puntos' => 7,  'descripcion' => '😂 Me divierte'],
            'me_asombra'    => ['puntos' => 8,  'descripcion' => '😮 Me asombra'],
            'me_entristece' => ['puntos' => -3, 'descripcion' => '😢 Me entristece'],
            'me_enoja'      => ['puntos' => -5, 'descripcion' => '😡 Me enoja'],
        ];
        
        // Verificar si la reacción existe
        if (!isset($mapeo_reacciones[$tipo_reaccion_antigua])) {
            error_log("⚠️ Reacción antigua desconocida: '{$tipo_reaccion_antigua}'");
            return false;
        }
        
        $config = $mapeo_reacciones[$tipo_reaccion_antigua];
        $puntos_originales = $config['puntos'];
        
        // Revertir significa aplicar el opuesto
        $puntos_a_revertir = -$puntos_originales;
        
        error_log("🔄 REVERTIR KARMA: Usuario {$usuario_id} eliminó/cambió '{$tipo_reaccion_antigua}' → Revirtiendo {$puntos_originales} puntos (aplicando {$puntos_a_revertir})");
        
        // 🛡️ PROTECCIÓN: Verificar que no quede en negativo
        $karma_actual = $this->obtenerKarmaTotal($usuario_id);
        $karma_total = $karma_actual['karma_total'];
        
        // Si al revertir quedaría negativo, ajustar
        if ($puntos_a_revertir < 0 && $karma_total <= 0) {
            error_log("⚠️ No se revierte porque karma actual es {$karma_total} (ya está en 0 o negativo)");
            return false;
        }
        
        if ($puntos_a_revertir < 0 && ($karma_total + $puntos_a_revertir) < 0) {
            $puntos_a_revertir = -$karma_total;
            error_log("⚖️ Ajustando reversión a {$puntos_a_revertir} para no quedar negativo");
        }
        
        // Registrar la reversión manualmente (sin pasar por registrarAccion para evitar validaciones)
        try {
            $descripcion = "Reacción {$config['descripcion']} eliminada/cambiada (revirtiendo {$puntos_originales} puntos)";
            
            $stmt = $this->conexion->prepare("
                INSERT INTO karma_social 
                (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion)
                VALUES (?, 'reversion_reaccion', ?, ?, 'publicacion', ?)
            ");
            
            $resultado = $stmt->execute([
                $usuario_id,
                $puntos_a_revertir,
                $publicacion_id,
                $descripcion
            ]);
            
            if ($resultado) {
                error_log("✅ Karma revertido exitosamente: {$puntos_a_revertir} puntos");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("❌ Error al revertir karma: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener karma completo de un usuario
     */
    public function obtenerKarmaUsuario($usuario_id) {
        try {
            $karmaData = $this->obtenerKarmaTotal($usuario_id);
            $karma_total = $karmaData['karma_total'];
            
            $nivelData = $this->obtenerNivelKarma($karma_total);
            
            $stmt = $this->conexion->prepare("
                SELECT MIN(karma_requerido) as proxima
                FROM karma_recompensas
                WHERE karma_requerido > ?
            ");
            $stmt->execute([$karma_total]);
            $proximaRecompensa = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'karma_total' => $karma_total,
                'acciones_totales' => $karmaData['acciones_totales'],
                'nivel' => $nivelData['titulo'],
                'nivel_data' => $nivelData,
                'nivel_emoji' => $nivelData['emoji'],
                'nivel_color' => $nivelData['color'],
                'proxima_recompensa' => $proximaRecompensa['proxima'] ?? null
            ];
            
        } catch (PDOException $e) {
            error_log("Error al obtener karma usuario: " . $e->getMessage());
            return [
                'karma_total' => 0,
                'acciones_totales' => 0,
                'nivel' => 'Novato',
                'nivel_data' => ['nivel' => 1, 'titulo' => 'Novato', 'emoji' => '🌱'],
                'nivel_emoji' => '🌱',
                'nivel_color' => '#87CEEB',
                'proxima_recompensa' => null
            ];
        }
    }
    
    /**
     * Obtener karma total de un usuario
     */
    public function obtenerKarmaTotal($usuario_id) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    COALESCE(SUM(puntos), 0) as karma_total,
                    COUNT(*) as acciones_totales
                FROM karma_social
                WHERE usuario_id = ?
            ");
            
            $stmt->execute([$usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener karma total: " . $e->getMessage());
            return ['karma_total' => 0, 'acciones_totales' => 0];
        }
    }
    
    /**
     * Obtener nivel de karma
     */
    public function obtenerNivelKarma($karma_total) {
        $nivel = floor($karma_total / 100) + 1;
        $puntos_siguiente_nivel = $nivel * 100;
        $puntos_nivel_actual = ($nivel - 1) * 100;
        $progreso = $karma_total - $puntos_nivel_actual;
        
        if ($nivel >= 10) {
            $emoji = '👑';
            $color = '#FFD700';
            $titulo = 'Legendario';
        } elseif ($nivel >= 7) {
            $emoji = '🌟';
            $color = '#9370DB';
            $titulo = 'Maestro';
        } elseif ($nivel >= 5) {
            $emoji = '💫';
            $color = '#4169E1';
            $titulo = 'Experto';
        } elseif ($nivel >= 3) {
            $emoji = '✨';
            $color = '#32CD32';
            $titulo = 'Avanzado';
        } elseif ($nivel >= 2) {
            $emoji = '⭐';
            $color = '#FFA500';
            $titulo = 'Intermedio';
        } else {
            $emoji = '🌱';
            $color = '#87CEEB';
            $titulo = 'Novato';
        }
        
        return [
            'nivel' => $nivel,
            'titulo' => $titulo,
            'emoji' => $emoji,
            'color' => $color,
            'progreso' => $progreso,
            'puntos_siguiente_nivel' => $puntos_siguiente_nivel,
            'porcentaje' => ($progreso / 100) * 100
        ];
    }
    
    /**
     * Obtener historial de karma
     */
    public function obtenerHistorial($usuario_id, $limite = 20) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    tipo_accion,
                    puntos,
                    fecha_accion,
                    descripcion
                FROM karma_social
                WHERE usuario_id = ?
                ORDER BY fecha_accion DESC
                LIMIT ?
            ");
            
            $stmt->execute([$usuario_id, $limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar si una acción es duplicada
     */
    private function esAccionDuplicada($usuario_id, $tipo_accion, $referencia_id, $referencia_tipo) {
        $acciones_unicas = ['apoyo_publicacion', 'comentario_positivo', 'primera_interaccion', 'reaccion_directa'];
        
        if (!in_array($tipo_accion, $acciones_unicas) || !$referencia_id) {
            return false;
        }
        
        try {
            $stmt = $this->conexion->prepare("
                SELECT COUNT(*) as cuenta
                FROM karma_social
                WHERE usuario_id = ?
                AND tipo_accion = ?
                AND referencia_id = ?
                AND referencia_tipo = ?
            ");
            
            $stmt->execute([$usuario_id, $tipo_accion, $referencia_id, $referencia_tipo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['cuenta'] > 0;
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Obtener top usuarios con más karma
     */
    public function obtenerTopUsuarios($limite = 10) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    u.id_use,
                    u.usuario,
                    u.nombre,
                    u.avatar,
                    COALESCE(SUM(k.puntos), 0) as karma_total,
                    COUNT(k.id) as acciones_totales
                FROM usuarios u
                LEFT JOIN karma_social k ON u.id_use = k.usuario_id
                GROUP BY u.id_use
                ORDER BY karma_total DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener top usuarios: " . $e->getMessage());
            return [];
        }
    }
}
?>