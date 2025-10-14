<?php
/**
 * Triggers automáticos para registrar Karma Social
 * Se deben llamar cuando ocurren las acciones correspondientes
 */

require_once __DIR__ . '/karma-social-helper.php';

class KarmaSocialTriggers {
    private $karmaHelper;
    
    public function __construct($conexion) {
        $this->karmaHelper = new KarmaSocialHelper($conexion);
    }
    
    /**
     * COMENTARIOS
     */
    public function nuevoComentario($usuario_id, $comentario_id, $texto_comentario) {
        // Analizar si el comentario es positivo
        return $this->karmaHelper->analizarComentario($usuario_id, $comentario_id, $texto_comentario);
    }
    
    /**
     * REACCIONES
     */
    public function nuevaReaccion($usuario_id, $publicacion_id, $tipo_reaccion) {
        // Registrar karma por reacción positiva
        return $this->karmaHelper->registrarReaccionPositiva($usuario_id, $publicacion_id, $tipo_reaccion);
    }
    
    /**
     * AMISTAD ACEPTADA
     */
    public function amistadAceptada($usuario_id, $amigo_id) {
        // Primera interacción con este usuario
        return $this->karmaHelper->registrarAccion(
            $usuario_id,
            'primera_interaccion',
            $amigo_id,
            'amistad',
            'Nueva amistad establecida'
        );
    }
    
    /**
     * MENSAJE ENVIADO
     */
    public function mensajeEnviado($usuario_id, $destinatario_id, $texto_mensaje) {
        // Analizar si es un mensaje motivador
        $palabras_motivadoras = ['ánimo', 'fuerza', 'puedes', 'confío', 'apoyo', 'ayuda'];
        $texto_lower = mb_strtolower($texto_mensaje);
        
        foreach ($palabras_motivadoras as $palabra) {
            if (strpos($texto_lower, $palabra) !== false) {
                return $this->karmaHelper->registrarAccion(
                    $usuario_id,
                    'mensaje_motivador',
                    $destinatario_id,
                    'mensaje',
                    'Mensaje de apoyo enviado'
                );
            }
        }
        
        return false;
    }
    
    /**
     * COMPARTIR CONOCIMIENTO
     */
    public function comentarioEducativo($usuario_id, $comentario_id, $texto_comentario) {
        // Detectar si comparte conocimiento (comentarios largos con información)
        $longitud = mb_strlen($texto_comentario);
        $palabras_educativas = ['aprende', 'tutorial', 'guía', 'explicación', 'cómo', 'paso', 'método'];
        $texto_lower = mb_strtolower($texto_comentario);
        
        // Si es largo y tiene palabras educativas
        if ($longitud > 100) {
            foreach ($palabras_educativas as $palabra) {
                if (strpos($texto_lower, $palabra) !== false) {
                    return $this->karmaHelper->registrarAccion(
                        $usuario_id,
                        'compartir_conocimiento',
                        $comentario_id,
                        'comentario',
                        'Comentario educativo compartido'
                    );
                }
            }
        }
        
        return false;
    }
}
?>
