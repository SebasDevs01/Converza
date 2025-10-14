<?php
/**
 * Helper para Aplicar Recompensas Equipadas Visualmente
 * Maneja la aplicación de marcos, temas, insignias en el perfil y sistema
 */

class RecompensasAplicarHelper {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtener todas las recompensas equipadas de un usuario
     */
    public function obtenerEquipadas($usuario_id) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT kr.* 
                FROM usuario_recompensas ur
                JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
                WHERE ur.usuario_id = ? AND ur.equipada = 1
            ");
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener recompensas equipadas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener clase CSS del marco equipado
     */
    public function getMarcoClase($usuario_id) {
        $equipadas = $this->obtenerEquipadas($usuario_id);
        foreach ($equipadas as $rec) {
            if ($rec['tipo'] == 'marco') {
                return $this->getClaseCSS($rec['nombre']);
            }
        }
        return '';
    }
    
    /**
     * Obtener clase CSS del tema equipado (NUEVA - Para <body>)
     */
    public function getTemaClaseBody($usuario_id) {
        $equipadas = $this->obtenerEquipadas($usuario_id);
        foreach ($equipadas as $rec) {
            if ($rec['tipo'] == 'tema') {
                return $this->mapearTemaAClase($rec['nombre']);
            }
        }
        return 'tema-default'; // Tema por defecto de Converza
    }
    
    /**
     * Mapear nombre de tema a clase CSS del body
     */
    private function mapearTemaAClase($nombreTema) {
        $mapeo = [
            'Tema Oscuro Premium' => 'tema-oscuro',
            'Tema Galaxy' => 'tema-galaxy',
            'Tema Sunset' => 'tema-sunset',
            'Tema Neon' => 'tema-neon',
            'Tema Nocturno' => 'tema-oscuro',
            'Tema Bosque' => 'tema-default',
            'Tema Océano' => 'tema-default',
            'Tema Atardecer' => 'tema-sunset',
            'Tema Aurora' => 'tema-galaxy',
            'Tema Galáctico' => 'tema-galaxy',
            'Tema Cyberpunk' => 'tema-neon',
            'Tema Místico' => 'tema-galaxy',
            'Tema Real' => 'tema-default',
        ];
        
        return $mapeo[$nombreTema] ?? 'tema-default';
    }
    
    /**
     * Obtener CSS del tema equipado
     */
    public function getTemaCSS($usuario_id) {
        $equipadas = $this->obtenerEquipadas($usuario_id);
        foreach ($equipadas as $rec) {
            if ($rec['tipo'] == 'tema') {
                return $this->getCSSPorTema($rec['nombre']);
            }
        }
        return '';
    }
    
    /**
     * Obtener insignias equipadas
     */
    public function getInsignias($usuario_id) {
        $equipadas = $this->obtenerEquipadas($usuario_id);
        $insignias = [];
        foreach ($equipadas as $rec) {
            if ($rec['tipo'] == 'insignia') {
                $insignias[] = $rec;
            }
        }
        return $insignias;
    }
    
