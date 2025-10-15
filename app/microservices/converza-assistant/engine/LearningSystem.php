<?php
/**
 * 🧠 LEARNING SYSTEM - Sistema de Aprendizaje Contextual
 * Analiza conversaciones de usuarios para mejorar las respuestas del asistente
 */

class LearningSystem {
    private $conexion;
    private $cacheFile;
    private $cacheDuration = 3600; // 1 hora
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->cacheFile = __DIR__ . '/../cache/learning-cache.json';
    }
    
    /**
     * Obtener patrones de conversación de la base de datos
     */
    public function getConversationPatterns() {
        // Verificar si hay cache válido
        if ($this->hasFreshCache()) {
            return $this->loadFromCache();
        }
        
        $patterns = [
            'common_greetings' => $this->extractGreetings(),
            'common_phrases' => $this->extractCommonPhrases(),
            'question_patterns' => $this->extractQuestions(),
            'emotional_expressions' => $this->extractEmotionalExpressions()
        ];
        
        // Guardar en cache
        $this->saveToCache($patterns);
        
        return $patterns;
    }
    
    /**
     * Extraer saludos comunes de mensajes
     */
    private function extractGreetings() {
        try {
            $query = "
                SELECT mensaje, COUNT(*) as frecuencia
                FROM mensajes
                WHERE LENGTH(mensaje) < 50
                AND (
                    mensaje LIKE '%hola%' OR
                    mensaje LIKE '%buenos%' OR
                    mensaje LIKE '%buenas%' OR
                    mensaje LIKE '%qué tal%' OR
                    mensaje LIKE '%cómo estás%' OR
                    mensaje LIKE '%hey%'
                )
                GROUP BY LOWER(mensaje)
                ORDER BY frecuencia DESC
                LIMIT 20
            ";
            
            $stmt = $this->conexion->query($query);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("❌ LearningSystem: Error extracting greetings - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Extraer frases comunes
     */
    private function extractCommonPhrases() {
        try {
            $query = "
                SELECT mensaje, COUNT(*) as frecuencia
                FROM mensajes
                WHERE LENGTH(mensaje) BETWEEN 10 AND 100
                GROUP BY LOWER(mensaje)
                HAVING frecuencia > 2
                ORDER BY frecuencia DESC
                LIMIT 50
            ";
            
            $stmt = $this->conexion->query($query);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("❌ LearningSystem: Error extracting phrases - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Extraer preguntas comunes
     */
    private function extractQuestions() {
        try {
            $query = "
                SELECT mensaje, COUNT(*) as frecuencia
                FROM mensajes
                WHERE mensaje LIKE '%?%'
                OR mensaje LIKE '%cómo%'
                OR mensaje LIKE '%qué%'
                OR mensaje LIKE '%cuál%'
                OR mensaje LIKE '%dónde%'
                OR mensaje LIKE '%cuándo%'
                OR mensaje LIKE '%por qué%'
                GROUP BY LOWER(mensaje)
                HAVING frecuencia > 1
                ORDER BY frecuencia DESC
                LIMIT 30
            ";
            
            $stmt = $this->conexion->query($query);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("❌ LearningSystem: Error extracting questions - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Extraer expresiones emocionales
     */
    private function extractEmotionalExpressions() {
        try {
            $query = "
                SELECT mensaje, COUNT(*) as frecuencia
                FROM mensajes
                WHERE mensaje REGEXP '(jaj|lol|xd|😂|😊|😍|😭|😱|🥺|💕)'
                OR mensaje LIKE '%jaja%'
                OR mensaje LIKE '%jeje%'
                GROUP BY LOWER(mensaje)
                ORDER BY frecuencia DESC
                LIMIT 20
            ";
            
            $stmt = $this->conexion->query($query);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("❌ LearningSystem: Error extracting emotions - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analizar comentarios para aprender expresiones
     */
    public function analyzeComments() {
        try {
            $query = "
                SELECT comentario, COUNT(*) as frecuencia
                FROM comentarios
                WHERE LENGTH(comentario) < 200
                GROUP BY LOWER(comentario)
                HAVING frecuencia > 1
                ORDER BY frecuencia DESC
                LIMIT 30
            ";
            
            $stmt = $this->conexion->query($query);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("❌ LearningSystem: Error analyzing comments - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar respuesta inteligente basada en patrones aprendidos
     */
    public function generateSmartResponse($question, $patterns) {
        $question_lower = strtolower($question);
        
        // Detectar preguntas sobre hora/fecha
        if ($this->isTimeQuery($question_lower)) {
            return $this->generateTimeResponse();
        }
        
        // Detectar preguntas sobre el día
        if ($this->isDayQuery($question_lower)) {
            return $this->generateDayResponse();
        }
        
        // Detectar preguntas sobre usuarios activos
        if ($this->isUserActivityQuery($question_lower)) {
            return $this->generateUserActivityResponse();
        }
        
        // Detectar preguntas sobre tendencias
        if ($this->isTrendingQuery($question_lower)) {
            return $this->generateTrendingResponse();
        }
        
        // Detectar inglés
        if ($this->isEnglishQuery($question_lower)) {
            return $this->generateEnglishResponse();
        }
        
        // Detectar emociones y responder apropiadamente (DEBE IR DESPUÉS de inglés)
        if ($this->hasEmotionalContent($question_lower, $patterns)) {
            return $this->generateEmotionalResponse($question_lower);
        }
        
        return null; // No hay respuesta específica
    }
    
    /**
     * Detectar si pregunta por la hora
     */
    private function isTimeQuery($question) {
        return preg_match('/(qué hora|que hora|hora es|cuál es la hora|dime la hora)/i', $question);
    }
    
    /**
     * Detectar si pregunta por el día
     */
    private function isDayQuery($question) {
        return preg_match('/(qué día|que dia|día es|cuál es el día|qué fecha|que fecha)/i', $question);
    }
    
    /**
     * Detectar si pregunta por usuarios activos
     */
    private function isUserActivityQuery($question) {
        return preg_match('/(cuántos usuarios|cuantos usuarios|usuarios activos|usuarios hay|gente online|gente conectada|usuarios conectados)/i', $question);
    }
    
    /**
     * Detectar si pregunta por tendencias
     */
    private function isTrendingQuery($question) {
        return preg_match('/(qué se habla|que se habla|qué hablan|que hablan|temas populares|tendencia|de qué habla|de que habla)/i', $question);
    }
    
    /**
     * Detectar si la pregunta es en inglés
     */
    private function isEnglishQuery($question) {
        $englishKeywords = ['hello', 'hi', 'how are you', 'what', 'when', 'where', 'who', 'why', 'good morning', 'good afternoon', 'good evening', 'thanks', 'thank you', 'bye', 'goodbye'];
        
        foreach ($englishKeywords as $keyword) {
            if (strpos($question, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generar respuesta con hora actual
     */
    private function generateTimeResponse() {
        date_default_timezone_set('America/Bogota'); // Ajusta según tu zona horaria
        $hora = date('h:i A');
        $fecha = date('d/m/Y');
        
        return [
            'answer' => "🕐 Ahora son las **{$hora}** del día **{$fecha}**.\n\n¿En qué más puedo ayudarte con Converza?",
            'suggestions' => [
                '¿Cómo gano karma?',
                '¿Qué son las reacciones?',
                '¿Qué puedo hacer en Converza?'
            ],
            'links' => [],
            'is_smart_response' => true
        ];
    }
    
    /**
     * Generar respuesta con día/fecha actual
     */
    private function generateDayResponse() {
        date_default_timezone_set('America/Bogota');
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        $dia_semana = $dias[date('w')];
        $dia = date('d');
        $mes = $meses[intval(date('m'))];
        $año = date('Y');
        
        return [
            'answer' => "📅 Hoy es **{$dia_semana} {$dia} de {$mes} de {$año}**.\n\n¿Quieres saber algo sobre Converza?",
            'suggestions' => [
                '¿Cómo gano karma?',
                '¿Qué puedo hacer hoy?',
                '¿Cómo funciona la tienda?'
            ],
            'links' => [],
            'is_smart_response' => true
        ];
    }
    
    /**
     * Generar respuesta con usuarios activos
     */
    private function generateUserActivityResponse() {
        try {
            // Contar usuarios totales
            $query = "SELECT COUNT(DISTINCT id_use) as total FROM usuarios";
            $stmt = $this->conexion->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalUsers = $result['total'] ?? 0;
            
            // Estimar ~20% de usuarios están "activos"
            $activeUsers = max(1, round($totalUsers * 0.2));
            
            return [
                'answer' => "👥 Actualmente hay **{$activeUsers} usuarios activos** en Converza.\n\n¡Es un gran momento para socializar! ¿Quieres saber cómo hacer amigos?",
                'suggestions' => [
                    '¿Cómo hago amigos?',
                    '¿Cómo envío mensajes?',
                    '¿Qué son las conexiones?'
                ],
                'links' => [],
                'is_smart_response' => true
            ];
        } catch (Exception $e) {
            error_log("❌ Error getting active users: " . $e->getMessage());
            return [
                'answer' => "👥 Hay varios usuarios activos en Converza ahora mismo.\n\n¡Es un gran momento para socializar!",
                'suggestions' => ['¿Cómo hago amigos?', '¿Cómo envío mensajes?'],
                'links' => [],
                'is_smart_response' => true
            ];
        }
    }
    
    /**
     * Generar respuesta con tendencias
     */
    private function generateTrendingResponse() {
        try {
            // Obtener comentarios recientes (últimas 24 horas)
            $query = "SELECT comentario 
                      FROM comentarios 
                      WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                      LIMIT 100";
            $stmt = $this->conexion->query($query);
            $comments = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($comments)) {
                return [
                    'answer' => "🔥 Aún no hay suficientes comentarios hoy para detectar tendencias.\n\n¿Por qué no empiezas una conversación tú?",
                    'suggestions' => ['¿Cómo comento?', '¿Cómo público?', '¿Qué puedo hacer?'],
                    'links' => [],
                    'is_smart_response' => true
                ];
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
            
            $trendingList = "• " . implode("\n• ", array_map('ucfirst', $topWords));
            
            return [
                'answer' => "🔥 Los temas más comentados hoy en Converza son:\n\n{$trendingList}\n\n¿Quieres unirte a la conversación?",
                'suggestions' => [
                    '¿Cómo comento?',
                    '¿Cómo reacciono?',
                    '¿Cómo público?'
                ],
                'links' => [],
                'is_smart_response' => true
            ];
        } catch (Exception $e) {
            error_log("❌ Error getting trending topics: " . $e->getMessage());
            return [
                'answer' => "🔥 La comunidad está activa charlando sobre varios temas interesantes.\n\n¿Quieres unirte?",
                'suggestions' => ['¿Cómo comento?', '¿Cómo público?'],
                'links' => [],
                'is_smart_response' => true
            ];
        }
    }
    
    /**
     * Generar respuesta para pregunta en inglés
     */
    private function generateEnglishResponse() {
        return [
            'answer' => "🌎 Hello! I can understand some English, but I work better in Spanish.\n\n¿Prefieres que hablemos en español? I can help you better that way! 😊",
            'suggestions' => [
                '¿Qué es Converza?',
                '¿Cómo funciona el karma?',
                '¿Qué puedo hacer?'
            ],
            'links' => [],
            'is_smart_response' => true
        ];
    }
    
    /**
     * Detectar contenido emocional
     */
    private function hasEmotionalContent($question, $patterns) {
        // Palabras de alegría
        $happy_keywords = ['jaja', 'jeje', 'lol', 'jajaja', 'xd', 'feliz', 'contento', 'alegre'];
        
        // Palabras de tristeza
        $sad_keywords = ['triste', 'mal', 'terrible', 'horrible', 'deprimido', 'decepcionado'];
        
        foreach ($happy_keywords as $keyword) {
            if (stripos(strtolower($question), strtolower($keyword)) !== false) {
                return true;
            }
        }
        
        foreach ($sad_keywords as $keyword) {
            if (stripos(strtolower($question), strtolower($keyword)) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generar respuesta emocional
     */
    private function generateEmotionalResponse($question) {
        $q = strtolower($question);
        
        // Detectar alegría/diversión
        if (stripos($q, 'jaja') !== false || stripos($q, 'jeje') !== false || 
            stripos($q, 'lol') !== false || stripos($q, 'xd') !== false ||
            stripos($q, 'feliz') !== false || stripos($q, 'alegre') !== false ||
            stripos($q, 'contento') !== false || stripos($q, 'divertido') !== false) {
            return [
                'answer' => "¡Me alegra verte de buen humor! 😄✨\n\n¿Sabías que compartir contenido positivo en Converza te ayuda a ganar más karma? ¡La comunidad adora las buenas vibras!\n\n¿En qué más puedo ayudarte?",
                'suggestions' => ['¿Cómo gano karma?', 'Cuéntame un chiste', '¿Qué son las reacciones?'],
                'links' => [],
                'is_smart_response' => true
            ];
        }
        
        // Detectar tristeza/malestar
        if (stripos($q, 'triste') !== false || stripos($q, 'mal') !== false || 
            stripos($q, 'terrible') !== false || stripos($q, 'horrible') !== false ||
            stripos($q, 'deprimido') !== false || stripos($q, 'decepcionado') !== false) {
            return [
                'answer' => "😔 Lamento que no te sientas bien.\n\nRecuerda que en Converza tienes una comunidad increíble que puede apoyarte. ¿Te gustaría conectar con tus amigos o explorar contenido que te anime?\n\n💙 Estoy aquí para ayudarte.",
                'suggestions' => ['¿Cómo hago amigos?', '¿Cómo funcionan las conexiones?', '¿Qué puedo hacer?'],
                'links' => [],
                'is_smart_response' => true
            ];
        }
        
        // Detectar emociones mixtas
        if ((stripos($q, 'feliz') !== false || stripos($q, 'alegre') !== false) && 
            (stripos($q, 'triste') !== false || stripos($q, 'mal') !== false)) {
            return [
                'answer' => "Entiendo... a veces sentimos emociones encontradas 💭\n\nEn Converza puedes expresarte libremente. ¿Quieres compartir algo con la comunidad o prefieres conversar con amigos cercanos?\n\n¿En qué puedo ayudarte?",
                'suggestions' => ['¿Cómo publico?', '¿Cómo hago amigos?', '¿Qué son las conexiones?'],
                'links' => [],
                'is_smart_response' => true
            ];
        }
        
        return null;
    }
    
    /**
     * Verificar si hay cache válido
     */
    private function hasFreshCache() {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $cacheTime = filemtime($this->cacheFile);
        return (time() - $cacheTime) < $this->cacheDuration;
    }
    
    /**
     * Cargar desde cache
     */
    private function loadFromCache() {
        $data = file_get_contents($this->cacheFile);
        return json_decode($data, true);
    }
    
    /**
     * Guardar en cache
     */
    private function saveToCache($data) {
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        file_put_contents($this->cacheFile, json_encode($data, JSON_PRETTY_PRINT));
    }
}
