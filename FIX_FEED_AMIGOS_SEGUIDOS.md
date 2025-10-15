# 🐛 FIX: FEED NO MUESTRA PUBLICACIONES DE AMIGOS Y SEGUIDOS

## 📋 PROBLEMA REPORTADO

**Usuario**: "Le doy a SEGUIR a alguien, lo sigue correctamente, pero voy al INDEX y NO ME SALE su publicación. Pasa con AMIGOS y SEGUIDOS. Me meto a su perfil y sí tienen publicaciones, pero NO aparecen en mi feed."

---

## 🔍 DIAGNÓSTICO

### 1. Verificación de Código

He revisado el código del feed (`app/presenters/publicaciones.php`) y la query SQL está **CORRECTAMENTE** implementada:

```php
// Líneas 12-47 de publicaciones.php
$stmt = $conexion->prepare("
    SELECT DISTINCT p.*, u.usuario, u.avatar, u.id_use AS usuario_id 
    FROM publicaciones p 
    JOIN usuarios u ON p.usuario = u.id_use 
    WHERE ($filtroBloqueos) AND (
        p.usuario = :user_id                    // ✅ Tus publicaciones
        OR p.usuario IN (
            SELECT s.seguido_id                  // ✅ Publicaciones de usuarios seguidos
            FROM seguidores s 
            WHERE s.seguidor_id = :user_id2
        )
        OR p.usuario IN (
            SELECT CASE                          // ✅ Publicaciones de amigos
                WHEN a.de = :user_id3 THEN a.para 
                ELSE a.de 
            END as amigo_id
            FROM amigos a 
            WHERE (a.de = :user_id4 OR a.para = :user_id5) 
            AND a.estado = 1                     // ✅ Solo amistades confirmadas
        )
    )
    ORDER BY p.id_pub DESC 
    LIMIT :offset, :limit
");
```

### 2. Posibles Causas del Problema

| Causa | Probabilidad | Descripción |
|-------|--------------|-------------|
| **Tabla `seguidores` vacía** | 🔴 ALTA | El botón "Seguir" no está insertando en la tabla |
| **Estado `amigos` incorrecto** | 🟡 MEDIA | Amistades en estado 0 (pendiente) en vez de 1 (confirmado) |
| **Publicaciones no existen** | 🟢 BAJA | Los usuarios seguidos/amigos no han publicado nada |
| **Cache de navegador** | 🟢 BAJA | El navegador muestra versión antigua del feed |
| **Error en `$filtroBloqueos`** | 🟡 MEDIA | Variable mal construida filtra todo |

---

## 🛠️ SOLUCIÓN: SCRIPT DE DIAGNÓSTICO

He creado el archivo **`debug_feed.php`** en la raíz del proyecto.

### Cómo Usar el Diagnóstico

1. **Accede al script**:
   ```
   http://localhost/Converza/debug_feed.php
   ```

2. **El script verificará**:
   - ✅ Tus publicaciones propias
   - ✅ Usuarios que sigues (tabla `seguidores`)
   - ✅ Publicaciones de usuarios seguidos
   - ✅ Tus amigos confirmados (tabla `amigos`, estado = 1)
   - ✅ Publicaciones de amigos
   - ✅ Query completa del feed
   - ✅ Contenido de tablas `seguidores` y `amigos`

3. **Identificar el problema**:
   - Si "Usuarios que Sigo" = 0 → **Problema en botón Seguir**
   - Si "Mis Amigos" = 0 → **Problema en sistema de amistad**
   - Si hay usuarios seguidos pero "Publicaciones de Usuarios Seguidos" = 0 → **No han publicado nada**
   - Si "Query COMPLETA del Feed" = 0 → **Problema con el filtro de bloqueos**

---

## 🔧 POSIBLES FIXES

### FIX 1: Verificar que el Botón Seguir Funciona

**Archivo**: `app/presenters/seguir_usuario.php` (líneas 48-56)

El código está correcto:
```php
$stmt = $conexion->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
$stmt->execute([$usuarioActual, $usuarioSeguir]);
```

**Verificación manual en MySQL**:
```sql
-- Verificar tabla seguidores
SELECT s.*, 
       u1.usuario as seguidor_nombre,
       u2.usuario as seguido_nombre
FROM seguidores s
JOIN usuarios u1 ON s.seguidor_id = u1.id_use
JOIN usuarios u2 ON s.seguido_id = u2.id_use
ORDER BY s.id DESC
LIMIT 20;
```

