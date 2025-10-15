# 🌟 Sistema de Karma por Interacciones Sociales

## 📋 Tabla de Puntos por Acción

| Acción | Puntos | Descripción |
|--------|--------|-------------|
| **Seguir a alguien** | +5 | Al hacer clic en "Seguir" en el perfil |
| **Enviar solicitud de amistad** | +3 | Al enviar solicitud desde perfil o Daily Shuffle |
| **Aceptar solicitud de amistad** | +10 | Al aceptar una solicitud pendiente |
| **Aceptar solicitud de mensaje** | +8 | Al aceptar que alguien te escriba |
| **Reacción** | +3 a +15 | Según tipo (Like +10, Love +15, etc.) |
| **Comentario positivo** | +8 a +10 | Si contiene palabras positivas |
| **Comentario negativo** | -5 | Si contiene palabras negativas |
| **Comentario neutral** | 0 | Sin palabras clave (no da puntos) |

---

## 🎯 Objetivo del Sistema

Fomentar **interacciones positivas** y animar a los usuarios a:
- ✅ Conectar con otros usuarios
- ✅ Construir su red social
- ✅ Ser amigables y abiertos
- ✅ Participar activamente en la plataforma

---

## 📁 Archivos Modificados

### 1. **app/presenters/seguir_usuario.php**

**Acción**: Seguir a un usuario  
**Karma**: +5 puntos

```php
// Cuando el usuario hace clic en "Seguir"
$stmt = $conexion->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
$stmt->execute([$usuarioActual, $usuarioSeguir]);

// 🌟 +5 puntos de karma
$stmtKarma = $conexion->prepare("UPDATE usuarios SET karma = karma + 5 WHERE id_use = ?");
$stmtKarma->execute([$usuarioActual]);

// Retornar karma actualizado
return [
    'success' => true,
    'karma_actualizado' => [
        'karma' => 105,
        'nivel_emoji' => '⭐',
        'nivel_titulo' => 'Novato'
    ]
];
```

**Flujo**:
1. Usuario hace clic en botón "Seguir"
2. Se inserta relación en tabla `seguidores`
3. Se suman +5 puntos de karma
4. Se obtiene karma actualizado
5. Se retorna JSON con `karma_actualizado`
6. Frontend actualiza contador silenciosamente

---

### 2. **app/presenters/solicitud.php**

#### **Acción A**: Enviar solicitud de amistad  
**Karma**: +3 puntos

```php
// Cuando el usuario envía solicitud desde perfil
$stmt = $conexion->prepare('INSERT INTO amigos (de, para, estado, fecha) VALUES (:yo, :id, 0, NOW())');
$stmt->execute();

// 🌟 +3 puntos de karma
$stmtKarma = $conexion->prepare('UPDATE usuarios SET karma = karma + 3 WHERE id_use = :yo');
$stmtKarma->execute([':yo' => $yo]);

return [
    'success' => true,
    'karma_actualizado' => [...]
];
```

#### **Acción B**: Aceptar solicitud de amistad  
**Karma**: +10 puntos

```php
// Cuando el usuario acepta una solicitud pendiente
$stmt = $conexion->prepare('UPDATE amigos SET estado = 1 WHERE para = :yo AND de = :id AND estado = 0');
$stmt->execute();

// 🌟 +10 puntos de karma
$stmtKarma = $conexion->prepare('UPDATE usuarios SET karma = karma + 10 WHERE id_use = :yo');
$stmtKarma->execute([':yo' => $yo]);

return [
    'success' => true,
    'karma_actualizado' => [...]
];
```

**Flujo**:
1. Usuario recibe solicitud de amistad
2. Hace clic en botón "Aceptar" (✓)
3. Se actualiza estado en tabla `amigos`
4. Se suman +10 puntos de karma
5. Se genera notificación al solicitante
6. Frontend actualiza contador silenciosamente

---

### 3. **app/presenters/gestionar_solicitud_mensaje.php**

**Acción**: Aceptar solicitud de mensaje  
**Karma**: +8 puntos

```php
// Cuando el usuario acepta que alguien le escriba
if (aceptarSolicitudMensaje($conexion, $solicitudId, $usuarioActual)) {
    // 🌟 +8 puntos de karma
    $stmtKarma = $conexion->prepare('UPDATE usuarios SET karma = karma + 8 WHERE id_use = :yo');
    $stmtKarma->execute([':yo' => $usuarioActual]);
    
    return [
        'success' => true,
        'karma_actualizado' => [...]
    ];
}
```

