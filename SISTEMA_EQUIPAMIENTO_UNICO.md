# 🔒 Sistema de Equipamiento Único - Tienda de Karma

## 🎯 Objetivo

Implementar un sistema donde **solo se puede equipar 1 ítem por categoría a la vez**, forzando a los usuarios a desequipar el ítem actual antes de equipar otro del mismo tipo.

---

## 📋 Categorías Afectadas

El sistema de equipamiento único se aplica a **TODAS** las categorías de recompensas:

1. 🖼️ **Marcos de Perfil** - Solo 1 marco activo
2. 🎨 **Temas Personalizados** - Solo 1 tema activo
3. 🏆 **Insignias** - Solo 1 insignia equipada
4. ⭐ **Íconos Especiales** - Solo 1 ícono visible
5. 🌈 **Colores de Nombre** - Solo 1 color aplicado
6. 🎁 **Sticker Bonus** - Solo 1 pack de stickers activo

---

## ⚙️ Cómo Funciona

### 1️⃣ **Al Desbloquear una Recompensa**

Cuando un usuario desbloquea una recompensa nueva:

```php
// Para TODOS los tipos: desequipar otros del mismo tipo antes de equipar
$stmtDesequipar = $conexion->prepare("
    UPDATE usuario_recompensas ur
    JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
    SET ur.equipada = FALSE
    WHERE ur.usuario_id = ? AND kr.tipo = ?
");
$stmtDesequipar->execute([$usuario_id, $tipo]);
```

**Resultado:**
- ✅ La recompensa nueva se equipa automáticamente
- ✅ Todas las demás del mismo tipo se desequipan
- ✅ Solo queda 1 ítem equipado por categoría

---

### 2️⃣ **Al Equipar Manualmente**

Cuando un usuario intenta equipar una recompensa que ya tiene desbloqueada:

```php
// Si va a equipar, desequipar otros del mismo tipo primero
if ($nueva_equipada) {
    $tipo = $usuarioRecompensa['tipo'];
    $stmtDesequiparOtros = $conexion->prepare("
        UPDATE usuario_recompensas ur
        JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
        SET ur.equipada = FALSE
        WHERE ur.usuario_id = ? AND kr.tipo = ? AND ur.recompensa_id != ?
    ");
    $stmtDesequiparOtros->execute([$usuario_id, $tipo, $recompensa_id]);
}
```

**Resultado:**
- ✅ El ítem anterior se desequipa automáticamente
- ✅ El nuevo ítem se equipa
- ✅ Mensaje: "Recompensa equipada (otras del mismo tipo se desequiparon automáticamente)"

---

### 3️⃣ **Validación en la Interfaz**

La interfaz detecta si ya hay un ítem equipado del mismo tipo:

```php
// Obtener qué tipos tienen ítems equipados
$stmtTiposEquipados = $conexion->prepare("
    SELECT DISTINCT kr.tipo 
    FROM usuario_recompensas ur
    JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
    WHERE ur.usuario_id = ? AND ur.equipada = TRUE
");
$stmtTiposEquipados->execute([$usuario_id]);
$tipos_con_equipado = [];
while ($row = $stmtTiposEquipados->fetch(PDO::FETCH_ASSOC)) {
    $tipos_con_equipado[] = $row['tipo'];
}
```

**Estados de Botones:**

| Estado | Botón | Mensaje |
|--------|-------|---------|
| ✅ **Equipada** | `Desequipar` (verde) | Permite desequipar |
| ❌ **Otro equipado** | `Ya tienes un [tipo] equipado` (gris, deshabilitado) | "Desequipa el otro primero" |
| ⚪ **Ninguna equipada** | `Equipar` (azul) | Permite equipar |

---

## 🎨 Estados Visuales

### Estado 1: Equipada Actualmente
```
┌─────────────────────────────────┐
│  🖼️ Marco Dorado                │
│  Marco dorado brillante          │
│  ⭐ 50 Karma                     │
│                                  │
│  ┌──────────────────────────┐   │
│  │  ✅ Desequipar           │   │
│  └──────────────────────────┘   │
└─────────────────────────────────┘
```

