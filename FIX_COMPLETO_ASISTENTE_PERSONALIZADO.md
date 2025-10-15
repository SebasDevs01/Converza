# 🔧 FIX COMPLETO: Nombre y Foto Real del Usuario en Asistente

**Fecha:** 15 de octubre de 2025  
**Estado:** ✅ COMPLETADO Y FUNCIONANDO

---

## 🐛 Problema Original

El asistente mostraba:
- ❌ "Invitado" en las respuestas en lugar del nombre real
- ❌ Icono genérico en lugar de la foto de perfil
- ❌ El backend obtenía los datos pero no los enviaba al frontend

---

## ✅ Solución Completa Aplicada

### 1. **Backend - ContextManager.php**

Agregado consulta de `foto_perfil` de la base de datos:

```php
// Obtener datos del usuario incluyendo foto
$stmt = $conexion->prepare("SELECT usuario, email, foto_perfil FROM usuarios WHERE id_use = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Determinar foto de perfil
$fotoPerfil = '/Converza/app/static/img/default-avatar.png';
if (!empty($user['foto_perfil'])) {
    $fotoPerfil = '/Converza/' . $user['foto_perfil'];
}

return [
    'user_id' => $userId,
    'username' => $user['usuario'],
    'email' => $user['email'],
    'foto_perfil' => $fotoPerfil,  // ← NUEVO
    'karma' => $karmaData['karma_total'] ?? 0,
    // ... resto de datos
];
```

También actualizado el contexto de invitado:

```php
private function getGuestContext() {
    return [
        'user_id' => 0,
        'username' => 'Invitado',
        'email' => null,
        'foto_perfil' => '/Converza/app/static/img/default-avatar.png',  // ← NUEVO
        // ... resto de datos
    ];
}
```

### 2. **API - assistant.php**

Agregado `user_photo` en la respuesta del API:

```php
echo json_encode([
    'success' => true,
    'answer' => $response['answer'],
    'intent' => $intent['name'],
    'confidence' => $intent['confidence'],
    'suggestions' => $response['suggestions'] ?? [],
    'links' => $response['links'] ?? [],
    'context' => [
        'user_karma' => $userContext['karma'] ?? 0,
        'user_level' => $userContext['nivel'] ?? 1,
        'user_name' => $userContext['username'] ?? 'Usuario',
        'user_photo' => $userContext['foto_perfil'] ?? '/Converza/app/static/img/default-avatar.png'  // ← NUEVO
    ]
]);
```

### 3. **Frontend - assistant-widget.js**

#### A. Actualización automática desde el API:

```javascript
.then(data => {
    hideTyping();
    
    if (data.success) {
        // Actualizar datos del usuario si vienen en el contexto
        if (data.context) {
            if (data.context.user_name && data.context.user_name !== 'Usuario') {
                window.USER_NAME = data.context.user_name;
                console.log('✅ Nombre actualizado desde API:', window.USER_NAME);
            }
            if (data.context.user_photo) {
                window.USER_PHOTO = data.context.user_photo;
                console.log('✅ Foto actualizada desde API:', window.USER_PHOTO);
            }
        }
        
        // ... resto del código
    }
});
```

#### B. Uso dinámico en mensajes:

```javascript
function addMessage(text, type) {
    // ... código previo ...
    
    if (type === 'user') {
        // Obtener datos actualizados desde variables globales
        const currentUserName = window.USER_NAME || userName;
        const currentUserPhoto = window.USER_PHOTO || userPhoto;
        
        messageDiv.innerHTML = `
            <div class="message-avatar">
                <img src="${currentUserPhoto}" alt="${currentUserName}" 
                     onerror="this.src='/Converza/app/static/img/default-avatar.png'">
            </div>
            <div class="message-content">
                <div class="message-name">${currentUserName}</div>
                <div class="message-bubble">${text}</div>
                <div class="message-time">${time}</div>
            </div>
        `;
    }
    // ...
}
```

#### C. Mensaje de bienvenida personalizado:

```javascript
// Cargar mensaje de bienvenida con el nombre actual
const currentUserName = window.USER_NAME || userName;
const welcomeMessage = `¡Hola <strong>${currentUserName}</strong>! 👋 Soy el asistente de Converza.

Puedo ayudarte con:

• 🎯 Sistema de Karma
• 😊 Reacciones
• 🔔 Notificaciones
• 👥 Amigos y conexiones
• 🛍️ Tienda

¿En qué puedo ayudarte?`;

addMessage(welcomeMessage, 'assistant');
```

---

## 🎯 Flujo de Datos Completo

### Carga Inicial (PHP):
```
1. index.php/perfil.php → Define window.USER_NAME y window.USER_PHOTO
2. assistant-widget.html → Define window.ASSISTANT_USER_DATA desde PHP
3. assistant-widget.js → Usa ambas fuentes como fallback
```

