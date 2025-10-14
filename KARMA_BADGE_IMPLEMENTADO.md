# 🎯 IMPLEMENTACIÓN COMPLETA - KARMA BADGE CONTADOR

## ✅ CAMBIOS REALIZADOS

### 1. **Sistema de Niveles Progresivo** ✨
**Archivo**: `karma-social-helper.php`

**Nuevo sistema**: Cada 100 puntos = 1 nivel
- 0-99 puntos → Nivel 1 (Novato 🌱)
- 100-199 puntos → Nivel 2 (Intermedio ⭐)
- 200-299 puntos → Nivel 3 (Avanzado ✨)
- 300-399 puntos → Nivel 4 (Experto 💫)
- Y así sucesivamente...

### 2. **Badge Contador en Botón** 🔴
**Archivo**: `karma-navbar-badge.php`

**Funcionalidad**:
- Badge aparece con "+8" o "-7" según comportamiento
- Verde para positivo, rojo para negativo
- Se queda 5 segundos y desaparece
- Igual que el badge de notificaciones 🔔

### 3. **Actualización Automática en Tienda** 🔄
**Archivo**: `karma_tienda.php`

**Funcionalidad**:
- Polling cada 3 segundos
- Actualiza puntos con animación
- No necesitas recargar la página

### 4. **API Actualizada** 📡
**Archivo**: `get_karma.php`

**Retorna**:
```json
{
  "karma": 125,
  "nivel": 2,
  "nivel_titulo": "Intermedio",
  "nivel_emoji": "⭐"
}
```

---

## 🎨 CÓMO SE VE

### Botón de Karma (ANTES de ganar puntos):
```
┌───────────────────┐
│  🌱  95           │
│      Nv.1         │
└───────────────────┘
```

### Botón de Karma (DESPUÉS de ganar +8):
```
┌───────────────────┐
│  ⭐  103   [+8]   │  ← Badge verde
│      Nv.2         │
└───────────────────┘
```

### Después de 5 segundos:
```
┌───────────────────┐
│  ⭐  103          │  ← Badge desaparece
│      Nv.2         │
└───────────────────┘
```

---

## 🧪 PROBAR EL SISTEMA

### Opción 1: Comentario Positivo
1. Ve a `index.php`
2. Escribe un comentario: "¡Excelente post!"
3. **Observa el botón de karma** → Badge [+8] aparece
4. Los puntos se suman: 95 → 103
5. Nivel sube: Nv.1 → Nv.2

### Opción 2: SQL Directo
```sql
USE converza;

-- Ver tu karma actual
SELECT id, usuario, karma_social FROM usuarios WHERE id = 1;

-- Simular ganancia de 8 puntos
UPDATE usuarios SET karma_social = karma_social + 8 WHERE id = 1;

-- Insertar en historial
INSERT INTO karma_social (usuario_id, tipo_accion, puntos, detalle, fecha)
VALUES (1, 'comentario_positivo', 8, 'Comentario positivo genuino', NOW());

-- Crear notificación
INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, fecha_creacion)
VALUES (1, 'karma', '⭐ Karma Ganado', 'Has ganado +8 puntos', NOW());

-- Marcar karma pendiente en sesión (simular)
-- En PHP: $_SESSION['karma_pendiente'] = 8;
```

### Opción 3: Tienda Auto-actualización
1. Abre `http://localhost/converza/app/presenters/karma_tienda.php`
2. En otra pestaña, gana karma (comenta algo)
3. Vuelve a la tienda (SIN recargar)
4. **Espera ~3 segundos** → Puntos se actualizan solos ✨

---

## 📊 TABLA DE NIVELES COMPLETA

| Karma    | Nivel | Título      | Emoji | Color   |
|----------|-------|-------------|-------|---------|
| 0-99     | 1     | Novato      | 🌱    | #87CEEB |
| 100-199  | 2     | Intermedio  | ⭐    | #FFA500 |
| 200-299  | 3     | Avanzado    | ✨    | #32CD32 |
| 300-399  | 4     | Avanzado    | ✨    | #32CD32 |
| 400-499  | 5     | Experto     | 💫    | #4169E1 |
| 500-599  | 6     | Experto     | 💫    | #4169E1 |
| 600-699  | 7     | Maestro     | 🌟    | #9370DB |
| 700-799  | 8     | Maestro     | 🌟    | #9370DB |
| 800-899  | 9     | Maestro     | 🌟    | #9370DB |
| 900-999  | 10    | Legendario  | 👑    | #FFD700 |
| 1000+    | 11+   | Legendario  | 👑    | #FFD700 |

---

## 🔧 ARCHIVOS MODIFICADOS

1. ✏️ **karma-social-helper.php** (líneas 496-545)
   - Nueva función `obtenerNivelKarma()` con sistema progresivo
   - Devuelve nivel numérico + progreso

2. ✏️ **karma-navbar-badge.php** (TODO EL ARCHIVO)
   - Badge contador estilo notificaciones
   - JavaScript para actualizar en tiempo real
   - CSS para animaciones

3. ✏️ **get_karma.php** (líneas 20-35)
   - Retorna nivel numérico además del título
   - Soporte para nuevo formato

4. ✏️ **karma_tienda.php** (líneas 72-75, 380-420)
   - Variables `$nivel` y `$nivel_titulo`
   - Polling cada 3 segundos con `actualizarKarmaTienda()`

5. ✏️ **perfil.php** (líneas 137-147)
   - Botón movido dentro del navbar-nav

6. ✏️ **albumes.php** (líneas 130-140)
   - Botón movido dentro del navbar-nav

---

## ⚙️ CONFIGURACIÓN

### Cambiar frecuencia de actualización en tienda:
```javascript
// En karma_tienda.php línea ~392
setInterval(actualizarKarmaTienda, 3000); // 3000ms = 3 segundos
// Cambiar a 5000 para 5 segundos
```

### Cambiar duración del badge:
```javascript
// En karma-navbar-badge.php línea ~180
setTimeout(() => { /* quitar badge */ }, 5000); // 5000ms = 5 segundos
// Cambiar a 10000 para 10 segundos
```

### Cambiar puntos por nivel:
```php
// En karma-social-helper.php línea ~499
$nivel = floor($karma_total / 100) + 1;
// Cambiar 100 a 200 para hacer niveles más difíciles
```

---

## 🚀 TODO ESTÁ LISTO

El sistema ahora funciona exactamente como lo pediste:

- ✅ NO hay popup flotante
- ✅ Badge contador en el botón (como notificaciones)
- ✅ Sistema de niveles progresivo (cada 100 puntos)
- ✅ Actualización automática en tienda
- ✅ Badge verde/rojo según comportamiento
- ✅ Puntos se acumulan correctamente

**¡Prueba el sistema y avísame cómo funciona!** 🎉
