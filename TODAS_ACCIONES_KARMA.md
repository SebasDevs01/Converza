# 🎯 TODAS LAS ACCIONES QUE ACTIVAN EL SISTEMA KARMA

## ✅ **SÍ, NO ES SOLO COMENTARIOS - ES TODO**

El sistema de karma se activa con **TODAS** las interacciones del usuario en Converza. Aquí está la lista completa:

---

## 📊 **ACCIONES QUE DAN KARMA (+)**

### **1. 💬 COMENTARIOS POSITIVOS (+8 karma)**

**Cuándo se activa:**
- Usuario comenta en una publicación
- Sistema analiza el texto con 90+ palabras positivas
- Si detecta: "gracias", "excelente", "genial", etc.

**Ejemplos que activan:**
```
✅ "¡Gracias! Muy útil"
✅ "Excelente publicación, me encantó"
✅ "Felicidades por tu logro"
✅ "Increíble trabajo 👍"
✅ "Maravilloso, sigue así"
```

**Archivo:** `app/presenters/agregarcomentario.php`
```php
// Línea ~100
$karmaTriggers->nuevoComentario($usuario_id, $comentario_id, $comentario);
```

**Notificación:**
```
🔔 "⭐ Has ganado +8 puntos de karma por: Comentario positivo detectado"
Popup: "+8" verde
```

---

### **2. ❤️ REACCIONES A PUBLICACIONES (+3 karma)**

**Cuándo se activa:**
- Usuario da "Me gusta" ❤️
- Usuario reacciona con 😂, 😮, 😢, 😡
- Cualquier reacción a una publicación

**Archivo:** `app/presenters/save_reaction.php`
```php
// Línea ~50
$karmaTriggers->nuevaReaccion($usuario_id, $publicacion_id, $tipo_reaccion);
```

**Notificación:**
```
🔔 "⭐ Has ganado +3 puntos de karma por: Reacción positiva"
Popup: "+3" verde
```

---

### **3. 👥 HACER NUEVOS AMIGOS (+20 karma)**

**Cuándo se activa:**
- Usuario acepta una solicitud de amistad
- Usuario envía y le aceptan una solicitud

**Archivo:** `app/presenters/solicitud.php`
```php
// Línea ~80
$karmaTriggers->amistadAceptada($usuario_id, $amigo_id);
```

**Notificación:**
```
🔔 "⭐ Has ganado +20 puntos de karma por: Nueva amistad establecida"
Popup: "+20" verde
```

---

### **4. 👋 PRIMERA INTERACCIÓN DEL DÍA (+5 karma)**

**Cuándo se activa:**
- Primera acción del usuario en el día
- Puede ser: comentar, dar like, publicar, etc.

**Archivo:** `app/models/karma-social-helper.php`
```php
// Se valida automáticamente en cada acción
if ($this->esPrimeraInteraccionDelDia($usuario_id)) {
    registrarAccion('primera_interaccion');
}
```

**Notificación:**
```
🔔 "⭐ Has ganado +5 puntos de karma por: Primera interacción del día"
Popup: "+5" verde
```

---

### **5. 💬 MENSAJE MOTIVADOR (+10 karma)**

**Cuándo se activa:**
- Usuario envía mensaje directo
- Contiene palabras motivadoras: "ánimo", "fuerza", "puedes", "apoyo"

**Archivo:** `app/models/karma-social-triggers.php`
```php
// Línea ~50
public function mensajeEnviado($usuario_id, $destinatario_id, $texto_mensaje) {
    $palabras_motivadoras = ['ánimo', 'fuerza', 'puedes', 'confío', 'apoyo'];
    // Si detecta → +10 karma
}
```

**Ejemplos:**
```
✅ "¡Ánimo! Tú puedes lograrlo"
✅ "Cuenta con mi apoyo siempre"
✅ "Confío en ti, sigue adelante"
```

**Notificación:**
```
🔔 "⭐ Has ganado +10 puntos de karma por: Mensaje de apoyo enviado"
Popup: "+10" verde
```

---

### **6. 📚 COMPARTIR CONOCIMIENTO (+15 karma)**

**Cuándo se activa:**
- Usuario escribe comentario largo (100+ caracteres)
- Contiene palabras educativas: "tutorial", "aprende", "guía", "explicación"

**Archivo:** `app/models/karma-social-triggers.php`
```php
// Línea ~65
public function comentarioEducativo($usuario_id, $comentario_id, $texto) {
    if (strlen($texto) > 100 && tiene_palabras_educativas) {
        registrarAccion('compartir_conocimiento'); // +15 karma
    }
}
```

**Ejemplos:**
```
✅ "Te explico cómo funciona: primero debes... [tutorial largo]"
✅ "Aquí está la guía paso a paso para que puedas aprender..."
✅ "El método correcto es el siguiente: [explicación detallada]"
```

**Notificación:**
```
🔔 "⭐ Has ganado +15 puntos de karma por: Comentario educativo compartido"
Popup: "+15" verde
```

---

### **7. 🆘 AYUDAR A OTRO USUARIO (+12 karma)**

