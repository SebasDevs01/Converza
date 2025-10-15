# 🧠 SISTEMA DE APRENDIZAJE CONTEXTUAL - Converza Assistant

## 📋 Descripción General

El asistente de Converza ahora cuenta con un **sistema de aprendizaje contextual** que analiza las conversaciones reales de los usuarios en la base de datos para proporcionar respuestas mucho más inteligentes y naturales.

---

## ✨ Nuevas Capacidades

### 1️⃣ **Respuestas en Tiempo Real**

El asistente puede responder preguntas sobre datos actuales del sistema:

#### 🕐 Consultas de Hora y Fecha
```
Usuario: "¿Qué hora es?"
Asistente: "🕐 Actualmente son las 03:45 PM del día 15/10/2025.
¿En qué más puedo ayudarte con Converza, meliodasuwu?"

Usuario: "¿Qué día es hoy?"
Asistente: "📅 Hoy es Martes 15 de Octubre de 2025.
Perfecto día para ganar karma en Converza, ¿no crees meliodasuwu? 😊"
```

#### 👥 Usuarios Activos
```
Usuario: "¿Cuántos usuarios hay conectados?"
Asistente: "👥 Actualmente hay 8 usuarios activos en Converza.
¡Es un gran momento para socializar! ¿Quieres saber cómo hacer amigos?"
```

#### 🔥 Tendencias en la Plataforma
```
Usuario: "¿De qué se habla hoy?"
Asistente: "🔥 Los temas más comentados hoy en Converza son:

• Gaming
• Música
• Tecnología
• Películas
• Anime

¿Quieres unirte a la conversación?"
```

---

### 2️⃣ **Detección de Emociones**

El asistente detecta el estado emocional del usuario y responde apropiadamente:

#### 😄 Usuario Alegre
```
Usuario: "jajaja que risa xd"
Asistente: "¡Jaja! 😄 Me alegra verte de buen humor, meliodasuwu.
¿En qué puedo ayudarte con Converza?"
```

#### 😔 Usuario Triste
```
Usuario: "estoy triste"
Asistente: "😔 Lamento que no te sientas bien, meliodasuwu.
Recuerda que en Converza tienes una comunidad increíble. 
¿Quieres conversar con alguien? Puedo ayudarte a conectar con tus amigos."
```

---

### 3️⃣ **Aprendizaje de Patrones**

El sistema analiza la base de datos de Converza para aprender:

#### 📊 Patrones Extraídos
- ✅ **Saludos Comunes**: Expresiones de saludo más usadas
- ✅ **Frases Frecuentes**: Mensajes que se repiten (indica temas populares)
- ✅ **Preguntas Comunes**: Dudas recurrentes de los usuarios
- ✅ **Expresiones Emocionales**: Emojis y expresiones de sentimiento

#### 🗂️ Fuentes de Datos
```sql
-- Mensajes privados
SELECT mensaje FROM mensajes WHERE ...

-- Comentarios en publicaciones
SELECT comentario FROM comentarios WHERE ...

-- Análisis de frecuencia
GROUP BY LOWER(mensaje) HAVING frecuencia > 2
```

---

## 🛠️ Componentes del Sistema

### 📁 Archivos Nuevos

#### 1. `LearningSystem.php`
Sistema principal de aprendizaje que analiza la BD:

```php
class LearningSystem {
    // Extrae saludos comunes
    extractGreetings()
    
    // Extrae frases frecuentes
    extractCommonPhrases()
    
    // Extrae preguntas comunes
    extractQuestions()
    
    // Extrae expresiones emocionales
    extractEmotionalExpressions()
    
    // Analiza comentarios
    analyzeComments()
    
    // Genera respuestas inteligentes
    generateSmartResponse()
}
```

**Características:**
- 🔄 **Cache**: Almacena resultados por 1 hora para no sobrecargar BD
- 📊 **Análisis SQL**: Consultas optimizadas para extraer patrones
- 🧠 **Inteligencia**: Detecta contexto y genera respuestas apropiadas

---

#### 2. `contextual-kb.json`
Base de conocimiento con respuestas contextuales:

```json
{
  "intents": [
    {
      "name": "time_query",
      "keywords": ["hora", "qué hora", "dime la hora"],
      "dynamic": true,
      "requires_system_data": true
    },
    {
      "name": "date_query",
      "keywords": ["fecha", "día", "qué día"],
      "dynamic": true
    },
    {
      "name": "mood_detection_happy",
      "keywords": ["jaja", "lol", "😂", "😄"],
      "dynamic": false
    }
  ]
}
```

**Intents Incluidos:**
- 🕐 `time_query` - Consultas de hora
- 📅 `date_query` - Consultas de fecha
- 🌤️ `weather_query` - Consultas de clima (explica limitación)
- 👥 `user_activity_query` - Usuarios activos
- 🔥 `trending_topics` - Temas en tendencia
- 😄 `mood_detection_happy` - Detección de alegría
- 😔 `mood_detection_sad` - Detección de tristeza
- 🌎 `language_detection` - Detección de inglés

