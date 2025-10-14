# 📤 SISTEMA DE SOLICITUDES SIN REDIRECCIÓN

## ✅ Cambios Implementados

### 🎯 Problema Resuelto
- **ANTES**: Al enviar solicitud de amistad → Redirigía al `index.php`
- **AHORA**: Al enviar solicitud → Se queda en el perfil + muestra tarjeta bonita

---

## 🎨 Diseño de la Tarjeta

### Estado: "Solicitud Enviada"
```
┌─────────────────────────────────┐
│ 🕐 Solicitud Enviada    [❌]    │  ← Tarjeta amarilla
└─────────────────────────────────┘
   Botón warning con icono cancelar
```

**Características:**
- **Color**: Amarillo (btn-warning) como "pendiente"
- **Icono**: 🕐 (bi-clock-history)
- **Botón cancelar**: ❌ integrado
- **Estilo**: Similar al botón de "Siguiendo"

---

## 📁 Archivos Modificados

### 1. **solicitud.php** (Backend)
**Cambios**:
- ❌ Eliminados: `header('Location: ...')`
- ✅ Agregado: Respuestas JSON

**Código modificado:**
```php
// Antes:
echo 'Solicitud enviada correctamente.';
header('Location: /Converza/app/view/index.php');
exit;

// Ahora:
echo json_encode([
    'success' => true,
    'message' => 'Solicitud enviada correctamente'
]);
exit;
```

### 2. **perfil.php** (Frontend)
**Cambios**:
- ❌ Eliminado: `<a href="solicitud.php?...">`
- ✅ Agregado: `<button onclick="enviarSolicitudAmistad(...)">`
- ✅ Nueva función JavaScript: `enviarSolicitudAmistad()`

**Nueva función:**
```javascript
function enviarSolicitudAmistad(usuarioId) {
    // 1. Hace petición AJAX a solicitud.php
    // 2. Recibe respuesta JSON
    // 3. Muestra notificación
    // 4. Actualiza UI con tarjeta "Solicitud Enviada"
}
```

---

## 🔄 Flujo Completo

### Paso a Paso:

1. **Usuario visita perfil** → `perfil.php?id=123`

2. **Ve botón "Añadir Amigo"**:
   ```html
   <button onclick="enviarSolicitudAmistad(123)">
       <i class="bi bi-person-plus"></i> Añadir Amigo
   </button>
   ```

3. **Hace clic** → JavaScript ejecuta `enviarSolicitudAmistad(123)`

4. **AJAX a solicitud.php**:
   ```
   GET /converza/app/presenters/solicitud.php?action=agregar&id=123
   ```

5. **Respuesta JSON**:
   ```json
   {
     "success": true,
     "message": "Solicitud enviada correctamente"
   }
   ```

6. **JavaScript actualiza UI**:
   ```javascript
   container.innerHTML = `
       <div class="btn btn-warning btn-sm">
           🕐 Solicitud Enviada [❌]
       </div>
   `;
   ```

7. **Notificación toast**:
   ```
   ┌──────────────────────────────┐
   │ ✅ Solicitud de amistad      │
   │    enviada                   │
   └──────────────────────────────┘
   ```

8. **Usuario permanece en el perfil** ✨

---

## 🎨 Estados del Botón

### Estado 1: Sin Relación
```html
<button class="btn btn-outline-success btn-sm">
    <i class="bi bi-person-plus"></i> Añadir Amigo
</button>
```
**Color**: Verde outline (success)

### Estado 2: Solicitud Enviada (NUEVO)
```html
<div class="btn btn-warning btn-sm">
    <i class="bi bi-clock-history"></i> Solicitud Enviada
    <button onclick="cancelarSolicitud()">
        <i class="bi bi-x-circle"></i>
    </button>
</div>
```
**Color**: Amarillo (warning)

