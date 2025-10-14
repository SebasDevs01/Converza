# 🔗 GUÍA DE INTEGRACIÓN GLOBAL
## Aplicar Personalización en Toda la Red Social

---

## 📋 **ARCHIVOS A ACTUALIZAR**

Esta guía te muestra **exactamente** cómo integrar el sistema de personalización en cada archivo de Converza.

---

## 1️⃣ **index.php** (Feed Principal)

### **Ubicación**: `app/presenters/index.php`

### **Buscar esta línea** (aproximadamente línea 150-200):
```php
<?php echo htmlspecialchars($publicacion['nombre']); ?>
```

### **Reemplazar con**:
```php
<?php echo $recompensasHelper->renderNombreUsuario($publicacion['id_use'], $publicacion['nombre']); ?>
```

### **Código Completo del Bloque**:
```php
<div class="post-header">
    <div class="d-flex align-items-center">
        <!-- Avatar con marco -->
        <?php echo $recompensasHelper->renderAvatar(
            $publicacion['id_use'], 
            $publicacion['avatarFinal'], 
            50, 
            50
        ); ?>
        
        <div class="ms-3">
            <!-- Nombre con color e ícono -->
            <strong>
                <?php echo $recompensasHelper->renderNombreUsuario(
                    $publicacion['id_use'], 
                    $publicacion['nombre']
                ); ?>
            </strong>
            
            <div class="text-muted small">
                @<?php echo htmlspecialchars($publicacion['usuario']); ?> • 
                <?php echo tiempo_transcurrido($publicacion['fecha']); ?>
            </div>
        </div>
    </div>
</div>
```

---

## 2️⃣ **chat.php** (Sistema de Mensajería)

### **Ubicación**: `app/presenters/chat.php`

### **En la Lista de Conversaciones**:

#### **Buscar** (aproximadamente línea 200-250):
```php
<strong><?php echo htmlspecialchars($conversacion['nombre']); ?></strong>
```

#### **Reemplazar con**:
```php
<strong><?php echo $recompensasHelper->renderNombreUsuario($conversacion['id'], $conversacion['nombre']); ?></strong>
```

### **En el Header del Chat Activo**:

#### **Buscar** (aproximadamente línea 300-350):
```php
<h5><?php echo htmlspecialchars($destinatario['nombre']); ?></h5>
```

#### **Reemplazar con**:
```php
<h5><?php echo $recompensasHelper->renderNombreUsuario($destinatario['id'], $destinatario['nombre']); ?></h5>
```

### **En Mensajes Individuales**:

#### **Buscar**:
```php
<div class="mensaje-autor">
    <?php echo htmlspecialchars($mensaje['nombre']); ?>
</div>
```

#### **Reemplazar con**:
```php
<div class="mensaje-autor">
    <?php echo $recompensasHelper->renderNombreUsuario($mensaje['id_remitente'], $mensaje['nombre']); ?>
</div>
```

---

## 3️⃣ **albumes.php** (Galería de Fotos)

### **Ubicación**: `app/presenters/albumes.php`

### **En Tarjetas de Álbum**:

#### **Buscar** (aproximadamente línea 150-200):
```php
<p class="mb-1"><strong><?php echo htmlspecialchars($album['nombre']); ?></strong></p>
```

#### **Reemplazar con**:
```php
<p class="mb-1">
    <strong>
        <?php echo $recompensasHelper->renderNombreUsuario($album['id_use'], $album['nombre']); ?>
    </strong>
</p>
```

### **Código Completo del Bloque**:
```php
<div class="album-card">
    <!-- Avatar con marco -->
    <?php echo $recompensasHelper->renderAvatar(
        $album['id_use'], 
        $album['avatarFinal'], 
        40, 
        40
    ); ?>
    
    <div class="album-info">
        <p class="mb-1">
            <strong>
                <?php echo $recompensasHelper->renderNombreUsuario(
                    $album['id_use'], 
                    $album['nombre']
                ); ?>
            </strong>
        </p>
        <p class="text-muted small"><?php echo $album['titulo']; ?></p>
    </div>
</div>
```

---

## 4️⃣ **amigos.php** (Lista de Amigos/Seguidores)

### **Ubicación**: `app/presenters/amigos.php`

### **Buscar** (aproximadamente línea 100-150):
```php
<h6><?php echo htmlspecialchars($amigo['nombre']); ?></h6>
```

### **Reemplazar con**:
```php
<h6><?php echo $recompensasHelper->renderNombreUsuario($amigo['id'], $amigo['nombre']); ?></h6>
```

### **Código Completo del Bloque**:
```php
<div class="amigo-item">
    <a href="perfil.php?id=<?php echo $amigo['id']; ?>" class="d-flex align-items-center">
        <!-- Avatar con marco -->
        <?php echo $recompensasHelper->renderAvatar(
            $amigo['id'], 
            $amigo['avatarFinal'], 
            50, 
            50
        ); ?>
        
        <div class="ms-3">
            <!-- Nombre con color e ícono -->
            <h6 class="mb-0">
                <?php echo $recompensasHelper->renderNombreUsuario(
                    $amigo['id'], 
                    $amigo['nombre']
                ); ?>
            </h6>
            <small class="text-muted">@<?php echo htmlspecialchars($amigo['usuario']); ?></small>
        </div>
    </a>
</div>
```

