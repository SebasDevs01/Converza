<?php
/**
 * ✨ SISTEMA DE PREDICCIONES DIVERTIDAS
 * Genera predicciones sobre gustos e intereses de manera NO invasiva
 * Basado en patrones de comportamiento público
 */

class PrediccionesHelper {
    private $conexion;
    
    // Predicciones divertidas por categoría
    private const PREDICCIONES = [
        'musica' => [
            ['texto' => 'Probablemente eres fan de la música clásica 🎻', 'palabras' => ['beethoven', 'mozart', 'orquesta', 'sinfonía', 'violín']],
            ['texto' => 'Te gusta el rock y el metal 🎸', 'palabras' => ['rock', 'metal', 'guitarra', 'concierto', 'banda']],
            ['texto' => 'El reggaeton es tu favorito 🎵', 'palabras' => ['reggaeton', 'perreo', 'dembow', 'urbano', 'trap']],
            ['texto' => 'Eres amante del jazz y blues 🎷', 'palabras' => ['jazz', 'blues', 'saxofón', 'improvisación']],
            ['texto' => 'La música pop te encanta 🎤', 'palabras' => ['pop', 'cantar', 'karaoke', 'top']],
        ],
        'comida' => [
            ['texto' => 'Eres un foodie apasionado 🍕', 'palabras' => ['comida', 'comer', 'delicioso', 'rico', 'hambre', 'pizza', 'hamburguesa']],
            ['texto' => 'El café es tu combustible ☕', 'palabras' => ['café', 'cappuccino', 'latte', 'espresso', 'cafeína']],
            ['texto' => 'Los postres son tu debilidad 🍰', 'palabras' => ['postre', 'dulce', 'chocolate', 'pastel', 'helado']],
            ['texto' => 'Eres fan de la comida saludable 🥗', 'palabras' => ['saludable', 'fitness', 'dieta', 'vegetariano', 'vegano', 'ensalada']],
            ['texto' => 'La comida rápida te llama 🍔', 'palabras' => ['mcdonald', 'burger', 'kfc', 'rápida', 'delivery']],
        ],
        'hobbies' => [
            ['texto' => 'Los videojuegos son tu pasión 🎮', 'palabras' => ['juego', 'gaming', 'gamer', 'ps5', 'xbox', 'nintendo', 'videojuego']],
            ['texto' => 'Eres un apasionado de la lectura 📚', 'palabras' => ['libro', 'leer', 'lectura', 'novela', 'autor', 'biblioteca']],
            ['texto' => 'El arte y la creatividad te inspiran 🎨', 'palabras' => ['arte', 'pintura', 'dibujo', 'creatividad', 'diseño']],
            ['texto' => 'El deporte es tu vida 🏋️', 'palabras' => ['deporte', 'gym', 'ejercicio', 'fitness', 'entrenar', 'correr']],
            ['texto' => 'Amas la fotografía 📷', 'palabras' => ['foto', 'fotografía', 'cámara', 'imagen', 'capturar']],
        ],
        'viajes' => [
            ['texto' => 'Tienes alma de aventurero 🌍', 'palabras' => ['viajar', 'viaje', 'aventura', 'explorar', 'turismo', 'destino']],
            ['texto' => 'La playa es tu lugar favorito 🏖️', 'palabras' => ['playa', 'mar', 'arena', 'sol', 'vacaciones']],
            ['texto' => 'Prefieres la montaña 🏔️', 'palabras' => ['montaña', 'hiking', 'senderismo', 'naturaleza', 'bosque']],
            ['texto' => 'Las ciudades grandes te fascinan 🌆', 'palabras' => ['ciudad', 'urbano', 'rascacielos', 'metrópolis']],
        ],
        'personalidad' => [
            ['texto' => 'Eres una persona muy sociable 😊', 'palabras' => ['amigos', 'fiesta', 'salir', 'gente', 'social']],
            ['texto' => 'Tienes un gran sentido del humor 😂', 'palabras' => ['jaja', 'lol', 'gracioso', 'risa', 'divertido', 'chistoso']],
            ['texto' => 'Eres reflexivo y filosófico 🤔', 'palabras' => ['pensar', 'reflexión', 'filosofía', 'vida', 'sentido']],
            ['texto' => 'La tecnología te apasiona 💻', 'palabras' => ['tecnología', 'computadora', 'internet', 'app', 'software']],
            ['texto' => 'Eres amante de los animales 🐶', 'palabras' => ['perro', 'gato', 'mascota', 'animal', 'pet']],
        ],
    ];
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Genera una nueva predicción para el usuario
     */
    public function generarPrediccion($usuario_id) {
        error_log("⚙️ generarPrediccion() - Usuario: " . $usuario_id);
        
        try {
            // 1. Analizar publicaciones y comentarios del usuario
            $stmt = $this->conexion->prepare("
                SELECT p.contenido as texto FROM publicaciones p WHERE p.usuario = ? 
                UNION ALL
                SELECT c.comentario as texto FROM comentarios c WHERE c.usuario = ?
                ORDER BY RAND()
                LIMIT 50
            ");
            $stmt->execute([$usuario_id, $usuario_id]);
            $textos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            error_log("📊 Textos analizados: " . count($textos));
            
            if (empty($textos)) {
                error_log("👤 Usuario sin actividad → predicciones genéricas");
                // Usuario nuevo sin actividad → predicción genérica para cada categoría
                return $this->generarPrediccionesGenericas($usuario_id);
            }
            
            // 2. Unir todos los textos
            $textoCompleto = mb_strtolower(implode(' ', $textos));
            
            // 3. Generar UNA predicción por cada categoría
            $prediccionesGeneradas = [];
            
            foreach (self::PREDICCIONES as $categoria => $predicciones) {
                $mejorPrediccion = null;
                $mejorPuntuacion = 0;
                
                foreach ($predicciones as $prediccion) {
                    $puntuacion = 0;
                    
                    // Contar palabras clave encontradas
                    foreach ($prediccion['palabras'] as $palabra) {
                        $puntuacion += substr_count($textoCompleto, $palabra);
                    }
                    
                    if ($puntuacion > $mejorPuntuacion) {
                        $mejorPuntuacion = $puntuacion;
                        $mejorPrediccion = [
                            'categoria' => $categoria,
                            'texto' => $prediccion['texto'],
                            'confianza' => $this->calcularConfianza($puntuacion)
                        ];
                    }
                }
                
                // Si no se encontró nada, usar predicción aleatoria de la categoría
                if (!$mejorPrediccion || $mejorPuntuacion === 0) {
                    $prediccionAleatoria = $predicciones[array_rand($predicciones)];
                    $mejorPrediccion = [
                        'categoria' => $categoria,
                        'texto' => $prediccionAleatoria['texto'],
                        'confianza' => 'baja'
                    ];
                }
                
                error_log("💾 Guardando predicción [{$categoria}]: {$mejorPrediccion['texto']}");
                
                // Guardar predicción en BD
                $guardado = $this->guardarPrediccion($usuario_id, $mejorPrediccion);
                
                if ($guardado) {
                    error_log("✅ Predicción [{$categoria}] guardada");
                } else {
                    error_log("❌ Error guardando predicción [{$categoria}]");
                }
                
                $prediccionesGeneradas[] = $mejorPrediccion;
            }
            
            error_log("✅ Total predicciones generadas: " . count($prediccionesGeneradas));
            return $prediccionesGeneradas;
            
        } catch (Exception $e) {
            error_log("❌ Error generando predicción: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->generarPrediccionesGenericas($usuario_id);
        }
    }
    
    /**
     * Generar predicciones genéricas (una por categoría)
     */
    private function generarPrediccionesGenericas($usuario_id) {
        $prediccionesGenericas = [];
        
        foreach (self::PREDICCIONES as $categoria => $predicciones) {
            $prediccionAleatoria = $predicciones[array_rand($predicciones)];
            $prediccion = [
                'categoria' => $categoria,
                'texto' => $prediccionAleatoria['texto'],
                'confianza' => 'baja'
            ];
            
            $this->guardarPrediccion($usuario_id, $prediccion);
            $prediccionesGenericas[] = $prediccion;
        }
        
        return $prediccionesGenericas;
    }
    
    /**
     * Obtener predicciones no vistas del usuario
     */
    public function obtenerPredicciones($usuario_id) {
        error_log("🔍 obtenerPredicciones() - Usuario: " . $usuario_id);
        
        // Primero, verificar cuántas predicciones vistas tiene el usuario
        $stmtCount = $this->conexion->prepare("
            SELECT COUNT(*) as total FROM predicciones_usuarios 
            WHERE usuario_id = ?
        ");
        $stmtCount->execute([$usuario_id]);
        $totalPredicciones = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        error_log("📊 Total predicciones en BD: " . $totalPredicciones);
        
        // Obtener todas las predicciones no vistas
        $stmt = $this->conexion->prepare("
            SELECT * FROM predicciones_usuarios 
            WHERE usuario_id = ? AND visto = 0 
            ORDER BY FIELD(categoria, 'musica', 'comida', 'hobbies', 'viajes', 'personalidad'), fecha_generada DESC
        ");
        $stmt->execute([$usuario_id]);
        $predicciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("📊 Predicciones no vistas encontradas: " . count($predicciones));
        
        if (empty($predicciones)) {
            error_log("⚙️ Generando nuevas predicciones...");
            
            // Si ya tiene más de 5 predicciones vistas, limpiar las antiguas
            if ($totalPredicciones > 0) {
                error_log("🧹 Limpiando predicciones antiguas...");
                $stmtDelete = $this->conexion->prepare("DELETE FROM predicciones_usuarios WHERE usuario_id = ?");
                $stmtDelete->execute([$usuario_id]);
            }
            
            // No hay predicciones → generar nuevas (5 en total, una por categoría)
            $nuevasPredicciones = $this->generarPrediccion($usuario_id);
            
            error_log("✅ Predicciones generadas: " . count($nuevasPredicciones));
            
            // Convertir a formato de BD
            $stmt = $this->conexion->prepare("
                SELECT * FROM predicciones_usuarios 
                WHERE usuario_id = ? AND visto = 0 
                ORDER BY FIELD(categoria, 'musica', 'comida', 'hobbies', 'viajes', 'personalidad'), fecha_generada DESC
            ");
            $stmt->execute([$usuario_id]);
            $predicciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("📊 Predicciones obtenidas después de generar: " . count($predicciones));
        }
        
        return $predicciones;
    }
    
    /**
     * Obtener estadísticas de predicciones
     */
    public function obtenerEstadisticas($usuario_id) {
        $stmt = $this->conexion->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN visto = 0 THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN visto = 1 THEN 1 ELSE 0 END) as vistas
            FROM predicciones_usuarios 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Marcar predicción como vista
     */
    public function marcarVista($prediccion_id) {
        $stmt = $this->conexion->prepare("UPDATE predicciones_usuarios SET visto = 1 WHERE id = ?");
        return $stmt->execute([$prediccion_id]);
    }
    
    /**
     * Guardar valoración (me gusta / no me gusta)
     */
    public function valorarPrediccion($prediccion_id, $me_gusta) {
        $stmt = $this->conexion->prepare("UPDATE predicciones_usuarios SET me_gusta = ?, visto = 1 WHERE id = ?");
        return $stmt->execute([$me_gusta, $prediccion_id]);
    }
    
    /**
     * Calcular confianza según puntuación
     */
    private function calcularConfianza($puntuacion) {
        if ($puntuacion >= 5) return 'alta';
        if ($puntuacion >= 2) return 'media';
        return 'baja';
    }
    
    /**
     * Guardar predicción en BD
     */
    private function guardarPrediccion($usuario_id, $prediccion) {
        $emoji = $this->obtenerEmoji($prediccion['categoria']);
        
        $stmt = $this->conexion->prepare("
            INSERT INTO predicciones_usuarios (usuario_id, categoria, prediccion, emoji, confianza)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $usuario_id,
            $prediccion['categoria'],
            $prediccion['texto'],
            $emoji,
            $prediccion['confianza']
        ]);
    }
    
    /**
     * Obtener emoji representativo
     */
    private function obtenerEmoji($categoria) {
        $emojis = [
            'musica' => '🎵',
            'comida' => '🍽️',
            'hobbies' => '🎯',
            'viajes' => '✈️',
            'personalidad' => '✨'
        ];
        return $emojis[$categoria] ?? '🔮';
    }
    
    /**
     * Predicción genérica para usuarios nuevos
     */
    private function prediccionGenerica() {
        return [
            'categoria' => 'personalidad',
            'texto' => 'Tienes un gran potencial por descubrir ✨',
            'confianza' => 'media',
            'emoji' => '🔮'
        ];
    }
    
    /**
     * Predicción aleatoria si no se detecta nada
     */
    private function prediccionAleatoria() {
        $categorias = array_keys(self::PREDICCIONES);
        $categoriaAleatoria = $categorias[array_rand($categorias)];
        $prediccionesCategoria = self::PREDICCIONES[$categoriaAleatoria];
        $prediccionAleatoria = $prediccionesCategoria[array_rand($prediccionesCategoria)];
        
        return [
            'categoria' => $categoriaAleatoria,
            'texto' => $prediccionAleatoria['texto'],
            'confianza' => 'baja',
            'emoji' => $this->obtenerEmoji($categoriaAleatoria)
        ];
    }
}
?>