### Estado 2: Otro Ítem Equipado
```
┌─────────────────────────────────┐
│  🖼️ Marco Neón                  │
│  Marco con luces neón            │
│  ⭐ 100 Karma                    │
│                                  │
│  ┌──────────────────────────┐   │
│  │  ⛔ Ya tienes un marco    │   │
│  │     equipado              │   │
│  └──────────────────────────┘   │
│  ℹ️ Desequipa el otro primero   │
└─────────────────────────────────┘
```

### Estado 3: Disponible para Equipar
```
┌─────────────────────────────────┐
│  🖼️ Marco Arcoíris              │
│  Marco con colores vibrantes     │
│  ⭐ 150 Karma                    │
│                                  │
│  ┌──────────────────────────┐   │
│  │  Equipar                  │   │
│  └──────────────────────────┘   │
└─────────────────────────────────┘
```

---

## 📝 Flujo de Usuario

### Escenario 1: Desbloquear Nueva Recompensa

```
1. Usuario tiene "Marco Dorado" equipado
2. Usuario compra "Marco Neón" (100 karma)
   → Sistema desequipa automáticamente "Marco Dorado"
   → Sistema equipa automáticamente "Marco Neón"
   → Mensaje: "¡Desbloqueado: Marco Neón! 🖼️ Marco aplicado a tu avatar (Equipado automáticamente)"
3. Ahora solo "Marco Neón" está equipado
```

### Escenario 2: Cambiar Manualmente

```
1. Usuario tiene "Marco Neón" equipado
2. Usuario ve "Marco Dorado" (ya desbloqueado)
   → Botón: "Equipar" (disponible)
3. Usuario hace clic en "Equipar"
   → Sistema desequipa "Marco Neón" automáticamente
   → Sistema equipa "Marco Dorado"
   → Mensaje: "Recompensa equipada (otras del mismo tipo se desequiparon automáticamente)"
4. Ahora solo "Marco Dorado" está equipado
```

### Escenario 3: Intentar Equipar Sin Desequipar

```
1. Usuario tiene "Marco Neón" equipado
2. Usuario ve "Marco Arcoíris" (ya desbloqueado)
   → Botón: "⛔ Ya tienes un marco equipado" (deshabilitado)
   → Mensaje: "ℹ️ Desequipa el otro primero"
3. Usuario debe:
   - Ir a "Marco Neón"
   - Hacer clic en "Desequipar"
   - Volver a "Marco Arcoíris"
   - Ahora puede hacer clic en "Equipar"
```

---

## 🔧 Archivos Modificados

### `karma_tienda.php` - Líneas 40-70

**Cambio:** Sistema de auto-equipado único al desbloquear

```php
// ANTES: Permitía múltiples ítems equipados en iconos/colores/stickers
if (in_array($tipo, ['icono', 'color_nombre', 'color', 'sticker'])) {
    $auto_equipar = true; // ❌ Sin desequipar otros
}

// AHORA: Todos los tipos desequipan otros antes de equipar
$stmtDesequipar = $conexion->prepare("
    UPDATE usuario_recompensas ur
    JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
    SET ur.equipada = FALSE
    WHERE ur.usuario_id = ? AND kr.tipo = ?
");
$stmtDesequipar->execute([$usuario_id, $tipo]); // ✅ Desequipa TODOS del mismo tipo
```

### `karma_tienda.php` - Líneas 136-173

**Cambio:** Equipamiento manual único

```php
// Si va a equipar, desequipar otros del mismo tipo primero
if ($nueva_equipada) {
    $tipo = $usuarioRecompensa['tipo'];
    $stmtDesequiparOtros = $conexion->prepare("
        UPDATE usuario_recompensas ur
        JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
        SET ur.equipada = FALSE
        WHERE ur.usuario_id = ? AND kr.tipo = ? AND ur.recompensa_id != ?
    ");
    $stmtDesequiparOtros->execute([$usuario_id, $tipo, $recompensa_id]);
}
```

### `karma_tienda.php` - Líneas 175-195

**Cambio:** Consulta de tipos equipados

```php
// Obtener qué tipos tienen ítems equipados (para validación)
$stmtTiposEquipados = $conexion->prepare("
    SELECT DISTINCT kr.tipo 
    FROM usuario_recompensas ur
    JOIN karma_recompensas kr ON ur.recompensa_id = kr.id
    WHERE ur.usuario_id = ? AND ur.equipada = TRUE
");
$stmtTiposEquipados->execute([$usuario_id]);
$tipos_con_equipado = [];
while ($row = $stmtTiposEquipados->fetch(PDO::FETCH_ASSOC)) {
    $tipos_con_equipado[] = $row['tipo'];
}
```

