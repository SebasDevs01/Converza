# 🔧 Fix: Karma Popup y Posicionamiento

## 📋 Problemas Solucionados

### 1. ❌ Popup flotante no aparecía
**Problema**: El contador verde "+8" o rojo "-15" no se mostraba cuando se ganaba/perdía karma.

**Causa**: La sesión `$_SESSION['karma_notification']` se eliminaba inmediatamente en la primera llamada AJAX.

**Solución**:
- Modificado `check_karma_notification.php` para usar un sistema de banderas
- Primera llamada: marca como `karma_shown` pero NO elimina
- Segunda llamada: limpia ambas variables
- Añadido logging en consola para debug
- Aumentado timeout de 500ms a 1000ms en `publicaciones.php`

### 2. ❌ Karma no se actualizaba en la tienda
**Problema**: Al ganar karma, el contador de la tienda no se actualizaba en tiempo real.

**Solución**:
- Añadido polling AJAX cada 3 segundos en `karma_tienda.php`
- Función `actualizarKarmaTienda()` que consulta `get_karma.php`
- Animación visual cuando cambia el valor (escala 1.2x, color azul)
- Actualiza también el nivel mostrado

### 3. ❌ Botón de karma mal posicionado
**Problema**: En `perfil.php` y `albumes.php` el botón estaba ANTES del collapse del navbar, lejos de "Inicio".

**Solución**:
- Movido el include de `karma-navbar-badge.php` DENTRO del `<ul class="navbar-nav">`
- Ahora está como primer `<li>` antes de "Inicio"
- Posición consistente con `index.php`

---

## 📁 Archivos Modificados

### 1️⃣ `app/presenters/perfil.php`
```php
<!-- ANTES -->
<a class="navbar-brand">Converza</a>
<?php include 'karma-navbar-badge.php'; ?>
<button class="navbar-toggler">...</button>
<div class="collapse">
  <ul class="navbar-nav">
    <li>Inicio</li>

<!-- DESPUÉS -->
<a class="navbar-brand">Converza</a>
<button class="navbar-toggler">...</button>
<div class="collapse">
  <ul class="navbar-nav">
    <li><?php include 'karma-navbar-badge.php'; ?></li>
    <li>Inicio</li>
```

### 2️⃣ `app/presenters/albumes.php`
- Mismo cambio que `perfil.php`
- Botón ahora dentro del `<ul>` como `<li>`

### 3️⃣ `app/presenters/check_karma_notification.php`
```php
// NUEVO SISTEMA DE BANDERAS
if (isset($_SESSION['karma_notification']) && !isset($_SESSION['karma_shown'])) {
    $response['success'] = true;
    $response['data'] = $_SESSION['karma_notification'];
    $_SESSION['karma_shown'] = true; // Marcar como mostrada
} else if (isset($_SESSION['karma_shown'])) {
    // Limpiar en segunda llamada
    unset($_SESSION['karma_notification']);
    unset($_SESSION['karma_shown']);
}
```

### 4️⃣ `app/view/components/karma-navbar-badge.php`
```javascript
// AÑADIDO LOGGING PARA DEBUG
function verificarKarmaPendiente() {
    console.log('🔍 Verificando karma pendiente...');
    fetch('/converza/app/presenters/check_karma_notification.php')
        .then(response => response.json())
        .then(data => {
            console.log('📨 Respuesta:', data);
            if (data.success && data.data) {
                console.log('🎉 ¡Karma detectado!', data.data);
                mostrarPuntosKarma(data.data.puntos);
                // ...
            } else {
                console.log('ℹ️ No hay karma pendiente');
            }
        });
}
```

### 5️⃣ `app/presenters/publicaciones.php`
```javascript
// AUMENTADO TIMEOUT Y AÑADIDO LOGGING
setTimeout(() => {
    console.log('🔔 Llamando a verificarKarmaPendiente()...');
    window.verificarKarmaPendiente();
}, 1000); // Era 500ms, ahora 1000ms
```

