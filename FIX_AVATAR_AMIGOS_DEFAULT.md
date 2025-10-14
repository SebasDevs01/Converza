# 🖼️ FIX: Avatar por Defecto en Lista de Amigos

## 🎯 Objetivo
Cuando un usuario no tiene imagen de perfil, debe cargarse automáticamente la imagen por defecto (`defect.jpg`) en lugar de mostrar imágenes rotas (404).

## 📍 Ubicaciones Corregidas

### ✅ 1. Lista de Amigos en Perfil (`perfil.php`)

**Ubicación:** Sección "Amigos" en la vista de perfil (líneas ~725-735)

**ANTES:**
```php
if ($amigos):
    foreach ($amigos as $am): ?>
        <a href="perfil.php?id=<?php echo (int)$am['id_use']; ?>" class="d-inline-block text-center me-2 mb-2">
          <img src="/Converza/public/avatars/<?php echo htmlspecialchars($am['avatar']); ?>" 
               class="rounded-circle" width="48" height="48" alt="Avatar">
          <div class="small fw-bold"><?php echo htmlspecialchars($am['usuario']); ?></div>
        </a>
    <?php endforeach;
```

**Problema:**
- ❌ Si `$am['avatar']` es NULL, vacío o no existe el archivo → 404 error
- ❌ No valida existencia del archivo
- ❌ Imagen rota en el frontend

**DESPUÉS:**
```php
if ($amigos):
    foreach ($amigos as $am): 
        // Validar avatar
        $avatarAmigo = htmlspecialchars($am['avatar']);
        $avatarPath = realpath(__DIR__.'/../../public/avatars/'.$avatarAmigo);
        $avatarWeb = '/Converza/public/avatars/'.$avatarAmigo;
        
        if ($avatarAmigo && $avatarAmigo !== 'default_avatar.svg' && $avatarPath && file_exists($avatarPath)) {
            $imgAmigo = $avatarWeb;
        } else {
            $imgAmigo = '/Converza/public/avatars/defect.jpg';
        }
    ?>
        <a href="perfil.php?id=<?php echo (int)$am['id_use']; ?>" class="d-inline-block text-center me-2 mb-2">
          <img src="<?php echo $imgAmigo; ?>" 
               class="rounded-circle" width="48" height="48" alt="Avatar" loading="lazy">
          <div class="small fw-bold"><?php echo htmlspecialchars($am['usuario']); ?></div>
        </a>
    <?php endforeach;
```

**Solución:**
- ✅ Valida si el avatar existe en el sistema de archivos
- ✅ Valida que no sea NULL o vacío
- ✅ Excluye `default_avatar.svg` (obsoleto)
- ✅ Usa `defect.jpg` como fallback
- ✅ Agrega `loading="lazy"` para optimización

### ✅ 2. Panel de Solicitudes de Amistad (`_navbar_panels.php`)

**Estado:** ✅ **YA ESTABA CORRECTO**

```php
$avatarU = htmlspecialchars($us['avatar']);
$avatarUPath = realpath(__DIR__.'/../../public/avatars/'.$avatarU);
$avatarUWeb = '/Converza/public/avatars/'.$avatarU;

if ($avatarU && $avatarU !== 'default_avatar.svg' && $avatarUPath && file_exists($avatarUPath)) {
    $imgU = '<img src="'.$avatarUWeb.'" class="rounded-circle me-2" width="40" height="40" alt="Avatar" loading="lazy">';
} else {
    $imgU = '<img src="/Converza/public/avatars/defect.jpg" class="rounded-circle me-2" width="40" height="40" alt="Avatar por defecto" loading="lazy">';
}
```

Este archivo **ya tenía la lógica correcta** implementada.

## 🧪 Validación

### Validaciones Aplicadas
1. **Existe el campo en BD:**
   ```php
   $avatarAmigo = htmlspecialchars($am['avatar']);
   ```

2. **No es NULL o vacío:**
   ```php
   if ($avatarAmigo && ...)
   ```

3. **No es avatar obsoleto:**
   ```php
   $avatarAmigo !== 'default_avatar.svg'
   ```

4. **Archivo existe físicamente:**
   ```php
   $avatarPath = realpath(__DIR__.'/../../public/avatars/'.$avatarAmigo);
   file_exists($avatarPath)
   ```

