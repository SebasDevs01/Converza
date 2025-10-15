# 🤖 Converza Assistant - Microservicio de Asistencia Inteligente

## 📋 Descripción

Microservicio de asistencia conversacional que responde preguntas sobre el funcionamiento de Converza, incluyendo:

- **Sistema de Karma**: Cómo ganar/perder puntos, niveles, badges
- **Reacciones**: Tipos de reacciones y puntos que otorgan
- **Notificaciones**: Cómo funcionan las notificaciones
- **Sistema Social**: Amigos, conexiones místicas, coincidence alerts
- **Tienda**: Qué se puede comprar con karma
- **Publicaciones**: Cómo publicar, comentar, reaccionar

---

## 🏗️ Arquitectura

```
converza-assistant/
├── api/
│   ├── assistant.php          # Endpoint principal (recibe pregunta, devuelve respuesta)
│   ├── context.php            # Obtiene contexto del usuario actual
│   └── health.php             # Health check del microservicio
├── knowledge/
│   ├── karma-kb.json          # Base de conocimientos: Karma
│   ├── reactions-kb.json      # Base de conocimientos: Reacciones
│   ├── notifications-kb.json  # Base de conocimientos: Notificaciones
│   ├── social-kb.json         # Base de conocimientos: Sistema social
│   └── general-kb.json        # Base de conocimientos: General
├── engine/
│   ├── NLPProcessor.php       # Procesador de lenguaje natural
│   ├── IntentClassifier.php   # Clasificador de intenciones
│   ├── ResponseGenerator.php  # Generador de respuestas
│   └── ContextManager.php     # Gestor de contexto del usuario
├── widget/
│   ├── assistant-widget.html  # Widget de chat flotante
│   ├── assistant-widget.css   # Estilos del widget
│   └── assistant-widget.js    # Lógica del widget
└── config/
    └── assistant-config.php   # Configuración del asistente
```

---

## 🚀 Características

### 1. Procesamiento de Lenguaje Natural
- Detecta intención de la pregunta
- Extrae palabras clave
- Soporta sinónimos y variaciones

### 2. Base de Conocimientos
- JSON estructurado con preguntas frecuentes
- Respuestas con ejemplos prácticos
- Enlaces a páginas relevantes

### 3. Contexto del Usuario
- Conoce el karma actual del usuario
- Sabe qué nivel tiene
- Personaliza respuestas según el contexto

### 4. Widget Flotante
- Chat estilo moderno
- Historial de conversación
- Sugerencias de preguntas

---

## 📊 Casos de Uso

### Ejemplo 1: Pregunta sobre Karma
```
Usuario: "¿Cómo gano karma?"
Asistente: "¡Hola! Puedes ganar karma de varias formas:
  
  ✅ Publicando contenido (+3 puntos)
  ✅ Recibiendo reacciones en tus publicaciones
     • Me gusta 👍 → +5 pts
     • Me encanta ❤️ → +10 pts
     • Me divierte 😂 → +7 pts
     • Me asombra 😮 → +8 pts
  ✅ Comentando en publicaciones (+2 puntos)
  ✅ Haciendo amigos (+5 puntos)
  
  Actualmente tienes 150 puntos y eres nivel 3 🌟"
```

### Ejemplo 2: Pregunta sobre Reacciones Negativas
```
Usuario: "¿Por qué perdí karma?"
Asistente: "Las reacciones negativas quitan karma:
  
  ⚠️ Me entristece 😢 → -3 puntos
  ⚠️ Me enoja 😡 → -5 puntos
  
  Si alguien reaccionó negativamente a tu publicación,
  verás un badge rojo con ↓-3 o ↓-5.
  
  Tip: Publica contenido positivo y constructivo para
  evitar reacciones negativas."
```

### Ejemplo 3: Pregunta sobre Niveles
```
Usuario: "¿Qué nivel soy?"
Asistente: "Eres nivel 3 🌟 (Entusiasta) con 150 puntos.
  
  Para llegar al siguiente nivel (4 - Experto):
  • Necesitas 250 puntos
  • Te faltan 100 puntos
  
  Beneficios del nivel 4:
  • Acceso a marcos premium
  • Descuentos en la tienda
  • Badge especial en tu perfil"
```

---

## 🔧 Instalación

### 1. Copiar archivos
```bash
# Ya están en:
# C:\xampp\htdocs\Converza\app\microservices\converza-assistant\
```

### 2. Incluir widget en las páginas
```php
// En index.php, publicaciones.php, etc.
<?php require_once(__DIR__.'/../../microservices/converza-assistant/widget/assistant-widget.html'); ?>
```

### 3. Configurar base de datos (opcional)
```sql
-- Tabla para historial de conversaciones (opcional)
CREATE TABLE IF NOT EXISTS assistant_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    intent VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id_use)
);
```

---

## 📡 API Endpoints

### POST `/api/assistant.php`
Envía una pregunta y recibe una respuesta.

**Request:**
```json
{
  "question": "¿Cómo gano karma?",
  "user_id": 19
}
```

