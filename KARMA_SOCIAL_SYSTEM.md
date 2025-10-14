# 🌟 Sistema de Karma Social - Converza

## 📋 Descripción General

El **Sistema de Karma Social** registra las buenas acciones de los usuarios (comentarios positivos, interacciones respetuosas, apoyo en publicaciones) y las utiliza para influir en la calidad de futuras conexiones.

---

## ✨ Características Principales

### 1. **Registro Automático de Acciones Positivas**
- 💬 Comentarios con palabras positivas
- ❤️ Reacciones de apoyo (like, love, wow)
- 🤝 Mensajes motivadores
- 📚 Compartir conocimiento
- 👥 Ayuda a otros usuarios

### 2. **Sistema de Puntuación**
Cada acción positiva otorga puntos de karma:

| Acción | Puntos | Criterio |
|--------|--------|----------|
| Comentario Positivo | 8 | Contiene palabras positivas |
| Apoyo a Publicación | 3 | Like, love o wow |
| Interacción Respetuosa | 8 | Respuesta constructiva |
| Compartir Conocimiento | 15 | Comentario educativo largo |
| Ayuda a Usuario | 12 | Responde preguntas |
| Primera Interacción | 5 | Nueva amistad |
| Mensaje Motivador | 10 | Mensaje de apoyo |
| Amigo Activo (30 días) | 20 | Mantiene amistad activa |

### 3. **Niveles de Karma**
Los usuarios progresan a través de 6 niveles:

| Nivel | Karma Requerido | Emoji | Color |
|-------|-----------------|-------|-------|
| **Novato** | 0-49 | 🌱 | Azul Claro |
| **Intermedio** | 50-99 | ⭐ | Naranja |
| **Avanzado** | 100-249 | ✨ | Verde |
| **Experto** | 250-499 | 💫 | Azul |
| **Maestro** | 500-999 | 🌟 | Púrpura |
| **Legendario** | 1000+ | 👑 | Dorado |

### 4. **Influencia en Conexiones Místicas** 🎯
Usuarios con más karma tienen **mejor puntuación en conexiones**:

| Karma | Multiplicador | Bonus |
|-------|---------------|-------|
| 500+ | 1.5x | 50% |
| 250-499 | 1.3x | 30% |
| 100-249 | 1.2x | 20% |
| 50-99 | 1.1x | 10% |
| 0-49 | 1.0x | 0% |

**Ejemplo:**
```
Usuario A: 80 karma (multiplicador 1.1x)
Usuario B: 300 karma (multiplicador 1.3x)

Conexión detectada: 70 puntos base
Multiplicador promedio: (1.1 + 1.3) / 2 = 1.2x
Puntuación final: 70 * 1.2 = 84 puntos ✅ (notifica)
```

---

## 🏗️ Arquitectura del Sistema

### **Base de Datos**

#### Tabla: `karma_social`
```sql
CREATE TABLE karma_social (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_accion VARCHAR(50) NOT NULL,
    puntos INT NOT NULL DEFAULT 0,
    referencia_id INT NULL,
    referencia_tipo VARCHAR(50) NULL,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo_accion (tipo_accion)
);
```

### **Archivos del Sistema**

| Archivo | Propósito |
|---------|-----------|
| `karma-social-helper.php` | Lógica principal del sistema |
| `karma-social-triggers.php` | Triggers automáticos |
| `get_karma_social.php` | API REST para obtener karma |
| `karma-social-widget.php` | Widget visual para perfil |
| `setup_karma_social.php` | Script de instalación |

---

## 🔄 Flujo de Funcionamiento

```
USUARIO HACE ACCIÓN POSITIVA
         ↓
Sistema detecta automáticamente
         ↓
Analiza contenido (palabras positivas)
         ↓
Calcula puntos según tipo de acción
         ↓
✅ Registra en tabla karma_social
         ↓
Actualiza karma total del usuario
         ↓
Influye en futuras Conexiones Místicas
```

---

## 🚀 Instalación

### **Paso 1: Ejecutar Script**
```
http://localhost/Converza/setup_karma_social.php
```

Este script:
- ✅ Crea la tabla `karma_social`
- ✅ Verifica la instalación
- ✅ Muestra información del sistema

