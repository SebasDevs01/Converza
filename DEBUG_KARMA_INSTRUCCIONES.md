# 🧪 DEBUG Y TESTING - SISTEMA DE KARMA

## 🔍 Problema Detectado

El contador de karma se actualiza a **0** en vez de incrementar/decrementar correctamente.

**Síntoma en consola:**
```javascript
✅ Karma parseado correctamente: 0
📊 Valores parseados: {karmaActual: 0, nivel: 1, ...}
```

---

## ✅ Cambios Aplicados para Debug

### 1. save_reaction.php - Debug Mode Activado

```php
define('DEBUG_KARMA', true);  // ← ACTIVADO

function debugLog($message, $data = null) {
    if (DEBUG_KARMA) {
        error_log("🔥 KARMA DEBUG: " . $message . " | " . json_encode($data));
    }
}
```

**Logs agregados:**
- ✅ Datos recibidos (POST)
- ✅ Puntos calculados
- ✅ Karma ANTES de actualizar
- ✅ UPDATE ejecutado + rows affected
- ✅ Karma DESPUÉS de actualizar
- ✅ Nivel recalculado
- ✅ Respuesta final enviada al frontend

---

## 🧪 Test Script Creado

**Ubicación:** `http://localhost/Converza/test_karma.php`

**Qué verifica:**
1. ✅ Sesión activa
2. ✅ Conexión a BD
3. ✅ Karma actual del usuario
4. ✅ Estructura de tabla `usuarios` (columna `karma`)
5. ✅ Test de UPDATE (suma +10 y revierte)
6. ✅ Reacciones recientes del usuario

---

## 🚀 Pasos para Debugging

### Paso 1: Verificar Estado Inicial

```bash
# Abre en navegador:
http://localhost/Converza/test_karma.php
```

**Busca:**
- ✅ ¿Existe la columna `karma` en tabla `usuarios`?
- ✅ ¿Cuál es el karma actual? (si es 0, es el problema)
- ✅ ¿El TEST de UPDATE funciona?

---

### Paso 2: Abrir Log en Tiempo Real

**PowerShell (nueva terminal):**
```powershell
Get-Content 'C:\xampp\php\logs\php_error_log' -Wait | Select-String 'KARMA DEBUG'
```

**Bash/Linux:**
```bash
tail -f /var/log/php_error.log | grep 'KARMA DEBUG'
```

---

### Paso 3: Dar una Reacción

1. Ve al feed (index.php)
2. Da una reacción (👍, ❤️, 😂, etc.)
3. **Observa los logs en tiempo real**

---

## 📊 Logs Esperados (Flujo Normal)

```
🔥 KARMA DEBUG: 📥 Datos recibidos | {"id_usuario":23,"id_publicacion":197,"tipo_reaccion":"me_encanta"}

🔥 KARMA DEBUG: 🎯 Puntos calculados | {"tipo_reaccion":"me_encanta","puntos":10,"mensaje":"❤️ ¡Me encanta!","tipo":"positivo"}

🔥 KARMA DEBUG: 📊 Karma ANTES de actualizar | {"usuario_id":23,"karma_antes":0}

🔥 KARMA DEBUG: 💾 UPDATE ejecutado | {"resultado":true,"rows_affected":1}

🔥 KARMA DEBUG: 📊 Karma DESPUÉS de actualizar | {"karma_despues":10,"diferencia":10,"esperado":10}

🔥 KARMA DEBUG: 🏆 Nivel recalculado | {"nivel":1,"titulo":"Novato","emoji":"🌱"}

🔥 KARMA DEBUG: ✅ karma_actualizado final | {"karma":"10","nivel":1,"nivel_titulo":"Novato","nivel_emoji":"🌱"}

🔥 KARMA DEBUG: 🚀 RESPUESTA FINAL | {"success":true,"karma_actualizado":{"karma":"10",...}}
```

---

## 🔍 Posibles Problemas y Soluciones

### Problema 1: Columna `karma` no existe

**Síntoma:**
```
❌ Columna 'karma' NO existe
```

**Solución:**
```sql
ALTER TABLE usuarios ADD COLUMN karma INT DEFAULT 0 NOT NULL;
```

