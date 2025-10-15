# 💬📸 CHAT CON FOTOS DE PERFIL - IMPLEMENTADO

## ✅ MEJORAS APLICADAS

### 🎨 **Diseño de Chat Mejorado**

**ANTES**:
- Iconos genéricos para todos los mensajes
- Sin nombre de usuario visible
- Sin foto de perfil personalizada
- Layout básico

**AHORA**:
- ✅ **Foto de perfil del usuario** en mensajes
- ✅ **Nombre del usuario** en cada mensaje
- ✅ **Layout intercalado** (usuario derecha, asistente izquierda)
- ✅ **Foto por defecto** si usuario no tiene foto
- ✅ **Saludo personalizado** con nombre del usuario

---

## 🖼️ CARACTERÍSTICAS

### **1. Foto de Perfil del Usuario** 📸

```
┌─────────────────────────────────┐
│  [Foto]  "Sebastian"           │ <- Nombre
│          "Hola"                 │ <- Mensaje
│          "10:30 AM"             │ <- Hora
└─────────────────────────────────┘
```

- **Fuente**: Tomada de `$_SESSION['foto_perfil']`
- **Fallback**: `/Converza/app/static/img/default-avatar.png`
- **Formato**: Redonda, 32x32px, con `object-fit: cover`
- **Error handling**: Si la foto no carga, muestra foto por defecto

### **2. Layout Intercalado** ↔️

**Mensajes del Asistente** (Izquierda):
```
[⭐] Asistente Converza
     "¡Hola Sebastian! 👋..."
     10:30 AM
```

**Mensajes del Usuario** (Derecha):
```
                  Sebastian [📸]
           "¿Cómo gano karma?"
                      10:31 AM
```

### **3. Información Personalizada** 👤

- **Nombre**: Se muestra en cada mensaje del usuario
- **ID**: Se envía al servidor para contexto
- **Bienvenida**: `¡Hola [NOMBRE]! 👋 Soy el asistente...`

---

## 🔧 ARCHIVOS MODIFICADOS

### **1. assistant-widget.php** (PHP Backend)
```php
// Obtener datos del usuario de la sesión
$foto_perfil_widget = '/Converza/app/static/img/default-avatar.png';
$nombre_usuario_widget = 'Usuario';
$id_usuario_widget = 0;

if (isset($_SESSION['id'])) {
    $id_usuario_widget = $_SESSION['id'];
    $nombre_usuario_widget = $_SESSION['nombre'] ?? 'Usuario';
    
    if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
        $foto_perfil_widget = '/Converza/' . $_SESSION['foto_perfil'];
    }
}
```

**Función**: Extrae datos del usuario desde la sesión PHP

### **2. assistant-widget.html** (HTML Template)
```html
<script>
    window.ASSISTANT_USER_DATA = {
        id: <?php echo $id_usuario_widget; ?>,
        nombre: "<?php echo htmlspecialchars($nombre_usuario_widget); ?>",
        foto: "<?php echo htmlspecialchars($foto_perfil_widget); ?>"
    };
</script>
```

**Función**: Pasa datos de PHP a JavaScript

### **3. assistant-widget.js** (JavaScript Logic)
```javascript
const userName = window.ASSISTANT_USER_DATA?.nombre || 'Usuario';
const userPhoto = window.ASSISTANT_USER_DATA?.foto || '/Converza/app/static/img/default-avatar.png';

// Avatar con foto
if (type === 'user') {
    avatarHTML = `
        <div class="message-avatar user-avatar">
            <img src="${userPhoto}" alt="${userName}" 
                 onerror="this.src='/Converza/app/static/img/default-avatar.png'">
        </div>
    `;
}
```

**Función**: Renderiza foto y nombre en cada mensaje

### **4. assistant-widget.css** (Estilos)
```css
/* Avatar del usuario con foto */
.message-avatar.user-avatar {
    background: #e5e7eb;
    padding: 0;
    overflow: hidden;
}

.message-avatar.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* Mensajes del usuario a la derecha */
.user-message {
    flex-direction: row-reverse;
}

/* Nombre del usuario */
.message-name {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
}
```

**Función**: Estiliza fotos redondas y layout intercalado

---

## 📊 ESTRUCTURA DE MENSAJES

### **Mensaje del Asistente**:
```html
<div class="assistant-message assistant-msg">
    <div class="message-avatar assistant-avatar">
        <i class="bi bi-stars"></i>
    </div>
    <div class="message-content">
        <div class="message-bubble">¡Hola Sebastian! 👋...</div>
        <div class="message-time">10:30 AM</div>
    </div>
</div>
```

### **Mensaje del Usuario**:
```html
<div class="assistant-message user-message">
    <div class="message-avatar user-avatar">
        <img src="/Converza/app/uploads/usuarios/123/foto.jpg" 
             alt="Sebastian">
    </div>
    <div class="message-content">
        <div class="message-name">Sebastian</div>
        <div class="message-bubble">¿Cómo gano karma?</div>
        <div class="message-time">10:31 AM</div>
    </div>
</div>
```

---

## 🧪 EJEMPLO DE CONVERSACIÓN

```
┌─────────────────────────────────────────┐
│ [⭐] Asistente Converza                 │
│      ¡Hola Sebastian! 👋                │
│      Soy el asistente de Converza...    │
│      10:30 AM                           │
│                                         │
│                      Sebastian [📸]    │
│               ¿Cómo gano karma?        │
│                          10:31 AM      │
│                                         │
│ [⭐] Asistente Converza                 │
│      Puedes ganar karma de varias...   │
│      10:31 AM                           │
│                                         │
│                      Sebastian [📸]    │
│                     Gracias            │
│                          10:32 AM      │
└─────────────────────────────────────────┘
```

