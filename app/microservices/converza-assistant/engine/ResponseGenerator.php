<?php
/**
 * 🤖 RESPONSE GENERATOR - Generador de Respuestas
 * Genera respuestas personalizadas según la intención y contexto
 */

class ResponseGenerator {
    
    /**
     * Generar respuesta según intención y contexto
     * @param array $intent Intención clasificada
     * @param array $context Contexto del usuario
     * @return array ['answer' => '...', 'suggestions' => [...], 'links' => [...]]
     */
    public function generate($intent, $context) {
        $intentName = $intent['name'];
        $intentData = $intent['data'];
        
        // Si la intención es desconocida
        if ($intentName === 'unknown' || !$intentData) {
            return $this->generateUnknownResponse($context);
        }
        
        // Si requiere datos del sistema (hora, fecha, usuarios activos)
        if (isset($intentData['requires_system_data']) && $intentData['requires_system_data']) {
            return $this->generateDynamicResponse($intentName, $intentData, $context);
        }
        
        // Obtener respuesta base
        $answer = $intentData['answer'] ?? 'Lo siento, no tengo información sobre eso.';
        
        // Personalizar con contexto del usuario
        $answer = $this->personalizeAnswer($answer, $context);
        
        // Generar sugerencias
        $suggestions = $this->generateSuggestions($intentName);
        
        // Obtener links relevantes
        $links = $intentData['links'] ?? [];
        
        return [
            'answer' => $answer,
            'suggestions' => $suggestions,
            'links' => $links
        ];
    }
    
    /**
     * Generar respuestas dinámicas con datos del sistema en tiempo real
     */
    private function generateDynamicResponse($intentName, $intentData, $context) {
        date_default_timezone_set('America/Bogota');
        
        switch ($intentName) {
            case 'time_query':
                $currentTime = date('h:i A');
                $currentDate = date('d/m/Y');
                $answer = str_replace('{current_time}', $currentTime, $intentData['answers'][0]);
                $answer = str_replace('{current_date}', $currentDate, $answer);
                break;
                
            case 'date_query':
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                
                $dayName = $dias[date('w')];
                $dayNumber = date('d');
                $monthName = $meses[intval(date('m'))];
                $year = date('Y');
                
                $answer = str_replace('{day_name}', $dayName, $intentData['answers'][0]);
                $answer = str_replace('{day_number}', $dayNumber, $answer);
                $answer = str_replace('{month_name}', $monthName, $answer);
                $answer = str_replace('{year}', $year, $answer);
                break;
                
            case 'user_activity_query':
                $activeUsers = $this->getActiveUsersCount();
                $answer = str_replace('{active_users}', $activeUsers, $intentData['answers'][0]);
                break;
                
            case 'trending_topics':
                $topics = $this->getTrendingTopics();
                $answer = str_replace('{trending_topics}', $topics, $intentData['answers'][0]);
                break;
                
            default:
                $answer = $intentData['answers'][0] ?? 'Lo siento, no tengo esa información.';
        }
        
        // Personalizar con contexto del usuario
        $answer = $this->personalizeAnswer($answer, $context);
        
        return [
            'answer' => $answer,
            'suggestions' => $intentData['suggestions'] ?? $this->generateSuggestions($intentName),
            'links' => $intentData['links'] ?? []
        ];
    }
    
    /**
     * Obtener cantidad de usuarios activos (últimos 15 minutos)
     * NOTA: Como la tabla usuarios no tiene campo ultima_conexion,
     * usamos un conteo de usuarios registrados como aproximación
     */
    private function getActiveUsersCount() {
        try {
            global $conexion;
            if (!isset($conexion)) {
                require_once(__DIR__ . '/../../../models/config.php');
            }
            
            // Contar usuarios totales registrados como aproximación
            // En el futuro se puede agregar el campo ultima_conexion
            $query = "SELECT COUNT(DISTINCT id_use) as total FROM usuarios";
            $stmt = $conexion->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalUsers = $result['total'] ?? 0;
            
            // Estimar ~20% de usuarios están "activos" en cualquier momento
            $activeUsers = max(1, round($totalUsers * 0.2));
            
            return $activeUsers;
        } catch (Exception $e) {
            error_log("❌ Error getting active users: " . $e->getMessage());
            return 5; // Valor por defecto
        }
    }
    
