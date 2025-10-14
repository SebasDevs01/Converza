# 🎯 SISTEMA DE AUTO-EQUIPADO INTELIGENTE
## Aplicación Automática de Recompensas al Desbloquear

---

## 🎨 **¿QUÉ ES EL AUTO-EQUIPADO?**

El sistema de **auto-equipado inteligente** aplica automáticamente las recompensas desbloqueadas según su tipo y lugar de uso, **sin necesidad de equipar manualmente**.

---

## 📊 **REGLAS DE AUTO-EQUIPADO POR TIPO**

| Tipo | ¿Auto-Equipa? | ¿Múltiples Activos? | Comportamiento |
|------|---------------|---------------------|----------------|
| **🖼️ Marco** | ✅ SÍ | ❌ NO (solo 1) | Reemplaza marco anterior |
| **🎨 Tema** | ✅ SÍ | ❌ NO (solo 1) | Reemplaza tema anterior |
| **⭐ Ícono** | ✅ SÍ | ❌ NO (solo 1) | Reemplaza ícono anterior |
| **🌈 Color Nombre** | ✅ SÍ | ❌ NO (solo 1) | Reemplaza color anterior |
| **😊 Sticker** | ✅ SÍ | ✅ SÍ (múltiples) | Se añade a colección |
| **🏅 Insignia** | ⚠️ AUTOMÁTICO | ✅ SÍ (por nivel) | No requiere equipar |

---

## 🔄 **FLUJO DE DESBLOQUEO**

### **Paso 1: Usuario Desbloquea**
```
Usuario en Tienda → Clic "Desbloquear" → Confirma
```

### **Paso 2: Sistema Verifica**
```php
if ($karma >= $recompensa['karma_requerido']) {
    // ✅ Tiene suficiente karma
    // Procede al desbloqueo
}
```

### **Paso 3: Descuenta Karma**
```php
$karmaHelper->modificarKarma($usuario_id, -$costo, "Desbloqueo: Nombre");
```

### **Paso 4: Aplica Auto-Equipado** ⚡
```php
// 🎯 LÓGICA INTELIGENTE
switch($tipo) {
    case 'marco':
    case 'tema':
        // Desequipar otros del mismo tipo
        desequiparTipo($usuario_id, $tipo);
        // Equipar nuevo
        $auto_equipar = true;
        break;
        
    case 'icono':
    case 'color_nombre':
        // Desequipar otros del mismo tipo
        desequiparTipo($usuario_id, $tipo);
        // Equipar nuevo
        $auto_equipar = true;
        break;
        
    case 'sticker':
        // NO desequipa otros (múltiples activos)
        $auto_equipar = true;
        break;
        
    case 'insignia':
        // Automático por nivel
        $auto_equipar = false;
        break;
}
```

### **Paso 5: Inserta en DB**
```php
INSERT INTO usuario_recompensas (usuario_id, recompensa_id, equipada) 
VALUES ($usuario_id, $recompensa_id, $auto_equipar);
```

### **Paso 6: Mensaje de Confirmación** 🎉
```
"¡Desbloqueado: Marco Dorado! 🖼️ Marco aplicado a tu avatar (Equipado automáticamente)"
```

---

## 🎨 **EJEMPLOS POR TIPO**

### **1. Marcos de Perfil** 🖼️

#### Comportamiento:
- ✅ Se equipa automáticamente
- ❌ Solo 1 marco activo a la vez
- 🔄 Reemplaza marco anterior

#### Ejemplo:
```
Usuario desbloquea "Marco Dorado"
→ Sistema desequipa "Marco Anterior" (si existía)
→ Sistema equipa "Marco Dorado"
→ Avatar muestra marco dorado inmediatamente
```

#### Código:
```php
if ($tipo == 'marco') {
    // Desequipar otros marcos
    $stmtDesequipar->execute([$usuario_id, 'marco']);
    // Equipar nuevo
    $auto_equipar = true;
}
```

---

### **2. Temas** 🎨

#### Comportamiento:
- ✅ Se equipa automáticamente
- ❌ Solo 1 tema activo a la vez
- 🔄 Reemplaza tema anterior

#### Ejemplo:
```
Usuario desbloquea "Tema Neon Cyberpunk"
→ Sistema desequipa "Tema Anterior" (si existía)
→ Sistema equipa "Tema Neon Cyberpunk"
→ Perfil muestra colores neón inmediatamente
```

---

### **3. Íconos Especiales** ⭐