---

## 🎯 FLUJO DE DATOS

```
PHP SESSION
   ↓
$_SESSION['id'] → $id_usuario_widget
$_SESSION['nombre'] → $nombre_usuario_widget  
$_SESSION['foto_perfil'] → $foto_perfil_widget
   ↓
HTML (assistant-widget.html)
   ↓
<script> window.ASSISTANT_USER_DATA = {...} </script>
   ↓
JavaScript (assistant-widget.js)
   ↓
const userName = window.ASSISTANT_USER_DATA?.nombre
const userPhoto = window.ASSISTANT_USER_DATA?.foto
   ↓
addMessage(text, 'user') → Renderiza con foto + nombre
```

---

## ✅ VALIDACIONES

### **1. Foto de perfil no existe**:
```javascript
onerror="this.src='/Converza/app/static/img/default-avatar.png'"
```
→ Carga foto por defecto automáticamente

### **2. Usuario no logueado**:
```php
$nombre_usuario_widget = 'Usuario'; // Por defecto
$foto_perfil_widget = '/Converza/app/static/img/default-avatar.png';
```
→ Usa valores por defecto

### **3. Sesión sin foto_perfil**:
```php
if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
    $foto_perfil_widget = '/Converza/' . $_SESSION['foto_perfil'];
}
```
→ Solo cambia si existe y no está vacía

### **4. JavaScript sin datos**:
```javascript
const userName = window.ASSISTANT_USER_DATA?.nombre || 'Usuario';
const userPhoto = window.ASSISTANT_USER_DATA?.foto || '/Converza/app/static/img/default-avatar.png';
```
→ Usa optional chaining + fallback

---

## 🎨 ESTILOS RESPONSIVE

### **Desktop (380px ancho)**:
- Foto: 32x32px
- Nombre: 12px, bold
- Bubble: Max 280px ancho
- Layout: Lado a lado

### **Mobile (< 400px)**:
- Panel: `calc(100vw - 40px)`
- Foto: Mantiene 32x32px
- Bubble: Se adapta al ancho
- Layout: Se mantiene intercalado

---

## 📝 CÓMO PROBAR

### **1. Con Usuario Logueado**:
```
1. Iniciar sesión en Converza
2. Abrir asistente (botón flotante ✨)
3. Verificar:
   - Saludo personalizado con tu nombre
   - Tu foto de perfil en mensajes del usuario
   - Tu nombre sobre cada mensaje
```

### **2. Con Foto de Perfil**:
```
1. Usuario con foto subida
2. Abrir asistente
3. Enviar mensaje
4. Verificar que aparece tu foto (no icono genérico)
```

### **3. Sin Foto de Perfil**:
```
1. Usuario nuevo sin foto
2. Abrir asistente
3. Enviar mensaje
4. Verificar que aparece foto por defecto (avatar genérico)
```

### **4. Foto Rota/Invalida**:
```
1. Editar sesión con ruta de foto inválida
2. Abrir asistente
3. Verificar que carga foto por defecto (onerror)
```

---

## 🐛 DEBUGGING

### **Si no aparece la foto**:
```
1. Abrir consola (F12)
2. Verificar: console.log('👤 Usuario:', userName, '(ID:', userId, ')')
3. Verificar window.ASSISTANT_USER_DATA en consola
4. Inspeccionar elemento <img> y ver src
5. Verificar que ruta de foto existe en servidor
```

### **Si aparece "Usuario" genérico**:
```
1. Verificar que $_SESSION['nombre'] existe
2. Verificar que sesión está activa
3. Revisar logs de PHP para errores
```

### **Si no se alinea correctamente**:
```
1. Limpiar caché (Ctrl+Shift+Delete)
2. Verificar que assistant-widget.css se cargó
3. Inspeccionar clases: .user-message, .assistant-msg
```

---

## 📚 REFERENCIAS

**Archivos relacionados**:
- `assistant-widget.php` → Backend (sesión)
- `assistant-widget.html` → Template
- `assistant-widget.js` → Lógica (renderizado)
- `assistant-widget.css` → Estilos (layout)

**Sesiones relacionadas**:
- `$_SESSION['id']` → ID del usuario
- `$_SESSION['nombre']` → Nombre del usuario
- `$_SESSION['foto_perfil']` → Ruta de la foto

**Assets**:
- `/Converza/app/static/img/default-avatar.png` → Foto por defecto
- `/Converza/app/uploads/usuarios/[ID]/foto.jpg` → Fotos de usuarios

---

## ✅ CHECKLIST DE VERIFICACIÓN

- [ ] Foto de perfil aparece en mensajes del usuario
- [ ] Nombre del usuario aparece sobre mensajes
- [ ] Saludo personalizado: "¡Hola [NOMBRE]! 👋"
- [ ] Mensajes del usuario alineados a la derecha
- [ ] Mensajes del asistente alineados a la izquierda
- [ ] Foto redonda (border-radius: 50%)
- [ ] Foto por defecto si no existe
- [ ] `onerror` funciona (carga default-avatar.png)
- [ ] Layout responsive en móvil
- [ ] Sin errores en consola

---

**Fecha:** 15 de octubre de 2025  
**Estado:** ✅ COMPLETADO  
**Archivos modificados:** 4  
**Mejora:** Chat con fotos de perfil personalizado tipo WhatsApp/Messenger