    /**
     * Obtener temas en tendencia (palabras más usadas en comentarios recientes)
     */
    private function getTrendingTopics() {
        try {
            global $conexion;
            if (!isset($conexion)) {
                require_once(__DIR__ . '/../../../models/config.php');
            }
            
            // Obtener comentarios recientes (últimas 24 horas)
            $query = "SELECT comentario 
                      FROM comentarios 
                      WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                      LIMIT 100";
            $stmt = $conexion->query($query);
            $comments = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($comments)) {
                return "• No hay temas en tendencia aún hoy";
            }
            
            // Contar palabras (simple)
            $words = [];
            foreach ($comments as $comment) {
                $tokens = preg_split('/\s+/', strtolower($comment));
                foreach ($tokens as $token) {
                    $token = trim($token, '.,!?¡¿');
                    if (strlen($token) > 3) { // Solo palabras de más de 3 letras
                        $words[$token] = ($words[$token] ?? 0) + 1;
                    }
                }
            }
            
            arsort($words);
            $topWords = array_slice(array_keys($words), 0, 5);
            
            return "• " . implode("\n• ", array_map('ucfirst', $topWords));
        } catch (Exception $e) {
            error_log("❌ Error getting trending topics: " . $e->getMessage());
            return "• Error al obtener tendencias";
        }
    }
    
    /**
     * Personalizar respuesta con contexto del usuario
     */
    private function personalizeAnswer($answer, $context) {
        $username = $context['username'] ?? 'Usuario';
        $karma = $context['karma'] ?? 0;
        $nivel = $context['nivel'] ?? 1;
        $nivelTitulo = $context['nivel_titulo'] ?? 'Novato';
        $nivelEmoji = $context['nivel_emoji'] ?? '🌱';
        
        // Reemplazar placeholders
        $answer = str_replace('{username}', $username, $answer);
        $answer = str_replace('{karma}', $karma, $answer);
        $answer = str_replace('{nivel}', $nivel, $answer);
        $answer = str_replace('{nivel_titulo}', $nivelTitulo, $answer);
        $answer = str_replace('{nivel_emoji}', $nivelEmoji, $answer);
        
        // Calcular puntos faltantes para siguiente nivel
        $puntosProximoNivel = $this->calculateNextLevelPoints($nivel);
        $puntosFaltantes = max(0, $puntosProximoNivel - $karma);
        
        $answer = str_replace('{puntos_proximo_nivel}', $puntosProximoNivel, $answer);
        $answer = str_replace('{puntos_faltantes}', $puntosFaltantes, $answer);
        
        return $answer;
    }
    
    /**
     * Calcular puntos necesarios para el siguiente nivel
     */
    private function calculateNextLevelPoints($nivelActual) {
        $niveles = [
            1 => 0,
            2 => 50,
            3 => 150,
            4 => 300,
            5 => 500,
            6 => 800,
            7 => 1200,
            8 => 1700,
            9 => 2500,
            10 => 3500
        ];
        
        $nivelSiguiente = min($nivelActual + 1, 10);
        return $niveles[$nivelSiguiente] ?? 5000;
    }
    
    /**
     * Generar sugerencias de preguntas relacionadas
     */
    private function generateSuggestions($intentName) {
        $suggestionMap = [
            'karma_gain' => [
                '¿Qué son las reacciones?',
                '¿Cómo subo de nivel?',
                '¿Qué puedo comprar con karma?'
            ],
            'greeting' => [
                '¿Cómo gano karma?',
                '¿Qué puedes hacer?',
                '¿Cómo funciona la tienda?'
            ],
            'assistant_status' => [
                '¿Cómo gano más karma?',
                '¿Qué nivel es el siguiente?',
                '¿Qué puedo comprar?'
            ],
            'thanks' => [
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Cómo funciona la tienda?'
            ],
            'goodbye' => [
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Cómo subo de nivel?'
            ],
            'assistant_identity' => [
                '¿Qué puedes hacer?',
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?'
            ],
            'assistant_capabilities' => [
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Cómo funciona la tienda?'
            ],
            'compliment' => [
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Cómo funciona la tienda?'
            ],
            'joke_request' => [
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Cómo funciona la tienda?'
            ],
            'help' => [
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Cómo funciona el sistema de niveles?'
            ],
            'converza_overview' => [
                '¿Cómo gano karma?',
                '¿Qué funciones tiene Converza?',
                '¿Cómo empiezo?'
            ],
            'converza_features' => [
                '¿Qué puedo hacer en Converza?',
                '¿Cómo gano karma?',
                '¿Cómo funciona la tienda?'
            ],
            'converza_getting_started' => [
                '¿Cómo gano karma rápido?',
                '¿Qué son las reacciones?',
                '¿Cómo hago amigos?'
            ],
            'converza_best_practices' => [
                '¿Cómo subo de nivel?',
                '¿Qué puedo comprar?',
                '¿Qué son las conexiones místicas?'
            ],
            'converza_purpose' => [
                '¿Qué puedo hacer en Converza?',
                '¿Cómo funciona el karma?',
                '¿Qué son las conexiones místicas?'
            ]
        ];
        
        return $suggestionMap[$intentName] ?? [
            'karma_loss' => [
                '¿Qué reacciones quitan puntos?',
                '¿Cómo recupero karma perdido?',
                '¿Puedo ver quién me quitó puntos?'
            ],
            'karma_levels' => [
                '¿Cuántos niveles hay?',
                '¿Qué beneficios tiene cada nivel?',
                '¿Cómo gano karma más rápido?'
            ],
            'reactions_info' => [
                '¿Cuántos puntos da cada reacción?',
                '¿Qué reacciones son negativas?',
                '¿Puedo quitar una reacción?'
            ],
            'notifications' => [
                '¿Cómo desactivo notificaciones?',
                '¿Por qué no me llegan notificaciones?',
                '¿Puedo ver notificaciones antiguas?'
            ],
            'friends' => [
                '¿Cómo envío solicitud de amistad?',
                '¿Puedo bloquear a alguien?',
                '¿Qué beneficios tiene tener amigos?'
            ],
            'mystic_connections' => [
                '¿Cómo se calculan las conexiones?',
                '¿Qué es un coincidence alert?',
                '¿Puedo ver mis conexiones?'
            ],
            'shop' => [
                '¿Qué marcos hay disponibles?',
                '¿Cómo equipar un marco?',
                '¿Los marcos cuestan karma?'
            ]
        ];
        
        return $suggestionMap[$intentName] ?? [
            '¿Cómo gano karma?',
            '¿Qué son las reacciones?',
            '¿Cómo funciona la tienda?'
        ];
    }
    
    /**
     * Generar respuesta para intención desconocida
     */
    private function generateUnknownResponse($context = []) {
        // Respuestas especiales para preguntas filosóficas/generales comunes
        $specialResponses = [
            'amor' => "¡Qué pregunta tan profunda! 💭 El amor es un tema fascinante, pero yo me especializo más en ayudarte con Converza.\n\nPor cierto, ¿sabías que aquí puedes hacer conexiones especiales con personas afines? Las **conexiones místicas** usan compatibilidad para sugerirte amigos perfectos. 💫\n\n¿Quieres saber cómo funciona?",
            'vida' => "La vida... tema profundo 🌟 Aunque soy un asistente de Converza, puedo decirte que aquí puedes compartir momentos importantes de tu vida con una comunidad increíble.\n\n¿Te gustaría saber cómo conectar con personas afines?",
            'felicidad' => "La felicidad es importante 😊 En Converza puedes encontrar alegría compartiendo con amigos, publicando momentos especiales y construyendo una comunidad positiva.\n\n¿Quieres saber cómo empezar?",
            'sentido' => "Preguntas existenciales, ¿eh? 🤔 Yo le encuentro sentido ayudándote con Converza jaja.\n\n¿En qué puedo asistirte hoy?"
        ];
        
        // Verificar si la pregunta coincide con alguna respuesta especial
        $question = strtolower($context['last_question'] ?? '');
        foreach ($specialResponses as $keyword => $response) {
            if (stripos($question, $keyword) !== false) {
                return [
                    'answer' => $response,
                    'suggestions' => [
                        '¿Qué son las conexiones?',
                        '¿Cómo hago amigos?',
                        '¿Cómo gano karma?'
                    ],
                    'links' => []
                ];
            }
        }
        
        // Respuestas genéricas variadas
        $genericAnswers = [
            "Hmm, esa es una pregunta interesante 🤔. Aunque no tengo información específica sobre eso, puedo ayudarte con **Converza**: cómo funciona la plataforma, el sistema de karma, las reacciones, niveles y más. ¿Te gustaría saber algo de esto?",
            "No estoy completamente seguro de cómo responder a eso, pero déjame ayudarte con lo que sé mejor 😊:\n\n• 📱 **Qué es Converza** y qué puedes hacer\n• 🎯 **Sistema de karma** - ganar y usar puntos\n• 😊 **Reacciones** - tipos y valores\n• 📊 **Niveles** - cómo subir y beneficios\n• 🛍️ **Tienda** - marcos y personalización\n\n¿Sobre qué te gustaría saber?",
            "Esa pregunta está un poco fuera de mi área, pero te puedo contar todo sobre Converza 💡:\n\n¿Sabías que puedes ganar karma interactuando? Actualmente tienes **{karma} puntos** y eres nivel **{nivel} {nivel_emoji}**.\n\n¿Quieres saber cómo maximizar tu experiencia en la plataforma?",
            "Interesante pregunta 🤓, aunque no tengo una respuesta específica. Lo que sí puedo hacer es ayudarte a dominar Converza:\n\n✨ **Entender Converza** - qué es y qué ofrece\n🎯 **Ganar karma** - estrategias efectivas\n📈 **Subir de nivel** - requisitos y beneficios\n🛍️ **Personalizar** - tienda y recompensas\n\n¿Qué te interesa explorar?"
        ];
        
        $answer = $genericAnswers[array_rand($genericAnswers)];
        
        // Personalizar con contexto si está disponible
        if (!empty($context)) {
            $answer = $this->personalizeAnswer($answer, $context);
        }
        
        return [
            'answer' => $answer,
            'suggestions' => [
                '¿Qué puedo hacer en Converza?',
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Cómo funciona la tienda?'
            ],
            'links' => []
        ];
    }
}
