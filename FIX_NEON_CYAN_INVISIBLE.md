## 🔧 **FIX: COLOR NEON CYAN INVISIBLE**

### ❌ **PROBLEMA**
El color **Neon Cyan** (#00ffff) no se veía en la tienda porque:
- Usaba `color` + `text-shadow` que requiere fondo oscuro
- El preview tenía fondo blanco claro `rgba(0,0,0,0.03)`
- El efecto neón es invisible sobre fondos claros

---

### ✅ **SOLUCIÓN APLICADA**

#### 1. **CSS Actualizado** (karma-recompensas.css)

**Antes** (invisible):
```css
.nombre-neon-cyan {
    color: #00ffff;
    text-shadow: 0 0 10px #00ffff,
                 0 0 20px #00ffff,
                 0 0 30px #00ffff;
}
```

**Después** (visible siempre):
```css
.nombre-neon-cyan {
    background: linear-gradient(135deg, #00ffff, #00e5e5, #00ffff);
    background-size: 200% 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 0 8px rgba(0, 255, 255, 0.6));
    animation: nombre-neon-cyan-pulso 2s ease-in-out infinite;
}
```

**Lo mismo para Neon Rosa** (#ff69b4)

---

#### 2. **Preview con Fondo Oscuro** (karma_tienda.php)

Agregué detección automática:
```php
// Determinar si necesita fondo oscuro (colores neón)
$bg_style = 'background: rgba(0,0,0,0.03);';
if (stripos($recompensa['nombre'], 'Neon') !== false) {
    $bg_style = 'background: linear-gradient(135deg, #1a1a2e, #16213e);';
}
```

---

### 🎨 **RESULTADO**

✅ **Neon Cyan**: Ahora visible con gradiente cian brillante + drop-shadow
✅ **Neon Rosa**: Ahora visible con gradiente rosa brillante + drop-shadow
✅ **Fondo oscuro automático**: Solo para colores neón en preview
✅ **Animación**: Ambos colores pulsan con brightness 1 → 1.3
✅ **Funciona en cualquier fondo**: Usa gradiente en lugar de text-shadow

---

**¡RECARGA LA TIENDA Y VERÁS AMBOS COLORES NEÓN PERFECTAMENTE VISIBLES!** 💙💖✨
