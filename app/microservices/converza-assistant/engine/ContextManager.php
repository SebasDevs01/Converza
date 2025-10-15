<?php
/**
 * 🤖 CONTEXT MANAGER - Gestor de Contexto del Usuario
 * Obtiene información relevante del usuario para personalizar respuestas
 */

class ContextManager {
    
    /**
     * Obtener contexto del usuario actual
     * @param int $userId ID del usuario
     * @return array Contexto con karma, nivel, nombre, etc.
     */
    public function getUserContext($userId) {
        error_log("🔍 ContextManager: getUserContext llamado con userId = " . var_export($userId, true));
        error_log("🔍 ContextManager: Tipo de userId: " . gettype($userId));
        
        if (!$userId || $userId <= 0) {
            error_log("⚠️ ContextManager: userId inválido o 0, retornando contexto invitado");
            return $this->getGuestContext();
        }
        
        error_log("✅ ContextManager: userId válido ($userId), buscando en BD...");
        
        try {
            // IMPORTANTE: Declarar global ANTES de require_once
            global $conexion;
            
            // Verificar que existen los archivos necesarios
            $configPath = __DIR__.'/../../../models/config.php';
            $karmaHelperPath = __DIR__.'/../../../models/karma-social-helper.php';
            
            error_log("📂 ContextManager: Verificando archivos...");
            error_log("   config.php: " . (file_exists($configPath) ? 'EXISTS' : 'NOT FOUND'));
            error_log("   karma-helper: " . (file_exists($karmaHelperPath) ? 'EXISTS' : 'NOT FOUND'));
            
            if (!file_exists($configPath)) {
                error_log("⚠️ Context Manager: No se encuentra config.php en: " . $configPath);
                return $this->getGuestContext();
            }
            
            if (!file_exists($karmaHelperPath)) {
                error_log("⚠️ Context Manager: No se encuentra karma-social-helper.php en: " . $karmaHelperPath);
                return $this->getGuestContext();
            }
            
            require_once($configPath);
            require_once($karmaHelperPath);
            
            error_log("✅ ContextManager: Archivos cargados");
            
            error_log("🔍 ContextManager: Variable \$conexion = " . var_export(isset($conexion), true));
            
            // Verificar que existe la conexión
            if (!isset($conexion) || !$conexion) {
                error_log("⚠️ Context Manager: Variable \$conexion no existe o está vacía");
                return $this->getGuestContext();
            }
            
            error_log("✅ ContextManager: Conexión BD obtenida, ejecutando query...");
            
            // Obtener datos del usuario - NOTA: El campo se llama 'avatar' en la BD, no 'foto_perfil'
            $stmt = $conexion->prepare("SELECT usuario, email, avatar FROM usuarios WHERE id_use = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("✅ ContextManager: Query ejecutada, resultado: " . json_encode($user));
            
            if (!$user) {
                error_log("⚠️ Context Manager: Usuario no encontrado con ID: " . $userId);
                return $this->getGuestContext();
            }
            
            // Determinar foto de perfil - BUSCAR EN AVATARS PRIMERO
            $fotoPerfil = '/Converza/public/avatars/defect.jpg';
            if (!empty($user['avatar']) && $user['avatar'] !== 'defect.jpg') {
                $avatar = $user['avatar'];
                
                // Rutas absolutas correctas desde ContextManager.php
                // ContextManager está en: app/microservices/converza-assistant/engine/
                // public está en: public/ (raíz del proyecto)
                $projectRoot = __DIR__ . '/../../../../';  // Subir 4 niveles
                $avatarPath = $projectRoot . 'public/avatars/' . $avatar;
                $uploadsPath = $projectRoot . 'public/uploads/' . $avatar;
                
                error_log("🔍 ContextManager: Buscando avatar '$avatar'");
                error_log("   Project root: " . realpath($projectRoot));
                error_log("   Ruta avatars: $avatarPath");
                error_log("   Existe: " . (file_exists($avatarPath) ? 'SI' : 'NO'));
                error_log("   Ruta uploads: $uploadsPath");
                error_log("   Existe: " . (file_exists($uploadsPath) ? 'SI' : 'NO'));
                
                if (file_exists($avatarPath)) {
                    // Está en avatars
                    $fotoPerfil = '/Converza/public/avatars/' . $avatar;
                    error_log("✅ ContextManager: Usando avatars - " . $fotoPerfil);
                } elseif (file_exists($uploadsPath)) {
                    // Está en uploads
                    $fotoPerfil = '/Converza/public/uploads/' . $avatar;
                    error_log("✅ ContextManager: Usando uploads - " . $fotoPerfil);
                } elseif (strpos($avatar, 'public/avatars/') === 0 || strpos($avatar, 'public/uploads/') === 0) {
                    // Ya tiene la ruta relativa completa
                    $fotoPerfil = '/Converza/' . $avatar;
                    error_log("✅ ContextManager: Usando ruta completa - " . $fotoPerfil);
                } else {
                    // Si no se encuentra, usar por defecto
                    error_log("⚠️ ContextManager: Avatar no encontrado, usando defect.jpg");
                }
            }
            
            error_log("✅ ContextManager: Foto determinada: $fotoPerfil");
            
            // Obtener karma
            $karmaHelper = new KarmaSocialHelper($conexion);
            $karmaData = $karmaHelper->obtenerKarmaUsuario($userId);
            
            error_log("✅ Context Manager: Usuario cargado - " . $user['usuario'] . " (ID: $userId) - Foto: " . $fotoPerfil);
            
            return [
                'user_id' => $userId,
                'username' => $user['usuario'],
                'email' => $user['email'],
                'foto_perfil' => $fotoPerfil,
                'karma' => $karmaData['karma_total'] ?? 0,
                'nivel' => $karmaData['nivel_data']['nivel'] ?? 1,
                'nivel_titulo' => $karmaData['nivel_data']['titulo'] ?? 'Novato',
                'nivel_emoji' => $karmaData['nivel_emoji'] ?? '🌱',
                'puntos_siguiente_nivel' => $this->calculateNextLevelPoints($karmaData['nivel_data']['nivel'] ?? 1),
                'puntos_faltantes' => max(0, $this->calculateNextLevelPoints($karmaData['nivel_data']['nivel'] ?? 1) - ($karmaData['karma_total'] ?? 0))
            ];
            
        } catch (Exception $e) {
            error_log("❌ Context Manager Error: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            return $this->getGuestContext();
        }
    }
    
    /**
     * Contexto para usuarios invitados (sin sesión)
     */
    private function getGuestContext() {
        return [
            'user_id' => 0,
            'username' => 'Invitado',
            'email' => null,
            'foto_perfil' => '/Converza/public/avatars/defect.jpg',
            'karma' => 0,
            'nivel' => 1,
            'nivel_titulo' => 'Novato',
            'nivel_emoji' => '🌱',
            'puntos_siguiente_nivel' => 50,
            'puntos_faltantes' => 50
        ];
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
}
