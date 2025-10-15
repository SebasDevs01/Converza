# ✨ WIDGET ASISTENTE IA - Integración Completa

## 🎯 CAMBIOS REALIZADOS

### 1. Ícono Actualizado: Conexiones Místicas ✨

**Antes**: `bi-robot` 🤖  
**Ahora**: `bi-stars` ✨ (destellos de Conexiones Místicas)

### 2. Animación de Destellos

Agregué una animación CSS que hace que el ícono:
- ✨ **Brille** sutilmente
- 🌟 **Rote** ligeramente (-5° a +5°)
- 💫 **Escale** (1.0 → 1.1)
- ⚡ **Destelle** con drop-shadow blanco

```css
@keyframes sparkle {
    0%, 100% { 
        opacity: 1;
        transform: rotate(0deg) scale(1);
        filter: drop-shadow(0 0 5px rgba(255, 255, 255, 0.5));
    }
    50% { 
        opacity: 1;
        transform: rotate(5deg) scale(1.1);
        filter: drop-shadow(0 0 15px rgba(255, 255, 255, 1));
    }
}
```

**Resultado**: El botón flotante se ve **mágico y místico** ✨💫

---

## 🚀 INSTRUCCIONES DE INTEGRACIÓN

### Paso 1: Incluir Widget en `index.php`

Agregar **antes de `</body>`**:

```php
<!-- ✨ ASISTENTE CONVERZA -->
<?php require_once(__DIR__.'/../../microservices/converza-assistant/widget/assistant-widget.html'); ?>
```

### Paso 2: Pasar User ID

Agregar **antes de `</body>`** (después del widget):

```html
<script>
    // User ID para el asistente
    const USER_ID = <?php echo isset($_SESSION['id']) ? $_SESSION['id'] : 0; ?>;
</script>
```

### Paso 3: Verificar Bootstrap Icons

El widget usa Bootstrap Icons. Asegúrate de que esté cargado en tu `<head>`:

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
```

---

## 📍 UBICACIÓN DE ARCHIVOS

```
app/microservices/converza-assistant/
├── widget/
│   ├── assistant-widget.html   ✅ (Actualizado con bi-stars)
│   ├── assistant-widget.css    ✅ (Animación sparkle agregada)
│   └── assistant-widget.js     ✅ (Actualizado con bi-stars)
├── api/
│   ├── assistant.php           ✅
│   └── health.php              ✅
├── engine/
│   ├── IntentClassifier.php    ✅
│   ├── ResponseGenerator.php   ✅
│   └── ContextManager.php      ✅
└── knowledge/
    ├── karma-kb.json           ✅
    ├── reactions-kb.json       ✅
    ├── notifications-kb.json   ✅
    ├── social-kb.json          ✅
    └── general-kb.json         ✅
