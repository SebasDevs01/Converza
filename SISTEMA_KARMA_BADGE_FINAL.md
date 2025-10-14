# 🎯 SISTEMA DE KARMA CON BADGE CONTADOR

## 📋 Resumen de Cambios

He implementado el sistema de karma **EXACTAMENTE** como lo pediste:

### ✅ Lo Implementado

1. **❌ ELIMINADO: Popup flotante** 
   - Ya no aparece el "+8" flotante que no te gustaba
   
2. **✅ BADGE EN BOTÓN DE KARMA**
   - Badge contador como el de notificaciones 🔔
   - Muestra "+8" o "-7" según el comportamiento
   - Aparece en verde para positivo, rojo para negativo
   - Se queda 5 segundos y desaparece con animación
   
3. **✅ SISTEMA DE NIVELES PROGRESIVO**
   - Cada 100 puntos = 1 nivel
   - Nivel 1 = 0-99 puntos (Novato 🌱)
   - Nivel 2 = 100-199 puntos (Intermedio ⭐)
   - Nivel 3 = 200-299 puntos (Avanzado ✨)
   - Nivel 4 = 300-399 puntos (Experto 💫)
   - Nivel 5+ = Maestro 🌟, Legendario 👑
   
4. **✅ ACTUALIZACIÓN EN TIENDA**
   - Los puntos se actualizan cada 3 segundos automáticamente
   - Animación cuando cambian
   - Muestra nivel numérico correcto

---

## 🎨 Cómo Funciona Visualmente

### Botón de Karma:
```
┌─────────────────────────┐
│  🌱  125    [+8]        │  ← Badge contador
│      Nv.2               │
└─────────────────────────┘
```

### Flujo:
1. Usuario comenta algo positivo → "+8 karma"
2. **Badge aparece** en botón: `[+8]` en verde
3. **Puntos se suman**: 125 → 133
4. **Nivel sube** si llega a 200: Nv.2 → Nv.3
5. Badge desaparece después de 5 segundos
6. En **tienda** los puntos ya están actualizados

---

## 📁 Archivos Modificados

### 1. `karma-social-helper.php`
**Cambio**: Sistema de niveles progresivo
```php
// Antes: Niveles fijos (50, 100, 250, 500, 1000)
// Ahora: Cada 100 puntos = +1 nivel
$nivel = floor($karma_total / 100) + 1;
```

### 2. `karma-navbar-badge.php`
**Cambio**: Badge contador en lugar de popup flotante
```html
<span class="karma-badge-counter">+8</span>
```
- Estilo igual que notificaciones
- Verde para positivo, rojo para negativo
- Animación de aparición con bounce

### 3. `get_karma.php`
**Cambio**: Devuelve nivel numérico
```json
{
  "karma": 125,
  "nivel": 2,        // ← Número
  "nivel_titulo": "Intermedio"
}
```

### 4. `karma_tienda.php`
**Cambio**: Polling cada 3 segundos
```javascript
setInterval(actualizarKarmaTienda, 3000);
```

---

## 🧪 Cómo Probar

### Test 1: Badge Contador
1. Ve a `index.php`
2. Comenta algo positivo: "Excelente"
3. **Mira el botón de karma** → Badge `[+8]` aparece
4. Espera 5 segundos → Badge desaparece

### Test 2: Niveles Progresivos
```sql
-- Ver tu karma actual
USE converza;
SELECT id, usuario, karma_social FROM usuarios WHERE id = TU_ID;

-- Si tienes 95 puntos (Nivel 1):
-- Gana 10 puntos → 105 puntos (Nivel 2) ✨

-- Si tienes 195 puntos (Nivel 2):
-- Gana 10 puntos → 205 puntos (Nivel 3) 💫
```

### Test 3: Actualización en Tienda
1. Abre `karma_tienda.php` en pestaña 1
2. Abre `index.php` en pestaña 2
3. En pestaña 2: Comenta para ganar karma
4. Vuelve a pestaña 1 (SIN recargar)
5. **En ~3 segundos**: Puntos se actualizan con animación

---

## 📊 Tabla de Niveles

| Nivel | Puntos      | Título      | Emoji |
|-------|-------------|-------------|-------|
| 1     | 0-99        | Novato      | 🌱    |
| 2     | 100-199     | Intermedio  | ⭐    |
| 3     | 200-299     | Avanzado    | ✨    |
| 4     | 300-399     | Experto     | 💫    |
| 5     | 400-499     | Experto     | 💫    |
| 6     | 500-599     | Maestro     | 🌟    |
| 7     | 600-699     | Maestro     | 🌟    |
| 8+    | 700-799     | Maestro     | 🌟    |
| 10+   | 1000+       | Legendario  | 👑    |

---

## 🎯 Diferencias Clave

### ❌ ANTES (Popup Flotante)
```
[Comentas] → Aparece popup flotante "+8" en esquina
           → Se va volando hacia arriba
           → Desaparece
```

### ✅ AHORA (Badge Contador)
```
[Comentas] → Badge [+8] aparece EN EL BOTÓN
           → Puntos se suman en botón
           → Badge desaparece después de 5s
           → Puntos quedan sumados
```

---

## 🔧 Detalles Técnicos

### Badge HTML:
```html
<span class="karma-badge-counter pulse">+8</span>
```

### Estilos:
- `background: linear-gradient(135deg, #10b981, #059669)` (verde)
- `background: linear-gradient(135deg, #ef4444, #dc2626)` (rojo)
- `animation: badge-pulse 1.5s infinite` (pulso constante)
- `box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3)` (borde blanco)

### Comportamiento:
1. Badge aparece con escala 0 → 1.2 → 1 (bounce)
2. Pulsa suavemente (escala 1 ↔ 1.1)
3. Después de 5s: escala 1 → 0 y se elimina del DOM

---

## 🚀 Próximos Pasos

Si quieres personalizar más:

1. **Cambiar duración del badge**: 
   - En `karma-navbar-badge.php` línea ~180: `setTimeout(..., 5000)` → `setTimeout(..., 10000)`
   
2. **Cambiar frecuencia de actualización**:
   - En `karma_tienda.php` línea ~392: `setInterval(..., 3000)` → `setInterval(..., 5000)`
   
3. **Ajustar umbrales de nivel**:
   - En `karma-social-helper.php` línea ~499: `$nivel = floor($karma_total / 100) + 1`
   - Cambiar `100` a `200` para niveles más difíciles

---

## ✅ Checklist Final

- [x] Popup flotante eliminado
- [x] Badge contador implementado
- [x] Sistema de niveles progresivo (cada 100 pts)
- [x] Actualización automática en tienda (3s)
- [x] Badge verde para positivo, rojo para negativo
- [x] Animación de aparición/desaparición
- [x] Puntos se acumulan correctamente
- [x] Nivel se muestra numéricamente

---

¡El sistema está completo y funcionando como lo pediste! 🎉
