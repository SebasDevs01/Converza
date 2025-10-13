# 🔥 MODO DEBUG EXTREMO ACTIVADO

## 📊 Archivos Modificados

### ✅ `agregarcomentario.php` - Backend de Comentarios
**Cambios realizados:**
- ✅ Agregado `ob_start()` para capturar salidas accidentales
- ✅ Cambiado `error_reporting(E_ALL)` para ver TODOS los errores
- ✅ Agregado archivo de log: `comentarios_debug.log`
- ✅ Logs en cada paso:
  - Inicio del script
  - Datos POST recibidos
  - Usuario bloqueado/no bloqueado
  - Inserción exitosa en BD
  - Respuesta enviada
  - Errores PDO con stack trace

**Logs que verás:**
```
=== AGREGARCOMENTARIO.PHP INICIADO ===
POST: {...}
SESSION ID: X
✅ Usuario NO bloqueado, continuando...
📨 Método POST detectado
✅ Comentario insertado correctamente. ID: X
📤 Enviando respuesta: {...}
```

---

### ✅ `publicaciones.php` - Frontend de Comentarios y Tooltips

#### **Sección 1: AJAX Comentarios (líneas ~628-728)**
**Cambios realizados:**
- ✅ Logs EXTREMOS en cada paso del fetch:
  - Inicio de envío
  - Datos del formulario
  - Respuesta HTTP recibida (status, headers)
  - **Respuesta RAW** (texto completo antes de parsear)
  - JSON parseado
  - Procesamiento de datos
  - Inserción en DOM
  - Actualización de contador
  - Errores con stack trace

**Logs que verás:**
```
🚀 === INICIO DE ENVÍO DE COMENTARIO ===
📋 Datos del formulario: {usuario: X, comentario: "...", publicacion: Y}
📤 Enviando fetch a: /Converza/app/presenters/agregarcomentario.php
📥 ===== RESPUESTA RECIBIDA =====
Status: 200
StatusText: OK
Headers: {contentType: "application/json", ...}
📄 Respuesta RAW: {"status":"success",...}
📄 Longitud: 250 caracteres
✅ JSON parseado correctamente: {...}
📊 ===== PROCESANDO DATOS =====
Status: success
Message: Tu comentario ha sido publicado.
✅ Éxito! Creando elemento de comentario...
✅ Comentario insertado en DOM
✅ Contador actualizado: 5 → 6
🔄 Recargando datos de comentarios para tooltip...
✅ ===== COMENTARIO AGREGADO EXITOSAMENTE =====
🏁 === FIN DE ENVÍO DE COMENTARIO ===
```

#### **Sección 2: loadReactionsData() (líneas ~802-860)**
**Cambios realizados:**
- ✅ Logs al cargar datos de cada post:
  - URLs de las APIs
  - Respuestas HTTP
  - Datos parseados (reacciones y comentarios)
  - Éxito/error de actualización
  - Errores con stack trace

**Logs que verás:**
```
🔄 ========== CARGANDO DATOS POST 123 ==========
URL Reacciones: /Converza/app/presenters/get_reactions.php?postId=123
URL Comentarios: /Converza/app/presenters/get_comentarios.php?postId=123
📥 Respuestas recibidas: [...]
  [0] Status: 200 OK
  [1] Status: 200 OK
📊 ========== DATOS PARSEADOS POST 123 ==========
Reacciones: {success: true, reactions: [...]}
Comentarios: {success: true, total: 5, comentarios: [...]}
✅ Reacciones exitosas, actualizando...
✅ Comentarios exitosos, actualizando...
```

#### **Sección 3: Inicialización (líneas ~1027-1045)**
**Cambios realizados:**
- ✅ Logs al inicializar todas las publicaciones:
  - Total de publicaciones encontradas
  - Cada publicación inicializada
  - Llamada a loadReactionsData

**Logs que verás:**
```
🚀 ========== INICIALIZANDO PUBLICACIONES ==========
📊 Total de publicaciones encontradas: 10
✅ [0] Publicación 123 inicializada
🔄 [0] Llamando loadReactionsData(123)...
✅ [1] Publicación 124 inicializada
🔄 [1] Llamando loadReactionsData(124)...
...
```

---

## 📁 Archivo de Log Creado

### `comentarios_debug.log`
**Ubicación**: `c:\xampp\htdocs\Converza\comentarios_debug.log`