---

### Problema 2: UPDATE no afecta ninguna fila

**Síntoma:**
```
💾 UPDATE ejecutado | {"resultado":true,"rows_affected":0}
```

**Causa:** El `id_use` no existe o no coincide

**Solución:**
```sql
SELECT id_use, usuario, karma FROM usuarios WHERE id_use = 23;
```

---

### Problema 3: Karma DESPUÉS sigue en 0

**Síntoma:**
```
📊 Karma ANTES: 0
📊 Karma DESPUÉS: 0
diferencia: 0 (esperado: 10)
```

**Causas posibles:**
1. Columna `karma` es NULL por defecto
2. Trigger en BD que resetea valores
3. Transacción no se commitea

**Solución:**
```sql
-- Verificar valor actual
SELECT id_use, karma FROM usuarios WHERE id_use = 23;

-- Actualizar manualmente
UPDATE usuarios SET karma = 0 WHERE id_use = 23;

-- Probar UPDATE
UPDATE usuarios SET karma = karma + 10 WHERE id_use = 23;

-- Verificar
SELECT karma FROM usuarios WHERE id_use = 23;
```

---

### Problema 4: Usuario tiene karma pero frontend muestra 0

**Síntoma:**
- BD: `karma = 100`
- Frontend: `karma = 0`

**Causa:** `obtenerKarmaUsuario()` devuelve datos incorrectos

**Solución:** Verificar `KarmaSocialHelper.php`:
```php
public function obtenerKarmaUsuario($userId) {
    $stmt = $this->conexion->prepare("SELECT karma FROM usuarios WHERE id_use = ?");
    $stmt->execute([$userId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'karma_total' => intval($data['karma'] ?? 0),
        // ...
    ];
}
```

---

## 🧰 Comandos de Testing Rápido

### Test 1: Ver karma de todos los usuarios
```sql
SELECT id_use, usuario, karma FROM usuarios ORDER BY karma DESC LIMIT 10;
```

### Test 2: Actualizar karma manualmente
```sql
UPDATE usuarios SET karma = 100 WHERE id_use = 23;
```

### Test 3: Ver reacciones del usuario
```sql
SELECT r.*, p.contenido 
FROM reacciones r
JOIN publicaciones p ON r.id_publicacion = p.id_pub
WHERE r.id_usuario = 23
ORDER BY r.fecha DESC;
```

### Test 4: Limpiar logs
```powershell
Clear-Content 'C:\xampp\php\logs\php_error_log'
```

---

## 📋 Checklist de Debugging

- [ ] Abrí `test_karma.php` y verifiqué estado
- [ ] Columna `karma` existe en tabla `usuarios`
- [ ] Karma actual del usuario NO es NULL
- [ ] TEST de UPDATE funciona en `test_karma.php`
- [ ] Abrí terminal con logs en tiempo real
- [ ] Di una reacción en el feed
- [ ] Vi los logs de `KARMA DEBUG`
- [ ] Karma DESPUÉS > Karma ANTES
- [ ] Frontend muestra karma actualizado

---

## 🎯 Resultado Esperado

Después de seguir estos pasos, deberías ver:

1. ✅ **test_karma.php** muestra karma actual correcto
2. ✅ **Logs** muestran UPDATE exitoso
3. ✅ **Frontend** muestra contador actualizado
4. ✅ **BD** tiene karma incrementado

**Si aún falla:** Copia los logs completos y envíalos para análisis.

---

## 🔄 Desactivar Debug Mode

Una vez solucionado:

```php
// En save_reaction.php línea 7:
define('DEBUG_KARMA', false);  // ← DESACTIVAR
```

---

## 📞 Logs de Ayuda

Si necesitas ayuda, proporciona:

1. **Output de test_karma.php** (captura de pantalla)
2. **Logs completos** del terminal (desde "📥 Datos recibidos" hasta "🚀 RESPUESTA FINAL")
3. **Consulta SQL:**
   ```sql
   SELECT id_use, usuario, karma FROM usuarios WHERE id_use = TU_ID;
   ```

---

**¡Buena suerte con el debugging!** 🚀