**Cuándo se activa:**
- Usuario responde a pregunta de otro
- Comentario contiene: "te ayudo", "solución", "respuesta"

**Archivo:** `app/models/karma-social-helper.php`
```php
// Detecta automáticamente palabras de ayuda
private const PALABRAS_AYUDA = ['ayudo', 'solución', 'respuesta', 'resuelvo'];
```

**Ejemplos:**
```
✅ "Te ayudo con eso, la solución es..."
✅ "La respuesta a tu pregunta es..."
✅ "Puedo ayudarte a resolver el problema"
```

**Notificación:**
```
🔔 "⭐ Has ganado +12 puntos de karma por: Has ayudado a otro usuario"
Popup: "+12" verde
```

---

### **8. ✅ SIN REPORTES EN 24 HORAS (+50 karma)**

**Cuándo se activa:**
- Sistema revisa automáticamente cada 24 horas
- Usuario no ha recibido reportes
- Usuario no ha sido bloqueado

**Archivo:** `app/models/karma-social-helper.php`
```php
// Trigger automático diario
if (sin_reportes_24h($usuario_id)) {
    registrarAccion('sin_reportes'); // +50 karma
}
```

**Notificación:**
```
🔔 "⭐ Has ganado +50 puntos de karma por: ¡Sin reportes en 24 horas!"
Popup: "+50" verde
```

---

## ❌ **ACCIONES QUE QUITAN KARMA (-)**

### **1. 🚫 COMENTARIO TÓXICO (-15 karma)**

**Cuándo se activa:**
- Usuario comenta con palabras negativas
- Detecta: "malo", "horrible", "idiota", "estúpido", insultos

**Archivo:** `app/presenters/agregarcomentario.php`
```php
$karmaTriggers->nuevoComentario($usuario_id, $comentario_id, $comentario);
// Si es negativo → -15 karma
```

**Ejemplos que quitan karma:**
```
❌ "Eso es horrible y malo"
❌ "Qué publicación tan estúpida"
❌ "Eres un idiota"
❌ "Qué basura de contenido"
```

**Notificación:**
```
🔔 "⚠️ Has perdido -15 puntos de karma por: Comportamiento negativo detectado"
Popup: "-15" rojo
```

---

### **2. 📢 SPAM DETECTADO (-20 karma)**

**Cuándo se activa:**
- Usuario publica 3+ veces el mismo contenido
- Comentarios repetidos múltiples veces
- Detección automática de duplicados

**Archivo:** `app/models/karma-social-helper.php`
```php
if ($this->esSpam($usuario_id, $contenido)) {
    registrarAccion('spam'); // -20 karma
}
```

**Notificación:**
```
🔔 "⚠️ Has perdido -20 puntos de karma por: Spam detectado"
Popup: "-20" rojo
```

---

### **3. 🚨 REPORTE RECIBIDO (-25 karma)**

**Cuándo se activa:**
- Otro usuario reporta tu publicación/comentario
- Moderador valida el reporte

**Notificación:**
```
🔔 "⚠️ Has perdido -25 puntos de karma por: Has recibido un reporte"
Popup: "-25" rojo
```

---

### **4. 🔇 BLOQUEO RECIBIDO (-30 karma)**

**Cuándo se activa:**
- Otro usuario te bloquea
- Sistema registra el bloqueo

**Archivo:** `app/models/bloqueos-helper.php`
```php
// Cuando alguien te bloquea
$karmaHelper->registrarAccion($bloqueado_id, 'bloqueo_recibido', -30);
```

**Notificación:**
```
🔔 "⚠️ Has perdido -30 puntos de karma por: Un usuario te ha bloqueado"
Popup: "-30" rojo
```

---

### **5. ❌ CONTENIDO ELIMINADO (-10 karma)**

**Cuándo se activa:**
- Moderador elimina tu publicación
- Contenido inapropiado removido

**Notificación:**
```
🔔 "⚠️ Has perdido -10 puntos de karma por: Contenido eliminado por moderador"
Popup: "-10" rojo
```

---

## 🎯 **DETECCIÓN INTELIGENTE**

### **Sistema Analiza:**

1. **90+ Palabras Positivas:**
   - Gratitud: gracias, aprecio, agradezco
   - Calidad: excelente, genial, increíble, perfecto
   - Ánimo: bravo, felicidades, ánimo, fuerza
   - Emojis: 👍, ❤️, 😊, 🙌, 💪, 🌟, ✨

2. **80+ Palabras Negativas:**
   - Insultos: idiota, estúpido, tonto, imbécil
   - Ofensas: horrible, basura, porquería, asco
   - Negatividad: malo, terrible, pésimo, patético

3. **Contexto:**
   - Longitud del comentario
   - Negaciones ("no es malo" = positivo)
   - Sarcasmo
   - Intencionalidad

---

## 📊 **RESUMEN DE PUNTOS**