**Response:**
```json
{
  "success": true,
  "answer": "¡Hola! Puedes ganar karma de varias formas...",
  "intent": "karma_gain",
  "confidence": 0.95,
  "suggestions": [
    "¿Qué son las reacciones?",
    "¿Cómo subo de nivel?",
    "¿Qué puedo comprar en la tienda?"
  ],
  "context": {
    "user_karma": 150,
    "user_level": 3,
    "user_name": "fabian"
  }
}
```

### GET `/api/context.php?user_id=19`
Obtiene el contexto del usuario.

**Response:**
```json
{
  "success": true,
  "context": {
    "user_id": 19,
    "username": "fabian",
    "karma": 150,
    "nivel": 3,
    "nivel_titulo": "Entusiasta",
    "nivel_emoji": "🌟",
    "puntos_siguiente_nivel": 250,
    "puntos_faltantes": 100
  }
}
```

### GET `/api/health.php`
Health check del microservicio.

**Response:**
```json
{
  "status": "healthy",
  "version": "1.0.0",
  "timestamp": "2025-10-15T14:30:00Z"
}
```

---

## 🎯 Intenciones Soportadas

| Intención | Palabras Clave | Ejemplos |
|-----------|----------------|----------|
| `karma_gain` | ganar, conseguir, obtener karma | "¿Cómo gano karma?" |
| `karma_loss` | perder, quitar, restar karma | "¿Por qué perdí karma?" |
| `karma_levels` | nivel, subir, rango | "¿Qué nivel soy?" |
| `reactions_info` | reacciones, emojis | "¿Qué son las reacciones?" |
| `reactions_points` | puntos reacciones | "¿Cuántos puntos da un like?" |
| `notifications` | notificaciones, alertas | "¿Cómo funcionan las notificaciones?" |
| `friends` | amigos, agregar | "¿Cómo agrego amigos?" |
| `mystic_connections` | conexiones místicas | "¿Qué son las conexiones místicas?" |
| `shop` | tienda, comprar, marcos | "¿Qué puedo comprar?" |
| `publish` | publicar, post | "¿Cómo publico?" |

---

## 💡 Ejemplos de Preguntas

### Karma
- "¿Cómo gano karma?"
- "¿Por qué perdí puntos?"
- "¿Qué nivel soy?"
- "¿Cuántos puntos necesito para subir de nivel?"

### Reacciones
- "¿Qué son las reacciones?"
- "¿Cuántos puntos da un like?"
- "¿Por qué la reacción enojado quita puntos?"
- "¿Qué reacción da más puntos?"

### Notificaciones
- "¿Cómo funcionan las notificaciones?"
- "¿Por qué no me llegan notificaciones?"
- "¿Puedo desactivar las notificaciones?"

### Social
- "¿Cómo agrego amigos?"
- "¿Qué son las conexiones místicas?"
- "¿Cómo bloqueo a alguien?"

### Tienda
- "¿Qué puedo comprar con karma?"
- "¿Cuánto cuesta un marco?"
- "¿Cómo equipar un marco?"

---

## 🔒 Seguridad

- ✅ Validación de sesión del usuario
- ✅ Sanitización de input (evita XSS)
- ✅ Rate limiting (máximo 10 preguntas por minuto)
- ✅ No expone información sensible

---

## 📈 Métricas (Futuro)

- Total de preguntas por día
- Intenciones más consultadas
- Preguntas sin respuesta (para mejorar KB)
- Satisfacción del usuario (👍/👎)

---

## 🚀 Roadmap

- [x] API básica con base de conocimientos
- [x] Widget de chat flotante
- [x] Procesamiento NLP simple
- [ ] Historial de conversaciones en BD
- [ ] Machine Learning para mejorar respuestas
- [ ] Integración con OpenAI API (opcional)
- [ ] Modo voz (speech-to-text)
- [ ] Analytics dashboard

---

## 📝 Notas Técnicas

### Base de Conocimientos (JSON)
Estructura de cada entrada:
```json
{
  "intent": "karma_gain",
  "keywords": ["ganar", "conseguir", "obtener", "karma", "puntos"],
  "questions": [
    "¿Cómo gano karma?",
    "¿Cómo consigo puntos?",
    "¿De qué forma obtengo karma?"
  ],
  "answer": "¡Hola! Puedes ganar karma de varias formas...",
  "examples": [
    "Publicar contenido → +3 pts",
    "Recibir 'me encanta' → +10 pts"
  ],
  "links": [
    {
      "text": "Ver sistema de karma",
      "url": "/converza/app/presenters/karma_tienda.php"
    }
  ]
}
```

### Algoritmo de Matching
1. Tokenizar pregunta del usuario
2. Extraer palabras clave (sin stopwords)
3. Calcular similitud con cada intención (Jaccard)
4. Si confianza > 0.6 → Responder
5. Si confianza < 0.6 → Respuesta genérica + sugerencias

---

**Autor**: Sistema Converza  
**Versión**: 1.0.0  
**Fecha**: 15 de octubre de 2025
