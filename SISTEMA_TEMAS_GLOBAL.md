# 🎨 Sistema de Temas Global - Implementación Completa

## ✅ Sistema Implementado

Se ha creado un **sistema de temas global** que aplica el tema equipado por el usuario a **TODA la interfaz** de Converza, no solo al perfil.

---

## 📁 Archivos Creados/Modificados

### 1️⃣ **`public/css/temas-sistema.css`** (NUEVO)
Archivo CSS con todos los temas disponibles:
- ✅ Tema Default (Converza original)
- ✅ Tema Oscuro Premium
- ✅ Tema Galaxy (Espacio con estrellas)
- ✅ Tema Sunset (Atardecer cálido)
- ✅ Tema Neon (Cyberpunk)

### 2️⃣ **`app/models/tema-global-aplicar.php`** (NUEVO)
Script PHP que:
- Detecta el tema equipado del usuario
- Aplica la clase CSS correspondiente al `<body>`
- Carga automáticamente el archivo `temas-sistema.css`

### 3️⃣ **`app/models/recompensas-aplicar-helper.php`** (MODIFICADO)
Se agregaron métodos:
- `getTemaClaseBody($usuario_id)` - Obtiene la clase CSS del tema
- `mapearTemaAClase($nombreTema)` - Mapea nombres a clases CSS

### 4️⃣ **`app/view/index.php`** (MODIFICADO)
Se incluyó el sistema de temas global en el `<head>`

---

## 🚀 Cómo Aplicar el Sistema a Otras Páginas

Para que **CUALQUIER página** del sistema aplique el tema equipado, agrega esto en el `<head>`:

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tu Página | Converza</title>
    
    <!-- Enlaces CSS Bootstrap, etc. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <?php 
    // 🎨 SISTEMA DE TEMAS GLOBAL - AGREGAR ESTA LÍNEA
    require_once __DIR__ . '/../models/tema-global-aplicar.php';
    ?>
    
</head>
<body>
    <!-- Contenido de la página -->
</body>
</html>
```

### 📝 Nota Importante
- El `require_once` debe ajustarse según la ubicación de tu archivo
- Si estás en `app/view/` usa: `__DIR__ . '/../models/tema-global-aplicar.php'`
- Si estás en `app/presenters/` usa: `__DIR__ . '/../models/tema-global-aplicar.php'`
- El script automáticamente agrega la clase correcta al `<body>`

---

## 🎨 Temas Disponibles y sus Clases CSS

| Nombre del Tema en BD | Clase CSS del Body | Descripción |
|-----------------------|--------------------|-------------|
| **Tema Oscuro Premium** | `tema-oscuro` | Fondo oscuro elegante con gradientes morados |
| **Tema Galaxy** | `tema-galaxy` | Espacio profundo con estrellas animadas |
| **Tema Sunset** | `tema-sunset` | Gradiente cálido tipo atardecer |
| **Tema Neon** | `tema-neon` | Estilo cyberpunk con efectos neón |
| *Sin tema equipado* | `tema-default` | Tema original de Converza |

---

## 🔧 Cómo Funciona

### 1. Usuario Equipa un Tema
```
Usuario → Tienda Karma → Desbloquea "Tema Neon" → Se equipa automáticamente
```

### 2. Sistema Detecta el Tema
```php
$recompensasHelper = new RecompensasAplicarHelper($conexion);
$temaClase = $recompensasHelper->getTemaClaseBody($_SESSION['id']);
// Resultado: "tema-neon"
```

### 3. Se Aplica al Body
```javascript
document.body.classList.add('tema-neon');
```

### 4. CSS Hace su Magia
```css
body.tema-neon {
    background: #0a0e27 !important;
    color: #00ffff !important;
}