---

### 🔄 Archivos Modificados

#### 3. `assistant.php`
Integra el sistema de aprendizaje en el flujo principal:

```php
// Cargar sistema de aprendizaje
require_once(__DIR__.'/../engine/LearningSystem.php');

// Inicializar
$learningSystem = new LearningSystem($conexion);
$conversationPatterns = $learningSystem->getConversationPatterns();

// Intentar respuesta inteligente primero
$smartResponse = $learningSystem->generateSmartResponse($question, $conversationPatterns);

if ($smartResponse) {
    // Retornar respuesta inteligente
    return $smartResponse;
}

// Si no, usar clasificador de intenciones normal
```

---

#### 4. `IntentClassifier.php`
Carga `contextual-kb.json` con prioridad alta:

```php
private function loadKnowledgeBase() {
    $files = [
        'contextual-kb.json',      // 🆕 PRIORIDAD 1
        'platform-kb.json',
        'conversational-kb.json',
        'karma-kb.json',
        // ...
    ];
}
```

---

#### 5. `ResponseGenerator.php`
Maneja respuestas dinámicas con datos del sistema:

```php
// Nueva función
private function generateDynamicResponse($intentName, $intentData, $context) {
    switch ($intentName) {
        case 'time_query':
            $currentTime = date('h:i A');
            return "🕐 Ahora son las $currentTime...";
            
        case 'user_activity_query':
            $activeUsers = $this->getActiveUsersCount();
            return "👥 Hay $activeUsers usuarios activos...";
    }
}

// Consulta usuarios activos (últimos 15 minutos)
private function getActiveUsersCount() {
    $query = "SELECT COUNT(DISTINCT id_use) 
              FROM usuarios 
              WHERE ultima_conexion >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
}

// Obtiene temas en tendencia (últimas 24 horas)
private function getTrendingTopics() {
    $query = "SELECT comentario 
              FROM comentarios 
              WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
              LIMIT 100";
    
    // Analiza palabras más frecuentes
    // Retorna top 5 temas
}
```

---

## 🎯 Ejemplos de Uso

### Caso 1: Pregunta de Hora
```
📥 Input: "¿Qué hora es?"

🔍 Procesamiento:
1. LearningSystem detecta patrón "hora"
2. isTimeQuery() retorna true
3. generateTimeResponse() obtiene hora actual
4. Personaliza con nombre de usuario

✅ Output:
"🕐 Actualmente son las 03:45 PM del día 15/10/2025.
¿En qué más puedo ayudarte con Converza, meliodasuwu?"
```

---

### Caso 2: Detección de Estado Emocional
```
📥 Input: "jajaja"

🔍 Procesamiento:
1. LearningSystem detecta keyword "jaja"
2. hasEmotionalContent() retorna true
3. generateEmotionalResponse() crea respuesta alegre
4. Incluye sugerencias relacionadas

✅ Output:
"¡Jaja! 😄 Me alegra verte de buen humor, meliodasuwu.
¿En qué puedo ayudarte con Converza?"

Sugerencias:
• ¿Cómo gano karma?
• Cuéntame un chiste
• ¿Qué puedo hacer?
```

---

### Caso 3: Usuarios Activos
```
📥 Input: "¿Cuántos usuarios hay conectados?"

🔍 Procesamiento:
1. IntentClassifier detecta "user_activity_query"
2. requires_system_data = true
3. ResponseGenerator ejecuta getActiveUsersCount()
4. Consulta BD: usuarios con ultima_conexion en últimos 15 min

✅ Output:
"👥 Actualmente hay 8 usuarios activos en Converza.
¡Es un gran momento para socializar! ¿Quieres saber cómo hacer amigos?"
```

---

## 💾 Sistema de Cache

### Ubicación
```
/app/microservices/converza-assistant/cache/learning-cache.json
```

### Funcionamiento
```php
// Cache por 1 hora (3600 segundos)
private $cacheDuration = 3600;

// Verificar cache
if ($this->hasFreshCache()) {
    return $this->loadFromCache();
}

// Si no hay cache o está viejo, extraer de BD
$patterns = [
    'common_greetings' => $this->extractGreetings(),
    'common_phrases' => $this->extractCommonPhrases(),
    'question_patterns' => $this->extractQuestions(),
    'emotional_expressions' => $this->extractEmotionalExpressions()
];

// Guardar en cache
$this->saveToCache($patterns);
```

**Beneficios:**
- ⚡ Reduce carga en BD
- 🚀 Respuestas más rápidas
- 📊 Actualización automática cada hora

---

## 🔧 Configuración

### Zona Horaria
```php
// LearningSystem.php y ResponseGenerator.php
date_default_timezone_set('America/Bogota');
```

