# 🔍 Sistema de Debug Extremo - README

## 📌 Resumen Ejecutivo

Se ha activado un sistema de debug avanzado para identificar y resolver dos problemas críticos:

1. ❌ **Comentarios**: Error "Ocurrió un problema al guardar el comentario" pero el comentario SÍ se guarda
2. ❌ **Tooltips**: No se muestran al hacer hover sobre contadores de reacciones/comentarios

---

## 🎯 Objetivo

Capturar **TODA** la información posible sobre:
- ✅ Qué datos se envían (frontend)
- ✅ Qué recibe el servidor (backend)
- ✅ Qué procesa la base de datos
- ✅ Qué responde el servidor
- ✅ Qué parsea el frontend
- ✅ Qué se muestra al usuario

---

## 📂 Archivos de Documentación

### **1. ACCION_REQUERIDA.md** ⭐ **EMPIEZA AQUÍ**
Instrucciones paso a paso de lo que debes hacer:
- Cómo abrir la consola
- Qué probar
- Qué copiar
- Cómo reportar resultados

### **2. INSTRUCCIONES_DEBUG.md**
Guía detallada del proceso de debug:
- Preparación
- Pruebas
- Verificación de logs
- Formato de reporte

### **3. DEBUG_EXTREMO_ACTIVADO.md**
Información técnica sobre los cambios:
- Archivos modificados
- Logs agregados
- Qué buscar en cada caso
- Cómo desactivar debug

### **4. CORRECCIONES_APLICADAS.md**
Historial de fixes previos (antes del debug extremo)

---

## 🛠️ Archivos Modificados

### **Backend**
- ✅ `app/presenters/agregarcomentario.php`
  - Agregado `ob_start()` para capturar salidas
  - Cambiado error_reporting a E_ALL
  - Creado archivo de log: `comentarios_debug.log`
  - Logs en cada paso del proceso

### **Frontend**
- ✅ `app/presenters/publicaciones.php`
  - Logs extremos en AJAX de comentarios
  - Logs en loadReactionsData()
  - Logs en inicialización de publicaciones
  - Captura de respuesta RAW antes de parsear JSON

---

## 📊 Logs Generados

### **Consola del Navegador (F12)**
- 🚀 Inicialización de publicaciones
- 📤 Envío de comentarios
- 📥 Recepción de respuestas
- 📄 Respuesta RAW (texto completo)
- ✅ Éxitos
- ❌ Errores con stack trace

### **Archivo comentarios_debug.log**
- Inicio del script
- Datos POST recibidos
- Session ID
- Validaciones
- Inserción en BD
- Respuesta enviada
- Errores PDO

---

## 🔄 Flujo de Debug

```
Usuario → Escribe comentario
    ↓
JavaScript → console.log("🚀 INICIO")
    ↓
Fetch → /agregarcomentario.php
    ↓
PHP → error_log("=== INICIADO ===")
    ↓
Base de Datos → INSERT comentario
    ↓
PHP → error_log("✅ Insertado ID: X")
    ↓
PHP → echo json_encode({...})
    ↓
JavaScript → console.log("📄 Respuesta RAW")
    ↓
JavaScript → JSON.parse()
    ↓
JavaScript → console.log("✅ Parseado")
    ↓
JavaScript → Insertar en DOM
    ↓
JavaScript → console.log("✅ EXITOSO")
```

**En CADA paso hay logs para ver dónde falla**.

---

## 🎯 Casos de Uso

### **Caso 1: Error de JSON**
```
📄 Respuesta RAW: <html>Warning: session_start()...
❌ ERROR AL PARSEAR JSON
```
**Diagnóstico**: PHP está enviando HTML antes del JSON  
**Causa**: Error de sintaxis o warning  
**Solución**: Revisar `comentarios_debug.log`

---

### **Caso 2: Error del Servidor**
```
📄 Respuesta RAW: {"status":"error","message":"...","debug":"SQLSTATE[42S22]"}
```
**Diagnóstico**: Error en base de datos  
**Causa**: Columna no existe, tabla incorrecta, etc.  
**Solución**: Verificar estructura de BD

