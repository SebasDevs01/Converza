# 🎯 REGLA DEFINITIVA: object-fit en Converza

## 📋 RESUMEN EJECUTIVO

**AVATARES (círculos)** → `object-fit: cover`  
**FOTOS DE PUBLICACIONES (rectangulares)** → `object-fit: contain`

---

## 🔵 AVATARES CIRCULARES → `object-fit: cover`

### ¿Por qué `cover`?
Los avatares son **círculos** (clase `rounded-circle`). Necesitan llenar completamente el círculo para verse bien, aunque se recorte parte de la imagen.

### ✅ Implementación correcta:
```html
<img src="..." 
     class="rounded-circle" 
     width="48" height="48" 
     style="object-fit: cover;">
```

### 📍 Ubicaciones en el código:

#### 1. **index.php** (línea ~339)
- Avatar del usuario en formulario de publicar (60x60)
```php
echo '<img src="'.$avatarWebPath.'" class="rounded-circle me-3" width="60" height="60" style="object-fit: cover;" alt="Avatar">';
```

#### 2. **publicaciones.php** (línea ~229)
- Avatar del autor de publicación (48x48)
```php
echo '<img src="'.$src.'" class="rounded-circle me-2" width="48" height="48" style="object-fit: cover;" alt="Avatar">';
```

#### 3. **publicaciones.php** (línea ~414)
- Avatar en comentarios existentes (32x32)
```php
$imgC = '<img class="rounded-circle me-2" src="'.$srcC.'" alt="Avatar" width="32" height="32" style="object-fit: cover;">';
```

#### 4. **publicaciones.php** (línea ~729 - JavaScript)
- Avatar en comentarios AJAX (32x32)
```javascript
<img src="${avatarPath}" 
     alt="Avatar" class="rounded-circle me-2" width="32" height="32" 
     style="object-fit: cover;">
```

#### 5. **perfil.php**
- Avatares en tarjetas de amigos, etc.
```php
<img src="..." class="rounded-circle" width="48" height="48" style="object-fit: cover;">
```

---

## 🖼️ FOTOS DE PUBLICACIONES → `object-fit: contain`

### ¿Por qué `contain`?
Las fotos de publicaciones son **rectangulares** y queremos ver la **imagen completa** sin recortes ni distorsión. Es mejor dejar espacio blanco que cortar partes de la foto.

### ✅ Implementación correcta:
```html
<div style="width: 180px; height: 180px; overflow: hidden;">
    <img src="..." 
         class="w-100 h-100" 
         style="object-fit: contain; display: block;">
</div>
```

### 📍 Ubicaciones en el código:

#### 1. **publicaciones.php** (línea ~289)
- Feed de publicaciones (180x180)
```php
echo '<div class="position-relative" style="width: 180px; height: 180px; overflow: hidden; border-radius: 8px;">';
echo '<img src="/converza/public/publicaciones/'.htmlspecialchars($img).'" class="w-100 h-100" style="object-fit: contain; display: block;">';
echo '</div>';
```

#### 2. **perfil.php** (línea ~870)
- Publicaciones en perfil de usuario (300x300)
```php
<div class="position-relative" style="width: 300px; height: 300px; overflow: hidden; border-radius: 8px;">
    <img src="..." 
         class="w-100 h-100" 
         style="object-fit: contain; display: block;" />
</div>
```

#### 3. **publicacion.php** (línea ~99)
- Vista individual de publicación (100% x 500px max)
```php
<div class="publication-image-container" style="width: 100%; max-height: 500px; overflow: hidden;">
    <img src="..." 
         class="img-fluid" 
         style="width: 100%; height: 100%; object-fit: contain; display: block;" />
</div>
```

---

## ⚠️ CASO ESPECIAL: Videos

Los **videos** pueden usar `object-fit: cover` porque generalmente queremos llenar el espacio:

```html
<video controls style="max-width:320px; max-height:240px; object-fit: cover;">
    <source src="..." type="video/mp4">
</video>
```

---

## 🔍 VERIFICACIÓN RÁPIDA

### ✅ Revisar que TODOS los avatares tengan:
```bash
# PowerShell
Get-Content app/presenters/publicaciones.php | Select-String "rounded-circle.*width.*height" | Select-String -NotMatch "object-fit: cover"
```

Si aparece algún resultado → **FALTA** `object-fit: cover`

### ✅ Revisar que fotos de publicaciones tengan:
```bash
# PowerShell
Get-Content app/presenters/publicaciones.php | Select-String "public/publicaciones" | Select-String -NotMatch "object-fit: contain"
```

Si aparece algún resultado → **FALTA** `object-fit: contain`

---

## 🎨 VISUAL EXPLICACIÓN

### `object-fit: cover` (Avatares)
```
┌─────────────┐     ┌─────────────┐
│  Imagen     │ --> │     ╱╲      │ (Rellena círculo,
│  Original   │     │    ◯◯◯◯     │  recorta bordes)
│             │     │     ╲╱      │
└─────────────┘     └─────────────┘
   rectangular         circular
```

### `object-fit: contain` (Publicaciones)
```
┌─────────────┐     ┌─────────────┐
│  Imagen     │ --> │             │ (Muestra imagen
│  Original   │     │   [FOTO]    │  completa, puede
│             │     │             │  dejar espacio)
└─────────────┘     └─────────────┘
   cualquier           rectangular
```

---

## 📝 CHECKLIST DE IMPLEMENTACIÓN

- [x] **index.php**: Avatar formulario publicar → `object-fit: cover` ✅
- [x] **publicaciones.php**: Avatar autor → `object-fit: cover` ✅
- [x] **publicaciones.php**: Avatar comentarios PHP → `object-fit: cover` ✅
- [x] **publicaciones.php**: Avatar comentarios AJAX → `object-fit: cover` ✅
- [x] **publicaciones.php**: Fotos feed → `object-fit: contain` ✅
- [x] **perfil.php**: Fotos publicaciones → `object-fit: contain` ✅
- [x] **publicacion.php**: Foto individual → `object-fit: contain` ✅

---

## 🚀 SOLUCIÓN PARA USUARIOS

Después de aplicar todos los cambios:

```bash
Ctrl + Shift + R  # Hard refresh
```

O abrir en modo incógnito:
```bash
Ctrl + Shift + N
```

---

## 🎯 REGLA DE ORO

> **Si es CÍRCULO (`rounded-circle`)** → `object-fit: cover`  
> **Si es PUBLICACIÓN (foto rectangular)** → `object-fit: contain`

---

**Última actualización:** 2025-10-14  
**Estado:** ✅ Todos los archivos corregidos
