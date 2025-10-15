# 🚀 INSTRUCCIONES RÁPIDAS - DEBUGGING ASISTENTE

## ⚡ ACCIÓN INMEDIATA

### **PASO 1: Probar el Endpoint** 🧪

Abre esta URL en tu navegador:
```
http://localhost/Converza/test-assistant-api.html
```

Haz clic en **"Probar Endpoint"**

**✅ SI FUNCIONA**: Verás un mensaje verde con la respuesta del servidor  
**❌ SI NO FUNCIONA**: Verás un mensaje rojo con el error

---

### **PASO 2: Revisar Consola del Navegador** 🔍

1. Abre la página principal de Converza (donde está el asistente)
2. Presiona **F12** (abre DevTools)
3. Ve a la pestaña **Console**
4. Busca estos mensajes:

```javascript
🔍 ASSISTANT_USER_DATA: {id: 1, nombre: "Sebastian", foto: "..."}
🔍 userId: 1
🔍 userName: Sebastian
👤 Usuario: Sebastian (ID: 1)
```

**SI VES ESTO**: ✅ Los datos se están cargando correctamente  
**SI VES `Usuario` EN LUGAR DE TU NOMBRE**: ⚠️ La sesión no tiene tu nombre

---

### **PASO 3: Enviar una Pregunta** 💬

1. Abre el asistente (botón flotante ✨)
2. Escribe: **"¿Cómo gano karma?"**
3. Presiona Enter
4. **Mira la consola (F12)**

**Busca estos logs:**
```javascript
📤 Enviando pregunta al servidor: ¿Cómo gano karma?
📤 Endpoint: /Converza/app/microservices/converza-assistant/api/assistant.php
📤 User ID: 1
📥 Response status: 200
📥 Response ok: true
📥 Response data: {success: true, answer: "..."}
```

**✅ SI VES ESTO**: El servidor responde correctamente  
**❌ SI VES ERROR**: Copia el mensaje de error completo

---

## 🐛 ERRORES COMUNES Y SOLUCIONES

### **Error: "Failed to fetch"**

**Causa**: Apache no está corriendo o el archivo no existe

**Solución**:
1. Abre XAMPP Control Panel
2. Verifica que Apache esté en **verde** (Running)
3. Si no, haz clic en **Start**

---

### **Error: "HTTP error! status: 404"**

**Causa**: El archivo assistant.php no existe en la ruta especificada

**Solución**:
1. Verifica que existe el archivo:
   ```
   C:\xampp\htdocs\Converza\app\microservices\converza-assistant\api\assistant.php
   ```
2. Si no existe, algo se movió o borró

---

### **Error: "HTTP error! status: 500"**

**Causa**: Error de PHP en el servidor

**Solución**:
1. Abre los logs de PHP:
   ```
   C:\xampp\php\logs\php_error_log
   ```
2. Busca la última línea con "Fatal error" o "Parse error"
3. Copia el error completo

---

### **Saludo muestra "Usuario" en lugar del nombre**

**Causa**: No hay sesión activa o falta el nombre en la sesión

**Solución**:
1. Verifica que estás **logueado** en Converza
2. Recarga la página (Ctrl+R)
3. Abre consola (F12) y busca:
   ```javascript
   🔍 ASSISTANT_USER_DATA: {id: ..., nombre: "..."}
   ```
4. Si `nombre: "Usuario"`, el problema está en PHP

**Verificación adicional**:
1. Crea archivo `test-session.php` en la raíz:
   ```php
   <?php
   session_start();
   echo "<pre>";
   print_r($_SESSION);
   echo "</pre>";
   ?>
   ```
2. Abre: `http://localhost/Converza/test-session.php`
3. Debe mostrar tu ID y nombre

---

## 📋 CHECKLIST RÁPIDO

Marca cada uno que funcione:

- [ ] Apache está corriendo (XAMPP verde)
- [ ] Puedo abrir `http://localhost/Converza`
- [ ] Estoy logueado en Converza
- [ ] `test-assistant-api.html` muestra ✅ verde
- [ ] Consola (F12) muestra mi nombre real (no "Usuario")
- [ ] Consola muestra `ASSISTANT_USER_DATA` con mis datos
- [ ] Al enviar pregunta, veo logs `📤 Enviando...`
- [ ] Al enviar pregunta, veo logs `📥 Response status: 200`
- [ ] El asistente responde correctamente (no "error de conexión")

---

## 🎯 SI TODO LO ANTERIOR ESTÁ BIEN PERO AÚN NO FUNCIONA

**Último recurso: Reiniciar Apache**

1. XAMPP Control Panel
2. Clic en **Stop** en Apache
3. Esperar 5 segundos
4. Clic en **Start** en Apache
5. Limpiar caché del navegador (Ctrl+Shift+Delete)
6. Recargar página (Ctrl+R)

---

## 📸 CÓMO DEBE VERSE CUANDO FUNCIONA

### **Consola del Navegador (F12)**:
```
🤖 Converza Assistant initialized
🔍 ASSISTANT_USER_DATA: {id: 1, nombre: "Sebastian", foto: "/Converza/..."}
🔍 userId: 1
🔍 userName: Sebastian
🔍 userPhoto: /Converza/app/uploads/usuarios/1/foto.jpg
👤 Usuario: Sebastian (ID: 1)
```

### **Al Enviar Pregunta**:
```
📤 Enviando pregunta al servidor: ¿Cómo gano karma?
📤 Endpoint: /Converza/app/microservices/converza-assistant/api/assistant.php
📤 User ID: 1
📥 Response status: 200
📥 Response ok: true
📥 Response data: {success: true, answer: "¡Hola Sebastian! Puedes ganar karma...", ...}
```

### **Chat del Asistente**:
```
[⭐] Asistente: ¡Hola Sebastian! 👋 Soy el asistente...
              Sebastian [📸]: ¿Cómo gano karma?
[⭐] Asistente: Puedes ganar karma de varias formas:
               ✅ Publicando contenido → +3 puntos
               ✅ Comentando en publicaciones → +2 puntos
               ...
```

---

## 🆘 NECESITAS AYUDA

Si después de hacer todo esto sigues teniendo problemas:

1. **Toma screenshot de:**
   - Consola del navegador (F12) con los errores
   - Resultado de `test-assistant-api.html`
   - Última línea del log de PHP

2. **Revisa estos archivos:**
   - `DEBUGGING_ASISTENTE.md` (guía completa)
   - `CHAT_FOTOS_PERFIL_IMPLEMENTADO.md` (documentación técnica)

3. **Verifica estructura de archivos:**
   ```
   Converza/
   ├── app/
   │   └── microservices/
   │       └── converza-assistant/
   │           ├── api/
   │           │   └── assistant.php ✓
   │           ├── engine/
   │           │   ├── IntentClassifier.php ✓
   │           │   ├── ResponseGenerator.php ✓
   │           │   └── ContextManager.php ✓
   │           └── widget/
   │               ├── assistant-widget.php ✓
   │               ├── assistant-widget.html ✓
   │               ├── assistant-widget.js ✓
   │               └── assistant-widget.css ✓
   ```

---

**Recuerda**: Los logs en consola (F12) son tu mejor amigo para debugging 🔍

**Fecha:** 15 de octubre de 2025  
**Estado:** Testing en progreso 🧪

