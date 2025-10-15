# 🔧 DEBUGGING ASISTENTE CONVERZA

## ❌ PROBLEMA ACTUAL

**Síntomas**:
- El asistente muestra: "Lo siento, no pude conectarme al servidor"
- No responde a las preguntas
- El saludo aparece como "¡Hola Usuario!" en lugar del nombre real

---

## 🧪 PASOS PARA DIAGNOSTICAR

### **1. Verificar Apache y PHP**

```bash
# Abrir XAMPP Control Panel
# Verificar que Apache está en "Running" (verde)
# Verificar que MySQL está en "Running" (verde)
```

Si Apache no está corriendo:
1. Hacer clic en "Start" para Apache
2. Si falla, revisar logs en XAMPP Control Panel → Logs → Apache Error Log

---

### **2. Probar el Endpoint API**

**Opción A: Usar archivo de test**

1. Abrir en navegador: `http://localhost/Converza/test-assistant-api.html`
2. Hacer clic en "Probar Endpoint"
3. Ver el resultado:
   - ✅ Verde = Funciona
   - ❌ Rojo = No funciona

**Opción B: Usar consola del navegador**

```javascript
// Abrir consola (F12) y pegar esto:
fetch('/Converza/app/microservices/converza-assistant/api/assistant.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        question: '¿Cómo gano karma?',
        user_id: 0
    })
})
.then(r => r.json())
.then(d => console.log('Respuesta:', d))
.catch(e => console.error('Error:', e));
```

**Resultado esperado**:
```json
{
    "success": true,
    "answer": "¡Hola Usuario! Puedes ganar karma de varias formas...",
    "intent": "karma_gain",
    "confidence": 85,
    "suggestions": [...],
    "context": {...}
}
```

**Si falla**:
- Error 404 → El archivo no existe o la ruta es incorrecta
- Error 500 → Error de PHP (revisar logs)
- CORS error → Problema de headers (ya están configurados)
- Network error → Apache no está corriendo

---

### **3. Verificar Sesión PHP**

**Abrir archivo de prueba de sesión:**

Crear `test-session.php` en la raíz de Converza:

```php
<?php
session_start();

echo "<h1>Test de Sesión</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "Contenido de \$_SESSION:\n";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['id'])) {
    echo "<p style='color:red;'>⚠️ No hay usuario logueado</p>";
    echo "<p><a href='/Converza/app/view/login.php'>Ir a Login</a></p>";
} else {
    echo "<p style='color:green;'>✅ Usuario logueado: " . $_SESSION['nombre'] . "</p>";
}
?>
```

**Luego**:
1. Abrir `http://localhost/Converza/test-session.php`
2. Verificar que muestra tu ID y nombre
3. Si no aparece, hacer login primero

---

### **4. Revisar Logs de PHP**

**Windows (XAMPP)**:
```
C:\xampp\php\logs\php_error_log
C:\xampp\apache\logs\error.log
```

**Buscar líneas con**:
- `🔍 Assistant Widget` → Debug de carga del widget
- `❌ Assistant Error` → Errores del asistente
- `Fatal error` → Errores críticos de PHP

---

### **5. Revisar Consola del Navegador**

1. Abrir página con el asistente
2. Presionar **F12** para abrir DevTools
3. Ir a pestaña **Console**
4. Buscar mensajes:

**✅ Mensajes esperados:**
```
🤖 Converza Assistant initialized
🔍 ASSISTANT_USER_DATA: {id: 1, nombre: "Sebastian", foto: "..."}
🔍 userId: 1
🔍 userName: Sebastian
🔍 userPhoto: /Converza/...
👤 Usuario: Sebastian (ID: 1)
```

**❌ Mensajes de error:**
```
❌ Assistant Error: Failed to fetch
❌ Error details: {message: "...", endpoint: "..."}
```

**Si `ASSISTANT_USER_DATA` es `undefined`**:
→ El PHP no está pasando los datos correctamente

**Si `userName` es "Usuario"**:
→ La sesión no tiene el nombre del usuario

---

### **6. Verificar Archivos Existen**

```powershell
# En PowerShell
Test-Path "C:\xampp\htdocs\Converza\app\microservices\converza-assistant\api\assistant.php"
Test-Path "C:\xampp\htdocs\Converza\app\microservices\converza-assistant\widget\assistant-widget.php"
Test-Path "C:\xampp\htdocs\Converza\app\microservices\converza-assistant\widget\assistant-widget.js"
Test-Path "C:\xampp\htdocs\Converza\app\microservices\converza-assistant\engine\IntentClassifier.php"
Test-Path "C:\xampp\htdocs\Converza\app\microservices\converza-assistant\engine\ResponseGenerator.php"
```