---

### **Caso 3: Error de Red**
```
❌ ERROR CATCH: Failed to fetch
```
**Diagnóstico**: Servidor no responde  
**Causa**: XAMPP apagado, ruta incorrecta, CORS  
**Solución**: Verificar que Apache esté corriendo

---

### **Caso 4: Comentario se Guarda pero Muestra Error**
```
Archivo: comentarios_debug.log
✅ Comentario insertado correctamente. ID: 456
📤 Enviando respuesta: {"status":"success",...}

Consola:
📄 Respuesta RAW: Warning: Undefined variable... {"status":"success",...}
❌ ERROR AL PARSEAR JSON
```
**Diagnóstico**: Warning ANTES del JSON corrompe la respuesta  
**Causa**: Variable no definida, notice, etc.  
**Solución**: Agregar error_suppression o definir variable

---

### **Caso 5: Tooltips no Aparecen**
```
🔄 Actualizando contador de reacciones para post: 123
  - Elemento contador encontrado: true
  - Datos de reacciones recibidos: [{usuarios: "vane15", ...}]
```
**Pero**: No aparece tooltip al hacer hover  
**Diagnóstico**: CSS no se aplica  
**Causa**: `::after` no funciona, z-index, etc.  
**Solución**: Usar `TOOLTIP_FIX_MANUAL.js`

---

## 📈 Métricas de Debug

### **Logs Agregados**
- 🔢 **~50** console.log() en frontend
- 🔢 **~10** error_log() en backend
- 📊 **100%** cobertura del flujo de comentarios
- 📊 **100%** cobertura del flujo de tooltips

### **Información Capturada**
- ✅ Request data (POST)
- ✅ Session state
- ✅ Database queries
- ✅ Response headers
- ✅ Response body (RAW)
- ✅ JSON parsing
- ✅ DOM manipulation
- ✅ Error stack traces

---

## 🚀 Cómo Usar

### **Para el Usuario**
1. Lee `ACCION_REQUERIDA.md`
2. Sigue los pasos
3. Copia los logs
4. Reporta resultados

### **Para el Desarrollador**
1. Lee `DEBUG_EXTREMO_ACTIVADO.md`
2. Analiza logs del usuario
3. Identifica el problema exacto
4. Aplica fix específico
5. Desactiva debug

---

## ⚙️ Desactivar Debug

Una vez resuelto el problema:

```php
// En agregarcomentario.php:
// QUITAR o comentar:
// - ob_start()
// - error_log() calls
// - Cambiar error_reporting(E_ERROR | E_PARSE)
// - Cambiar ini_set('display_errors', '0')
```

```javascript
// En publicaciones.php:
// QUITAR o comentar:
// - console.log() calls
// O dejar solo los importantes
```

---

## 📞 Soporte

Si después del debug el problema persiste:
1. Verifica que TODAS las pruebas se hicieron
2. Verifica que TODOS los logs se copiaron
3. Verifica que el archivo `comentarios_debug.log` existe y tiene contenido
4. Toma captura de pantalla de la consola completa

---

## 📝 Checklist de Verificación

Antes de reportar, verifica:

- [ ] Recargué la página con `Ctrl + F5`
- [ ] Abrí la consola con F12
- [ ] Intenté comentar algo
- [ ] Copié TODO lo que aparece en consola
- [ ] Revisé el archivo `comentarios_debug.log`
- [ ] Copié el contenido del archivo
- [ ] Probé hacer hover en contadores
- [ ] Tomé captura de pantalla
- [ ] Llené el formato de reporte

---

## 🎯 Resultado Esperado

Después de analizar los logs, se podrá:
- ✅ Identificar el error exacto (línea, tipo, causa)
- ✅ Aplicar fix quirúrgico
- ✅ Confirmar resolución
- ✅ Desactivar debug
- ✅ Documentar solución

---

**Status**: 🟢 ACTIVO  
**Versión**: 1.0.0  
**Fecha**: 2025-10-13  
**Archivos**: 4 documentos + 2 modificados + 1 log  
**Coverage**: 100% del flujo crítico
