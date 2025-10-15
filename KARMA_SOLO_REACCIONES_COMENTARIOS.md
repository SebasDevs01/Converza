# ✅ Sistema de Karma Solo para Reacciones y Comentarios

## 🎯 Sistema Final Implementado

**Karma SOLO para**:
- ✅ **Reacciones** (Like, Love, Care, etc.)
- ✅ **Comentarios** (con análisis inteligente)

**NO hay karma para**:
- ❌ Seguir usuarios
- ❌ Enviar solicitudes de amistad
- ❌ Aceptar solicitudes de amistad
- ❌ Aceptar solicitudes de mensaje

---

## 📋 Tabla de Puntos

| Acción | Puntos | Descripción |
|--------|--------|-------------|
| **Reaccionar ❤️ Like** | +10 | Por dar like a publicación |
| **Reaccionar 😍 Love** | +15 | Por dar love a publicación |
| **Reaccionar 🤗 Care** | +12 | Por dar care a publicación |
| **Reaccionar 😂 Haha** | +8 | Por dar haha a publicación |
| **Reaccionar 😮 Wow** | +10 | Por dar wow a publicación |
| **Reaccionar 😢 Sad** | +5 | Por dar sad a publicación |
| **Reaccionar 😠 Angry** | +3 | Por dar angry a publicación |
| **Comentario positivo** | +8 | Si contiene palabras positivas |
| **Comentario positivo largo** | +10 | +2 extra si >100 caracteres |
| **Comentario negativo** | -5 | Si contiene palabras negativas |
| **Comentario neutral** | 0 | Sin palabras clave detectadas |

---

## 📁 Archivos del Sistema

### **1. Frontend: public/js/karma-system.js**

**Funciones**:
```javascript
// Solo actualizar contador (sin notificaciones flotantes)
actualizarContadorKarma(karmaData)

// Procesar respuesta (solo si hay karma_actualizado)
procesarRespuestaKarma(response)

// Interceptor global de fetch
initKarmaSystem()
```

**Comportamiento**:
- ✅ Intercepta todas las respuestas fetch()
- ✅ Actualiza contador silenciosamente
- ✅ Animación discreta (scale 1.15)
- ❌ Sin notificaciones flotantes
- ❌ Sin sonidos

---

### **2. Backend: app/presenters/save_reaction.php**

**Sistema de puntos por reacción**:
```php
switch ($tipo_reaccion) {
    case 'like':  $puntosGanados = 10; break;
    case 'love':  $puntosGanados = 15; break;
    case 'care':  $puntosGanados = 12; break;
    case 'haha':  $puntosGanados = 8;  break;
    case 'wow':   $puntosGanados = 10; break;
    case 'sad':   $puntosGanados = 5;  break;
    case 'angry': $puntosGanados = 3;  break;
}

// Retorna karma_actualizado
return [
    'success' => true,
    'karma_actualizado' => [
        'karma' => 123,
        'nivel_emoji' => '⭐',
        'nivel_titulo' => 'Novato'
    ]
];
```

---

### **3. Backend: app/presenters/agregarcomentario.php**

**Sistema inteligente de comentarios**:
```php
// Sin puntos base
$puntosGanados = 0;
$otorgarKarma = false;

// Palabras positivas (+8 puntos)
$palabrasPositivas = [
    'me encanta', 'excelente', 'increíble', 'genial', 'perfecto',
    'amor', 'hermoso', 'maravilloso', 'fantástico', 'espectacular',
    'brillante', 'asombroso', 'impresionante', 'extraordinario'
];

foreach ($palabrasPositivas as $palabra) {
    if (strpos($comentarioLower, $palabra) !== false) {
        $puntosGanados = 8;
        $otorgarKarma = true;
        break;
    }
}

// Palabras negativas (-5 puntos)
$palabrasNegativas = [
    'odio', 'horrible', 'malo', 'pésimo', 'basura', 'asco', 'terrible'
];

foreach ($palabrasNegativas as $palabra) {
    if (strpos($comentarioLower, $palabra) !== false) {
        $puntosGanados = -5;
        $otorgarKarma = true;
        break;
    }
}

// Bonus por comentario largo (solo si es positivo)
if ($puntosGanados > 0 && strlen($comentario) > 100) {
    $puntosGanados += 2; // Total: +10
}

// Solo actualizar si detectó palabras clave
return [
    'karma_actualizado' => [...],
    'karma_notificacion' => [
        'mostrar' => $otorgarKarma, // false si no hay palabras clave
        'puntos' => $puntosGanados,
        'tipo' => $tipoNotificacion,
        'mensaje' => $mensajeNotificacion
    ]
];
```

