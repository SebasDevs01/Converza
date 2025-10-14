## 🐛 **ERROR CORREGIDO: Undefined variable $karma**

### ❌ **PROBLEMA:**
```
Warning: Undefined variable $karma in C:\xampp\htdocs\Converza\app\presenters\karma_tienda.php on line 63
```

**Causa:** La variable `$karma` se usaba antes de ser definida debido a código duplicado.

---

### ✅ **SOLUCIÓN APLICADA:**

#### 1. **Código Duplicado Eliminado:**

**ANTES (3 lugares obteniendo karma):**
```php
// Línea 17-22: Primera obtención (incompleta)
$karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
$karma_actual = $karmaData['karma_total'];

// Línea 63: USO SIN DEFINIR ❌
if ($recompensa && $karma >= $recompensa['karma_requerido']) {

// Línea 128-131: Segunda obtención (duplicada)
$karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
$karma = $karmaData['karma_total'];
$nivel = $karmaData['nivel_data']['nivel'] ?? 1;
```

**AHORA (1 lugar, bien definido):**
```php
// Línea 17-23: Obtención ÚNICA al inicio ✅
$karmaHelper = new KarmaSocialHelper($conexion);
$usuario_id = $_SESSION['id'];

$karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
$karma = $karmaData['karma_total'];
$nivel = $karmaData['nivel_data']['nivel'] ?? 1;
$nivel_titulo = $karmaData['nivel'];
```

#### 2. **Variable Unificada:**
- Todas las referencias usan `$karma` consistentemente
- Se define UNA SOLA VEZ al inicio del archivo
- Se actualiza después de desbloquear/equipar

#### 3. **Actualización en Acciones:**

**Después de desbloquear:**
```php
// Línea 101-103
$karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
$karma = $karmaData['karma_total'];
```

**Después de equipar/desequipar:**
```php
// Línea 127-129
$karmaData = $karmaHelper->obtenerKarmaUsuario($usuario_id);
$karma = $karmaData['karma_total'];
```

---

### 📊 **CAMBIOS REALIZADOS:**

| Línea | Antes | Después |
|-------|-------|---------|
| 17-23 | `$karma_actual` (incompleto) | `$karma`, `$nivel`, `$nivel_titulo` ✅ |
| 32 | `$karma_actual >= ...` | `$karma >= ...` ✅ |
| 101-103 | Ya existía ✅ | Sin cambios |
| 127-129 | Agregado ✅ | Refrescar karma al equipar |
| 128-131 | Duplicado ❌ | **ELIMINADO** ✅ |

---

### 🎯 **RESULTADO:**

✅ **Variable `$karma` definida correctamente desde el inicio**
✅ **Sin código duplicado**
✅ **Se actualiza después de cada acción (desbloquear/equipar)**
✅ **Warning eliminado completamente**

---

**¡ERROR CORREGIDO! Recarga la página y el warning ya no aparecerá.** ✨
