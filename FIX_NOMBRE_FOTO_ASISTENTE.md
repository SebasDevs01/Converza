# 🔧 FIX: Nombre y Foto del Usuario en Asistente

**Fecha:** 15 de octubre de 2025  
**Estado:** ✅ COMPLETADO

---

## 🐛 Problema Identificado

El asistente mostraba:
- ❌ Texto genérico "Usuario" en lugar del nombre real
- ❌ Icono por defecto en lugar de la foto de perfil
- ❌ Las variables PHP no se pasaban correctamente al JavaScript

---

## ✅ Solución Aplicada

### 1. **Variables JavaScript Globales** (index.php, perfil.php, albumes.php)

Se agregaron las variables globales para pasar los datos del usuario al widget:

```javascript
window.USER_ID = <?php echo isset($_SESSION['id']) ? intval($_SESSION['id']) : 0; ?>;
window.USER_NAME = "<?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre'], ENT_QUOTES) : 'Usuario'; ?>";
window.USER_PHOTO = "<?php 
    if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
        echo htmlspecialchars('/Converza/' . $_SESSION['foto_perfil'], ENT_QUOTES);
    } else {
        echo '/Converza/app/static/img/default-avatar.png';
    }
?>";
```

### 2. **Actualización del JavaScript del Widget** (assistant-widget.js)

Se actualizó la lógica de fallback para usar las nuevas variables:

```javascript
const userId = window.ASSISTANT_USER_DATA?.id || window.USER_ID || 0;
const userName = window.ASSISTANT_USER_DATA?.nombre || window.USER_NAME || 'Usuario';
const userPhoto = window.ASSISTANT_USER_DATA?.foto || window.USER_PHOTO || '/Converza/app/static/img/default-avatar.png';

// Debug mejorado
console.log('🤖 Datos del usuario para el asistente:');
console.log('   ID:', userId);
console.log('   Nombre:', userName);
console.log('   Foto:', userPhoto);
```

### 3. **Ajustes Visuales CSS** (assistant-widget.css)

```css
/* Avatar más grande */
.message-avatar {
    width: 36px;
    height: 36px;
}

/* Espaciado optimizado */
.assistant-message {
    gap: 6px;
}

/* Nombre más visible */
.message-name {
    font-size: 13px;
    font-weight: 600;
    color: #4b5563;
    margin-left: 2px;
    margin-bottom: 4px;
}
```

---

## 🎯 Jerarquía de Variables

El sistema ahora busca los datos del usuario en este orden:

1. **`window.ASSISTANT_USER_DATA`** (desde PHP del widget)
2. **`window.USER_ID/NAME/PHOTO`** (desde configuración global)
3. **Valores por defecto** (Usuario, foto default)

---

## 📁 Archivos Modificados

1. ✅ `/app/view/index.php` - Variables globales agregadas
2. ✅ `/app/presenters/perfil.php` - Variables globales agregadas
3. ✅ `/app/presenters/albumes.php` - Variables globales agregadas
4. ✅ `/app/microservices/converza-assistant/widget/assistant-widget.js` - Lógica mejorada
5. ✅ `/app/microservices/converza-assistant/widget/assistant-widget.css` - Estilos ajustados

---

## 🔍 Cómo Verificar

1. **Recargar página** con `Ctrl + F5`
2. **Abrir consola** (F12)
3. **Buscar en consola:**
   ```
   ✨ Asistente Converza iniciado
      Usuario ID: [tu_id]
      Nombre: [tu_nombre]
      Foto: [ruta_a_tu_foto]
   
   🤖 Datos del usuario para el asistente:
      ID: [tu_id]
      Nombre: [tu_nombre]
      Foto: [ruta_a_tu_foto]
   ```
4. **Abrir asistente** y enviar un mensaje
5. **Verificar que aparece:**
   - ✅ Tu foto de perfil (no icono genérico)
   - ✅ Tu nombre real (no "Usuario")

---

## 🎨 Resultado Visual

### Antes:
```
👤 Usuario
   ¿Cómo gano karma?
```

### Después:
```
[🖼️ Foto]  Tu Nombre
           ¿Cómo gano karma?
```

---

## 🐛 Script de Diagnóstico

Si hay problemas, usar:
```
http://localhost/Converza/debug_assistant_session.php
```

Este script muestra:
- Datos de sesión actual
- Variables del widget
- Vista previa de la foto
- Datos en consola JavaScript

---

## ✨ Beneficios

1. ✅ **Personalización completa** - Muestra datos reales del usuario
2. ✅ **Múltiples fallbacks** - Siempre funciona aunque falte algún dato
3. ✅ **Debug mejorado** - Fácil detectar problemas
4. ✅ **Diseño consistente** - Similar al chat de mensajería
5. ✅ **Sin romper funcionalidad** - Todo sigue funcionando igual

---

✨ **¡Todo listo para usar!**