---

## 🔄 Flujo del Sistema

### **Reacción**:
```
1. Usuario hace clic en ❤️
   ↓
2. JavaScript envía fetch a save_reaction.php
   ↓
3. Backend actualiza reacción en BD
   ↓
4. Backend suma +10 puntos de karma
   ↓
5. Backend obtiene karma actualizado
   ↓
6. Backend retorna JSON:
   {
     "success": true,
     "karma_actualizado": {
       "karma": 110,
       "nivel_emoji": "⭐",
       "nivel_titulo": "Novato"
     }
   }
   ↓
7. karma-system.js intercepta respuesta
   ↓
8. Actualiza contador navbar (silencioso)
   - Animación: scale 1.15 → 1.0 (300ms)
   - Sin popup, sin sonido
```

### **Comentario**:
```
1. Usuario escribe "me encanta esto"
   ↓
2. JavaScript envía fetch a agregarcomentario.php
   ↓
3. Backend guarda comentario en BD
   ↓
4. Backend detecta "me encanta" → +8 puntos
   ↓
5. Backend actualiza karma
   ↓
6. Backend retorna JSON con karma_actualizado
   ↓
7. karma-system.js intercepta respuesta
   ↓
8. Actualiza contador navbar (silencioso)
```

---

## 🧪 Ejemplos de Uso

### **Ejemplo 1: Usuario comenta neutral**
```
Comentario: "ok"
Resultado:
  ❌ NO actualiza karma (0 puntos)
  ❌ NO muestra notificación
  ✅ Solo guarda el comentario
```

### **Ejemplo 2: Usuario comenta positivo**
```
Comentario: "me encanta esto"
Resultado:
  ✅ +8 puntos de karma
  ✅ Contador actualiza silenciosamente
  ❌ Sin popup flotante
  ❌ Sin sonido
```

### **Ejemplo 3: Usuario comenta negativo**
```
Comentario: "odio esto"
Resultado:
  ⚠️ -5 puntos de karma
  ✅ Contador actualiza silenciosamente
  ❌ Sin popup flotante
  ❌ Sin sonido
```

### **Ejemplo 4: Usuario comenta positivo largo**
```
Comentario: "me encanta esto, es perfecto y además tiene todo lo que buscaba..." (>100 chars)
Resultado:
  ✅ +10 puntos (8 + 2 bonus)
  ✅ Contador actualiza silenciosamente
  ❌ Sin popup flotante
  ❌ Sin sonido
```

### **Ejemplo 5: Usuario reacciona**
```
Reacción: ❤️ Like
Resultado:
  ✅ +10 puntos
  ✅ Contador actualiza silenciosamente
  ❌ Sin popup flotante
  ❌ Sin sonido
```

---

## ⚙️ Archivos NO Modificados (Revertidos)

Estos archivos volvieron a su estado original **SIN karma**:

1. ✅ **app/presenters/seguir_usuario.php** - Sin karma por seguir
2. ✅ **app/presenters/solicitud.php** - Sin karma por solicitudes de amistad
3. ✅ **app/presenters/gestionar_solicitud_mensaje.php** - Sin karma por solicitudes de mensaje