---

## 5️⃣ **admin.php** (Panel de Administración)

### **Ubicación**: `app/view/admin.php`

### **En Tabla de Usuarios**:

#### **Buscar** (aproximadamente línea 200-250):
```php
<td><?php echo htmlspecialchars($user['nombre']); ?></td>
```

#### **Reemplazar con**:
```php
<td>
    <?php echo $recompensasHelper->renderNombreUsuario($user['id_use'], $user['nombre']); ?>
</td>
```

### **Código Completo del Bloque**:
```php
<tr>
    <td><?php echo $user['id_use']; ?></td>
    
    <td>
        <!-- Avatar con marco -->
        <?php echo $recompensasHelper->renderAvatar(
            $user['id_use'], 
            $user['avatarFinal'], 
            40, 
            40
        ); ?>
    </td>
    
    <td>
        <!-- Nombre con color e ícono -->
        <?php echo $recompensasHelper->renderNombreUsuario(
            $user['id_use'], 
            $user['nombre']
        ); ?>
        <br>
        <small class="text-muted">@<?php echo htmlspecialchars($user['usuario']); ?></small>
    </td>
    
    <td><?php echo $user['email']; ?></td>
    <td><?php echo $user['tipo']; ?></td>
</tr>
```

---

## 6️⃣ **buscar_usuarios.php** (Búsqueda)

### **Ubicación**: `app/presenters/buscar_usuarios.php`

### **Buscar**:
```php
<strong><?php echo htmlspecialchars($resultado['nombre']); ?></strong>
```

### **Reemplazar con**:
```php
<strong><?php echo $recompensasHelper->renderNombreUsuario($resultado['id_use'], $resultado['nombre']); ?></strong>
```

---

## 7️⃣ **comentarios.php** (Sistema de Comentarios)

### **Ubicación**: Dentro de componentes de comentarios

### **Buscar**:
```php
<strong><?php echo htmlspecialchars($comentario['nombre']); ?></strong>
```

### **Reemplazar con**:
```php
<strong><?php echo $recompensasHelper->renderNombreUsuario($comentario['id_use'], $comentario['nombre']); ?></strong>
```

---

## 📦 **PATRÓN UNIVERSAL DE INTEGRACIÓN**

### **Patrón General para CUALQUIER Archivo**:

```php
// ❌ ANTES
<?php echo htmlspecialchars($variable['nombre']); ?>

// ✅ DESPUÉS
<?php echo $recompensasHelper->renderNombreUsuario($variable['id'], $variable['nombre']); ?>
```

### **Para Avatares**:

```php
// ❌ ANTES
<img src="<?php echo $avatar; ?>" width="50" height="50" alt="Avatar">

// ✅ DESPUÉS
<?php echo $recompensasHelper->renderAvatar($usuario_id, $avatar, 50, 50); ?>
```

---

## 🔍 **CÓMO ENCONTRAR RÁPIDAMENTE**

### **Método 1: Buscar con grep (Linux/Mac) o findstr (Windows)**

#### Windows PowerShell:
```powershell
# Buscar todos los lugares donde se muestra el nombre
Get-ChildItem -Path "app" -Recurse -Filter "*.php" | Select-String "echo.*nombre"

# Buscar htmlspecialchars aplicado a nombre
Get-ChildItem -Path "app" -Recurse -Filter "*.php" | Select-String "htmlspecialchars.*nombre"
```

#### Linux/Mac:
```bash
# Buscar todos los lugares donde se muestra el nombre
grep -r "echo.*nombre" app/

# Buscar htmlspecialchars aplicado a nombre
grep -r "htmlspecialchars.*nombre" app/
```

### **Método 2: Buscar en VS Code**

1. Presiona `Ctrl + Shift + F` (o `Cmd + Shift + F` en Mac)
2. Busca: `htmlspecialchars.*nombre`
3. Marca "Use Regular Expression" (icono `.*`)
4. Filtra por archivos: `*.php`
5. Reemplaza uno por uno verificando el contexto

---

## ⚙️ **VARIABLES COMUNES POR ARCHIVO**

| Archivo | Variable ID | Variable Nombre | Variable Avatar |
|---------|-------------|-----------------|-----------------|
| `index.php` | `$publicacion['id_use']` | `$publicacion['nombre']` | `$publicacion['avatarFinal']` |
| `chat.php` | `$conversacion['id']` | `$conversacion['nombre']` | `$conversacion['avatar']` |
| `albumes.php` | `$album['id_use']` | `$album['nombre']` | `$album['avatarFinal']` |
| `amigos.php` | `$amigo['id']` | `$amigo['nombre']` | `$amigo['avatarFinal']` |
| `admin.php` | `$user['id_use']` | `$user['nombre']` | `$user['avatarFinal']` |
| `perfil.php` | `$usuario['id_use']` | `$usuario['nombre']` | `$usuario['avatarFinal']` |