### Durante Conversación (API):
```
1. Usuario envía pregunta
2. API consulta BD → Obtiene nombre y foto
3. API devuelve respuesta + context con user_name y user_photo
4. Widget actualiza window.USER_NAME y window.USER_PHOTO
5. Próximos mensajes usan datos actualizados
```

### Jerarquía de Fallback:
```javascript
const currentUserName = window.USER_NAME || window.ASSISTANT_USER_DATA?.nombre || 'Usuario';
const currentUserPhoto = window.USER_PHOTO || window.ASSISTANT_USER_DATA?.foto || '/default-avatar.png';
```

---

## 📁 Archivos Modificados

1. ✅ `/app/microservices/converza-assistant/engine/ContextManager.php`
   - Agregada consulta de `foto_perfil`
   - Actualizado contexto de invitado

2. ✅ `/app/microservices/converza-assistant/api/assistant.php`
   - Agregado `user_photo` en respuesta JSON

3. ✅ `/app/microservices/converza-assistant/widget/assistant-widget.js`
   - Actualización automática de datos desde API
   - Uso dinámico de variables globales en mensajes
   - Mensaje de bienvenida personalizado

4. ✅ `/app/view/index.php` (cambio previo)
   - Variables globales window.USER_NAME y window.USER_PHOTO

5. ✅ `/app/presenters/perfil.php` (cambio previo)
   - Variables globales window.USER_NAME y window.USER_PHOTO

6. ✅ `/app/presenters/albumes.php` (cambio previo)
   - Variables globales window.USER_NAME y window.USER_PHOTO

---

## 🔍 Cómo Verificar que Funciona

### 1. Abrir Consola del Navegador (F12)

Deberías ver:
```
✨ Asistente Converza iniciado
   Usuario ID: 1
   Nombre: TuNombre
   Foto: /Converza/app/upload/perfil/tu_foto.jpg

🤖 Datos del usuario para el asistente:
   ID: 1
   Nombre: TuNombre
   Foto: /Converza/app/upload/perfil/tu_foto.jpg
```

### 2. Abrir el Asistente

El mensaje de bienvenida debe decir:
```
¡Hola TuNombre! 👋 Soy el asistente de Converza.
```

### 3. Enviar una Pregunta

Deberías ver:
```
[TU_FOTO] TuNombre
          ¿Cómo gano karma?
```

Y la respuesta debe decir:
```
¡Hola TuNombre! Puedes ganar karma de varias formas...
```

### 4. Verificar Logs del API

Después de enviar una pregunta, en consola verás:
```
📥 Response status: 200
📥 Response text: {"success":true,"answer":"...","context":{"user_name":"TuNombre","user_photo":"..."}}
✅ Nombre actualizado desde API: TuNombre
✅ Foto actualizada desde API: /Converza/app/upload/perfil/tu_foto.jpg
```

---

## 🎨 Resultado Visual

### Mensaje de Bienvenida:
```
[⭐] Asistente Converza
     ¡Hola TuNombre! 👋 Soy el asistente de Converza.
     
     Puedo ayudarte con:
     • 🎯 Sistema de Karma
     ...
```

### Pregunta del Usuario:
```
                              TuNombre [🖼️]
               ¿Cómo gano karma?
```

### Respuesta del Asistente:
```
[⭐] Asistente Converza
     ¡Hola TuNombre! Puedes ganar karma de varias formas:
     
     ✅ Publicando contenido → +3 puntos
     ...
     
     Actualmente tienes 125 puntos y eres nivel 3 🌟 (Explorador).
```

---

## ✨ Características Implementadas

1. ✅ **Nombre real del usuario** en todas las respuestas
2. ✅ **Foto de perfil real** en mensajes del usuario
3. ✅ **Fallback a foto por defecto** si no tiene foto
4. ✅ **Actualización automática** de datos desde el API
5. ✅ **Mensaje de bienvenida personalizado**
6. ✅ **Múltiples fuentes de datos** con fallback robusto
7. ✅ **Logging completo** para debugging
8. ✅ **Sin romper funcionalidad existente**

---

## 🐛 Troubleshooting

### Si sigue mostrando "Usuario" o "Invitado":

1. **Verificar sesión activa:**
   ```
   http://localhost/Converza/debug_assistant_session.php
   ```

2. **Limpiar caché del navegador:**
   ```
   Ctrl + F5
   ```

3. **Verificar logs de PHP:**
   ```
   C:\xampp\apache\logs\error.log
   ```
   Buscar: `Context Manager: Usuario cargado`

4. **Verificar consola JavaScript:**
   Debe mostrar el nombre real, no "Usuario"

---

## 📝 Notas Importantes

- **Backend ya funcionaba** - Solo faltaba enviar los datos al frontend
- **Sistema robusto** - Múltiples fallbacks aseguran que siempre funcione
- **Actualización automática** - Los datos se actualizan con cada respuesta
- **Compatible** - Funciona con usuarios con/sin foto de perfil
- **Sin cambios en BD** - Usa la columna `foto_perfil` existente

---

✨ **¡Ahora el asistente es completamente personalizado!** ✨