**Razón**: Para evitar conflictos con código existente y mantener el sistema simple.

---

## 🎨 Actualización Visual

### **Contador de karma en navbar**:

**Antes**:
```html
<span data-karma-display>⭐ 100 pts</span>
```

**Durante acción** (300ms):
```html
<span data-karma-display style="transform: scale(1.15)">⭐ 108 pts</span>
```

**Después**:
```html
<span data-karma-display style="transform: scale(1)">⭐ 108 pts</span>
```

**Características**:
- ✅ Animación suave y discreta
- ✅ Sin interrupciones
- ✅ Sin elementos visuales adicionales
- ✅ Solo cambio en el número

---

## 🔍 Debugging

### **Consola del navegador (F12)**:

```javascript
// Verificar sistema cargado
console.log(window.actualizarContadorKarma); // [Function]
console.log(window.procesarRespuestaKarma);  // [Function]

// Ver interceptor funcionando
// Deberías ver: "🌐 Fetch interceptado: /Converza/app/presenters/save_reaction.php"

// Ver actualizaciones de karma
// Deberías ver: "🔄 Actualizando contador karma: {karma: 108, ...}"
```

### **Network tab (DevTools)**:

```json
// save_reaction.php response:
{
  "success": true,
  "karma_actualizado": {
    "karma": 108,
    "nivel_emoji": "⭐",
    "nivel_titulo": "Novato"
  },
  "karma_notificacion": {
    "mostrar": true,
    "puntos": 10,
    "tipo": "positivo",
    "mensaje": "¡Me gusta! ❤️"
  }
}
```

---

## 📊 Estadísticas del Sistema

### **Solo Reacciones y Comentarios**:

**Usuario activo típico (1 día)**:
- Reacciones: 20 → ~200 pts
- Comentarios positivos: 5 → ~40 pts
- Comentarios neutrales: 10 → 0 pts

**Total diario**: ~240 pts

**Usuario muy activo (1 semana)**:
- Reacciones: 100 → ~1000 pts
- Comentarios positivos: 30 → ~240 pts

**Total semanal**: ~1240 pts (🌟 Avanzado)

---

## ✅ Resumen Final

### **Sistema implementado**:
- ✅ Karma por reacciones (3-15 puntos)
- ✅ Karma por comentarios positivos (8-10 puntos)
- ⚠️ Penalización por comentarios negativos (-5 puntos)
- 🚫 Sin puntos por comentarios neutrales (0 puntos)

### **Sistema NO implementado**:
- ❌ Sin karma por seguir
- ❌ Sin karma por solicitudes de amistad
- ❌ Sin karma por solicitudes de mensaje

### **Características**:
- ✅ Actualización silenciosa
- ✅ Sin notificaciones flotantes
- ✅ Sin sonidos
- ✅ Animación discreta
- ✅ Automático (fetch interceptor)

### **Ventajas**:
1. ✅ Sistema simple y enfocado
2. ✅ No interfiere con código existente
3. ✅ Fácil de mantener
4. ✅ Motivador para interacciones de contenido
5. ✅ Penaliza toxicidad

---

## 📝 Estado Final

**Fecha**: 2025-01-14  
**Sistema**: Karma solo para Reacciones y Comentarios  
**Archivos modificados**: 3  
- ✅ public/js/karma-system.js (silencioso)
- ✅ app/presenters/save_reaction.php (con karma)
- ✅ app/presenters/agregarcomentario.php (sistema inteligente)

**Archivos revertidos**: 3  
- ✅ app/presenters/seguir_usuario.php (sin karma)
- ✅ app/presenters/solicitud.php (sin karma)
- ✅ app/presenters/gestionar_solicitud_mensaje.php (sin karma)

**Estado**: ✅ Funcionando y listo para producción

---

**¡Sistema simplificado y funcionando correctamente!** 🎉