### 6️⃣ `app/presenters/karma_tienda.php`
```javascript
// NUEVO: POLLING CADA 3 SEGUNDOS
document.addEventListener('DOMContentLoaded', function() {
    actualizarKarmaTienda();
    setInterval(actualizarKarmaTienda, 3000);
});

function actualizarKarmaTienda() {
    fetch('/converza/app/presenters/get_karma.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar con animación
                karmaDisplay.textContent = data.karma;
                nivelDisplay.textContent = 'Nivel ' + data.nivel;
            }
        });
}
```

---

## 🧪 Cómo Probar

### Test 1: Popup flotante
1. Ir a `index.php` o `perfil.php`
2. Escribir un comentario positivo (ej: "¡Excelente!")
3. Enviar comentario
4. **Resultado esperado**: 
   - Popup verde "+8" aparece en la esquina superior derecha
   - Número del botón de karma se anima
   - Campana 🔔 muestra nueva notificación

### Test 2: Posicionamiento del botón
1. Navegar entre `index.php`, `perfil.php`, `albumes.php`
2. **Resultado esperado**:
   - Botón de karma siempre está al lado de "Inicio"
   - Misma posición en las 3 páginas
   - Responsive en móviles

### Test 3: Actualización en tienda
1. Abrir `karma_tienda.php` en una pestaña
2. En otra pestaña, comentar algo para ganar karma
3. Volver a la tienda (sin recargar)
4. **Resultado esperado**:
   - Karma se actualiza automáticamente en ~3 segundos
   - Animación de escala cuando cambia
   - Nivel también se actualiza si cambió

---

## 🐛 Debug

### Ver logs en consola
Abrir DevTools (F12) → Console:

```
🔍 Verificando karma pendiente...
📨 Respuesta: {success: true, data: {puntos: 8, tipo: 'ganancia', mensaje: '...'}}
🎉 ¡Karma detectado! {puntos: 8, tipo: 'ganancia', mensaje: '...'}
```

Si NO aparece el popup:
1. Verificar que `karma-navbar-badge.php` está incluido
2. Verificar que `window.verificarKarmaPendiente` existe en console
3. Revisar que `check_karma_notification.php` devuelve datos
4. Confirmar que `mostrarPuntosKarma()` está definida

### Logs en PHP (backend)
En `karma-social-helper.php` línea ~140:
```php
$_SESSION['karma_notification'] = [
    'puntos' => $puntosGanados,
    'tipo' => $puntosGanados > 0 ? 'ganancia' : 'perdida',
    'mensaje' => $mensaje
];
```

---

## ✅ Estado Final

| Problema | Estado | Archivo Principal |
|----------|--------|-------------------|
| Popup no aparece | ✅ RESUELTO | `check_karma_notification.php` |
| Karma no actualiza en tienda | ✅ RESUELTO | `karma_tienda.php` |
| Botón mal posicionado | ✅ RESUELTO | `perfil.php`, `albumes.php` |

---

## 📊 Flujo Completo del Sistema

```
Usuario comenta
    ↓
agregarcomentario.php
    ↓
karma-social-helper.php::registrarAccion()
    ↓
    ├─→ INSERT en karma_social
    ├─→ INSERT en notificaciones (campana 🔔)
    └─→ $_SESSION['karma_notification'] = [...]
    
publicaciones.php (AJAX success)
    ↓
setTimeout 1000ms
    ↓
window.verificarKarmaPendiente()
    ↓
fetch('/converza/app/presenters/check_karma_notification.php')
    ↓
1ra llamada: Devuelve data + marca karma_shown
    ↓
mostrarPuntosKarma(puntos) → Popup "+8"
    ↓
actualizarKarmaNavbar() → Anima botón
    ↓
cargarNotificaciones() → Actualiza campana 🔔
    ↓
2da llamada: Limpia sesión

[Mientras tanto en tienda]
    ↓
karma_tienda.php polling cada 3s
    ↓
fetch('/converza/app/presenters/get_karma.php')
    ↓
Actualiza contador con animación ✨
```

---

## 🎯 Próximos Pasos (Opcional)

- [ ] Añadir sonido al popup (ding.mp3 para ganancia)
- [ ] Vibrar en móviles (Vibration API)
- [ ] Guardar historial de karma en LocalStorage
- [ ] Gráfico de karma ganado por día
- [ ] Notificación push cuando se sube de nivel

