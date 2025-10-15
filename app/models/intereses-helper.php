<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * 🎯 INTERESES HELPER
 * Sistema inteligente de análisis y matching de intereses
 * Integra: Predicciones + Conexiones Místicas + Daily Shuffle
 * ═══════════════════════════════════════════════════════════════
 */

class InteresesHelper {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtener intereses confirmados del usuario (predicciones con me_gusta = 1)
     * @return array ['musica' => true, 'comida' => false, ...]
     */
    public function obtenerInteresesConfirmados($usuario_id) {
        $stmt = $this->conexion->prepare("
            SELECT categoria, me_gusta 
            FROM predicciones_usuarios 
            WHERE usuario_id = ? AND visto = 1 AND me_gusta IS NOT NULL
            ORDER BY fecha_generada DESC
        ");
        $stmt->execute([$usuario_id]);
        $predicciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $intereses = [
            'musica' => null,
            'comida' => null,
            'hobbies' => null,
            'viajes' => null,
            'personalidad' => null
        ];
        
        foreach ($predicciones as $pred) {
            // Solo guardar el más reciente por categoría
            if ($intereses[$pred['categoria']] === null) {
                $intereses[$pred['categoria']] = (bool)$pred['me_gusta'];
            }
        }
        
        return $intereses;
    }
    
    /**
     * Calcular compatibilidad entre dos usuarios (0-100%)
     */
    public function calcularCompatibilidad($usuario1_id, $usuario2_id) {
        $intereses1 = $this->obtenerInteresesConfirmados($usuario1_id);
        $intereses2 = $this->obtenerInteresesConfirmados($usuario2_id);
        
        $coincidencias = 0;
        $comparaciones = 0;
        
        foreach ($intereses1 as $categoria => $valor1) {
            $valor2 = $intereses2[$categoria];
            
            // Solo comparar si ambos tienen valoración en esta categoría
            if ($valor1 !== null && $valor2 !== null) {
                $comparaciones++;
                if ($valor1 === $valor2 && $valor1 === true) {
                    // Ambos dieron "me gusta" a la misma categoría
                    $coincidencias++;
                }
            }
        }
        
        if ($comparaciones === 0) return 0;
        
        return round(($coincidencias / $comparaciones) * 100);
    }
    
    /**
     * Obtener usuarios con intereses similares
     * @param int $limite Cantidad máxima de usuarios a retornar
     * @return array Usuarios ordenados por compatibilidad
     */
    public function obtenerUsuariosSimilares($usuario_id, $limite = 10) {
        // 1. Obtener todos los usuarios que han completado predicciones
        $stmt = $this->conexion->prepare("
            SELECT DISTINCT u.id_use, u.usuario, u.nombre, u.foto_perfil
            FROM usuarios u
            INNER JOIN predicciones_usuarios p ON u.id_use = p.usuario_id
            WHERE u.id_use != ? 
                AND u.tipo != 'blocked'
                AND p.visto = 1 
                AND p.me_gusta IS NOT NULL
            GROUP BY u.id_use
            HAVING COUNT(DISTINCT p.categoria) >= 2
        ");
        $stmt->execute([$usuario_id]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Calcular compatibilidad con cada usuario
        $usuariosConScore = [];
        foreach ($usuarios as $usuario) {
            $compatibilidad = $this->calcularCompatibilidad($usuario_id, $usuario['id_use']);
            
            if ($compatibilidad > 0) {
                $usuario['compatibilidad'] = $compatibilidad;
                $usuario['intereses_comunes'] = $this->obtenerInteresesComunes($usuario_id, $usuario['id_use']);
                $usuariosConScore[] = $usuario;
            }
        }
        
        // 3. Ordenar por compatibilidad descendente
        usort($usuariosConScore, function($a, $b) {
            return $b['compatibilidad'] - $a['compatibilidad'];
        });
        
        return array_slice($usuariosConScore, 0, $limite);
    }
    
    /**
     * Obtener categorías de interés en común entre dos usuarios
     */
    public function obtenerInteresesComunes($usuario1_id, $usuario2_id) {
        $intereses1 = $this->obtenerInteresesConfirmados($usuario1_id);
        $intereses2 = $this->obtenerInteresesConfirmados($usuario2_id);
        
        $comunes = [];
        $emojis = [
            'musica' => '🎵',
            'comida' => '🍽️',
            'hobbies' => '🎯',
            'viajes' => '✈️',
            'personalidad' => '✨'
        ];
        
        foreach ($intereses1 as $categoria => $valor1) {
            $valor2 = $intereses2[$categoria];
            
            if ($valor1 === true && $valor2 === true) {
                $comunes[] = [
                    'categoria' => $categoria,
                    'emoji' => $emojis[$categoria],
                    'nombre' => ucfirst($categoria)
                ];
            }
        }
        
        return $comunes;
    }
    
    /**
     * Mejorar Daily Shuffle con intereses
     * Prioriza usuarios con al menos 1 interés en común
     */
    public function mejorarDailyShuffle($usuario_id, $candidatos) {
        $candidatosMejorados = [];
        
        foreach ($candidatos as $candidato) {
            $compatibilidad = $this->calcularCompatibilidad($usuario_id, $candidato['id_use']);
            $candidato['compatibilidad'] = $compatibilidad;
            $candidato['tiene_intereses_comunes'] = $compatibilidad > 0;
            $candidatosMejorados[] = $candidato;
        }
        
        // Ordenar: primero con intereses comunes, luego aleatorio
        usort($candidatosMejorados, function($a, $b) {
            if ($a['tiene_intereses_comunes'] && !$b['tiene_intereses_comunes']) {
                return -1;
            }
            if (!$a['tiene_intereses_comunes'] && $b['tiene_intereses_comunes']) {
                return 1;
            }
            return 0; // Mantener orden aleatorio entre iguales
        });
        
        return $candidatosMejorados;
    }
    
    /**
     * Mejorar Conexiones Místicas con intereses
     * Añade peso extra por compatibilidad de intereses
     */
    public function mejorarConexionesMisticas($usuario_id, $conexiones) {
        $conexionesMejoradas = [];
        
        foreach ($conexiones as $conexion) {
            $compatibilidad = $this->calcularCompatibilidad($usuario_id, $conexion['usuario_id']);
            
            // Bonus de compatibilidad (0-20 puntos extra)
            $bonus = round($compatibilidad / 5);
            $conexion['score_original'] = $conexion['score'];
            $conexion['score'] += $bonus;
            $conexion['compatibilidad'] = $compatibilidad;
            $conexion['intereses_comunes'] = $this->obtenerInteresesComunes($usuario_id, $conexion['usuario_id']);
            
            $conexionesMejoradas[] = $conexion;
        }
        
        // Re-ordenar por nuevo score
        usort($conexionesMejoradas, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $conexionesMejoradas;
    }
    
    /**
     * Obtener resumen de intereses del usuario para mostrar en perfil
     */
    public function obtenerResumenIntereses($usuario_id) {
        $intereses = $this->obtenerInteresesConfirmados($usuario_id);
        
        $confirmados = [];
        $rechazados = [];
        
        $nombres = [
            'musica' => 'Música',
            'comida' => 'Comida',
            'hobbies' => 'Hobbies',
            'viajes' => 'Viajes',
            'personalidad' => 'Personalidad'
        ];
        
        $emojis = [
            'musica' => '🎵',
            'comida' => '🍽️',
            'hobbies' => '🎯',
            'viajes' => '✈️',
            'personalidad' => '✨'
        ];
        
        foreach ($intereses as $categoria => $valor) {
            if ($valor === true) {
                $confirmados[] = [
                    'categoria' => $categoria,
                    'nombre' => $nombres[$categoria],
                    'emoji' => $emojis[$categoria]
                ];
            } elseif ($valor === false) {
                $rechazados[] = [
                    'categoria' => $categoria,
                    'nombre' => $nombres[$categoria],
                    'emoji' => $emojis[$categoria]
                ];
            }
        }
        
        return [
            'confirmados' => $confirmados,
            'rechazados' => $rechazados,
            'total_evaluados' => count(array_filter($intereses, fn($v) => $v !== null)),
            'porcentaje_completado' => round((count(array_filter($intereses, fn($v) => $v !== null)) / 5) * 100)
        ];
    }
}
?>