body.tema-neon .card {
    background: rgba(10, 14, 39, 0.95) !important;
    border: 2px solid #00ffff !important;
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
}
```

---

## 📋 Lista de Páginas a Actualizar

Para que el sistema funcione en TODA la plataforma, agregar el `require_once` en:

### ✅ Ya Implementado
- [x] `app/view/index.php` - Página principal

### 📝 Pendientes de Implementar
- [ ] `app/view/perfil.php` - Perfil de usuario
- [ ] `app/view/mensajes.php` - Mensajes
- [ ] `app/view/albumes.php` - Álbumes
- [ ] `app/view/admin.php` - Panel admin
- [ ] `app/presenters/chat.php` - Chat
- [ ] `app/presenters/karma_tienda.php` - Tienda (si tiene HTML)
- [ ] Cualquier otra página con interfaz visual

---

## 🧪 Pruebas

### Probar el Sistema:

1. **Inicia sesión** con `testingtienda` / `Testing2025!`

2. **Ve a la Tienda** → http://localhost/Converza/karma_tienda.php

3. **Desbloquea temas:**
   - Tema Sunset (150 karma)
   - Tema Galaxy (100 karma)
   - Tema Neon (200 karma)

4. **Navega por el sitio:**
   - Página principal → Debe tener el tema aplicado
   - Perfil → Debe tener el tema aplicado
   - Mensajes → Debe tener el tema aplicado
   - etc.

5. **Cambia de tema:**
   - Equipar otro tema → Toda la interfaz cambia instantáneamente

6. **Desequipa el tema:**
   - Desequipar tema → Vuelve al tema default de Converza

---

## 🎯 Beneficios del Sistema

### ✅ Para el Usuario
- **Personalización total** - El tema se aplica a TODA la plataforma
- **Experiencia consistente** - No hay páginas con tema y otras sin él
- **Cambio instantáneo** - Equipar/desequipar cambia todo inmediatamente

### ✅ Para el Desarrollador
- **Centralizado** - Un solo archivo CSS con todos los temas
- **Fácil de mantener** - Agregar temas nuevos es simple
- **Reutilizable** - Un solo `require_once` en cada página
- **Sin conflictos** - Usa clases CSS específicas en el `<body>`

---

## 🆕 Agregar Nuevos Temas

### 1. Crear el Tema en la Base de Datos
```sql
INSERT INTO karma_recompensas (tipo, nombre, descripcion, karma_requerido, icono, activo)
VALUES ('tema', 'Tema Bosque', 'Colores verdes naturales', 150, '🌲', TRUE);
```

### 2. Agregar Mapeo en el Helper
```php
// En recompensas-aplicar-helper.php
private function mapearTemaAClase($nombreTema) {
    $mapeo = [
        'Tema Bosque' => 'tema-bosque', // ⬅️ NUEVO
        // ... otros temas
    ];
    return $mapeo[$nombreTema] ?? 'tema-default';
}
```

### 3. Crear Estilos en temas-sistema.css
```css
/* TEMA BOSQUE */
body.tema-bosque {
    background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%) !important;
    color: #333 !important;
}

body.tema-bosque .card {
    background: rgba(255, 255, 255, 0.95) !important;
    border: 2px solid rgba(86, 171, 47, 0.3) !important;
}

body.tema-bosque .navbar {
    background: linear-gradient(135deg, #56ab2f, #a8e063) !important;
}
```

### 4. ¡Listo!
El nuevo tema estará disponible automáticamente en todo el sistema.

---

## 🐛 Troubleshooting

### Problema: El tema no se aplica
**Solución:**
1. Verificar que `tema-global-aplicar.php` esté incluido en el `<head>`
2. Verificar que el usuario tenga sesión activa (`$_SESSION['id']`)
3. Revisar la consola del navegador: debe mostrar "✨ Tema aplicado: tema-xxx"

### Problema: Algunos elementos no cambian de color
**Solución:**
1. Agregar `!important` en los estilos CSS del tema
2. Asegurar que el selector CSS sea lo suficientemente específico
3. Ejemplo: `body.tema-neon .card` en lugar de solo `.card`

### Problema: El tema se ve mal en una página específica
**Solución:**
1. Verificar que esa página no tenga estilos CSS que sobrescriban el tema
2. Revisar si hay estilos inline (`style="..."`) que interfieren
3. Ajustar los estilos del tema para esa página específica

---

## 📊 Estructura del Sistema

```
Converza/
├── public/
│   └── css/
│       └── temas-sistema.css ⬅️ Todos los estilos de temas
│
├── app/
│   ├── models/
│   │   ├── tema-global-aplicar.php ⬅️ Script de aplicación
│   │   └── recompensas-aplicar-helper.php ⬅️ Helper con lógica
│   │
│   └── view/
│       ├── index.php ⬅️ Ya implementado
│       ├── perfil.php ⬅️ Pendiente
│       ├── mensajes.php ⬅️ Pendiente
│       └── ...
│
└── karma_tienda.php ⬅️ Donde se gestionan los temas
```

---

## ✅ Checklist de Implementación

Para cada página nueva que agregues al sistema:

- [ ] Agregar `require_once` de `tema-global-aplicar.php` en el `<head>`
- [ ] Probar con cada tema disponible
- [ ] Verificar legibilidad de textos
- [ ] Verificar contraste de botones
- [ ] Probar en modo oscuro y claro
- [ ] Verificar animaciones y transiciones
- [ ] Confirmar que el tema persiste en navegación

---

**Fecha de implementación:** 14 de Octubre, 2025  
**Desarrollador:** SebasDevs01  
**Versión:** 1.0