**Todos deben retornar `True`**

---

### **7. Verificar Permisos de Archivos**

En Windows, verificar que:
- Usuario `SYSTEM` tiene permisos de lectura
- Usuario `Everyone` tiene permisos de lectura (opcional)
- Apache puede leer los archivos

**Clic derecho en carpeta** → **Propiedades** → **Seguridad**

---

## 🔍 CHECKLIST DE DIAGNÓSTICO

- [ ] Apache está corriendo en XAMPP
- [ ] MySQL está corriendo en XAMPP
- [ ] Puedo acceder a `http://localhost/Converza`
- [ ] Usuario está logueado (sesión activa)
- [ ] `test-assistant-api.html` muestra ✅ verde
- [ ] `test-session.php` muestra mi nombre
- [ ] Consola del navegador muestra datos de usuario
- [ ] No hay errores 404 o 500 en Network tab
- [ ] Logs de PHP no muestran errores fatales
- [ ] Todos los archivos PHP existen
- [ ] Permisos de archivos correctos

---

## 🛠️ SOLUCIONES COMUNES

### **Problema: "Lo siento, no pude conectarme al servidor"**

**Causa 1: Apache no está corriendo**
```
Solución: Abrir XAMPP → Start Apache
```

**Causa 2: Ruta del endpoint incorrecta**
```javascript
// Verificar en assistant-widget.js línea 18:
const API_ENDPOINT = '/Converza/app/microservices/converza-assistant/api/assistant.php';

// Debe coincidir con la estructura de carpetas
```

**Causa 3: Error de sintaxis en assistant.php**
```
Solución: Revisar logs de PHP
Buscar: Parse error, Fatal error
```

**Causa 4: Archivos de engine faltantes**
```
Solución: Verificar que existen:
- IntentClassifier.php
- ResponseGenerator.php
- ContextManager.php
```

---

### **Problema: Saludo muestra "Usuario" en lugar del nombre real**

**Causa 1: No hay sesión activa**
```
Solución: Hacer login en la aplicación
```

**Causa 2: Sesión no tiene campo 'nombre'**
```php
// Verificar en login.php que se guarda:
$_SESSION['nombre'] = $usuario['nombre'];
```

**Causa 3: Widget se carga antes de session_start()**
```php
// Verificar en archivo que incluye el widget:
session_start(); // Debe estar ANTES de incluir el widget
require_once('path/to/assistant-widget.php');
```

---

### **Problema: Foto de perfil no aparece**

**Causa 1: Ruta de foto incorrecta**
```javascript
// Verificar en consola:
console.log('🔍 userPhoto:', userPhoto);

// Debe mostrar: /Converza/app/uploads/usuarios/1/foto.jpg
```

**Causa 2: Archivo de foto no existe**
```
Solución: Verificar que el archivo existe en:
C:\xampp\htdocs\Converza\app\uploads\usuarios\[ID]\foto.jpg
```

**Causa 3: Error en onerror handler**
```javascript
// Verificar que existe:
/Converza/app/static/img/default-avatar.png
```

---

## 📞 CONTACTO DE SOPORTE

Si después de seguir todos estos pasos el problema persiste:

1. **Recopilar información**:
   - Screenshot de la consola del navegador (F12)
   - Logs de PHP (últimas 50 líneas)
   - Resultado de `test-assistant-api.html`
   - Resultado de `test-session.php`

2. **Revisar documentación**:
   - `CHAT_FOTOS_PERFIL_IMPLEMENTADO.md`
   - `MEJORAS_ASISTENTE_CONVERZA.md`
   - `ERRORES_LINTER_PHP_HTML.md`

3. **Reiniciar servicios**:
   ```
   XAMPP Control Panel → Stop Apache → Stop MySQL
   Esperar 5 segundos
   XAMPP Control Panel → Start Apache → Start MySQL
   ```

4. **Limpiar caché**:
   ```
   Navegador: Ctrl+Shift+Delete → Borrar caché
   Recargar: Ctrl+R (o F5)
   ```

---

**Fecha:** 15 de octubre de 2025  
**Autor:** GitHub Copilot  
**Versión:** 1.0