**Flujo**:
1. Usuario recibe solicitud de mensaje (alguien quiere escribirle)
2. Hace clic en "Aceptar"
3. Se actualiza estado en `solicitudes_mensaje`
4. Se crea conversación en `c_chats`
5. Se inserta primer mensaje en `chats`
6. Se suman +8 puntos de karma
7. Frontend actualiza contador silenciosamente

---

### 4. **app/presenters/agregarcomentario.php** (YA EXISTÍA)

**Sistema inteligente**: Solo da puntos si detecta contenido especial

```php
$puntosGanados = 0; // Sin puntos base

// Palabras positivas: +8 puntos
if (contiene "me encanta", "excelente", "increíble") {
    $puntosGanados = 8;
}

// Palabras negativas: -5 puntos
if (contiene "odio", "horrible", "malo") {
    $puntosGanados = -5;
}

// Bonus largo: +2 adicionales (solo si es positivo)
if ($puntosGanados > 0 && strlen($comentario) > 100) {
    $puntosGanados += 2; // Total: +10
}
```

---

### 5. **app/presenters/save_reaction.php** (YA EXISTÍA)

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
```

---

## 🔄 Flujo General del Sistema

### **Frontend → Backend → Frontend**

```
1. Usuario realiza acción (seguir, aceptar, etc.)
   ↓
2. JavaScript envía fetch() a endpoint PHP
   ↓
3. Backend procesa acción:
   - Actualiza base de datos
   - Suma/resta karma
   - Obtiene karma actualizado
   ↓
4. Backend retorna JSON:
   {
     "success": true,
     "karma_actualizado": {
       "karma": 123,
       "nivel_emoji": "⭐",
       "nivel_titulo": "Novato"
     }
   }
   ↓
5. karma-system.js intercepta respuesta automáticamente
   ↓
6. Actualiza contador en navbar (silenciosamente)
   - Sin popup flotante
   - Sin sonido
   - Solo animación discreta (scale)
