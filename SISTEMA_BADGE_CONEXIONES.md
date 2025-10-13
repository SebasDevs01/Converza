# 🔔 SISTEMA DE BADGE PARA CONEXIONES MÍSTICAS

## ✅ Badge de Contador Implementado

Ahora el botón "⭐ Místicas" muestra un **badge con número** cuando hay conexiones nuevas (no vistas).

---

## 🎨 Apariencia del Badge

```
┌──────────────────────┐
│  ⭐ Místicas    (3)  │  ← Badge morado con número
└──────────────────────┘
```

**Características visuales:**
- 🟣 **Color:** Gradiente morado (#667eea → #764ba2)
- ⚪ **Número blanco:** Cantidad de conexiones nuevas
- 💫 **Animación:** Pulso sutil cada 2 segundos
- 📍 **Posición:** Esquina superior derecha del botón

---

## 🔄 Cómo Funciona

### **1. Detección de Conexiones Nuevas**

```sql
-- Cuenta conexiones no vistas de los últimos 7 días
SELECT COUNT(*) as nuevas
FROM conexiones_misticas
WHERE ((usuario1_id = ? AND visto_usuario1 = 0) 
    OR (usuario2_id = ? AND visto_usuario2 = 0))
AND fecha_deteccion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
```

**Condiciones:**
- ✅ No ha sido vista por el usuario
- ✅ Detectada en los últimos 7 días
- ✅ El usuario está en cualquiera de los dos lados de la conexión

---

### **2. Mostrar Badge**

```php
<?php if ($conexionesNuevas > 0): ?>
    <span class="conexiones-badge">
        <?php echo $conexionesNuevas; ?>
    </span>
<?php endif; ?>
```

**Lógica:**
- Si `$conexionesNuevas > 0` → Muestra badge
- Si `$conexionesNuevas = 0` → No muestra nada

---

### **3. Marcar Como Visto**

**Cuando el usuario abre el offcanvas:**

```javascript
// 1. Usuario hace click en "⭐ Místicas"
// 2. Se cargan las conexiones
// 3. Automáticamente se marcan como vistas
marcarConexionesVistas()

// 4. Badge desaparece
badge.style.display = 'none';
```

**Actualización en BD:**
```sql
UPDATE conexiones_misticas 
SET 
    visto_usuario1 = 1,  -- Si es usuario1
    visto_usuario2 = 1   -- Si es usuario2
WHERE usuario1_id = ? OR usuario2_id = ?
```

---

## 📊 Flujo Completo

```
┌─────────────────────────────────┐
│ Sistema detecta nueva conexión  │
│ (cada 6 horas automáticamente)  │
└──────────────┬──────────────────┘
               ↓
┌─────────────────────────────────┐
│ visto_usuario1 = 0              │
│ visto_usuario2 = 0              │
│ (Marcado como NO visto)         │
└──────────────┬──────────────────┘
               ↓
┌─────────────────────────────────┐
│ Badge aparece: ⭐ Místicas (1) │
│ (Contador visible)              │
└──────────────┬──────────────────┘
               ↓
┌─────────────────────────────────┐
│ Usuario hace click              │
│ Offcanvas se abre               │
└──────────────┬──────────────────┘
               ↓
┌─────────────────────────────────┐
│ JavaScript marca como visto     │
│ visto_usuario1 = 1              │
└──────────────┬──────────────────┘
               ↓
┌─────────────────────────────────┐
│ Badge desaparece                │
│ ⭐ Místicas (sin badge)         │
└─────────────────────────────────┘
```

---

## 🎯 Casos de Uso

### **Caso 1: Nueva Conexión Detectada**
```
Usuario: Ana
Sistema detecta: 3 nuevas conexiones
Badge muestra: (3)
Estado BD: visto = 0
```

### **Caso 2: Usuario Abre Panel**
```
Usuario: Ana hace click en "⭐ Místicas"
Panel se abre con las 3 conexiones
JavaScript marca: visto = 1
Badge desaparece
```

### **Caso 3: Usuario Vuelve Después**
```
Usuario: Ana regresa al día siguiente
Sistema detecta: 2 nuevas conexiones más
Badge muestra: (2)
Estado BD: visto = 0 (solo las nuevas)
```

---

## 📁 Archivos del Sistema

### **Nuevos:**

1. **`app/view/components/conexiones-badge.php`**
   - Componente reutilizable del badge
   - Cuenta conexiones no vistas
   - Muestra badge con animación

2. **`app/presenters/marcar_conexiones_vistas.php`**
   - Endpoint AJAX para marcar vistas
   - Actualiza campos `visto_usuario1` y `visto_usuario2`

### **Modificados:**

3. **`app/view/index.php`** (línea ~262)
   - Include del badge en navbar

4. **`app/presenters/perfil.php`** (línea ~136)
   - Include del badge en navbar

5. **`app/presenters/albumes.php`** (línea ~131)
   - Include del badge en navbar

6. **`app/view/_navbar_panels.php`** (línea ~640)
   - JavaScript para marcar vistas automáticamente

---

## 🎨 Estilos del Badge

```css
.conexiones-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    font-size: 11px;
    font-weight: bold;
    animation: pulse-conexiones 2s infinite;
}

@keyframes pulse-conexiones {
    0%, 100% { 
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 0 0 8px rgba(102, 126, 234, 0);
        transform: scale(1.05);
    }
}
```

**Características:**
- ✅ Gradiente morado temático
- ✅ Animación de pulso cada 2 segundos
- ✅ Sombra que se expande
- ✅ Escala ligeramente en la animación

---

## ⚙️ Configuración

### **Cambiar tiempo de "nuevas" (por defecto 7 días):**

Editar `conexiones-badge.php` línea 11:

```php
AND fecha_deteccion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
//                                               ↑ Cambiar número
```

**Opciones:**
- `1 DAY` - Solo del último día
- `3 DAY` - Últimos 3 días
- `7 DAY` - Última semana (recomendado)
- `30 DAY` - Último mes

---

### **Cambiar color del badge:**

Editar `conexiones-badge.php` línea 20:

```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
/*                                  ↑ color1   ↑ color2      */
```

**Ejemplos:**
- Azul: `#4A90E2 0%, #357ABD 100%`
- Verde: `#56AB2F 0%, #A8E063 100%`
- Naranja: `#FF6B6B 0%, #FFE66D 100%`

---

## 🔍 Comparación con Notificaciones

| Aspecto | Notificaciones | Conexiones Místicas |
|---------|---------------|---------------------|
| **Color** | Rojo (#dc3545) | Morado (gradiente) |
| **Icono** | 🔔 Campana | ⭐ Estrellas |
| **Actualización** | Tiempo real | Cada 6 horas |
| **Marcar visto** | Click en notificación | Abrir panel |
| **Animación** | Pulso rojo | Pulso morado |
| **Propósito** | Actividad reciente | Conexiones nuevas |

---

## 🚀 Ventajas del Sistema

### **1. Notificación Visual**
- Usuario sabe inmediatamente que hay nuevas conexiones
- No necesita abrir el panel para comprobarlo

### **2. Gestión Automática**
- Badge aparece cuando hay nuevas conexiones
- Desaparece automáticamente al ver

### **3. No Intrusivo**
- Solo muestra número, no interrumpe navegación
- Badge sutil pero visible

### **4. Experiencia Consistente**
- Mismo patrón que notificaciones
- Usuario familiarizado con el comportamiento

---

## 📊 Monitoreo (Admin)

### **Ver conexiones no vistas por usuario:**

```sql
SELECT 
    u.usuario,
    COUNT(CASE WHEN cm.usuario1_id = u.id_use AND cm.visto_usuario1 = 0 THEN 1 END) +
    COUNT(CASE WHEN cm.usuario2_id = u.id_use AND cm.visto_usuario2 = 0 THEN 1 END) as no_vistas
FROM usuarios u
LEFT JOIN conexiones_misticas cm 
    ON u.id_use = cm.usuario1_id OR u.id_use = cm.usuario2_id
WHERE cm.fecha_deteccion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY u.usuario
HAVING no_vistas > 0
ORDER BY no_vistas DESC;
```

---

## ✅ Testing Checklist

- [x] Badge aparece cuando hay conexiones nuevas
- [x] Número correcto de conexiones mostrado
- [x] Badge desaparece al abrir offcanvas
- [x] Animación de pulso funciona
- [x] Campos `visto_usuario1` y `visto_usuario2` se actualizan
- [x] Badge funciona en Index, Perfil y Álbumes
- [x] No hay errores en consola JavaScript
- [x] Query SQL es eficiente

---

## 💡 Mejoras Futuras Posibles

### **1. Badge Diferenciado por Tipo**
```
⭐ Místicas (3)
  └─ 2 gustos compartidos
  └─ 1 amigo de amigo
```

### **2. Notificación Push**
Avisar cuando se detecta nueva conexión importante (>80%)

### **3. Historico de Vistas**
Guardar fecha de última vista por usuario

### **4. Badge con Tiempo**
"Nueva hace 2h" en lugar de solo número

---

## 🎉 Conclusión

**El badge de contador está completamente funcional:**

- ✅ Muestra número de conexiones nuevas
- ✅ Animación sutil y atractiva
- ✅ Se marca automáticamente como visto
- ✅ Integrado en las 3 páginas principales
- ✅ Consistente con el sistema existente

**¡Los usuarios ahora saben cuándo tienen nuevas conexiones místicas!** 🔮✨