### Estado 3: Solicitud Recibida
```html
<span class="btn btn-info btn-sm">
    <i class="bi bi-person-check"></i> Solicitud Recibida
</span>
```
**Color**: Azul (info)

### Estado 4: Ya son Amigos
```html
<button class="btn btn-success btn-sm dropdown-toggle">
    <i class="bi bi-people-fill"></i> Amigos
</button>
```
**Color**: Verde (success)

---

## 🧪 Cómo Probar

### Test 1: Enviar Solicitud
1. Cierra sesión y crea 2 usuarios:
   - Usuario A (id=1)
   - Usuario B (id=2)

2. Inicia sesión como Usuario A

3. Ve al perfil de Usuario B:
   ```
   http://localhost/converza/app/presenters/perfil.php?id=2
   ```

4. Haz clic en **"Añadir Amigo"**

5. **Observa**:
   - ✅ Notificación verde: "Solicitud de amistad enviada"
   - ✅ Botón cambia a: "🕐 Solicitud Enviada [❌]"
   - ✅ NO redirige al index
   - ✅ Permaneces en el perfil de Usuario B

### Test 2: Cancelar Solicitud
1. Con la solicitud enviada, haz clic en el botón ❌

2. **Observa**:
   - ✅ Notificación: "Solicitud cancelada"
   - ✅ Botón vuelve a: "Añadir Amigo"
   - ✅ Permaneces en el perfil

### Test 3: Verificar en DB
```sql
USE converza;

-- Ver solicitud creada
SELECT * FROM amigos 
WHERE de = 1 AND para = 2 AND estado = 0;

-- Resultado esperado:
-- de: 1, para: 2, estado: 0, fecha: NOW()
```

---

## 🎯 Comparación Visual

### ❌ ANTES (Con Redirección):
```
[Perfil Usuario B]
   ↓ Clic "Añadir Amigo"
   ↓ Envía solicitud
   ↓ header('Location: index.php')
[Index.php] ← Te sacaba del perfil 😢
```

### ✅ AHORA (Sin Redirección):
```
[Perfil Usuario B]
   ↓ Clic "Añadir Amigo"
   ↓ AJAX a solicitud.php
   ↓ Respuesta JSON
   ↓ Actualiza botón
[Perfil Usuario B] ← ¡Te quedas aquí! 😊
   └─ 🕐 Solicitud Enviada [❌]
```

---

## 🔧 Personalización

### Cambiar color de la tarjeta:
```javascript
// En perfil.php línea ~445
container.innerHTML = `
    <div class="btn btn-warning ...">  // Cambiar a: btn-info, btn-primary, etc.
```

### Cambiar texto:
```javascript
<span>Solicitud Enviada</span>  // Cambiar a: "Pendiente", "En espera", etc.
```

### Cambiar icono:
```javascript
<i class="bi bi-clock-history"></i>  // Cambiar a: bi-hourglass, bi-send, etc.
```

### Cambiar duración de la notificación:
```javascript
// En perfil.php línea ~694
setTimeout(function() {
    alerta.style.opacity = '0';
}, 3000);  // Cambiar a 5000 para 5 segundos
```

---

## ✅ Checklist de Cambios

- [x] `solicitud.php` retorna JSON en lugar de redirigir
- [x] Botón "Añadir Amigo" usa `onclick` en lugar de `href`
- [x] Función `enviarSolicitudAmistad()` implementada
- [x] AJAX envía solicitud sin recargar página
- [x] Tarjeta "Solicitud Enviada" se muestra automáticamente
- [x] Notificación toast aparece en esquina superior derecha
- [x] Botón de cancelar (❌) integrado en la tarjeta
- [x] Usuario permanece en el perfil
- [x] Sistema compatible con estado "Solicitud Recibida"

---

## 🎉 ¡Todo Listo!

El sistema ahora funciona **sin redirección** y con una **tarjeta bonita** que indica que la solicitud fue enviada. 

**¡Pruébalo ahora!** 🚀