#### Comportamiento:
- ✅ Se equipa automáticamente
- ❌ Solo 1 ícono activo a la vez
- 🔄 Reemplaza ícono anterior

#### Ejemplo:
```
Usuario desbloquea "Ícono Corona 👑"
→ Sistema desequipa "Ícono Anterior" (si existía)
→ Sistema equipa "Ícono Corona"
→ Nombre muestra "Juan Pérez 👑" inmediatamente
```

---

### **4. Colores de Nombre** 🌈

#### Comportamiento:
- ✅ Se equipa automáticamente
- ❌ Solo 1 color activo a la vez
- 🔄 Reemplaza color anterior

#### Ejemplo:
```
Usuario desbloquea "Nombre Arcoíris"
→ Sistema desequipa "Color Anterior" (si existía)
→ Sistema equipa "Nombre Arcoíris"
→ Nombre muestra gradiente arcoíris inmediatamente
```

---

### **5. Stickers Premium** 😊

#### Comportamiento:
- ✅ Se equipa automáticamente
- ✅ Múltiples stickers activos simultáneamente
- ➕ Se añade a la colección (NO reemplaza)

#### Ejemplo:
```
Usuario desbloquea "Pack Premium"
→ Sistema NO desequipa stickers anteriores
→ Sistema equipa "Pack Premium"
→ Perfil muestra stickers antiguos + nuevos
→ Ejemplo: [😊 Feliz] [💪 Motivado] [🎨 Creativo]
```

#### Diferencia Clave:
- **Pack Básico** (50 karma): 😊😢🤩
- **Pack Premium** (120 karma): 😌💪🎨
- **Ambos desbloqueados** → Perfil muestra: 😊😢🤩😌💪🎨 (6 stickers)

---

### **6. Insignias** 🏅

#### Comportamiento:
- ⚠️ **NO se equipan** (automáticas por nivel)
- ✅ Aparecen según nivel del usuario
- 📊 Sistema lee nivel y asigna insignia

#### Ejemplo:
```
Usuario alcanza Nivel 5
→ Sistema verifica nivel
→ Renderiza insignia "Intermedio" ⭐⭐
→ No requiere desbloqueo ni equipado manual
```

---

## 💾 **ESTRUCTURA EN BASE DE DATOS**

### **Tabla `usuario_recompensas`**
```sql
CREATE TABLE usuario_recompensas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    recompensa_id INT NOT NULL,
    fecha_desbloqueo DATETIME DEFAULT NOW(),
    equipada TINYINT(1) DEFAULT 0,  -- ⚡ Auto-equipada al desbloquear
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use),
    FOREIGN KEY (recompensa_id) REFERENCES karma_recompensas(id)
);
```

### **Ejemplo de Datos**
```
| id | usuario_id | recompensa_id | equipada | tipo          |
|----|------------|---------------|----------|---------------|
| 1  | 5          | 10            | 1        | marco         |
| 2  | 5          | 15            | 1        | tema          |
| 3  | 5          | 20            | 1        | icono         |
| 4  | 5          | 25            | 1        | color_nombre  |
| 5  | 5          | 30            | 1        | sticker       |
| 6  | 5          | 31            | 1        | sticker       |
| 7  | 5          | 32            | 1        | sticker       |
```

**Nota**: Usuario tiene 3 packs de stickers equipados simultáneamente (IDs 30, 31, 32).

---

## 🎯 **VENTAJAS DEL SISTEMA**

### **Para Usuarios**
1. ✨ **Inmediatez**: No hay pasos adicionales
2. 🎮 **UX Fluida**: Desbloquea → Ve resultado
3. 🤔 **Sin Confusión**: No busca "¿Dónde lo equipo?"
4. 😊 **Satisfacción**: Gratificación instantánea

### **Para la Plataforma**
1. 📈 **Mayor Engagement**: Usuarios ven valor inmediato
2. 🎯 **Conversión**: Motivación para desbloquear más
3. 🔄 **Retención**: Experiencia sin fricciones
4. 💰 **Monetización**: Usuarios gastan más karma

---

## 🔧 **CASOS ESPECIALES**

### **Caso 1: Usuario Desbloquea Segundo Marco**
```
Estado Inicial:
- Marco Dorado (equipado)

Usuario desbloquea "Marco Fuego":
→ Sistema desequipa "Marco Dorado"
→ Sistema equipa "Marco Fuego"
→ Usuario ve "Marco Fuego" en avatar

Usuario puede re-equipar "Marco Dorado" manualmente después
```

