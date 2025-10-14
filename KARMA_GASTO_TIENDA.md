## 💰 **GASTO DE KARMA EN TIENDA - IMPLEMENTADO**

### ✅ **PROBLEMA RESUELTO:**

**Antes:** Los usuarios podían desbloquear recompensas pero **NO se descontaban los puntos de Karma**.

**Ahora:** Cuando un usuario desbloquea una recompensa, se **descuentan automáticamente los puntos de Karma** y se registra en el historial.

---

### 🔧 **CAMBIOS APLICADOS:**

#### 1. **Eliminada llamada incorrecta:**
```php
// ❌ ANTES (método no existía)
$karmaHelper->modificarKarma($usuario_id, -$recompensa['karma_requerido'], "Desbloqueo: {$recompensa['nombre']}");
```

#### 2. **Implementado descuento directo en base de datos:**
```php
// ✅ AHORA (línea 71-83 karma_tienda.php)
$karma_gastado = -$recompensa['karma_requerido'];
$stmt_karma = $conexion->prepare("
    INSERT INTO karma_social 
    (usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt_karma->execute([
    $usuario_id,
    'compra_tienda',
    $karma_gastado,
    $recompensa_id,
    'recompensa',
    "Compra en tienda: {$recompensa['nombre']}"
]);
```

#### 3. **Nuevo tipo de acción en helper:**
```php
// karma-social-helper.php (línea 10-24)
private const PUNTOS = [
    'comentario_positivo' => 8,
    'comentario_negativo' => -5,
    // ... otros tipos
    'compra_tienda' => 0  // ⬅️ NUEVO (puntos dinámicos negativos)
];
```

---

### 📊 **CÓMO FUNCIONA:**

1. **Usuario desbloquea recompensa:**
   - Costo: 10 Karma
   - Karma actual: 11 puntos

2. **Sistema verifica:**
   ```php
   if ($karma >= $recompensa['karma_requerido']) {
   ```

3. **Sistema descuenta:**
   ```sql
   INSERT INTO karma_social 
   VALUES (usuario_id, 'compra_tienda', -10, recompensa_id, 'recompensa', 'Compra: Marco Dorado')
   ```

4. **Resultado:**
   - Karma nuevo: 1 punto (11 - 10)
   - Historial: "Compra en tienda: Marco Dorado (-10)"
   - Recompensa: Desbloqueada y equipada

---

### 🎯 **REGISTRO EN HISTORIAL:**

Cada compra se registra en la tabla `karma_social` con:
- **tipo_accion:** `compra_tienda`
- **puntos:** `-[karma_requerido]` (negativo)
- **referencia_id:** ID de la recompensa
- **referencia_tipo:** `recompensa`
- **descripcion:** "Compra en tienda: [Nombre Recompensa]"

---

### ✨ **VENTAJAS:**

✅ Los puntos se **gastan realmente** al desbloquear
✅ Queda **registro en historial** de karma
✅ Sistema **coherente** con ganancia/pérdida de karma
✅ Usuarios deben **gestionar bien su karma** para comprar
✅ **Economía interna** funcional en la red social

---

**¡AHORA EL KARMA SE GASTA REALMENTE CUANDO COMPRAN EN LA TIENDA!** 💰✨
