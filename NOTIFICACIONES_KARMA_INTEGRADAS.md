# 🔔 SISTEMA DE NOTIFICACIONES DE KARMA INTEGRADO

## ✅ Cambio Implementado

Se eliminaron las **notificaciones flotantes** y ahora el sistema de karma usa las **notificaciones nativas** (campanita 🔔) que ya tiene Converza.

---

## 🔄 ¿Qué cambió?

### **ANTES:**
- Notificaciones flotantes en esquina superior derecha
- Se auto-cerraban en 5 segundos
- Aparecían como ventanas emergentes

### **AHORA:**
- Notificaciones en el sistema de campanita 🔔
- Se quedan registradas en el historial
- Se pueden revisar en cualquier momento
- Contador de notificaciones no leídas se actualiza

---

## 🏗️ Arquitectura del Sistema

### Flujo Completo:

```
Usuario hace acción (reacción/comentario)
    ↓
Backend analiza y calcula puntos
    ↓
INSERT en karma_social
    ↓
Trigger actualiza karma_total_usuarios
    ↓
INSERT en tabla notificaciones 🔔
    ↓
Frontend actualiza contador de karma
    ↓
Frontend actualiza contador de campanita
    ↓
Usuario ve notificación en campanita
```

---

## 📝 Archivos Modificados

### 1. **`app/presenters/save_reaction.php`**

**Agregado (líneas ~463-485):**
```php
// 🔔 CREAR NOTIFICACIÓN EN EL SISTEMA (campanita)
if ($puntosGanados != 0) {
    try {
        $notificacionesTriggers = new NotificacionesTriggers($conexion);
        
        $signo = $puntosGanados > 0 ? '+' : '';
        $notifMensaje = "{$signo}{$puntosGanados} Karma: {$mensajeNotificacion}";
        
        $notificacionesTriggers->crearNotificacion(
            $id_usuario,           // Para quién
            'karma',               // Tipo
            $notifMensaje,         // Mensaje: "+5 Karma: 👍 Me gusta"
            null,                  // De usuario (sistema)
            $id_publicacion,       // Referencia
            'reaccion',            // Tipo referencia
            null                   // URL
        );
        
        debugLog("🔔 Notificación de karma creada en sistema");
    } catch (Exception $e) {
        debugLog("⚠️ Error al crear notificación karma");
    }
}
```

### 2. **`app/presenters/agregarcomentario.php`**

**Agregado (líneas ~475-495):**
```php
// 🔔 CREAR NOTIFICACIÓN EN EL SISTEMA (campanita)
if ($otorgarKarma && $puntosGanados != 0) {
    try {
        $signo = $puntosGanados > 0 ? '+' : '';
        $notifMensaje = "{$signo}{$puntosGanados} Karma: {$mensajeNotificacion}";
        
        $notificacionesTriggers->crearNotificacion(
            $_SESSION['id'],       // Para quién
            'karma',               // Tipo
            $notifMensaje,         // Mensaje: "+8 Karma: 😊 Comentario positivo"
            null,                  // De usuario (sistema)
            $comentarioId,         // Referencia
            'comentario',          // Tipo referencia
            null                   // URL
        );
        
        error_log("🔔 Notificación de karma por comentario creada");
    } catch (Exception $e) {
        error_log("⚠️ Error al crear notificación karma");
    }
}
```

### 3. **`app/models/notificaciones-triggers.php`**

**Agregado método genérico (líneas ~356-370):**
```php
/**
 * 🎯 MÉTODO GENÉRICO PARA CREAR NOTIFICACIONES
 * Útil para karma y otros tipos de notificaciones
 */
public function crearNotificacion($usuario_id, $tipo, $mensaje, 
                                  $de_usuario_id = null, 
                                  $referencia_id = null, 
                                  $referencia_tipo = null, 
                                  $url = null) {
    return $this->notificacionesHelper->crear(
        $usuario_id,
        $tipo,
        $mensaje,
        $de_usuario_id,
        $referencia_id,
        $referencia_tipo,
        $url
    );
}
```

### 4. **`public/js/karma-system.js`**

**Eliminado:**
- ❌ Función `mostrarNotificacionKarma()` completa (~80 líneas)
- ❌ Llamada a `mostrarNotificacionKarma()` en `procesarRespuestaKarma()`
- ❌ Código CSS de animaciones flotantes

**Modificado:**
```javascript
// ANTES: Mostraba notificación flotante
mostrarNotificacionKarma(puntos, tipo, mensaje, categoria);

// AHORA: Solo log en consola
console.log('🔔 Notificación enviada al sistema de campanita');
```

### 5. **`app/presenters/publicaciones.php`**

**Eliminado:**
- ❌ Todo el código de notificación flotante (~120 líneas)
- ❌ Creación de elemento DOM `notification`
- ❌ Estilos inline de notificación
- ❌ Animaciones CSS

**Reemplazado por:**
```javascript
// 🔔 LOG DE KARMA (sin notificación flotante - va a campanita)
if (data.karma_notificacion && data.karma_notificacion.mostrar) {
    console.log('🎉 KARMA POR COMENTARIO');
    console.log('🔔 Notificación enviada al sistema (campanita)');
}
```

---

## 🔔 Formato de Notificaciones

### Ejemplo de notificaciones que aparecerán:

| Acción | Mensaje en Campanita |
|--------|---------------------|
| Reacción 👍 Me gusta | `+5 Karma: 👍 Me gusta` |
| Reacción ❤️ Me encanta | `+10 Karma: ❤️ Me encanta` |
| Reacción 😡 Me enoja | `-5 Karma: 😡 Me enoja` |
| Comentario positivo | `+8 Karma: 😊 Comentario positivo` |
| Comentario muy positivo | `+12 Karma: ⭐ ¡Comentario muy positivo!` |
| Comentario negativo | `-3 Karma: 😕 Comentario negativo` |
| Comentario ofensivo | `-7 Karma: ⛔ Comentario ofensivo` |
| Contenido obsceno | `-10 Karma: ⚠️ Contenido inapropiado` |

---

## 📊 Estructura de la Tabla `notificaciones`

Las notificaciones se guardan con esta estructura:

```sql
INSERT INTO notificaciones (
    usuario_id,        -- ID del usuario que recibe (quien ganó/perdió karma)
    tipo,              -- 'karma'
    mensaje,           -- '+5 Karma: 👍 Me gusta'
    de_usuario_id,     -- NULL (es del sistema)
    referencia_id,     -- ID de la publicación o comentario
    referencia_tipo,   -- 'reaccion' o 'comentario'
    url_redireccion,   -- NULL
    leida,             -- 0 (no leída)
    fecha_creacion     -- NOW()
)
```

---

## 🎯 Ventajas del Nuevo Sistema

### ✅ **Persistencia**
- Las notificaciones se guardan en la base de datos
- No se pierden si el usuario refresca la página
- Historial completo de karma ganado/perdido

### ✅ **Integración Nativa**
- Usa el sistema de campanita existente
- Contador de notificaciones no leídas
- UX consistente con el resto de la app

### ✅ **Menos Código**
- Eliminadas ~200 líneas de código flotante
- Más mantenible y simple
- Menos JavaScript en el cliente

### ✅ **Mejor UX**
- No interrumpe la experiencia del usuario
- El usuario decide cuándo revisar notificaciones
- Notificaciones centralizadas en un solo lugar

---

## 🧪 Cómo Probarlo

### 1. **Dale una reacción a una publicación**
```
Click en ❤️ Me encanta
```

**Resultado esperado:**
1. ✅ Contador de karma se actualiza (+10)
2. ✅ Aparece notificación en campanita 🔔
3. ✅ Badge de notificaciones no leídas aumenta
4. ✅ En consola: "🔔 Notificación de karma creada en sistema"

### 2. **Haz un comentario positivo**
```
"¡Me encanta tu publicación! 😍❤️"
```

**Resultado esperado:**
1. ✅ Comentario publicado
2. ✅ Contador de karma se actualiza (+18)
3. ✅ Aparece notificación en campanita 🔔
4. ✅ Badge de notificaciones aumenta
5. ✅ En consola: "🔔 Notificación de karma por comentario creada"

### 3. **Revisa las notificaciones**
```
Click en la campanita 🔔
```

**Deberías ver:**
```
[🔔] +10 Karma: ❤️ Me encanta
     Hace 2 minutos

[🔔] +18 Karma: ⭐ ¡Comentario muy positivo!
     Hace 5 minutos
```

---

## 📝 Logs de Consola

### Reacción Exitosa:
```
🎯 Puntos calculados: {tipo_reaccion: "me_encanta", puntos: 10}
💾 INSERT en karma_social ejecutado
📊 Karma DESPUÉS: 115
🔔 Notificación de karma creada en sistema
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎯 KARMA GANADO
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Puntos: +10
🎯 Categoría: me_encanta
💬 Mensaje: ❤️ Me encanta
🔔 Notificación enviada al sistema de campanita
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### Comentario Exitoso:
```
✅ Comentario insertado en DOM
🎯 Actualizando karma desde comentario: {karma: "133", nivel: 3}
✅ Contador de karma actualizado: 133
🎉 KARMA POR COMENTARIO
Puntos: +18
Tipo: positivo
Categoría: muy positivo
Mensaje: ⭐ ¡Comentario muy positivo!
🔔 Notificación enviada al sistema (campanita)
```

---

## 🔧 Configuración

### Desactivar notificaciones de karma temporalmente

En `save_reaction.php` o `agregarcomentario.php`, comenta las líneas:

```php
// 🔔 CREAR NOTIFICACIÓN EN EL SISTEMA (campanita)
/*
if ($puntosGanados != 0) {
    // ... código de notificación
}
*/
```

### Cambiar formato del mensaje

En `save_reaction.php` línea ~470:
```php
$notifMensaje = "{$signo}{$puntosGanados} Karma: {$mensajeNotificacion}";

// Puedes cambiarlo a:
$notifMensaje = "Has ganado {$puntosGanados} puntos de karma por {$mensajeNotificacion}";
// O cualquier otro formato
```

---

## ✅ Checklist de Implementación

- [x] Eliminadas notificaciones flotantes
- [x] Integrado sistema de campanita para reacciones
- [x] Integrado sistema de campanita para comentarios
- [x] Método genérico `crearNotificacion()` agregado
- [x] Notificaciones persistentes en BD
- [x] Contador de karma sigue funcionando
- [x] Logs en consola informativos
- [x] Código flotante eliminado (~200 líneas menos)
- [x] Sistema unificado y consistente

---

## 🎉 ¡Sistema Actualizado!

Ahora las notificaciones de karma:

✅ Se guardan en la base de datos
✅ Aparecen en la campanita 🔔
✅ Tienen historial persistente
✅ Son consistentes con el resto de la app
✅ No interrumpen la navegación
✅ Se pueden revisar en cualquier momento

**¡A disfrutar del sistema de notificaciones integrado!** 🚀