**Si la tabla está vacía**, el problema está en el botón. **Verificar consola del navegador** para errores AJAX.

---

### FIX 2: Verificar Amistades Confirmadas

**Problema**: Si las amistades están en estado `0` (pendiente) en vez de `1` (confirmado), no aparecen en el feed.

**Verificación manual en MySQL**:
```sql
-- Ver todas las amistades (pendientes y confirmadas)
SELECT a.*, 
       u1.usuario as de_nombre,
       u2.usuario as para_nombre,
       CASE a.estado 
           WHEN 0 THEN 'Pendiente' 
           WHEN 1 THEN 'Confirmado' 
           ELSE 'Desconocido' 
       END as estado_texto
FROM amigos a
JOIN usuarios u1 ON a.de = u1.id_use
JOIN usuarios u2 ON a.para = u2.id_use
ORDER BY a.id DESC
LIMIT 20;
```

**Si hay amistades en estado 0**, deben ser aceptadas. **Ir a Solicitudes → Aceptar**.

---

### FIX 3: Simplificar Filtro de Bloqueos (Temporal)

Si el problema persiste, puede ser el `$filtroBloqueos`. Vamos a simplificarlo temporalmente para diagnóstico.

**Archivo**: `app/presenters/publicaciones.php`

**CAMBIO TEMPORAL** (línea 14):
```php
// ANTES (línea 14):
$filtroBloqueos = generarFiltroBloqueos($conexion, $sessionUserId, 'p.usuario');

// DESPUÉS (temporal para diagnóstico):
$filtroBloqueos = "1=1"; // Deshabilitar filtro de bloqueos temporalmente
```

**⚠️ IMPORTANTE**: Este cambio es **SOLO PARA DIAGNÓSTICO**. Si el feed empieza a mostrar publicaciones, el problema es el filtro de bloqueos.

---

### FIX 4: Limpiar Cache del Navegador

A veces el problema es simplemente cache.

**Soluciones**:
1. **Ctrl + F5** (Windows) o **Cmd + Shift + R** (Mac) para forzar recarga
2. **Abrir en modo incógnito/privado**
3. **Limpiar cache del navegador**:
   - Chrome: Configuración → Privacidad → Borrar datos de navegación
   - Firefox: Configuración → Privacidad → Limpiar historial reciente

---

### FIX 5: Verificar que Existan Publicaciones

**Verificación manual en MySQL**:
```sql
-- Ver publicaciones de usuarios específicos
SELECT p.id_pub, p.contenido, p.fecha, u.usuario, u.id_use
FROM publicaciones p
JOIN usuarios u ON p.usuario = u.id_use
ORDER BY p.id_pub DESC
LIMIT 50;
```

**Si no hay publicaciones**, el feed estará vacío (es normal).

---

## 📊 EJEMPLO DE DIAGNÓSTICO

### Caso Real: Usuario A (ID: 5)

**Resultado del diagnóstico**:
```
1️⃣ Mis Publicaciones: 3 publicaciones ✅
2️⃣ Usuarios que Sigo: 0 usuarios seguidos ❌ PROBLEMA DETECTADO
3️⃣ Publicaciones de Usuarios Seguidos: 0 ❌
4️⃣ Mis Amigos: 2 amigos ✅
5️⃣ Publicaciones de Amigos: 5 publicaciones ✅
6️⃣ Query COMPLETA del Feed: 8 publicaciones ✅
7️⃣ Tabla Seguidores: 0 filas ❌ PROBLEMA DETECTADO
8️⃣ Tabla Amigos: 4 filas ✅
```

**Interpretación**:
- ✅ El sistema de **amistades funciona** (2 amigos, 5 publicaciones)
- ❌ El sistema de **seguidos NO funciona** (0 usuarios seguidos, 0 en tabla)
- ❌ El botón "Seguir" **NO está insertando** en la tabla `seguidores`

**Solución**: Verificar JavaScript del botón Seguir en `perfil.php`.

---

## 🚀 PASOS PARA RESOLVER

### Paso 1: Ejecutar Diagnóstico
```
1. Ir a: http://localhost/Converza/debug_feed.php
2. Anotar qué secciones muestran "0" o están vacías
3. Identificar el problema según la tabla de arriba
```