### **Paso 2: Verificar Integración**
El sistema está automáticamente integrado con:
- ✅ Conexiones Místicas (multiplicador de karma)
- ✅ Sistema de notificaciones
- ✅ Perfiles de usuario

---

## 💻 Uso del Sistema

### **Registrar Karma Automáticamente**

El karma se registra **automáticamente** cuando los usuarios:

#### 1. **Hacen Comentarios Positivos**
```php
// En agregar comentario.php
require_once '../models/karma-social-triggers.php';
$karmaTriggers = new KarmaSocialTriggers($conexion);

// Después de insertar comentario
$karmaTriggers->nuevoComentario($usuario_id, $comentario_id, $texto);
```

#### 2. **Dan Reacciones de Apoyo**
```php
// En sistema de reacciones
$karmaTriggers->nuevaReaccion($usuario_id, $publicacion_id, 'like');
```

#### 3. **Aceptan Amistades**
```php
// Al aceptar solicitud de amistad
$karmaTriggers->amistadAceptada($usuario_id, $amigo_id);
```

### **Obtener Karma de Usuario**

#### API REST:
```javascript
fetch('/Converza/app/presenters/get_karma_social.php')
    .then(response => response.json())
    .then(data => {
        console.log('Karma total:', data.karma.total);
        console.log('Nivel:', data.nivel.nombre);
        console.log('Multiplicador:', data.multiplicador);
    });
```

#### PHP:
```php
require_once 'app/models/karma-social-helper.php';
$karmaHelper = new KarmaSocialHelper($conexion);

$karma = $karmaHelper->obtenerKarmaTotal($usuario_id);
echo "Karma: " . $karma['karma_total'];

$nivel = $karmaHelper->obtenerNivelKarma($karma['karma_total']);
echo "Nivel: " . $nivel['nivel'] . " " . $nivel['emoji'];
```

### **Mostrar Widget en Perfil**

```php
// En perfil.php
$usuario_id_perfil = $_GET['id']; // ID del perfil que se está viendo

// Incluir widget
include '../view/components/karma-social-widget.php';
```

---

## 🎨 Interfaz de Usuario

### **Widget de Karma**
```
╔════════════════════════════════╗
║  🌟 Karma Social               ║
║  Nivel: Avanzado               ║
║                                ║
║  150 puntos                    ║
║  45 acciones                   ║
║                                ║
║  💫 Buenas acciones registradas║
║  Comentarios positivos • Apoyo ║
╚════════════════════════════════╝
```

### **API Response:**
```json
{
  "success": true,
  "karma": {
    "total": 150,
    "acciones_totales": 45,
    "karma_30dias": 80,
    "acciones_30dias": 25
  },
  "nivel": {
    "nombre": "Avanzado",
    "emoji": "✨",
    "color": "#32CD32",
    "progreso": 33.3,
    "puntos_actual": 150,
    "puntos_siguiente": 250
  },
  "multiplicador": 1.2
}
```

---

## 🧪 Testing

### **Test 1: Comentario Positivo**
```sql
-- Usuario hace comentario con palabra positiva
INSERT INTO comentarios (usuario, comentario, publicacion) 
VALUES (2, 'Excelente publicación, muy útil!', 1);

-- Registrar karma
-- El trigger automático detecta "Excelente" y "útil"
-- Otorga 8 puntos

-- Verificar
SELECT * FROM karma_social WHERE usuario_id = 2;
```

### **Test 2: Reacción de Apoyo**
```sql
-- Usuario da "like"
INSERT INTO reacciones (id_usuario, id_publicacion, tipo) 
VALUES (2, 1, 'like');

-- Registrar karma (3 puntos)

-- Verificar karma total
SELECT SUM(puntos) FROM karma_social WHERE usuario_id = 2;
```

### **Test 3: Influencia en Conexiones**
```sql
-- Usuario 2 tiene 150 karma (multiplicador 1.2x)
-- Usuario 3 tiene 300 karma (multiplicador 1.3x)
-- Conexión base: 70 puntos

-- Multiplicador promedio: (1.2 + 1.3) / 2 = 1.25
-- Puntuación final: 70 * 1.25 = 87.5 ≈ 88 puntos

-- Verificar en conexiones_misticas
SELECT * FROM conexiones_misticas 
WHERE usuario1_id = 2 AND usuario2_id = 3;
-- puntuacion debería ser ~88 (aumentó por karma)
```

