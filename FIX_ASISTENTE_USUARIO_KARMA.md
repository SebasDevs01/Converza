# 🔧 FIX: Asistente - Usuario y Karma

## 🎯 Problemas Identificados

### 1️⃣ **Backend devuelve "Invitado" en lugar del usuario real**
- **Síntoma**: JavaScript envía `user_id: 20`, pero API responde con `"user_name": "Invitado"` y `"user_karma": 0`
- **Causa Probable**: 
  - La variable `$conexion` no está disponible en el scope de `ContextManager`
  - El campo de la BD se llama `avatar`, no `foto_perfil`
  - Puede haber error en la query SQL

### 2️⃣ **Foto de perfil no se carga**
- **Síntoma**: 
  - En consola se ve: `Foto: /Converza/public/avatars/defect.jpg` (inicial)
  - Luego en logs de PHP: `Foto: /Converza/public/uploads/20.jpg` (correcto)
  - Error 404: `GET http://localhost/Converza/public/uploads/20.jpg 404 (Not Found)`
- **Causa**: 
  - La sesión tiene `$_SESSION['avatar'] = '20.jpg'` (solo el nombre)
  - El código construye la ruta como `/Converza/public/uploads/20.jpg`
  - Pero el archivo real está en otra ubicación

### 3️⃣ **Karma no se actualiza en tiempo real**
- **Síntoma**: Animaciones de reacciones funcionan, pero contador no se actualiza
- **Causa**: Sistema de karma funciona pero no se refleja en el contexto del asistente

---

## 🔍 DIAGNÓSTICO PASO A PASO

### Paso 1: Revisar logs de Apache

1. Abre `C:\xampp\apache\logs\error.log`
2. Busca las líneas que empiezan con:
   ```
   🔍 ContextManager: getUserContext llamado con userId = 20
   ```
3. Anota **exactamente** qué mensaje aparece después

### Paso 2: Verificar ubicación real de la foto

1. Navega a: `C:\xampp\htdocs\Converza\public\uploads\`
2. ¿Existe el archivo `20.jpg`?
   - ✅ **SÍ**: El problema es la ruta en el código
   - ❌ **NO**: Busca en `C:\xampp\htdocs\Converza\public\avatars\`

### Paso 3: Verificar datos en la BD

Ejecuta en phpMyAdmin:
```sql
SELECT id_use, usuario, email, avatar FROM usuarios WHERE id_use = 20;
```

Anota el valor de la columna `avatar`:
- ¿Es `20.jpg`?
- ¿Es `public/uploads/20.jpg`?
- ¿Es `defect.jpg`?

---

## 🛠️ SOLUCIONES

### Solución 1: Si $conexion no está disponible

El problema es que `require_once` dentro de una función no hace que la variable `$conexion` esté disponible.

**Cambio necesario en `ContextManager.php`:**

```php
// ANTES (mal):
require_once($configPath);
global $conexion;

// DESPUÉS (bien):
global $conexion;
require_once($configPath);
```

### Solución 2: Si la foto existe pero en otra ruta

**Cambio necesario en `perfil.php`, `index.php`, `albumes.php`:**

```php
// Verificar dónde está realmente el archivo
if (file_exists(__DIR__ . '/../../public/uploads/' . $_SESSION['avatar'])) {
    echo '/Converza/public/uploads/' . $_SESSION['avatar'];
} elseif (file_exists(__DIR__ . '/../../public/avatars/' . $_SESSION['avatar'])) {
    echo '/Converza/public/avatars/' . $_SESSION['avatar'];
} else {
    echo '/Converza/public/avatars/defect.jpg';
}
```

### Solución 3: Si el campo avatar en BD tiene ruta completa

Si en la BD `avatar = 'public/uploads/20.jpg'`, entonces:

```php
// En perfil.php, index.php, albumes.php
if (isset($_SESSION['avatar']) && $_SESSION['avatar'] !== 'defect.jpg') {
    // Si ya tiene "public/" en el valor, solo agregar /Converza/
    if (strpos($_SESSION['avatar'], 'public/') === 0) {
        echo '/Converza/' . $_SESSION['avatar'];
    } else {
        // Si es solo el nombre del archivo
        echo '/Converza/public/uploads/' . $_SESSION['avatar'];
    }
}
```

---

## 📋 CHECKLIST DE VERIFICACIÓN

- [ ] Logs de Apache muestran que `$conexion` está disponible
- [ ] Query SQL se ejecuta correctamente y devuelve datos del usuario
- [ ] Foto de perfil se carga sin error 404
- [ ] Asistente muestra el nombre correcto del usuario
- [ ] Asistente muestra el karma correcto del usuario
- [ ] Karma se actualiza cuando el usuario gana puntos

---

## 🚀 PRÓXIMOS PASOS

1. **Recarga la página** con Ctrl+F5
2. **Envía una pregunta** al asistente
3. **Revisa los logs de Apache** y anota los mensajes
4. **Comparte esos logs** para diagnóstico preciso
5. **Verificar ubicación física** del archivo `20.jpg`