```

---

## 🧪 Ejemplos de Uso

### **Ejemplo 1: Usuario nuevo se une a la red**

```
Usuario: Juan (@juan#1234)
Karma inicial: 0 pts

Acciones:
1. Sigue a María (+5 pts) → 5 pts
2. Envía solicitud a Pedro (+3 pts) → 8 pts
3. María lo sigue de vuelta (+0 pts, él no hace nada)
4. Pedro acepta su solicitud (+0 pts, Pedro gana +10)
5. Juan acepta solicitud de mensaje de Ana (+8 pts) → 16 pts
6. Comenta "me encanta esto" (+8 pts) → 24 pts
7. Reacciona ❤️ a publicación (+10 pts) → 34 pts

Karma final: 34 pts (⭐ Novato)
```

### **Ejemplo 2: Usuario activo construye su red**

```
Usuario: Ana (@ana#5678)
Karma inicial: 50 pts

Acciones en 1 día:
- Sigue a 10 usuarios (+50 pts)
- Envía 5 solicitudes de amistad (+15 pts)
- Acepta 3 solicitudes recibidas (+30 pts)
- Acepta 2 solicitudes de mensaje (+16 pts)
- Comenta 5 veces positivo (+40 pts)
- Reacciona 20 veces (+200 pts aprox)

Karma final: 50 + 351 = 401 pts (🌟 Avanzado)
```

---

## 🎨 Actualización Visual del Contador

### **Antes de la acción**:
```
Navbar: ⭐ 100 pts
```

### **Durante la acción** (300ms):
```
Navbar: ⭐ 105 pts (escala 1.15, transición suave)
```

### **Después de la acción**:
```
Navbar: ⭐ 105 pts (escala normal 1.0)
```

**Sin interrupciones**:
- ❌ Sin popup flotante
- ❌ Sin sonido
- ❌ Sin notificación push
- ✅ Solo cambio visual discreto

---

## 💡 Beneficios del Sistema

### **Para los usuarios**:
1. ✅ **Gamificación**: Se sienten motivados a interactuar
2. ✅ **Feedback instantáneo**: Ven su progreso en tiempo real
3. ✅ **Incentivo a socializar**: Ganan puntos por conectar
4. ✅ **Recompensa la amabilidad**: Palabras positivas valen más
5. ✅ **Penaliza toxicidad**: Comentarios negativos quitan karma

### **Para la plataforma**:
1. ✅ **Más engagement**: Usuarios más activos
2. ✅ **Mejor comunidad**: Fomenta interacciones positivas
3. ✅ **Retención**: Los usuarios vuelven para ganar karma
4. ✅ **Viralidad**: Los usuarios invitan amigos para seguirlos
5. ✅ **Datos valiosos**: Métricas de qué usuarios son más sociales

---

## 🔍 Debugging

### **Verificar que el karma se actualiza**:

```javascript
// En consola del navegador (F12):

// 1. Seguir usuario
// Buscar en Network → seguir_usuario.php → Response:
{
  "success": true,
  "karma_actualizado": {
    "karma": 105,
    "nivel_emoji": "⭐",
    "nivel_titulo": "Novato"
  }
}

// 2. Verificar intercepción
console.log(window.actualizarContadorKarma); // [Function]

// 3. Ver logs del sistema
// Deberías ver: "🔄 Actualizando contador karma: {karma: 105, ...}"
```

### **Verificar en base de datos**:

```sql
-- Ver karma actual de un usuario
SELECT id_use, usuario, karma FROM usuarios WHERE id_use = 1;

-- Ver últimas acciones sociales
SELECT * FROM seguidores WHERE seguidor_id = 1 ORDER BY id DESC LIMIT 10;
SELECT * FROM amigos WHERE de = 1 OR para = 1 ORDER BY fecha DESC LIMIT 10;
SELECT * FROM solicitudes_mensaje WHERE de = 1 OR para = 1 ORDER BY fecha_solicitud DESC LIMIT 10;
```

---

## 📊 Estadísticas Esperadas

### **Usuario promedio activo** (1 mes):
- **Seguidos**: 20 usuarios → 100 pts
- **Amigos**: 10 aceptadas → 100 pts
- **Solicitudes enviadas**: 15 → 45 pts
- **Mensajes aceptados**: 5 → 40 pts
- **Comentarios**: 30 positivos → 240 pts
- **Reacciones**: 100 → 1000 pts

**Total mensual**: ~1525 pts (🚀 Experto)

---

## ⚙️ Configuración Técnica

### **Puntos editables**:

Si quieres cambiar los puntos, edita estos archivos:

```php
// seguir_usuario.php (línea ~52)
$stmtKarma = $conexion->prepare("UPDATE usuarios SET karma = karma + 5 WHERE id_use = ?");
// Cambiar +5 por el valor deseado

// solicitud.php - Enviar solicitud (línea ~73)
$stmtKarma = $conexion->prepare('UPDATE usuarios SET karma = karma + 3 WHERE id_use = :yo');
// Cambiar +3 por el valor deseado

// solicitud.php - Aceptar solicitud (línea ~103)
$stmtKarma = $conexion->prepare('UPDATE usuarios SET karma = karma + 10 WHERE id_use = :yo');
// Cambiar +10 por el valor deseado

// gestionar_solicitud_mensaje.php (línea ~37)
$stmtKarma = $conexion->prepare('UPDATE usuarios SET karma = karma + 8 WHERE id_use = :yo');
// Cambiar +8 por el valor deseado
```

---

## 🚀 Próximas Mejoras (Futuras)

### **Ideas para expandir el sistema**:

1. **Karma diario**: +10 puntos por iniciar sesión cada día
2. **Rachas**: Bonus si comentas 7 días seguidos
3. **Multipliers**: x2 karma en eventos especiales
4. **Logros**: Badges al alcanzar hitos (100 seguidores, etc.)
5. **Leaderboard**: Top 10 usuarios con más karma
6. **Karma decay**: Perder puntos si estás inactivo >30 días
7. **Karma por voz**: +20 por mensaje de voz (más personal)
8. **Karma por foto**: +15 por enviar foto en chat

---

## ✅ Estado del Sistema

**Fecha de implementación**: 2025-01-14  
**Archivos modificados**: 3 nuevos (seguir, solicitud, mensaje)  
**Sistema funcional**: ✅ SÍ  
**Frontend integrado**: ✅ karma-system.js intercepta automáticamente  
**Backend retorna**: ✅ `karma_actualizado` en todas las respuestas  
**UI actualiza**: ✅ Contador silencioso sin interrupciones  

---

## 📝 Resumen Ejecutivo

### **Sistema completo de karma social**:

**Acciones que dan karma**:
1. ✅ Seguir → +5
2. ✅ Enviar solicitud → +3
3. ✅ Aceptar amistad → +10
4. ✅ Aceptar mensaje → +8
5. ✅ Reaccionar → +3 a +15
6. ✅ Comentar positivo → +8 a +10
7. ⚠️ Comentar negativo → -5

**Características**:
- ✅ Actualización automática (fetch interceptor)
- ✅ Silencioso (sin popups/sonidos)
- ✅ Instantáneo (sin recargar página)
- ✅ Discreto (animación suave)
- ✅ Universal (funciona en todas las páginas)

**Objetivo**:
Animar a los usuarios a ser sociales, amigables y activos en la plataforma mediante recompensas gamificadas instantáneas.

---

**¡Sistema listo para uso en producción!** 🎉