    /**
     * Obtener HTML de insignias para mostrar
     */
    public function renderInsignias($usuario_id) {
        $insignias = $this->getInsignias($usuario_id);
        if (empty($insignias)) {
            return '';
        }
        
        $html = '<div class="insignias-karma-container mt-2">';
        foreach ($insignias as $insignia) {
            $emoji = $this->getEmojiInsignia($insignia['nombre']);
            $html .= '<span class="insignia-karma-badge" title="'.htmlspecialchars($insignia['descripcion']).'">';
            $html .= $emoji . ' ' . htmlspecialchars($insignia['nombre']);
            $html .= '</span>';
        }
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Mapeo de nombres de marcos a clases CSS
     */
    private function getClaseCSS($nombre) {
        $mapeo = [
            'Marco Dorado' => 'marco-dorado',
            'Marco Diamante' => 'marco-diamante',
            'Marco de Fuego' => 'marco-fuego',
            'Marco Arcoíris' => 'marco-arcoiris',
            'Marco Arcoiris' => 'marco-arcoiris',  // Sin tilde también
            'Marco Legendario' => 'marco-legendario',
            'Marco Halloween' => 'marco-halloween'
        ];
        return $mapeo[$nombre] ?? '';
    }
    
    /**
     * Mapeo de nombres de insignias a emojis
     */
    private function getEmojiInsignia($nombre) {
        $mapeo = [
            'Insignia Novato' => '🌱',
            'Insignia Intermedio' => '⭐',
            'Insignia Avanzado' => '✨',
            'Insignia Experto' => '💫',
            'Insignia Maestro' => '🌟',
            'Insignia Legendario' => '👑'
        ];
        return $mapeo[$nombre] ?? '🏅';
    }
    
    /**
     * CSS personalizado según el tema equipado
     */
    private function getCSSPorTema($nombre) {
        switch ($nombre) {
            case 'Tema Oscuro Premium':
                return "
                    body {
                        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
                        color: #e0e0e0;
                    }
                    .card, .list-group-item {
                        background: rgba(255,255,255,0.05) !important;
                        border: 1px solid rgba(255,255,255,0.1) !important;
                        color: #e0e0e0 !important;
                    }
                    .navbar {
                        background: linear-gradient(135deg, #0f3460, #16213e) !important;
                    }
                ";
                
            case 'Tema Galaxy':
                return "
                    body {
                        background: #0a0e27 url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cmFkaWFsR3JhZGllbnQgaWQ9ImEiPjxzdG9wIG9mZnNldD0iMCIgc3RvcC1jb2xvcj0iI2ZmZiIgc3RvcC1vcGFjaXR5PSIuOCIvPjxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iI2ZmZiIgc3RvcC1vcGFjaXR5PSIwIi8+PC9yYWRpYWxHcmFkaWVudD48L2RlZnM+PGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iMiIgZmlsbD0idXJsKCNhKSIvPjxjaXJjbGUgY3g9IjE1MCIgY3k9IjgwIiByPSIxIiBmaWxsPSJ1cmwoI2EpIi8+PGNpcmNsZSBjeD0iODAiIGN5PSIxNTAiIHI9IjEuNSIgZmlsbD0idXJsKCNhKSIvPjwvc3ZnPg==') !important;
                        animation: galaxy-stars 120s linear infinite;
                    }
                    @keyframes galaxy-stars {
                        0% { background-position: 0 0; }
                        100% { background-position: -200px -200px; }
                    }
                    .card {
                        backdrop-filter: blur(10px);
                        background: rgba(10, 14, 39, 0.8) !important;
                        border: 1px solid rgba(100, 100, 255, 0.3) !important;
                    }
                ";
                
            case 'Tema Sunset':
                return "
                    body {
                        background: linear-gradient(135deg, #ff6b6b 0%, #feca57 50%, #ff9ff3 100%) !important;
                    }
                    .card {
                        background: rgba(255,255,255,0.9) !important;
                        border: 2px solid rgba(255,107,107,0.3) !important;
                        box-shadow: 0 8px 32px rgba(255,107,107,0.2);
                    }
                    .navbar {
                        background: linear-gradient(135deg, #ff6b6b, #feca57) !important;
                    }
                ";
                
            case 'Tema Neon':
                return "
                    body {
                        background: #0a0e27 !important;
                        color: #00ffff;
                    }
                    .card {
                        background: rgba(10,14,39,0.95) !important;
                        border: 2px solid #00ffff !important;
                        box-shadow: 0 0 20px #00ffff, inset 0 0 20px rgba(0,255,255,0.1);
                    }
                    .btn-primary {
                        background: linear-gradient(135deg, #00ffff, #ff00ff) !important;
                        border: none !important;
                        box-shadow: 0 0 20px rgba(0,255,255,0.5);
                    }
                    .navbar {
                        background: #0a0e27 !important;
                        border-bottom: 2px solid #00ffff;
                        box-shadow: 0 0 20px #00ffff;
                    }
                    h1, h2, h3, h4, h5, h6 {
                        color: #00ffff;
                        text-shadow: 0 0 10px #00ffff;
                    }
                ";
                
            default:
                return '';
        }
    }
    
    /**
     * Renderizar avatar con marco aplicado
     */
    public function renderAvatar($usuario_id, $avatarPath, $width = 60, $height = 60, $extraClasses = '') {
        $marcoClase = $this->getMarcoClase($usuario_id);
        
        // ✅ Detectar si es avatar grande de perfil (120x120)
        $isPerfilAvatar = ($width >= 120 && $height >= 120);
        $containerClass = $isPerfilAvatar ? 'avatar-karma-perfil-principal' : '';
        
        $html = '<div class="avatar-karma-container ' . $marcoClase . ' ' . $containerClass . '">';
        $html .= '<img src="' . htmlspecialchars($avatarPath) . '" ';
        $html .= 'class="avatar-karma-img ' . $extraClasses . '" ';
        $html .= 'width="' . $width . '" height="' . $height . '" ';
        $html .= 'alt="Avatar" loading="lazy">';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obtener ícono especial equipado
     */
    public function getIconoEspecial($usuario_id) {
        $equipadas = $this->obtenerEquipadas($usuario_id);
        foreach ($equipadas as $rec) {
            if ($rec['tipo'] == 'icono') {
                return $this->mapearIcono($rec['nombre']);
            }
        }
        return '';
    }
    
    /**
     * Mapear nombre de ícono a HTML
     */
    private function mapearIcono($nombre) {
        $iconos = [
            'Ícono Estrella' => '<span class="icono-especial icono-estrella">⭐</span>',
            'Ícono Corona' => '<span class="icono-especial icono-corona">👑</span>',
            'Ícono Fuego' => '<span class="icono-especial icono-fuego">🔥</span>',
            'Ícono Corazón' => '<span class="icono-especial icono-corazon">💖</span>',
            'Ícono Rayo' => '<span class="icono-especial icono-rayo">⚡</span>',
            'Ícono Diamante' => '<span class="icono-especial icono-diamante">💎</span>',
        ];
        
        foreach ($iconos as $key => $html) {
            if (stripos($nombre, $key) !== false) {
                return $html;
            }
        }
        return '';
    }
    
    /**
     * Obtener clase CSS del color de nombre equipado
     */
    public function getColorNombreClase($usuario_id) {
        $equipadas = $this->obtenerEquipadas($usuario_id);
        foreach ($equipadas as $rec) {
            if ($rec['tipo'] == 'color_nombre' || $rec['tipo'] == 'color') {
                return $this->mapearColorNombre($rec['nombre']);
            }
        }
        return '';
    }
    
    /**
     * Mapear nombre de color a clase CSS
     */
    private function mapearColorNombre($nombre) {
        $colores = [
            'Nombre Dorado' => 'nombre-dorado',
            'Nombre Arcoíris' => 'nombre-arcoiris',
            'Nombre Fuego' => 'nombre-fuego',
            'Nombre Océano' => 'nombre-oceano',
            'Nombre Neon Cyan' => 'nombre-neon-cyan',
            'Nombre Neon Rosa' => 'nombre-neon-rosa',
            'Nombre Galaxia' => 'nombre-galaxia',
            // 4 NUEVOS COLORES
            'Púrpura Real' => 'nombre-purpura-real',
            'Rosa Neón' => 'nombre-rosa-neon',
            'Esmeralda' => 'nombre-esmeralda',
            'Oro Premium' => 'nombre-oro-premium',
        ];
        
        foreach ($colores as $key => $clase) {
            if (stripos($nombre, $key) !== false) {
                return $clase;
            }
        }
        return '';
    }
    
    /**
     * Renderizar nombre de usuario con color e ícono
     */
    public function renderNombreUsuario($usuario_id, $nombreUsuario) {
        $colorClase = $this->getColorNombreClase($usuario_id);
        $icono = $this->getIconoEspecial($usuario_id);
        
        $html = '<span class="nombre-usuario ' . $colorClase . '">';
        $html .= htmlspecialchars($nombreUsuario);
        $html .= '</span>';
        $html .= $icono;
        
        return $html;
    }
    
    /**
     * Obtener stickers equipados
     */
    public function getStickersEquipados($usuario_id) {
        $equipadas = $this->obtenerEquipadas($usuario_id);
        $stickers = [];
        
        foreach ($equipadas as $rec) {
            if ($rec['tipo'] == 'sticker') {
                $stickers = array_merge($stickers, $this->extraerStickers($rec['nombre']));
            }
        }
        
        return $stickers;
    }
    
    /**
     * Extraer lista de stickers de la descripción del pack
     */
    private function extraerStickers($nombrePack) {
        $packs = [
            'Pack Básico' => [
                ['emoji' => '😊', 'nombre' => 'Feliz', 'clase' => 'sticker-feliz'],
                ['emoji' => '😢', 'nombre' => 'Triste', 'clase' => 'sticker-triste'],
                ['emoji' => '🤩', 'nombre' => 'Emocionado', 'clase' => 'sticker-emocionado'],
            ],
            'Pack Premium' => [
                ['emoji' => '😌', 'nombre' => 'Relajado', 'clase' => 'sticker-relajado'],
                ['emoji' => '💪', 'nombre' => 'Motivado', 'clase' => 'sticker-motivado'],
                ['emoji' => '🎨', 'nombre' => 'Creativo', 'clase' => 'sticker-creativo'],
            ],
            'Pack Elite' => [
                ['emoji' => '🤔', 'nombre' => 'Pensativo', 'clase' => 'sticker-pensativo'],
                ['emoji' => '⚡', 'nombre' => 'Energético', 'clase' => 'sticker-energetico'],
                ['emoji' => '🔥', 'nombre' => 'Legendario', 'clase' => 'sticker-motivado'],
            ],
        ];
        
        foreach ($packs as $key => $stickers) {
            if (stripos($nombrePack, $key) !== false) {
                return $stickers;
            }
        }
        
        return [];
    }
    
    /**
     * Renderizar stickers en perfil
     */
    public function renderStickers($usuario_id) {
        $stickers = $this->getStickersEquipados($usuario_id);
        
        if (empty($stickers)) {
            return '';
        }
        
        $html = '<div class="stickers-section">';
        $html .= '<h5 class="stickers-titulo">Estados de Ánimo 😊</h5>';
        $html .= '<div class="stickers-container">';
        
        foreach ($stickers as $sticker) {
            $html .= '<div class="sticker-item ' . $sticker['clase'] . '">';
            $html .= '<span class="sticker-emoji">' . $sticker['emoji'] . '</span>';
            $html .= '<span class="sticker-nombre">' . $sticker['nombre'] . '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