### Paso 2: Verificar Datos Manualmente
```sql
-- En phpMyAdmin o consola MySQL:

-- Verificar seguidores
SELECT * FROM seguidores WHERE seguidor_id = TU_ID_USUARIO;

-- Verificar amigos
SELECT * FROM amigos WHERE (de = TU_ID_USUARIO OR para = TU_ID_USUARIO) AND estado = 1;

-- Verificar publicaciones de amigos
SELECT p.* FROM publicaciones p
WHERE p.usuario IN (
    SELECT CASE WHEN a.de = TU_ID_USUARIO THEN a.para ELSE a.de END
    FROM amigos a 
    WHERE (a.de = TU_ID_USUARIO OR a.para = TU_ID_USUARIO) AND a.estado = 1
);
```

### Paso 3: Probar Fix Temporal
```php
// En publicaciones.php, línea 14:
$filtroBloqueos = "1=1"; // Deshabilitar temporalmente
```

### Paso 4: Verificar Consola del Navegador
```
1. Abrir index.php
2. F12 → Pestaña "Console"
3. Hacer clic en "Seguir" a alguien
4. Ver si hay errores AJAX o JavaScript
```

---

## ✅ VERIFICACIÓN FINAL

Después de aplicar los fixes, verificar:

1. **Seguir a un usuario**:
   - ✅ Botón cambia a "Siguiendo"
   - ✅ Aparece en "Usuarios que Sigo" en debug_feed.php
   - ✅ Su publicación aparece en el feed del index

2. **Agregar amigo**:
   - ✅ Enviar solicitud
   - ✅ El otro usuario la acepta
   - ✅ Estado = 1 (confirmado) en tabla `amigos`
   - ✅ Sus publicaciones aparecen en el feed

3. **Feed completo**:
   - ✅ Muestra tus publicaciones
   - ✅ Muestra publicaciones de seguidos
   - ✅ Muestra publicaciones de amigos
   - ✅ No muestra publicaciones de bloqueados

---

## 📝 NOTAS IMPORTANTES

### Sistema de Seguidos vs Amigos

| Característica | Seguidos | Amigos |
|----------------|----------|--------|
| **Tabla** | `seguidores` | `amigos` |
| **Relación** | Unidireccional | Bidireccional |
| **Requiere aceptación** | ❌ NO | ✅ SÍ |
| **Aparece en feed** | ✅ SÍ | ✅ SÍ |
| **Estado** | - | 0 (pendiente) o 1 (confirmado) |

### Diferencia entre Seguir y Ser Amigo

- **Seguir**: Tú sigues a alguien → Ves sus publicaciones (no requiere aceptación)
- **Amigo**: Ambos aceptaron la amistad → Ambos ven publicaciones del otro
- **Seguimiento Mutuo**: Ambos se siguen pero NO son amigos → Ambos ven publicaciones

---

## 🔗 ARCHIVOS INVOLUCRADOS

| Archivo | Líneas Clave | Función |
|---------|--------------|---------|
| `app/presenters/publicaciones.php` | 12-47 | Query del feed con seguidos y amigos |
| `app/presenters/seguir_usuario.php` | 48-56 | INSERT en tabla `seguidores` |
| `app/presenters/solicitud.php` | 30-85 | INSERT/UPDATE en tabla `amigos` |
| `app/presenters/perfil.php` | 1039-1085 | JavaScript botón "Seguir" |
| `debug_feed.php` | 1-300 | Script de diagnóstico |

---

## 🆘 SI EL PROBLEMA PERSISTE

1. **Compartir resultado de debug_feed.php**
2. **Verificar errores en consola del navegador** (F12 → Console)
3. **Ejecutar queries SQL manualmente** en phpMyAdmin
4. **Verificar que las tablas existen**:
   ```sql
   SHOW TABLES LIKE 'seguidores';
   SHOW TABLES LIKE 'amigos';
   DESCRIBE seguidores;
   DESCRIBE amigos;
   ```

---

## 📊 RESUMEN EJECUTIVO

| Componente | Estado | Acción |
|------------|--------|--------|
| **Query del feed** | ✅ CORRECTO | No requiere cambios |
| **Botón Seguir** | ⚠️ VERIFICAR | Ejecutar debug_feed.php |
| **Sistema de amistades** | ⚠️ VERIFICAR | Revisar estado = 1 |
| **Tabla seguidores** | ⚠️ VERIFICAR | Debe tener datos |
| **Tabla amigos** | ⚠️ VERIFICAR | Estado debe ser 1 |

---

**Próximo paso**: Ejecutar `debug_feed.php` y compartir los resultados para identificar el problema exacto.