5. **Fallback a imagen por defecto:**
   ```php
   $imgAmigo = '/Converza/public/avatars/defect.jpg';
   ```

## 🎨 Imagen por Defecto

**Ruta:** `/Converza/public/avatars/defect.jpg`

**Características:**
- ✅ Existe en el servidor
- ✅ Formato: JPG
- ✅ Tamaño recomendado: 200x200px mínimo
- ✅ Fondo neutro

## 📊 Escenarios de Prueba

### Caso 1: Usuario sin avatar (NULL en BD)
```sql
SELECT * FROM usuarios WHERE avatar IS NULL;
```
**Resultado Esperado:** Muestra `defect.jpg`

### Caso 2: Usuario con avatar que no existe en disco
```sql
SELECT * FROM usuarios WHERE avatar = '999.jpg';
-- Pero el archivo /public/avatars/999.jpg NO existe
```
**Resultado Esperado:** Muestra `defect.jpg`

### Caso 3: Usuario con avatar válido
```sql
SELECT * FROM usuarios WHERE avatar = '20.jpg';
-- Y el archivo /public/avatars/20.jpg SÍ existe
```
**Resultado Esperado:** Muestra `/Converza/public/avatars/20.jpg`

### Caso 4: Usuario con default_avatar.svg (obsoleto)
```sql
SELECT * FROM usuarios WHERE avatar = 'default_avatar.svg';
```
**Resultado Esperado:** Muestra `defect.jpg` (no usa el obsoleto)

## 🔍 Testing Manual

### Test 1: Ver perfil con amigos sin avatar
```
1. Ir a: http://localhost/Converza/app/presenters/perfil.php?id=1
2. Scroll hasta sección "Amigos"
3. Verificar que todos los avatares se cargan correctamente
4. Abrir DevTools → Network → Buscar errores 404
5. ✅ No debe haber errores 404 en imágenes
```

### Test 2: Ver solicitudes pendientes
```
1. Click en campana de notificaciones (navbar)
2. Ver panel de "Solicitudes de Amistad"
3. Verificar avatares de usuarios
4. ✅ Todos deben mostrar imagen válida
```

### Test 3: Crear usuario nuevo sin avatar
```sql
INSERT INTO usuarios (usuario, email, password) 
VALUES ('test_sin_avatar', 'test@test.com', 'hash123');
```
```
1. Hacer que este usuario sea amigo de tu usuario actual
2. Ver tu perfil → Sección Amigos
3. ✅ Debe mostrar defect.jpg para ese usuario
```

## 📁 Archivos Modificados

1. ✅ `app/presenters/perfil.php`
   - Líneas ~725-735
   - Agregada validación de avatar
   - Fallback a `defect.jpg`

## 🎯 Beneficios

✅ **UX Mejorada:** No más imágenes rotas  
✅ **Consistencia:** Mismo patrón que `_navbar_panels.php`  
✅ **Performance:** `loading="lazy"` para carga diferida  
✅ **Mantenibilidad:** Código reutilizable en otros componentes  
✅ **SEO/Accesibilidad:** Atributo `alt` descriptivo  

## 🔮 Recomendaciones Futuras

### 1. Crear helper function
```php
// app/models/avatar-helper.php
function obtenerAvatarODefecto($avatar) {
    $avatarPath = realpath(__DIR__.'/../../public/avatars/'.$avatar);
    if ($avatar && $avatar !== 'default_avatar.svg' && $avatarPath && file_exists($avatarPath)) {
        return '/Converza/public/avatars/' . htmlspecialchars($avatar);
    }
    return '/Converza/public/avatars/defect.jpg';
}
```

### 2. Aplicar a otros componentes
Buscar y reemplazar en:
- `chat.php` - Avatares en conversaciones
- `publicaciones.php` - Avatares en posts (YA CORREGIDO)
- `comentarios` - Avatares en comentarios (YA CORREGIDO)
- `daily_shuffle.php` - Avatares en shuffle diario
- `buscar_usuarios.php` - Resultados de búsqueda

### 3. Migrar avatares obsoletos
```sql
-- Actualizar todos los default_avatar.svg a NULL
UPDATE usuarios 
SET avatar = NULL 
WHERE avatar = 'default_avatar.svg' OR avatar = '';
```

---

**Fecha:** 13 de Octubre, 2025  
**Problema:** Avatares rotos (404) en lista de amigos  
**Status:** ✅ CORREGIDO