### **Caso 2: Usuario Desbloquea Múltiples Stickers**
```
Estado Inicial:
- Pack Básico (equipado): 😊😢🤩

Usuario desbloquea "Pack Premium":
→ Sistema NO desequipa Pack Básico
→ Sistema equipa "Pack Premium": 😌💪🎨
→ Usuario ve ambos packs: 😊😢🤩😌💪🎨

Usuario desbloquea "Pack Elite":
→ Sistema equipa "Pack Elite": 🤔⚡🔥
→ Usuario ve los 3 packs: 😊😢🤩😌💪🎨🤔⚡🔥
```

### **Caso 3: Usuario Cambia de Ícono**
```
Estado Inicial:
- Ícono Estrella ⭐ (equipado)

Usuario desbloquea "Ícono Corona 👑":
→ Sistema desequipa "Ícono Estrella ⭐"
→ Sistema equipa "Ícono Corona 👑"
→ Nombre muestra: "Juan Pérez 👑"

Usuario puede volver a "Ícono Estrella" desde tienda
```

---

## 📝 **MENSAJES DE CONFIRMACIÓN**

### **Por Tipo de Recompensa**:

```php
switch($tipo) {
    case 'marco':
        $mensaje = '🖼️ Marco aplicado a tu avatar';
        break;
    case 'tema':
        $mensaje = '🎨 Tema aplicado a tu perfil';
        break;
    case 'icono':
        $mensaje = '⭐ Ícono visible junto a tu nombre';
        break;
    case 'color_nombre':
        $mensaje = '🌈 Color aplicado a tu nombre';
        break;
    case 'sticker':
        $mensaje = '😊 Stickers disponibles en tu perfil';
        break;
    case 'insignia':
        $mensaje = '🏅 Insignia desbloqueada';
        break;
}
```

### **Ejemplo Completo**:
```
✅ ¡Desbloqueado: Marco Legendario!
🖼️ Marco aplicado a tu avatar (Equipado automáticamente)
✨ Ahora puedes disfrutar de tu nueva recompensa
```

---

## 🎨 **PREVIEWS VISUALES EN TIENDA**

### **Cada Tipo Muestra Preview Animado**:

#### **Marcos** 🖼️
```html
<div class="avatar-karma-container marco-dorado">
    <div class="avatar-preview-img"></div>
</div>
```
→ Usuario VE el marco animado antes de desbloquear

#### **Temas** 🎨
```html
<div class="tema-preview-box" style="background: linear-gradient(...);">
    Vista Previa
</div>
```
→ Usuario VE los colores del tema

#### **Íconos** ⭐
```html
Tu Nombre <span class="icono-especial icono-corona">👑</span>
```
→ Usuario VE cómo se verá el ícono junto a su nombre

#### **Colores de Nombre** 🌈
```html
<span class="nombre-usuario nombre-arcoiris">Tu Nombre</span>
```
→ Usuario VE el gradiente animado en su nombre

#### **Stickers** 😊
```html
<div class="sticker-item sticker-feliz">
    <span>😊</span> Feliz
</div>
```
→ Usuario VE los stickers con estilos reales

---

## 🚀 **CÓMO PROBARLO**

### **Paso 1: Ir a Tienda**
```
http://localhost/Converza/app/presenters/karma_tienda.php
```

### **Paso 2: Ver Previews**
- Observa animaciones en cada preview
- Marcos giran, colores brillan, stickers flotan

### **Paso 3: Desbloquear**
- Clic "Desbloquear"
- Confirma en diálogo

### **Paso 4: Ver Aplicación Inmediata**
- Mensaje de éxito con emoji
- Recompensa ya equipada
- Ve a perfil para confirmar

### **Paso 5: Verificar en Perfil**
```
http://localhost/Converza/app/presenters/perfil.php?id=TU_ID
```
- Marco en avatar
- Color en nombre
- Ícono junto a nombre
- Stickers en sección

---

## ✅ **CONCLUSIÓN**

El sistema de **auto-equipado inteligente** proporciona:

- 🎯 **UX óptima**: Sin pasos innecesarios
- ⚡ **Inmediatez**: Ve resultado al instante
- 🎨 **Previews visuales**: Sabe qué esperar
- 🔄 **Lógica inteligente**: Maneja conflictos automáticamente
- 😊 **Satisfacción**: Gratificación instantánea

**¡Los usuarios aman ver sus recompensas aplicadas inmediatamente! 🎉**
