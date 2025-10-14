# 🔧 FIX: Método obtenerKarmaUsuario() - Karma Navbar Badge

## ❌ Error Original
```
Fatal error: Uncaught Error: Call to undefined method 
KarmaSocialHelper::obtenerKarmaUsuario() in karma-navbar-badge.php:13
```

## 🔍 Causa del Error
La clase `KarmaSocialHelper` no tenía el método `obtenerKarmaUsuario()` que estaban usando:
- `karma-navbar-badge.php` (línea 13)
- `get_karma.php` (línea 20)

Los métodos existentes eran:
- ✅ `obtenerKarmaTotal($usuario_id)` - Solo retorna karma total
- ✅ `obtenerNivelKarma($karma_total)` - Solo retorna nivel basado en karma
- ❌ `obtenerKarmaUsuario($usuario_id)` - **NO EXISTÍA**

## ✅ Solución Implementada

### 1️⃣ Crear Método Faltante en `karma-social-helper.php`

Se agregó el método `obtenerKarmaUsuario()` que combina toda la información:

```php
/**
 * Obtener karma completo de un usuario (método conveniente)
 */
public function obtenerKarmaUsuario($usuario_id) {
    try {
        // Obtener karma total
        $karmaData = $this->obtenerKarmaTotal($usuario_id);
        $karma_total = $karmaData['karma_total'];
        
        // Obtener nivel
        $nivelData = $this->obtenerNivelKarma($karma_total);
        
        // Obtener próxima recompensa desbloqueada
        $stmt = $this->conexion->prepare("
            SELECT MIN(karma_necesario) as proxima
            FROM karma_recompensas
            WHERE karma_necesario > ?
        ");
        $stmt->execute([$karma_total]);
        $proximaRecompensa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'karma_total' => $karma_total,
            'acciones_totales' => $karmaData['acciones_totales'],
            'nivel' => $nivelData['nivel'],
            'nivel_emoji' => $nivelData['emoji'],
            'nivel_color' => $nivelData['color'],
            'proxima_recompensa' => $proximaRecompensa['proxima'] ?? null
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener karma usuario: " . $e->getMessage());
        return [
            'karma_total' => 0,
            'acciones_totales' => 0,
            'nivel' => 'Novato',
            'nivel_emoji' => '🌱',
            'nivel_color' => '#87CEEB',
            'proxima_recompensa' => null
        ];
    }
}
```

**Retorna:**
- `karma_total` (int) - Puntos totales de karma
- `acciones_totales` (int) - Número de acciones registradas
- `nivel` (string) - Nombre del nivel (Novato, Intermedio, Avanzado, Experto, Maestro, Legendario)
- `nivel_emoji` (string) - Emoji del nivel (🌱⭐✨💫🌟👑)
- `nivel_color` (string) - Color HEX del nivel
- `proxima_recompensa` (int|null) - Karma necesario para desbloquear siguiente recompensa

### 2️⃣ Actualizar `karma-navbar-badge.php`

**ANTES:**
```php
$karmaData = $karmaHelper->obtenerKarmaUsuario($_SESSION['id']);
$karma = $karmaData['karma_total'];
$nivel = $karmaData['nivel']; // ❌ Esto devolvía string "Novato"

$niveles_emoji = [
    1 => '🌱',
    2 => '⭐'
];
$emoji = $niveles_emoji[$nivel] ?? '🌱'; // ❌ No funciona con string
```

**DESPUÉS:**
```php
$karmaData = $karmaHelper->obtenerKarmaUsuario($_SESSION['id']);
$karma = $karmaData['karma_total'];
$nivel_nombre = $karmaData['nivel'];
$emoji = $karmaData['nivel_emoji']; // ✅ Emoji directo

// Convertir nombre a número para el badge
$niveles_numericos = [
    'Novato' => 1,
    'Intermedio' => 2,
    'Avanzado' => 3,
    'Experto' => 4,
    'Maestro' => 5,
    'Legendario' => 6
];
$nivel = $niveles_numericos[$nivel_nombre] ?? 1;
```

### 3️⃣ Actualizar `get_karma.php`

**ANTES:**
```php
echo json_encode([
    'success' => true,
    'karma' => $karmaData['karma_total'],
    'nivel' => $karmaData['nivel'],
    'proxima_recompensa' => $karmaData['proxima_recompensa'] ?? null
]);
```

**DESPUÉS:**
```php
echo json_encode([
    'success' => true,
    'karma' => $karmaData['karma_total'],
    'nivel' => $karmaData['nivel'],
    'nivel_emoji' => $karmaData['nivel_emoji'], // ✅ Agregado
    'proxima_recompensa' => $karmaData['proxima_recompensa']
]);
```

## 📊 Niveles de Karma

| Karma | Nivel | Emoji | Color | Descripción |
|-------|-------|-------|-------|-------------|
| 0-49 | Novato | 🌱 | `#87CEEB` | Principiante |
| 50-99 | Intermedio | ⭐ | `#FFA500` | En progreso |
| 100-249 | Avanzado | ✨ | `#32CD32` | Usuario activo |
| 250-499 | Experto | 💫 | `#4169E1` | Usuario destacado |
| 500-999 | Maestro | 🌟 | `#9370DB` | Usuario ejemplar |
| 1000+ | Legendario | 👑 | `#FFD700` | Usuario legendario |

## 🧪 Testing

### Test 1: Cargar Index con Navbar Badge
```
✅ http://localhost/Converza/app/view/index.php
✅ Badge debe mostrar emoji + karma del usuario actual
✅ Click debe llevar a karma_tienda.php
```

### Test 2: AJAX Update del Karma
```javascript
fetch('/Converza/app/presenters/get_karma.php')
    .then(r => r.json())
    .then(data => {
        console.log('Karma:', data.karma);
        console.log('Nivel:', data.nivel);
        console.log('Emoji:', data.nivel_emoji);
    });
```

### Test 3: Verificar Niveles
```php
$karmaHelper = new KarmaSocialHelper($conexion);

// Usuario con 0 karma
$data = $karmaHelper->obtenerKarmaUsuario(20);
// Debe retornar: nivel='Novato', nivel_emoji='🌱'

// Usuario con 100 karma
$data = $karmaHelper->obtenerKarmaUsuario(1);
// Debe retornar: nivel='Avanzado', nivel_emoji='✨'
```

## 📁 Archivos Modificados

1. ✅ `app/models/karma-social-helper.php`
   - Agregado método `obtenerKarmaUsuario()`
   - Ubicación: Antes de `obtenerKarmaTotal()` (línea ~374)

2. ✅ `app/view/components/karma-navbar-badge.php`
   - Corregido uso de `$karmaData['nivel']`
   - Agregado mapeo de nombre a número
   - Uso directo de `$karmaData['nivel_emoji']`

3. ✅ `app/presenters/get_karma.php`
   - Agregado `nivel_emoji` al JSON response
   - Mejora compatibilidad con JavaScript

## 🎯 Resultado

✅ **Navbar Badge funciona correctamente**
- Muestra emoji según nivel
- Muestra karma actual
- Click lleva a tienda

✅ **AJAX actualiza en tiempo real**
- Respuesta incluye emoji
- Frontend puede actualizar dinámicamente

✅ **Sin errores fatales**
- Método existe en la clase
- Retorna estructura completa
- Maneja errores con defaults

---

**Fecha:** 13 de Octubre, 2025  
**Error:** `Call to undefined method KarmaSocialHelper::obtenerKarmaUsuario()`  
**Status:** ✅ CORREGIDO
