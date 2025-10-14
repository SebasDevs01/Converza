-- Tabla para Personalización de Perfil
-- Almacena bio, signo zodiacal, género, estado de ánimo y otras personalizaciones

CREATE TABLE IF NOT EXISTS personalizacion_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    
    -- Bio y descripción
    bio TEXT NULL,
    descripcion_corta VARCHAR(255) NULL,
    
    -- Signo zodiacal
    signo_zodiacal ENUM(
        'aries', 'tauro', 'geminis', 'cancer', 
        'leo', 'virgo', 'libra', 'escorpio',
        'sagitario', 'capricornio', 'acuario', 'piscis'
    ) NULL,
    
    -- Género con icono
    genero ENUM('masculino', 'femenino', 'otro', 'prefiero_no_decir') NULL,
    mostrar_icono_genero BOOLEAN DEFAULT TRUE,
    
    -- Estado de ánimo actual
    estado_animo ENUM(
        'feliz', 'emocionado', 'relajado', 'creativo',
        'cansado', 'ocupado', 'triste', 'enojado',
        'motivado', 'inspirado', 'pensativo', 'nostalgico'
    ) NULL,
    estado_animo_personalizado VARCHAR(50) NULL,
    
    -- Personalizaciones desbloqueadas por karma
    tema_perfil VARCHAR(50) DEFAULT 'default',
    color_principal VARCHAR(7) DEFAULT '#667eea',
    icono_personalizado VARCHAR(100) NULL,
    marco_avatar VARCHAR(100) NULL,
    insignia_especial VARCHAR(100) NULL,
    
    -- Preferencias de privacidad
    mostrar_karma BOOLEAN DEFAULT TRUE,
    mostrar_signo BOOLEAN DEFAULT TRUE,
    mostrar_estado_animo BOOLEAN DEFAULT TRUE,
    
    -- Metadata
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Claves foráneas
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    
    -- Índices
    UNIQUE KEY idx_usuario (usuario_id),
    INDEX idx_signo (signo_zodiacal),
    INDEX idx_genero (genero)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para Recompensas Desbloqueables
CREATE TABLE IF NOT EXISTS karma_recompensas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo ENUM('tema', 'marco', 'insignia', 'icono', 'color', 'sticker') NOT NULL,
    karma_requerido INT NOT NULL,
    imagen_preview VARCHAR(255) NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo),
    INDEX idx_karma (karma_requerido)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para Recompensas Desbloqueadas por Usuario
CREATE TABLE IF NOT EXISTS usuario_recompensas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    recompensa_id INT NOT NULL,
    fecha_desbloqueo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (recompensa_id) REFERENCES karma_recompensas(id) ON DELETE CASCADE,
    
    UNIQUE KEY idx_usuario_recompensa (usuario_id, recompensa_id),
    INDEX idx_activa (activa)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar recompensas predefinidas
INSERT INTO karma_recompensas (nombre, descripcion, tipo, karma_requerido) VALUES
-- Temas
('Tema Oscuro Premium', 'Tema oscuro elegante con gradientes morados', 'tema', 50),
('Tema Galaxy', 'Tema espacial con estrellas animadas', 'tema', 100),
('Tema Sunset', 'Tema con colores cálidos del atardecer', 'tema', 150),
('Tema Neon', 'Tema vibrante con colores neón', 'tema', 200),

-- Marcos de avatar
('Marco Básico Dorado', 'Marco dorado simple para tu avatar', 'marco', 30),
('Marco Diamante', 'Marco de diamante brillante', 'marco', 100),
('Marco Fuego', 'Marco animado con llamas', 'marco', 150),
('Marco Arcoíris', 'Marco con colores del arcoíris', 'marco', 200),
('Marco Legendario', 'Marco exclusivo con animación premium', 'marco', 500),

-- Insignias
('Insignia Novato', 'Primera insignia de karma', 'insignia', 10),
('Insignia Intermedio', 'Insignia por alcanzar 50 de karma', 'insignia', 50),
('Insignia Avanzado', 'Insignia por alcanzar 100 de karma', 'insignia', 100),
('Insignia Experto', 'Insignia por alcanzar 250 de karma', 'insignia', 250),
('Insignia Maestro', 'Insignia por alcanzar 500 de karma', 'insignia', 500),
('Insignia Legendario', 'Insignia máxima por 1000 de karma', 'insignia', 1000),

-- Íconos especiales
('Ícono Estrella Dorada', 'Estrella dorada junto a tu nombre', 'icono', 75),
('Ícono Corona', 'Corona real para usuarios elite', 'icono', 200),
('Ícono Rayo', 'Rayo eléctrico animado', 'icono', 150),
('Ícono Corazón Brillante', 'Corazón con brillo especial', 'icono', 100),

-- Colores personalizados
('Color Púrpura Real', 'Color púrpura exclusivo para perfil', 'color', 60),
('Color Oro Premium', 'Color dorado premium', 'color', 120),
('Color Esmeralda', 'Verde esmeralda elegante', 'color', 90),
('Color Rosa Neón', 'Rosa vibrante neón', 'color', 80);