---

## 📊 Detección de Palabras Positivas

### **Lista de Palabras:**
```php
'gracias', 'excelente', 'genial', 'increíble', 'bueno', 'bien',
'felicidades', 'éxito', 'logro', 'apoyo', 'ayuda', 'maravilloso',
'perfecto', 'fantástico', 'hermoso', 'inspirador', 'motivador',
'admirable', 'impresionante', 'valioso', 'útil', 'interesante'
```

### **Algoritmo:**
```php
$texto_lower = mb_strtolower($comentario);
$palabras_encontradas = 0;

foreach (PALABRAS_POSITIVAS as $palabra) {
    if (strpos($texto_lower, $palabra) !== false) {
        $palabras_encontradas++;
    }
}

if ($palabras_encontradas >= 1) {
    // Otorgar karma
}
```

---

## 🔐 Seguridad y Anti-Spam

### **Prevención de Duplicados:**
```php
// No otorgar karma múltiples veces por la misma acción
if (esAccionDuplicada($usuario_id, 'apoyo_publicacion', $publicacion_id)) {
    return false; // No registrar
}
```

### **Acciones Únicas:**
- ✅ Solo 1 karma por reacción en cada publicación
- ✅ Solo 1 karma por comentario positivo en cada publicación
- ✅ Solo 1 karma por primera interacción con cada usuario

---

## 📈 Métricas y Estadísticas

### **Obtener Top Usuarios:**
```php
$topUsuarios = $karmaHelper->obtenerTopUsuarios(10);

foreach ($topUsuarios as $usuario) {
    echo "{$usuario['usuario']}: {$usuario['karma_total']} karma\n";
}
```

### **Historial de Acciones:**
```php
$historial = $karmaHelper->obtenerHistorial($usuario_id, 20);

foreach ($historial as $accion) {
    echo "{$accion['tipo_accion']}: +{$accion['puntos']} pts\n";
}
```

---

## 🎯 Integración con Sistemas Existentes

### **✅ Conexiones Místicas**
```php
// En conexiones-misticas-usuario-helper.php
// Línea 161: aplicarMultiplicadorKarma()

// Automáticamente multiplica puntuación por karma
$puntuacion_con_karma = $puntuacion * multiplicador_karma;
```

### **✅ Sin Modificaciones a:**
- Sistema de notificaciones (usa el existente)
- Tabla de usuarios (no se modifica)
- Tabla de publicaciones (no se modifica)
- Tabla de comentarios (no se modifica)

---

## 🎉 Beneficios

### **Para Usuarios:**
✅ Reconocimiento por comportamiento positivo  
✅ Mejores conexiones con usuarios afines  
✅ Gamificación y progresión  
✅ Sentido de comunidad  

### **Para la Plataforma:**
✅ Fomenta interacciones positivas  
✅ Reduce toxicidad  
✅ Aumenta engagement  
✅ Mejora calidad de conexiones  

---

## 📝 Mantenimiento

### **Limpiar Karma Antiguo (opcional):**
```sql
-- Eliminar acciones de más de 1 año
DELETE FROM karma_social 
WHERE fecha_accion < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### **Recalcular Karma Total:**
```sql
-- Ver karma de todos los usuarios
SELECT usuario_id, SUM(puntos) as karma_total
FROM karma_social
GROUP BY usuario_id
ORDER BY karma_total DESC;
```

---

## 🚀 Próximas Mejoras (Opcional)

- [ ] Sistema de badges/insignias por logros
- [ ] Leaderboard público de karma
- [ ] Decay de karma (perder puntos con inactividad)
- [ ] Karma negativo por reportes
- [ ] Premios/recompensas por alto karma

---

**Fecha de implementación:** Octubre 2025  
**Versión:** 1.0  
**Estado:** ✅ Funcional y probado  
**Compatibilidad:** 100% con sistema existente