### `karma_tienda.php` - Líneas 710-750

**Cambio:** Interfaz con validación de equipamiento único

```php
<?php 
// Verificar si hay otro ítem del mismo tipo equipado
$tipo_actual = $recompensa['tipo'];
$hay_otro_equipado = in_array($tipo_actual, $tipos_con_equipado) && !$equipada;
?>

<?php if ($equipada): ?>
    <!-- Botón: Desequipar -->
<?php elseif ($hay_otro_equipado): ?>
    <!-- Botón deshabilitado: Ya tienes un [tipo] equipado -->
<?php else: ?>
    <!-- Botón: Equipar -->
<?php endif; ?>
```

---

## ✅ Beneficios del Sistema

1. ✅ **Claridad Visual** - Usuario siempre sabe qué tiene equipado
2. ✅ **Previene Confusión** - No múltiples marcos/temas activos
3. ✅ **Experiencia Intuitiva** - Comportamiento predecible
4. ✅ **Consistencia** - Misma lógica para todas las categorías
5. ✅ **Feedback Claro** - Mensajes explicativos en cada acción
6. ✅ **Prevención de Errores** - Botones deshabilitados cuando no se puede equipar

---

## 🧪 Casos de Prueba

### ✅ Prueba 1: Desbloquear con Auto-Equipado

```
1. Desbloquear "Tema Oscuro"
   ✓ Se equipa automáticamente
2. Desbloquear "Tema Galaxy"
   ✓ "Tema Oscuro" se desequipa
   ✓ "Tema Galaxy" se equipa
   ✓ Solo 1 tema equipado
```

### ✅ Prueba 2: Equipar Manualmente

```
1. Tener "Icono Estrella" y "Icono Corazón" desbloqueados
2. "Icono Estrella" está equipado
3. Intentar equipar "Icono Corazón"
   ✓ Botón deshabilitado con mensaje
4. Desequipar "Icono Estrella"
5. Equipar "Icono Corazón"
   ✓ Ahora solo "Icono Corazón" está equipado
```

### ✅ Prueba 3: Cambio Rápido

```
1. Tener 5 marcos desbloqueados
2. Marco A está equipado
3. Hacer clic en "Equipar" de Marco B
   ✓ Marco A se desequipa automáticamente
   ✓ Marco B se equipa
   ✓ Mensaje de confirmación
```

---

## 🎯 Experiencia de Usuario Mejorada

| Antes | Ahora |
|-------|-------|
| ❌ Podía equipar múltiples marcos | ✅ Solo 1 marco a la vez |
| ❌ Confusión visual en perfil | ✅ Visual limpio y claro |
| ❌ No sabía cuál estaba activo | ✅ Indicador visual obvio |
| ❌ Comportamiento inconsistente | ✅ Lógica unificada |
| ❌ Sin feedback al equipar múltiples | ✅ Mensajes claros y preventivos |

---

## 📊 Impacto Técnico

- **Consultas SQL:** +1 consulta para obtener tipos equipados
- **Validaciones:** +3 validaciones en frontend
- **Mensajes:** +1 mensaje de confirmación automática
- **Performance:** Sin impacto (consulta simple con índices)
- **Compatibilidad:** ✅ Compatible con sistema existente

---

## 🚀 Próximas Mejoras (Opcional)

1. **Intercambio Rápido:** Botón "Cambiar por..." en ítem equipado
2. **Preview Comparativo:** Mostrar antes/después al cambiar
3. **Historial:** Ver qué estuvo equipado antes
4. **Favoritos:** Marcar ítems favoritos para cambio rápido
5. **Presets:** Guardar combinaciones completas (marco + tema + color)

---

## ✅ Conclusión

El sistema de **equipamiento único** garantiza que los usuarios solo tengan **1 ítem activo por categoría**, mejorando la experiencia visual y eliminando confusiones sobre qué está aplicado en su perfil.

**Fecha de implementación:** 14 de Octubre, 2025  
**Desarrollador:** SebasDevs01  
**Tipo de mejora:** UX + Lógica de Negocio