| Acción | Karma | Dónde se activa |
|--------|-------|-----------------|
| 💬 Comentario positivo | **+8** | Publicaciones |
| ❤️ Reacción | **+3** | Publicaciones |
| 👥 Nueva amistad | **+20** | Solicitudes |
| 👋 Primera del día | **+5** | Cualquier acción |
| 💬 Mensaje motivador | **+10** | Chat directo |
| 📚 Compartir conocimiento | **+15** | Comentarios largos |
| 🆘 Ayudar usuario | **+12** | Comentarios |
| ✅ Sin reportes 24h | **+50** | Automático |
| 🚫 Comentario tóxico | **-15** | Publicaciones |
| 📢 Spam | **-20** | Publicaciones/Comentarios |
| 🚨 Reporte | **-25** | Contenido reportado |
| 🔇 Bloqueo | **-30** | Relaciones |
| ❌ Contenido eliminado | **-10** | Publicaciones |

---

## 🔄 **FLUJO COMPLETO DE CUALQUIER ACCIÓN**

```
Usuario hace CUALQUIER acción (comentar, reaccionar, amistad, etc.)
         ↓
Sistema detecta el tipo de acción
         ↓
karma-social-helper.php analiza:
  - ¿Es positivo o negativo?
  - ¿Tiene palabras clave?
  - ¿Es spam?
  - ¿Primera del día?
         ↓
Registra karma en base de datos
         ↓
Crea notificación en campana 🔔:
  "⭐ Has ganado +8 karma por: X"
  "⚠️ Has perdido -15 karma por: Y"
         ↓
⏱️ 0.5 segundos después...
         ↓
JavaScript verifica karma pendiente (AJAX)
         ↓
Muestra popup inmediato:
  - "+8" verde (positivo)
  - "-15" rojo (negativo)
         ↓
Actualiza contador animado
         ↓
Usuario ve feedback INMEDIATO ✅
```

---

## 🧪 **EJEMPLOS DE TESTING**

### **Test 1: Comentario Positivo**
```
Usuario comenta: "¡Gracias! Excelente publicación"
↓
Sistema detecta: "gracias" (positiva) + "excelente" (positiva)
↓
Karma: +8
↓
Notificación: "⭐ Has ganado +8 puntos por: Comentario positivo detectado"
↓
Popup: "+8" verde flotando
```

### **Test 2: Reacción a Publicación**
```
Usuario da ❤️ a publicación
↓
Sistema registra reacción
↓
Karma: +3
↓
Notificación: "⭐ Has ganado +3 puntos por: Reacción positiva"
↓
Popup: "+3" verde
```

### **Test 3: Hacer Amigo**
```
Usuario acepta solicitud de amistad
↓
Sistema registra amistad
↓
Karma: +20
↓
Notificación: "⭐ Has ganado +20 puntos por: Nueva amistad establecida"
↓
Popup: "+20" verde
```

### **Test 4: Comentario Tóxico**
```
Usuario comenta: "Eso es horrible y malo"
↓
Sistema detecta: "horrible" (negativa) + "malo" (negativa)
↓
Karma: -15
↓
Notificación: "⚠️ Has perdido -15 puntos por: Comportamiento negativo"
↓
Popup: "-15" rojo
```

### **Test 5: Mensaje Motivador**
```
Usuario envía mensaje: "¡Ánimo! Tú puedes lograrlo"
↓
Sistema detecta: "ánimo" + "puedes" (motivadoras)
↓
Karma: +10
↓
Notificación: "⭐ Has ganado +10 puntos por: Mensaje de apoyo enviado"
↓
Popup: "+10" verde
```

---

## 📁 **ARCHIVOS QUE ACTIVAN KARMA**

| Archivo | Acción que detecta |
|---------|-------------------|
| `app/presenters/agregarcomentario.php` | 💬 Comentarios |
| `app/presenters/save_reaction.php` | ❤️ Reacciones |
| `app/presenters/solicitud.php` | 👥 Amistades |
| `app/presenters/enviar_mensaje.php` | 💬 Mensajes |
| `app/models/karma-social-helper.php` | 🧠 Lógica central |
| `app/models/karma-social-triggers.php` | 🎯 Triggers automáticos |

---

## ✅ **CONFIRMACIÓN FINAL**

**Pregunta:** ¿No es solo cuando comenta, también es cualquier tipo de comportamiento verdad?

**Respuesta:** **¡EXACTO! ✅**

El sistema karma se activa en:
- ✅ Comentarios (positivos/negativos)
- ✅ Reacciones (likes, emojis)
- ✅ Amistades (aceptar/enviar solicitudes)
- ✅ Mensajes directos (motivadores/tóxicos)
- ✅ Publicaciones (educativas/spam)
- ✅ Primera interacción del día
- ✅ Reportes recibidos
- ✅ Bloqueos recibidos
- ✅ Sin reportes 24h

**TODO** el comportamiento del usuario es evaluado y registrado automáticamente. 🎯

---

**Fecha:** 13 de Octubre, 2025  
**Sistema:** Karma Social Completo  
**Acciones Monitoreadas:** 13+  
**Status:** ✅ ACTIVO EN TODO CONVERZA