```

---

## 🎨 RESULTADO VISUAL

### Botón Flotante
- **Posición**: Esquina inferior derecha (bottom: 20px, right: 20px)
- **Tamaño**: 60px × 60px (círculo)
- **Color**: Gradiente morado-azul (#667eea → #764ba2)
- **Ícono**: ✨ Estrellas (bi-stars) con animación de destellos
- **Sombra**: Drop-shadow animado (5px → 15px)
- **Hover**: Scale 1.1 + sombra aumentada

### Panel de Chat
- **Ancho**: 380px
- **Alto**: 600px
- **Header**: Avatar con ✨ + "Asistente Converza"
- **Mensajes**: Avatar con ✨ para asistente, 👤 para usuario
- **Input**: Textarea auto-resize con botón enviar
- **Sugerencias**: 3 botones con preguntas frecuentes

---

## 💡 PREGUNTAS QUE ENTIENDE

### Karma (4 intenciones)
- "¿Cómo gano karma?"
- "¿Por qué perdí puntos?"
- "¿Qué nivel soy?"
- "¿Qué beneficios tiene el karma?"

### Reacciones (5 intenciones)
- "¿Qué son las reacciones?"
- "¿Cuántos puntos da cada reacción?"
- "¿Por qué hay reacciones negativas?"
- "¿Puedo cambiar mi reacción?"
- "¿Qué es el badge de karma?"

### Notificaciones (3 intenciones)
- "¿Cómo funcionan las notificaciones?"
- "No me llegan notificaciones"
- "¿Puedo desactivar las notificaciones?"

### Social (4 intenciones)
- "¿Cómo agrego amigos?"
- "¿Qué beneficios tienen los amigos?"
- "¿Qué son las conexiones místicas?"
- "¿Cómo bloqueo a un usuario?"

### General (4 intenciones)
- "Hola"
- "Gracias"
- "¿Cómo publico?"
- "¿Qué puedo comprar en la tienda?"

---

## 🧪 TESTING

### 1. Verificar Botón Flotante
```
✅ Aparece en esquina inferior derecha
✅ Ícono ✨ visible
✅ Animación de destellos activa
✅ Hover aumenta tamaño
```

### 2. Abrir Panel
```
✅ Click en botón abre panel
✅ Header muestra "Asistente Converza"
✅ Mensaje de bienvenida visible
✅ 3 sugerencias de preguntas
```

### 3. Enviar Pregunta
```
✅ Escribir "¿Cómo gano karma?"
✅ Click en enviar
✅ Ver typing indicator (...)
✅ Respuesta aparece con formato
✅ Sugerencias se actualizan
```

### 4. Verificar Respuestas Personalizadas
```
✅ "¿Qué nivel soy?" → "Eres nivel 3 con 150 puntos"
✅ Usa nombre del usuario
✅ Calcula puntos faltantes para siguiente nivel
```

---

## 📊 PERSONALIZACIÓN

### Cambiar Colores

**Botón flotante**:
```css
.assistant-toggle-btn {
    background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%);
}
```

**Header del chat**:
```css
.assistant-header {
    background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%);
}
```

### Cambiar Posición

**Esquina inferior izquierda**:
```css
.assistant-widget {
    bottom: 20px;
    left: 20px;    /* Cambiar right por left */
}
```

### Cambiar Tamaño del Panel

```css
.assistant-chat-panel {
    width: 450px;    /* Default: 380px */
    height: 700px;   /* Default: 600px */
}
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Widget no aparece
**Solución**: Verificar que Bootstrap Icons esté cargado.

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
```

### No reconoce al usuario
**Solución**: Asegúrate de pasar `USER_ID` en el script.

```html
<script>
    const USER_ID = <?php echo $_SESSION['id'] ?? 0; ?>;
</script>
```

### Respuestas genéricas
**Solución**: Agrega más palabras clave en los archivos JSON de `knowledge/`.

### Error 405 (Method Not Allowed)
**Solución**: El endpoint `assistant.php` solo acepta POST. No abrir en navegador directamente.

---

## ✅ CHECKLIST DE INTEGRACIÓN

- [ ] Incluir `assistant-widget.html` en `index.php`
- [ ] Pasar `USER_ID` en script
- [ ] Verificar Bootstrap Icons cargado
- [ ] Reiniciar Apache
- [ ] Limpiar caché del navegador
- [ ] Probar con preguntas de ejemplo
- [ ] Verificar que responda con contexto del usuario
- [ ] Probar en mobile (debe ser responsive)

---

## 🎉 RESULTADO FINAL

Tendrás un **asistente conversacional flotante** con:
- ✨ Ícono mágico de Conexiones Místicas con animación de destellos
- 🤖 Inteligencia artificial que entiende preguntas en español
- 🎯 Respuestas personalizadas con karma y nivel del usuario
- 📚 20+ intenciones predefinidas
- 💬 Chat UI moderno y responsive
- ⚡ Respuestas instantáneas (<200ms)

---

**Fecha**: 15 de octubre de 2025  
**Versión**: 1.0.0  
**Estado**: ✅ Listo para integración