**Contenido esperado:**
```
[13-Oct-2025 14:30:15] === AGREGARCOMENTARIO.PHP INICIADO ===
[13-Oct-2025 14:30:15] POST: {"usuario":"1","comentario":"test","publicacion":"123"}
[13-Oct-2025 14:30:15] SESSION ID: 1
[13-Oct-2025 14:30:15] ✅ Usuario NO bloqueado, continuando...
[13-Oct-2025 14:30:15] 📨 Método POST detectado
[13-Oct-2025 14:30:15] ✅ Comentario insertado correctamente. ID: 456
[13-Oct-2025 14:30:15] 📤 Enviando respuesta: {"status":"success",...}
```

**Si hay errores:**
```
[13-Oct-2025 14:30:15] ❌ ERROR PDO: SQLSTATE[42S22]: Column not found...
[13-Oct-2025 14:30:15] Stack trace: #0 /path/to/file.php(XX): PDO->execute()
```

---

## 🎯 Qué Buscar

### **Problema 1: Error al Comentar**

#### ✅ SI FUNCIONA:
- En consola: `✅ ===== COMENTARIO AGREGADO EXITOSAMENTE =====`
- Comentario aparece inmediatamente
- Contador se incrementa: `(5) → (6)`
- NO aparece alerta de error

#### ❌ SI NO FUNCIONA:

**Caso A: Error de JSON**
```
❌ ERROR AL PARSEAR JSON: SyntaxError: Unexpected token '<' at position 0
Primeros 500 caracteres: <html>...
```
**Causa**: PHP está devolviendo HTML en lugar de JSON
**Solución**: Revisar `comentarios_debug.log` para ver el error PHP

**Caso B: Error del Servidor**
```
❌ ===== ERROR DEL SERVIDOR =====
Message: La publicación no existe.
```
**Causa**: Problema en la lógica de negocio
**Solución**: Revisar validaciones en agregarcomentario.php

**Caso C: Error de Red**
```
❌ ===== ERROR CATCH =====
Error: Failed to fetch
```
**Causa**: Problema de conectividad o CORS
**Solución**: Verificar que XAMPP esté corriendo

---

### **Problema 2: Tooltips no Aparecen**

#### ✅ SI FUNCIONA:
- Al hacer hover sobre `(5)`, aparece tooltip con nombres: `❤️ vane15`

#### ❌ SI NO FUNCIONA:

**Caso A: No se Llama loadReactionsData**
```
(No aparece nada en consola sobre "CARGANDO DATOS POST")
```
**Causa**: JavaScript no se está ejecutando
**Solución**: Verificar que no haya errores de sintaxis en publicaciones.php

**Caso B: API Falla**
```
❌ ========== ERROR CARGANDO DATOS ==========
Error: HTTP error 500 en reacciones
```
**Causa**: Error en get_reactions.php o get_comentarios.php
**Solución**: Revisar esos archivos

**Caso C: Elemento no Existe**
```
❌ No se encontró elemento contador para post: 123
```
**Causa**: El HTML no tiene el elemento con ID `reaction_counter_123`
**Solución**: Verificar el código PHP que genera el HTML

**Caso D: Datos Vacíos**
```
✅ Reacciones exitosas, actualizando...
🔄 Actualizando contador de reacciones para post: 123
  - Elemento contador encontrado: true
  - Datos de reacciones recibidos: []
  - Sin reacciones, mostrando (0)
```
**Causa**: No hay reacciones aún, tooltip muestra "Sin reacciones"
**NO ES ERROR**: Funcionamiento normal

**Caso E: CSS no se Aplica**
- En consola se ve: `data-tooltip="❤️ vane15"`
- Pero al hacer hover NO aparece
**Causa**: Problema CSS con `::after` o `z-index`
**Solución**: Usar script manual `TOOLTIP_FIX_MANUAL.js`

---

## 🚀 Siguientes Pasos

1. **Recargar página**: `Ctrl + F5`
2. **Abrir consola**: `F12`
3. **Intentar comentar**: Escribir "test debug" y enviar
4. **Copiar TODO** lo que aparezca en consola
5. **Revisar archivo**: `c:\xampp\htdocs\Converza\comentarios_debug.log`
6. **Reportar resultados**: Ver `INSTRUCCIONES_DEBUG.md`

---

## 🔧 Para Desactivar Debug

Una vez encontrado el problema, cambiar en `agregarcomentario.php`:

```php
// DESACTIVAR DEBUG
error_reporting(E_ERROR | E_PARSE); // Solo errores críticos
ini_set('display_errors', '0'); // No mostrar en pantalla
// Comentar o eliminar líneas de error_log()
```

---

**Status**: 🔥 MODO DEBUG EXTREMO ACTIVO  
**Fecha**: 2025-10-13  
**Archivos Modificados**: 2 (agregarcomentario.php, publicaciones.php)  
**Logs Agregados**: ~50 console.log(), ~10 error_log()
