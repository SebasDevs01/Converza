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
        'compartir_conocimiento' => 15,
        'ayuda_usuario' => 12,
        'primera_interaccion' => 5,
        'mensaje_motivador' => 10,
        'reaccion_constructiva' => 3,
        'sin_reportes' => 50,
        'amigo_activo' => 20,
        'compra_tienda' => 0  // Los puntos se especifican dinámicamente (negativos)
    ];
    
    // Palabras positivas para detección (EXPANDIDO)
    private const PALABRAS_POSITIVAS = [
        'gracias', 'excelente', 'genial', 'increíble', 'bueno', 'bien',
        'felicidades', 'éxito', 'logro', 'apoyo', 'ayuda', 'maravilloso',
        'perfecto', 'fantástico', 'hermoso', 'inspirador', 'motivador',
        'admirable', 'impresionante', 'valioso', 'útil', 'interesante',
        // Emociones positivas
        'feliz', 'alegre', 'contento', 'emocionado', 'entusiasta', 'optimista',
        // Apreciación
        'aprecio', 'agradezco', 'reconozco', 'valoro', 'respeto', 'admiro',
        // Calidad
        'calidad', 'profesional', 'impecable', 'destacado', 'brillante', 'superior',
        'extraordinario', 'espectacular', 'fenomenal', 'sobresaliente',
        // Apoyo y ánimo
        'bravo', 'felicitaciones', 'enhorabuena', 'ánimo', 'adelante', 'sigue',
        'continúa', 'persevera', 'fuerza', 'puedes', 'lograrás', 'conseguirás',
        // Gratitud
        'thank', 'thanks', 'grazie', 'merci', 'danke', 'obrigado',
        // Afirmaciones positivas
        'sí', 'claro', 'absolutamente', 'totalmente', 'definitivamente', 'cierto',
        'correcto', 'exacto', 'preciso', 'acertado',
        // Recomendaciones
        'recomiendo', 'sugiero', 'aconsejo', 'propongo', 'compartiré',
        // Emojis positivos (convertidos a texto)
        '👍', '❤️', '😊', '🙌', '💪', '🌟', '✨', '🎉', '👏', '💯'
    ];
    
    // Palabras negativas para DESCARTAR comentarios tóxicos (EXPANDIDO)
    private const PALABRAS_NEGATIVAS = [
        // Insultos directos
        'malo', 'horrible', 'terrible', 'pésimo', 'odio', 'detesto',
        'asco', 'basura', 'porquería', 'idiota', 'estúpido', 'tonto',
        'inútil', 'fracaso', 'patético', 'ridículo', 'absurdo',
        'imbécil', 'pendejo', 'payaso', 'burro', 'bruto', 'lerdo',
        'tarado', 'bobo', 'zonzo', 'menso', 'baboso',
        // Ofensas
        'feo', 'asqueroso', 'repugnante', 'nauseabundo', 'disgusto',
        'despreciable', 'miserable', 'maldito', 'condenado',
        // Negatividad general
        'aburrido', 'pesado', 'molesto', 'fastidioso', 'irritante',
        'decepcionante', 'mediocre', 'deficiente', 'inferior',
        'pobre', 'lamentable', 'triste', 'deprimente',
        // Crítica destructiva
        'error', 'equivocado', 'falso', 'mentira', 'engaño', 'estafa',
        'fraude', 'copia', 'plagio', 'robo', 'robado',
        // Discriminación
        'gordo', 'flaco', 'enano', 'gigante', 'raro', 'extraño',
        // Vulgaridades (censuradas)
        'mierda', 'carajo', 'joder', 'chingar', 'verga',
        // Emojis negativos
        '😠', '😡', '🤬', '💩', '🖕', '😤', '😒', '🙄'
    ];
    
    // Patrones de SARCASMO y DOBLE SENTIDO
    private const PATRONES_SARCASMO = [
        'jaja claro', 'sí claro', 'obvio', 'seguro', 'claro que sí',
        'muy inteligente', 'qué listo', 'genio', 'crack', 
        'ya veo', 'ajá', 'sí sí', 'como no', 'por supuesto',
        'felicidades campeón', 'bravo genio', 'wow qué original'
    ];
    
    // Negaciones que INVIERTEN el sentimiento
    private const NEGACIONES = [
        'no', 'nunca', 'jamás', 'nada', 'ningún', 'ninguna',
        'tampoco', 'ni', 'sin'
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
     * SIN depender de palabras específicas
     */
    private function analizarSentimientoInteligente($texto) {
        $texto_lower = mb_strtolower($texto);
        $longitud = mb_strlen($texto);
        
        // Puntuación de sentimiento (0-100)
        $puntuacion = 50; // Neutral por defecto
        $razon = 'Comentario neutral';
        
        // ============================================
        // 1️⃣ ANÁLISIS DE EMOJIS (muy importante)
        // ============================================
        $emojis_positivos = ['😊', '😃', '😄', '😁', '🙂', '😍', '🥰', '😘', '❤️', '💕', '💖', '💗', '💙', '💚', '💛', '💜', '🧡', 
                            '👍', '👏', '🙌', '💪', '✨', '⭐', '🌟', '💫', '🎉', '🎊', '🎈', '🏆', '🥇', '🔥', '💯', '👌', '🤩', 
                            '😻', '💝', '🌺', '🌸', '🌼', '🌻', '🌹', '🦋', '🌈', '☀️', '🎵', '🎶', '🍀'];
        
        $emojis_negativos = ['😠', '😡', '🤬', '😤', '😒', '🙄', '😑', '😐', '😕', '😟', '😞', '😔', '😢', '😭', '😩', '😫',
                            '💩', '🖕', '👎', '❌', '🚫', '⛔', '💔', '🗑️', '😾', '🤮', '🤢', '😷', '🤧', '😵', '💀', '☠️'];
        
        $contador_emojis_positivos = 0;
        $contador_emojis_negativos = 0;
        
        foreach ($emojis_positivos as $emoji) {
            if (strpos($texto, $emoji) !== false) {
                $contador_emojis_positivos += 2; // Peso alto
            }
        }
        
        foreach ($emojis_negativos as $emoji) {
            if (strpos($texto, $emoji) !== false) {
                $contador_emojis_negativos += 3; // Peso muy alto
            }
        }
        
        // ============================================
        // 2️⃣ ANÁLISIS DE TONO Y ESTRUCTURA
        // ============================================
        
        // Signos de exclamación (entusiasmo positivo)
        $exclamaciones = substr_count($texto, '!');
        if ($exclamaciones > 0 && $exclamaciones <= 3) {
            $puntuacion += ($exclamaciones * 5);
        } elseif ($exclamaciones > 3) {
            $puntuacion -= 10; // Demasiadas = agresividad
        }
        
        // Preguntas genuinas (constructivas)
        $preguntas = substr_count($texto, '?') + substr_count($texto, '¿');
        if ($preguntas > 0 && $longitud > 15) {
            $puntuacion += 5; // Preguntas genuinas son positivas
        }
        
        // Mayúsculas excesivas (gritos/agresividad)
        $mayusculas = preg_match_all('/[A-ZÁÉÍÓÚÑ]/', $texto);
        $porcentaje_mayusculas = $longitud > 0 ? ($mayusculas / $longitud) * 100 : 0;
        if ($porcentaje_mayusculas > 60) {
            $puntuacion -= 20; // Gritos = negativo
        }
        
        // ============================================
        // 3️⃣ ANÁLISIS SEMÁNTICO (palabras indicadoras)
        // ============================================
        
        // Indicadores positivos (adjetivos, verbos, sustantivos positivos)
        $indicadores_positivos = [
            // Adjetivos positivos generales
            'bonit', 'lind', 'herman', 'preciso', 'buen', 'mejor', 'perfect', 'excelent', 'genial', 'increíbl',
            'maravillos', 'fantástic', 'espectacular', 'impresionant', 'asombros', 'extraordinari', 'excepcional',
            'fenomenal', 'estupend', 'magnifico', 'espléndid', 'sobresaliente', 'notable', 'admirable', 'destacad',
            'brillant', 'radiante', 'resplandecient', 'deslumbrant', 'fascinant', 'encantador', 'adorable', 'dulce',
            'tier', 'delicad', 'suave', 'agradable', 'placentero', 'satisfactori', 'grato', 'ameno', 'divino',
            'celestial', 'glorios', 'sublime', 'elegante', 'sofisticad', 'refinad', 'distinguid', 'chic', 'cool',
            
            // Emociones positivas
            'feliz', 'alegr', 'content', 'emocionad', 'entusiasm', 'animad', 'motivad', 'inspirad', 'esperanzad',
            'optimist', 'positiv', 'satisfech', 'compiacid', 'agradecid', 'reconocid', 'valorad', 'estimad',
            
            // Verbos positivos
            'me encanta', 'me gusta', 'me fascina', 'me agrada', 'disfrut', 'aprovech', 'celebr', 'felicit',
            'aplaudo', 'admiro', 'respeto', 'aprecio', 'valoro', 'recomiendo', 'aconsejo', 'sugiero', 'comparto',
            'amo', 'adoro', 'quiero', 'apoy', 'alent', 'animo', 'motivo', 'inspir',
            
            // Sustantivos positivos
            'éxito', 'logro', 'triunfo', 'victoria', 'conquista', 'calidad', 'talento', 'habilidad', 'maestría',
            'arte', 'belleza', 'armonía', 'paz', 'amor', 'cariño', 'afecto', 'ternura', 'gratitud',
            
            // Interjecciones positivas
            'wow', 'guau', 'bravo', 'ole', 'hurra', 'viva', 'yeah', 'yay', 'woo', 'yuju'
        ];
        
        // Indicadores negativos (insultos, críticas, negatividad)
        $indicadores_negativos = [
            // Adjetivos negativos
            'mal', 'peor', 'horribl', 'terribl', 'pésim', 'desastrós', 'espantos', 'atroz', 'nefasto', 'funest',
            'fe', 'asqueros', 'repugnant', 'nauseabund', 'desagradabl', 'molest', 'fastidios', 'irritant',
            'aburrid', 'pesad', 'tedi', 'insípid', 'soso', 'insulso', 'mediocr', 'deficient', 'inferior',
            'lamentabl', 'patétic', 'ridícul', 'absurd', 'estúpid', 'idiota', 'imbécil', 'tont', 'brut',
            'inútil', 'incompetent', 'torpe', 'negativ', 'toxic', 'dañin', 'perjudicial', 'nociv',
            
            // Insultos y ofensas
            'idiota', 'tonto', 'estúpido', 'imbécil', 'pendejo', 'gilipollas', 'cabrón', 'hijo de', 'mierda',
            'caca', 'porquería', 'basura', 'desecho', 'escoria', 'payaso', 'bufón', 'fracasad', 'perdedor',
            
            // Emociones negativas
            'odio', 'detesto', 'aborrezc', 'desprecio', 'asco', 'rabia', 'ira', 'enojo', 'furia', 'cólera',
            'molest', 'disgust', 'fastidi', 'enfad', 'irritad', 'frustrad', 'decepcionad', 'triste', 'deprimi',
            'melanc', 'apenado', 'afligid', 'angustiad', 'preocupad', 'ansioso', 'nervioso', 'tenso'
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
                $contador_negativos += 2; // Peso mayor para negativos
            }
        }
        
        // ============================================
        // 4️⃣ DETECCIÓN DE SARCASMO E IRONÍA
        // ============================================
        $patrones_sarcasmo = [
            'jaja.*claro', 'sí.*claro', 'claro que sí', 'obvio', 'seguro', 'ajá', 'ya.*veo',
            'qué.*original', 'muy.*inteligente', 'qué.*listo', 'genio.*el', 'crack.*el',
            'felicidades.*campeón', 'bravo.*genio', 'como.*no'
        ];
        
        $tiene_sarcasmo = false;
        foreach ($patrones_sarcasmo as $patron) {
            if (preg_match('/' . $patron . '/i', $texto_lower)) {
                $tiene_sarcasmo = true;
                break;
            }
        }
        
        // Detectar negaciones que invierten el sentimiento
        $negaciones = ['no', 'nunca', 'jamás', 'nada', 'ningún', 'tampoco', 'ni', 'sin'];
        $tiene_negacion = false;
        foreach ($negaciones as $negacion) {
            if (preg_match('/\b' . $negacion . '\b/i', $texto_lower)) {
                $tiene_negacion = true;
                break;
            }
        }
        
        // Si tiene negación + indicador positivo = posible sarcasmo
        if ($tiene_negacion && $contador_positivos > 0) {
            $puntuacion -= 15;
        }
        
        if ($tiene_sarcasmo) {
            $puntuacion -= 25;
        }
        
        // ============================================
        // 5️⃣ ANÁLISIS DE LONGITUD Y COMPLETITUD
        // ============================================
        if ($longitud > 80 && $contador_negativos === 0) {
            $puntuacion += 8; // Comentarios largos constructivos son positivos
        }
        
        if ($longitud < 5) {
            $puntuacion -= 10; // Comentarios muy cortos suelen ser neutros/spam
        }
        
        // ============================================
        // 6️⃣ CÁLCULO FINAL DE PUNTUACIÓN
        // ============================================
        
        // Aplicar peso de emojis
        $puntuacion += ($contador_emojis_positivos * 8);
        $puntuacion -= ($contador_emojis_negativos * 10);
        
        // Aplicar peso de indicadores semánticos
        $puntuacion += ($contador_positivos * 6);
        $puntuacion -= ($contador_negativos * 8);
        
        // Normalizar puntuación (0-100)
        $puntuacion = max(0, min(100, $puntuacion));
        
        // ============================================
        // 7️⃣ CLASIFICACIÓN FINAL
        // ============================================
        
        if ($puntuacion >= 65) {
            $tipo = 'positivo';
            $razon = "Sentimiento positivo detectado ({$puntuacion}/100)";
            if ($contador_emojis_positivos > 0) $razon .= " - Emojis positivos: {$contador_emojis_positivos}";
            if ($contador_positivos > 0) $razon .= " - Indicadores positivos: {$contador_positivos}";
            
        } elseif ($puntuacion <= 35) {
            $tipo = 'negativo';
            $razon = "Sentimiento negativo detectado ({$puntuacion}/100)";
            if ($contador_emojis_negativos > 0) $razon .= " - Emojis negativos: {$contador_emojis_negativos}";
            if ($contador_negativos > 0) $razon .= " - Indicadores negativos: {$contador_negativos}";
            if ($tiene_sarcasmo) $razon .= " - Sarcasmo detectado";
            
        } else {
            $tipo = 'neutral';
            $razon = "Sentimiento neutral ({$puntuacion}/100) - No hay suficientes indicadores claros";
        }
        
        return [
            'tipo' => $tipo,
            'puntuacion' => $puntuacion,
            'razon' => $razon,
            'detalles' => [
                'emojis_positivos' => $contador_emojis_positivos,
                'emojis_negativos' => $contador_emojis_negativos,
                'indicadores_positivos' => $contador_positivos,
                'indicadores_negativos' => $contador_negativos,
                'tiene_sarcasmo' => $tiene_sarcasmo,
                'tiene_negacion' => $tiene_negacion,
                'longitud' => $longitud
            ]
        ];
    }
    
    /**
     * Detectar si el comentario es spam
     * MEJORADO: Detecta más patrones de spam
     */
    private function contieneSpam($texto) {
        $texto_lower = mb_strtolower($texto);
        
        // Detectar spam común
        $spam_patterns = [
            'compra aquí', 'haz clic', 'gana dinero', 'visita mi perfil',
            'entra a mi web', 'sígueme', 'follow me', 'mira mi instagram',
            'www.', 'http', '.com', '.net', '.org', 'whatsapp', 'telegram',
            'inbox me', 'dm me', 'contactame', 'escribeme', 'link en bio',
            'solo hoy', 'oferta', 'descuento', 'gratis', 'promoción',
            'trabaja desde casa', 'dinero fácil', 'hazte rico'
        ];
        
        foreach ($spam_patterns as $pattern) {
            if (strpos($texto_lower, $pattern) !== false) {
                return true;
            }
        }
        
        // Detectar repetición excesiva (aaaaaa, jajajaja...)
        if (preg_match('/(.)\1{5,}/', $texto)) {
            return true;
        }
        
        // Detectar exceso de emojis (>5 emojis seguidos = probable spam)
        $emoji_count = preg_match_all('/[\x{1F300}-\x{1F9FF}]/u', $texto);
        if ($emoji_count > 5) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Registrar karma por reacción positiva
     * MEJORADO: Acepta cualquier tipo de reacción, excepto negativas
     */
    /**
     * 🤖 ANÁLISIS INTELIGENTE DE REACCIONES
     * Evalúa el contexto emocional de cada reacción automáticamente
     */
    public function registrarReaccionPositiva($usuario_id, $publicacion_id, $tipo_reaccion) {
        // 🤖 Análisis inteligente de la reacción
        $analisis = $this->analizarReaccionInteligente($tipo_reaccion);
        
        error_log("🤖 KARMA AI REACCIÓN: {$tipo_reaccion} → Sentimiento: {$analisis['tipo']} ({$analisis['puntos']} puntos) - {$analisis['razon']}");
        
        if ($analisis['tipo'] === 'positivo') {
            // ✅ REACCIÓN POSITIVA - DAR KARMA
            return $this->registrarAccion(
                $usuario_id,
                'apoyo_publicacion',
                $publicacion_id,
                'publicacion',
                $analisis['razon']
            );
            
        } elseif ($analisis['tipo'] === 'negativo') {
            // ❌ REACCIÓN NEGATIVA - QUITAR KARMA
            return $this->registrarAccion(
                $usuario_id,
                'reaccion_negativa',
                $publicacion_id,
                'publicacion',
                $analisis['razon']
            );
        }
        
        // ⚪ NEUTRAL - NO DAR NI QUITAR KARMA
        return false;
    }
    
    /**
     * 🤖 ANÁLISIS INTELIGENTE DE REACCIONES
     * Detecta automáticamente si una reacción es positiva, negativa o neutral
     */
    private function analizarReaccionInteligente($tipo_reaccion) {
        $tipo_lower = mb_strtolower($tipo_reaccion);
        
        // Mapeo inteligente de reacciones por categoría emocional
        
        // 💖 REACCIONES DE AMOR Y ADMIRACIÓN (+5 puntos)
        $amor_admiracion = [
            'love', 'me_encanta', 'encanta', 'amor', 'heart', 'corazon',
            'adorable', 'cute', 'hermoso', 'precioso', 'beautiful'
        ];
        
        // 👍 REACCIONES DE APOYO Y APROBACIÓN (+3 puntos)
        $apoyo_aprobacion = [
            'like', 'me_gusta', 'gusta', 'thumbsup', 'thumbs_up', 'pulgar',
            'ok', 'bien', 'good', 'great', 'excelente', 'genial',
            'aplaudir', 'clap', 'bravo', 'wow', 'asombro', 'sorpresa_positiva'
        ];
        
        // 😂 REACCIONES DE ALEGRÍA Y DIVERSIÓN (+3 puntos)
        $alegria_diversion = [
            'haha', 'jaja', 'risa', 'laugh', 'lol', 'funny', 'divertido',
            'me_divierte', 'divierte', 'gracioso', 'chistoso', 'humor',
            'feliz', 'happy', 'joy', 'alegria', 'contento', 'sonrisa'
        ];
        
        // 🎉 REACCIONES DE CELEBRACIÓN (+4 puntos)
        $celebracion = [
            'celebrar', 'fiesta', 'party', 'yay', 'hurra', 'victoria',
            'exito', 'logro', 'felicidades', 'congratulations'
        ];
        
        // 💪 REACCIONES DE MOTIVACIÓN (+3 puntos)
        $motivacion = [
            'fuerza', 'power', 'strong', 'fuerte', 'animo', 'vamos',
            'puedes', 'sigue', 'adelante', 'fight', 'lucha'
        ];
        
        // 🤔 REACCIONES NEUTRALES (0 puntos)
        $neutrales = [
            'pensativo', 'thinking', 'hmm', 'interesante', 'curious',
            'duda', 'pregunta', 'nose', 'maybe', 'quizas',
            'sorpresa', 'surprise', 'wow_neutro', 'asombro_neutro'
        ];
        
        // 😢 REACCIONES DE TRISTEZA (-1 punto, leve)
        $tristeza = [
            'sad', 'triste', 'me_entristece', 'entristece', 'cry', 'llanto',
            'lagrimas', 'pena', 'lastima', 'compasion', 'melancolico'
        ];
        
        // 😡 REACCIONES DE IRA Y RECHAZO (-3 puntos)
        $ira_rechazo = [
            'angry', 'enojo', 'me_enoja', 'enoja', 'furia', 'rabia',
            'mad', 'molesto', 'irritado', 'furioso', 'colera',
            'disgust', 'asco', 'desagrado', 'rechazo', 'odio'
        ];
        
        // 🤮 REACCIONES OFENSIVAS (-5 puntos)
        $ofensivas = [
            'vomit', 'puke', 'vomito', 'nausea', 'disgusting',
            'horrible', 'terrible', 'poo', 'caca', 'mierda',
            'basura', 'trash', 'porqueria'
        ];
        
        // 👎 REACCIONES DE DESAPROBACIÓN (-4 puntos)
        $desaprobacion = [
            'dislike', 'no_gusta', 'thumbsdown', 'thumbs_down',
            'mal', 'bad', 'wrong', 'nope', 'no', 'rechazo'
        ];
        
        // 🔍 ANÁLISIS: Buscar coincidencias en cada categoría
        
        // Amor y admiración
        foreach ($amor_admiracion as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'positivo',
                    'puntos' => 5,
                    'razon' => "Reacción de amor/admiración: {$tipo_reaccion}",
                    'categoria' => 'amor_admiracion'
                ];
            }
        }
        
        // Apoyo y aprobación
        foreach ($apoyo_aprobacion as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'positivo',
                    'puntos' => 3,
                    'razon' => "Reacción de apoyo/aprobación: {$tipo_reaccion}",
                    'categoria' => 'apoyo_aprobacion'
                ];
            }
        }
        
        // Alegría y diversión
        foreach ($alegria_diversion as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'positivo',
                    'puntos' => 3,
                    'razon' => "Reacción de alegría/diversión: {$tipo_reaccion}",
                    'categoria' => 'alegria_diversion'
                ];
            }
        }
        
        // Celebración
        foreach ($celebracion as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'positivo',
                    'puntos' => 4,
                    'razon' => "Reacción de celebración: {$tipo_reaccion}",
                    'categoria' => 'celebracion'
                ];
            }
        }
        
        // Motivación
        foreach ($motivacion as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'positivo',
                    'puntos' => 3,
                    'razon' => "Reacción motivadora: {$tipo_reaccion}",
                    'categoria' => 'motivacion'
                ];
            }
        }
        
        // Ofensivas (revisar primero para evitar falsos positivos)
        foreach ($ofensivas as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'negativo',
                    'puntos' => -5,
                    'razon' => "Reacción ofensiva: {$tipo_reaccion}",
                    'categoria' => 'ofensiva'
                ];
            }
        }
        
        // Ira y rechazo
        foreach ($ira_rechazo as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'negativo',
                    'puntos' => -3,
                    'razon' => "Reacción de ira/rechazo: {$tipo_reaccion}",
                    'categoria' => 'ira_rechazo'
                ];
            }
        }
        
        // Desaprobación
        foreach ($desaprobacion as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'negativo',
                    'puntos' => -4,
                    'razon' => "Reacción de desaprobación: {$tipo_reaccion}",
                    'categoria' => 'desaprobacion'
                ];
            }
        }
        
        // Tristeza
        foreach ($tristeza as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'negativo',
                    'puntos' => -1,
                    'razon' => "Reacción de tristeza: {$tipo_reaccion}",
                    'categoria' => 'tristeza'
                ];
            }
        }
        
        // Neutrales
        foreach ($neutrales as $patron) {
            if (strpos($tipo_lower, $patron) !== false) {
                return [
                    'tipo' => 'neutral',
                    'puntos' => 0,
                    'razon' => "Reacción neutral: {$tipo_reaccion}",
                    'categoria' => 'neutral'
                ];
            }
        }
        
        // 🎯 Si no se encontró coincidencia, analizar por emojis comunes
        // Esto permite que funcione incluso con nuevas reacciones
        
        $emojis_positivos_reaccion = ['😍', '🥰', '😘', '❤️', '💕', '💖', '💗', '👍', '👏', '🙌', '💪', '🎉', '🎊', '😊', '😃', '😄', '😁', '😂', '🤣'];
        $emojis_negativos_reaccion = ['😡', '😠', '🤬', '👎', '😢', '😭', '🤮', '💩', '😒', '🙄'];
        
        foreach ($emojis_positivos_reaccion as $emoji) {
            if (strpos($tipo_reaccion, $emoji) !== false) {
                return [
                    'tipo' => 'positivo',
                    'puntos' => 3,
                    'razon' => "Reacción emoji positivo: {$tipo_reaccion}",
                    'categoria' => 'emoji_positivo'
                ];
            }
        }
        
        foreach ($emojis_negativos_reaccion as $emoji) {
            if (strpos($tipo_reaccion, $emoji) !== false) {
                return [
                    'tipo' => 'negativo',
                    'puntos' => -2,
                    'razon' => "Reacción emoji negativo: {$tipo_reaccion}",
                    'categoria' => 'emoji_negativo'
                ];
            }
        }
        
        // 🤷 DEFAULT: Si no se puede clasificar, asumir positiva (beneficio de la duda)
        return [
            'tipo' => 'positivo',
            'puntos' => 2,
            'razon' => "Reacción no clasificada (asumida como apoyo): {$tipo_reaccion}",
            'categoria' => 'desconocida'
        ];
    }
    
    /**
     * Obtener karma completo de un usuario (método conveniente)
     */
    public function obtenerKarmaUsuario($usuario_id) {
        try {
            // Obtener karma total
            $karmaData = $this->obtenerKarmaTotal($usuario_id);
            $karma_total = $karmaData['karma_total'];
            
            // Obtener nivel
            $nivelData = $this->obtenerNivelKarma($karma_total);
            
            // Obtener próxima recompensa desbloqueada
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
                'nivel' => $nivelData['titulo'], // "Novato", "Intermedio"...
                'nivel_data' => $nivelData, // Array completo con nivel numérico
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
     * Obtener karma reciente (últimos 30 días)
     */
    public function obtenerKarmaReciente($usuario_id) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT 
                    COALESCE(SUM(puntos), 0) as karma_30dias,
                    COUNT(*) as acciones_30dias
                FROM karma_social
                WHERE usuario_id = ?
                AND fecha_accion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            
            $stmt->execute([$usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener karma reciente: " . $e->getMessage());
            return ['karma_30dias' => 0, 'acciones_30dias' => 0];
        }
    }
    
    /**
     * Obtener nivel de karma
     * Sistema progresivo: Nivel 1 = 100 pts, Nivel 2 = 200 pts, Nivel 3 = 300 pts, etc.
     */
    public function obtenerNivelKarma($karma_total) {
        // Calcular nivel: cada 100 puntos = 1 nivel
        $nivel = floor($karma_total / 100) + 1;
        
        // Puntos necesarios para el siguiente nivel
        $puntos_siguiente_nivel = $nivel * 100;
        $puntos_nivel_actual = ($nivel - 1) * 100;
        $progreso = $karma_total - $puntos_nivel_actual;
        
        // Emojis y colores por rango de nivel
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
     * Calcular multiplicador de karma para conexiones
     * Usuarios con más karma tienen prioridad en conexiones
     */
    public function calcularMultiplicadorConexiones($karma_total) {
        if ($karma_total >= 500) {
            return 1.5; // 50% de bonus
        } elseif ($karma_total >= 250) {
            return 1.3; // 30% de bonus
        } elseif ($karma_total >= 100) {
            return 1.2; // 20% de bonus
        } elseif ($karma_total >= 50) {
            return 1.1; // 10% de bonus
        } else {
            return 1.0; // Sin bonus
        }
    }
    
    /**
     * Verificar si una acción es duplicada
     */
    private function esAccionDuplicada($usuario_id, $tipo_accion, $referencia_id, $referencia_tipo) {
        // Acciones que no deben duplicarse
        $acciones_unicas = ['apoyo_publicacion', 'comentario_positivo', 'primera_interaccion'];
        
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
