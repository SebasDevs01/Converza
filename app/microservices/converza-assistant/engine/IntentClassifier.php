<?php
/**
 * 🤖 INTENT CLASSIFIER - Clasificador de Intenciones
 * Analiza la pregunta y determina la intención del usuario
 */

class IntentClassifier {
    private $knowledgeBase;
    
    public function __construct() {
        $this->loadKnowledgeBase();
    }
    
    /**
     * Cargar base de conocimientos desde JSON
     */
    private function loadKnowledgeBase() {
        $kbPath = __DIR__.'/../knowledge/';
        $files = [
            'contextual-kb.json',      // 🆕 Respuestas contextuales (hora, fecha, clima)
            'platform-kb.json',        // 🆕 Información general sobre Converza
            'conversational-kb.json',  // 🆕 Respuestas conversacionales
            'karma-kb.json',
            'reactions-kb.json',
            'notifications-kb.json',
            'social-kb.json',
            'general-kb.json'
        ];
        
        $this->knowledgeBase = [];
        foreach ($files as $file) {
            $path = $kbPath . $file;
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                $this->knowledgeBase = array_merge($this->knowledgeBase, $data['intents'] ?? []);
            }
        }
    }
    
    /**
     * Clasificar intención de una pregunta
     * @param string $question Pregunta del usuario
     * @return array ['name' => 'intent_name', 'confidence' => 0.85, 'data' => [...]]
     */
    public function classify($question) {
        $question = strtolower(trim($question));
        $tokens = $this->tokenize($question);
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($this->knowledgeBase as $intent) {
            // Calcular similitud con keywords
            $keywordScore = $this->calculateSimilarity($tokens, $intent['keywords']);
            
            // Calcular similitud con preguntas de ejemplo (más preciso)
            $questionScore = 0;
            if (isset($intent['questions'])) {
                foreach ($intent['questions'] as $exampleQ) {
                    $exampleTokens = $this->tokenize(strtolower($exampleQ));
                    $score = $this->calculateSimilarity($tokens, $exampleTokens);
                    $questionScore = max($questionScore, $score);
                }
            }
            
            // Promedio ponderado (60% keywords, 40% questions)
            $finalScore = ($keywordScore * 0.6) + ($questionScore * 0.4);
            
            // Bonus si contiene palabras exactas importantes
            foreach ($intent['keywords'] as $keyword) {
                if (strpos($question, $keyword) !== false) {
                    $finalScore += 0.15;
                }
            }
            
            if ($finalScore > $bestScore) {
                $bestScore = $finalScore;
                $bestMatch = $intent;
            }
        }
        
        // Si la confianza es muy baja, usar intención genérica
        // Reducido de 0.2 a 0.15 para ser más flexible
        if ($bestScore < 0.15) {
            return [
                'name' => 'unknown',
                'confidence' => $bestScore,
                'data' => null
            ];
        }
        
        return [
            'name' => $bestMatch['intent'],
            'confidence' => $bestScore,
            'data' => $bestMatch
        ];
    }
    
    /**
     * Tokenizar pregunta (eliminar stopwords)
     */
    private function tokenize($text) {
        // Stopwords en español
        $stopwords = [
            'el', 'la', 'de', 'que', 'y', 'a', 'en', 'un', 'ser', 'se', 'no', 'haber',
            'por', 'con', 'su', 'para', 'como', 'estar', 'tener', 'le', 'lo', 'todo',
            'pero', 'más', 'hacer', 'o', 'poder', 'decir', 'este', 'ir', 'otro', 'ese',
            'mi', 'tu', 'cual', 'muy', 'sin', 'sobre', 'hasta', 'entre', 'cuando', 'donde',
            'cómo', 'qué', 'quién', 'cuál', 'puedo', 'puede', 'me', 'te', 'son', 'es'
        ];
        
        // Normalizar texto
        $text = preg_replace('/[¿?¡!,.\-_]/', ' ', $text);
        $words = preg_split('/\s+/', $text);
        
        // Filtrar stopwords
        return array_filter($words, fn($w) => !in_array($w, $stopwords) && strlen($w) > 2);
    }
    
    /**
     * Calcular similitud (Jaccard Index)
     */
    private function calculateSimilarity($tokens, $keywords) {
        $intersection = count(array_intersect($tokens, $keywords));
        $union = count(array_unique(array_merge($tokens, $keywords)));
        
        if ($union === 0) return 0;
        
        return $intersection / $union;
    }
}