---

## 🎯 **CHECKLIST DE INTEGRACIÓN**

### **Por Archivo**:

```
index.php
├── [ ] Header de publicaciones
├── [ ] Comentarios en publicaciones
├── [ ] Reacciones (quién reaccionó)
└── [ ] Sugerencias de usuarios

chat.php
├── [ ] Lista de conversaciones
├── [ ] Header del chat activo
├── [ ] Mensajes individuales
└── [ ] Búsqueda de usuarios para nuevo chat

albumes.php
├── [ ] Tarjetas de álbumes
├── [ ] Vista detallada de álbum
└── [ ] Comentarios en fotos

amigos.php
├── [ ] Lista de amigos
├── [ ] Lista de seguidores
├── [ ] Lista de seguidos
└── [ ] Solicitudes pendientes

admin.php
├── [ ] Tabla de usuarios
├── [ ] Logs de actividad
└── [ ] Reportes

buscar_usuarios.php
├── [ ] Resultados de búsqueda
└── [ ] Sugerencias

notificaciones.php
├── [ ] Lista de notificaciones
└── [ ] Quién generó la notificación
```

---

## 🚀 **SCRIPT DE ACTUALIZACIÓN MASIVA**

### **PHP Script para Actualizar Automáticamente**:

Crea `actualizar_nombres_global.php`:

```php
<?php
/**
 * Script para actualizar visualización de nombres en toda la red
 */

$archivos_actualizar = [
    'app/presenters/index.php',
    'app/presenters/chat.php',
    'app/presenters/albumes.php',
    'app/presenters/amigos.php',
    'app/view/admin.php',
    'app/presenters/buscar_usuarios.php',
];

$patron = '/htmlspecialchars\s*\(\s*\$([a-zA-Z_]+)\[[\'"]+nombre[\'"]+\]\s*\)/';
$reemplazo = '$recompensasHelper->renderNombreUsuario($1[\'id\'], $1[\'nombre\'])';

foreach ($archivos_actualizar as $archivo) {
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $nuevo_contenido = preg_replace($patron, $reemplazo, $contenido);
        
        if ($contenido !== $nuevo_contenido) {
            file_put_contents($archivo . '.backup', $contenido); // Backup
            file_put_contents($archivo, $nuevo_contenido);
            echo "✅ Actualizado: $archivo\n";
        } else {
            echo "⚠️ Sin cambios: $archivo\n";
        }
    } else {
        echo "❌ No encontrado: $archivo\n";
    }
}

echo "\n✨ Proceso completado. Se crearon backups .backup de los archivos modificados.\n";
```

**⚠️ IMPORTANTE**: Revisa manualmente después de ejecutar este script.

---

## 🎨 **CSS ADICIONAL (OPCIONAL)**

Si quieres mejorar la visualización en secciones específicas:

```css
/* En index.php - Publicaciones */
.post-header .nombre-usuario {
    font-size: 1.1rem;
}

/* En chat.php - Mensajes */
.mensaje-autor .nombre-usuario {
    font-size: 0.95rem;
}

/* En amigos.php - Lista */
.amigo-item .nombre-usuario {
    font-size: 1rem;
}

/* En admin.php - Tabla */
.admin-table .nombre-usuario {
    font-size: 0.9rem;
}
```

---

## ⚠️ **PRECAUCIONES**

### **1. Variables de ID**
Asegúrate de usar la variable correcta para el ID:
- `id_use` (usuarios)
- `id` (otros contextos)
- `usuario_id` (mensajes)

### **2. Permisos Bloqueados**
El sistema respeta los bloqueos. Si un usuario A bloqueó a B, B verá el nombre sin personalización.

### **3. Performance**
El helper cachea resultados internamente. No hay consultas redundantes.

### **4. Backups**
Antes de modificar archivos en producción:
```bash
cp archivo.php archivo.php.backup
```

---

## 📊 **VERIFICACIÓN POST-INTEGRACIÓN**

### **Checklist de Pruebas**:

```
[ ] Nombre con color se ve en feed principal
[ ] Ícono aparece junto al nombre en publicaciones
[ ] Avatar con marco funciona en todas las vistas
[ ] Nombre personalizado en chat (lista y mensajes)
[ ] Stickers aparecen en perfil
[ ] No hay errores PHP en logs
[ ] Performance aceptable (<50ms adicionales)
[ ] Responsivo en móviles
```

---

## ✅ **CONCLUSIÓN**

Con esta guía tienes **TODO** lo necesario para integrar el sistema de personalización en cada rincón de Converza.

### **Prioridad de Integración**:
1. 🔥 **Alta**: `perfil.php`, `index.php`, `chat.php`
2. 🟡 **Media**: `albumes.php`, `amigos.php`
3. 🟢 **Baja**: `admin.php`, `buscar_usuarios.php`

**¡Empieza por los archivos de alta prioridad y expande progresivamente! 🚀**