**Ajustar según tu ubicación:**
- 🇨🇴 Colombia: `America/Bogota`
- 🇲🇽 México: `America/Mexico_City`
- 🇦🇷 Argentina: `America/Argentina/Buenos_Aires`
- 🇪🇸 España: `Europe/Madrid`

---

### Ventana de Actividad
```php
// Usuarios activos en últimos 15 minutos
WHERE ultima_conexion >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)

// Comentarios de últimas 24 horas
WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
```

**Personalizar intervalos según necesidad**

---

## 📊 Capacidades Actuales vs Futuras

### ✅ Implementado

| Característica | Estado | Descripción |
|---------------|--------|-------------|
| Hora actual | ✅ | Responde hora exacta |
| Fecha actual | ✅ | Responde día, mes, año |
| Usuarios activos | ✅ | Cuenta usuarios últimos 15 min |
| Temas tendencia | ✅ | Analiza comentarios recientes |
| Detección emociones | ✅ | Alegría, tristeza, risa |
| Cache inteligente | ✅ | 1 hora de duración |
| Aprendizaje BD | ✅ | Extrae patrones de mensajes |

---

### 🔮 Próximas Mejoras

| Característica | Prioridad | Descripción |
|---------------|-----------|-------------|
| Clima real | 🔴 Alta | API meteorológica |
| Análisis ML | 🟡 Media | Machine Learning avanzado |
| Historial conversación | 🔴 Alta | Recordar contexto previo |
| Personalidad adaptativa | 🟡 Media | Ajustar tono según usuario |
| Multilenguaje | 🟢 Baja | Soporte completo inglés |
| Integración GPT | 🔴 Alta | Respuestas generativas |

---

## 🎓 Cómo Funciona el Flujo

```
┌─────────────────┐
│ Usuario pregunta│
│  "¿Qué hora es?"│
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│  assistant.php          │
│  Recibe pregunta        │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│  LearningSystem         │
│  generateSmartResponse()│
└────────┬────────────────┘
         │
         ├── isTimeQuery? ──► SÍ ──┐
         │                         │
         ├── isDayQuery? ──► NO    │
         │                         │
         └── hasEmotional? ─► NO   │
                                   │
                                   ▼
                        ┌──────────────────┐
                        │ generateTimeResp │
                        │ date('h:i A')    │
                        └────────┬─────────┘
                                 │
                                 ▼
                        ┌─────────────────┐
                        │ Personalizar    │
                        │ con username    │
                        └────────┬────────┘
                                 │
                                 ▼
                        ┌─────────────────┐
                        │ Retornar JSON   │
                        │ al frontend     │
                        └─────────────────┘
```

---

## 🧪 Testing

### Probar Hora/Fecha
```javascript
// En consola del navegador
fetch('/Converza/app/microservices/converza-assistant/api/assistant.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        question: '¿Qué hora es?',
        user_id: 20
    })
}).then(r => r.json()).then(console.log);
```

### Probar Usuarios Activos
```javascript
fetch('/Converza/app/microservices/converza-assistant/api/assistant.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        question: '¿Cuántos usuarios hay?',
        user_id: 20
    })
}).then(r => r.json()).then(console.log);
```

### Probar Emociones
```javascript
fetch('/Converza/app/microservices/converza-assistant/api/assistant.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        question: 'jajaja que risa',
        user_id: 20
    })
}).then(r => r.json()).then(console.log);
```

---

## 🎯 Ventajas del Sistema

### Para Usuarios
- 💬 **Conversaciones Naturales**: Responde como un humano
- ⏰ **Información Útil**: Hora, fecha, estadísticas
- 😊 **Empatía**: Detecta y responde a emociones
- 🎯 **Relevancia**: Temas actuales y tendencias

### Para el Sistema
- 📊 **Datos Reales**: Aprende de conversaciones reales
- 🔄 **Auto-mejora**: Se actualiza con el tiempo
- ⚡ **Performance**: Cache reduce carga BD
- 🧠 **Inteligente**: Contexto y personalización

---

## 📝 Notas Técnicas

### Requisitos BD
```sql
-- Tabla usuarios debe tener:
ultima_conexion DATETIME

-- Tabla mensajes debe existir con:
mensaje TEXT
fecha DATETIME

-- Tabla comentarios debe tener:
comentario TEXT
fecha DATETIME
```

### Permisos
```bash
# Cache debe ser escribible
chmod 755 app/microservices/converza-assistant/cache/
```

---

## ✅ Resumen

El **Sistema de Aprendizaje Contextual** transforma el asistente de Converza en una IA verdaderamente inteligente que:

1. 🧠 **Aprende** de conversaciones reales de usuarios
2. ⏰ **Responde** preguntas de tiempo real (hora, fecha, usuarios)
3. 😊 **Detecta** emociones y responde apropiadamente
4. 🔥 **Analiza** tendencias y temas populares
5. 💾 **Optimiza** con cache inteligente
6. 🎯 **Personaliza** respuestas con contexto del usuario

**¡Todo funcionando en tiempo real! 🚀**
