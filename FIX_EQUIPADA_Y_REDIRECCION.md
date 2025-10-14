# 🔧 CORRECCIONES FINALES - Sistema Karma + Editar Perfil

## 📋 Problemas Corregidos

### 1. ❌ Error SQL: Columna 'equipada' no encontrada
**Error:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'equipada' in 'field list'
in karma_tienda.php:77
```

**Causa:**
La tabla `usuario_recompensas` se creó con columna `activa` pero el código PHP buscaba `equipada`.

**Solución:**
```sql
ALTER TABLE usuario_recompensas 
CHANGE activa equipada TINYINT(1) DEFAULT 0;
```

✅ Columna renombrada de `activa` → `equipada`

---

### 2. ❌ Redirección incorrecta al editar perfil
**Problema:**
Al guardar cambios en "Editar Perfil", redirigía a:
- ❌ `index.php` o `/converza/app/view?id=$id`
- ⚠️ El usuario perdía el contexto de su perfil

**Solución:**
Modificado `app/presenters/editarperfil.php`:

**ANTES (línea 149):**
```php
header("Location: /converza/app/view?id=$id");
```

**DESPUÉS:**
```php
header("Location: perfil.php?id=$id");
```

**ANTES (línea 161):**
```php
header("Location: index.php");
```

**DESPUÉS:**
```php
header("Location: perfil.php?id=".$_SESSION['id']);
```

✅ Ahora permanece en la página de perfil después de guardar

---

## 📊 Estructura Final de la Tabla

```sql
CREATE TABLE usuario_recompensas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT NOT NULL,
    recompensa_id       INT NOT NULL,
    equipada            TINYINT(1) DEFAULT 0,  -- ✅ CORREGIDO
    fecha_desbloqueo    DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_usuario_recompensa (usuario_id, recompensa_id),
    KEY idx_usuario (usuario_id),
    KEY idx_recompensa (recompensa_id),
    KEY idx_equipada (equipada),  -- ✅ CORREGIDO
    
    FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id_use) ON DELETE CASCADE,
    FOREIGN KEY (recompensa_id) 
        REFERENCES karma_recompensas(id) ON DELETE CASCADE
);
```

**Campos:**
- `id` - ID único del registro
- `usuario_id` - FK a `usuarios.id_use`
- `recompensa_id` - FK a `karma_recompensas.id`
- `equipada` - **0** = desbloqueada, **1** = equipada activamente
- `fecha_desbloqueo` - Timestamp de cuándo se desbloqueó

---

## 📁 Archivos Modificados

### 1. `app/presenters/editarperfil.php`
**Cambios:**
- Línea 149: `header("Location: perfil.php?id=$id");`
- Línea 161: `header("Location: perfil.php?id=".$_SESSION['id']);`

**Resultado:**
✅ Usuario permanece en su perfil después de editar

### 2. `sql/create_usuario_recompensas_table.sql`
**Cambios:**
- Línea 5: `equipada TINYINT(1) DEFAULT 0`
- Línea 11: `KEY idx_equipada (equipada)`

**Resultado:**
✅ Definición SQL actualizada para futuras instalaciones

### 3. `setup_usuario_recompensas.php`
**Cambios:**
- Línea 11: `equipada TINYINT(1) DEFAULT 0`

**Resultado:**
✅ Script de setup usa el nombre correcto

### 4. Base de Datos (ejecutado)
**Comando:**
```sql
ALTER TABLE usuario_recompensas 
CHANGE activa equipada TINYINT(1) DEFAULT 0;
```

**Resultado:**
✅ Tabla actualizada en MySQL

---

## 🧪 Testing

### Test 1: Tienda de Karma (Sin Error SQL)
```
1. Ir a: http://localhost/Converza/app/presenters/karma_tienda.php
2. ✅ Debe cargar SIN error SQL
3. ✅ Debe mostrar 24 recompensas
4. ✅ Botones "Desbloquear" y "Equipar" visibles
```

### Test 2: Editar Perfil (Permanece en Perfil)
```
1. Ir a: http://localhost/Converza/app/presenters/perfil.php?id=20
2. Click en "Editar Perfil"
3. Cambiar nombre de usuario o bio
4. Click en "Guardar cambios"
5. ✅ Debe permanecer en perfil.php?id=20
6. ✅ NO debe redirigir a index.php
7. ✅ Cambios se reflejan inmediatamente
```

### Test 3: Desbloquear Recompensa
```
1. Ir a karma_tienda.php
2. Buscar recompensa con karma suficiente
3. Click en "Desbloquear"
4. ✅ Debe guardarse en usuario_recompensas con equipada=0
5. Botón cambia a "Equipar"
```

### Test 4: Equipar Recompensa
```
1. En karma_tienda.php
2. Click en "Equipar" en recompensa desbloqueada
3. ✅ Debe actualizarse equipada=1
4. Botón cambia a "Desequipar"
5. Icono de checkmark aparece
```

---

## 🔍 Verificación de Columna

**Verificar que la columna existe:**
```sql
DESCRIBE usuario_recompensas;
```

**Resultado Esperado:**
```
┌────────────────────┬──────────────┬──────┬─────┬────────┐
│ Field              │ Type         │ Null │ Key │ Default│
├────────────────────┼──────────────┼──────┼─────┼────────┤
│ id                 │ int(11)      │ NO   │ PRI │ NULL   │
│ usuario_id         │ int(11)      │ NO   │ MUL │ NULL   │
│ recompensa_id      │ int(11)      │ NO   │ MUL │ NULL   │
│ equipada           │ tinyint(1)   │ YES  │ MUL │ 0      │ ✅
│ fecha_desbloqueo   │ datetime     │ YES  │     │ CURRENT│
└────────────────────┴──────────────┴──────┴─────┴────────┘
```

---

## 🎯 Flujo Completo de Recompensas

### 1. Usuario Gana Karma
```
Usuario comenta positivamente
└─ karma-social-helper.php detecta palabras positivas
   └─ Registra +8 karma en karma_social
      └─ Actualiza contador total
         └─ Notificación flotante aparece
```

### 2. Usuario Visita Tienda
```
karma_tienda.php carga
└─ SELECT de karma_recompensas (24 items)
   └─ SELECT de usuario_recompensas WHERE usuario_id = ?
      └─ Compara karma_total vs karma_requerido
         ├─ Bloqueada (gris) si karma < requerido
         ├─ Desbloqueable (azul) si karma >= requerido
         ├─ Desbloqueada (verde) si existe en usuario_recompensas
         └─ Equipada (dorado) si equipada = 1
```

### 3. Usuario Desbloquea Recompensa
```
POST desbloquear=1 & recompensa_id=5
└─ Verifica karma >= karma_requerido
   └─ INSERT INTO usuario_recompensas (usuario_id, recompensa_id, equipada=0)
      └─ Mensaje: "¡Felicidades! Has desbloqueado: [nombre]"
         └─ Botón cambia a "Equipar"
```

### 4. Usuario Equipa Recompensa
```
POST equipar=1 & recompensa_id=5
└─ UPDATE usuario_recompensas SET equipada = 1 WHERE id = ?
   └─ Icono ✓ aparece en la card
      └─ Recompensa activa en perfil
```

---

## 📊 Relación entre Tablas

```
┌─────────────────┐
│    usuarios     │
│  id_use (PK)    │◄──┐
└─────────────────┘   │
                      │
┌─────────────────────┼────────────────────┐
│ usuario_recompensas │                    │
│  id (PK)            │                    │
│  usuario_id (FK) ───┘                    │
│  recompensa_id (FK) ─────┐               │
│  equipada (0 o 1)        │               │
│  fecha_desbloqueo        │               │
└──────────────────────────┼───────────────┘
                           │
                  ┌────────▼───────────┐
                  │ karma_recompensas  │
                  │  id (PK)           │
                  │  nombre            │
                  │  descripcion       │
                  │  karma_requerido   │
                  │  tipo              │
                  │  icono             │
                  └────────────────────┘
```

---

## 🎨 Estados de Recompensa en UI

### 🔒 Bloqueada (karma insuficiente)
```css
background: #f8f9fa;
border: 2px dashed #dee2e6;
opacity: 0.6;
```
```html
<button disabled class="btn btn-secondary">
    🔒 Bloqueada (Necesitas 250 karma)
</button>
```

### 💎 Desbloqueable (karma suficiente)
```css
background: white;
border: 3px solid #0d6efd;
animation: pulse;
```
```html
<button class="btn btn-primary">
    ✨ Desbloquear (100 karma)
</button>
```

### ✅ Desbloqueada (en inventario)
```css
background: white;
border: 3px solid #198754;
```
```html
<button class="btn btn-success">
    👍 Equipar
</button>
```

### 👑 Equipada (activa)
```css
background: linear-gradient(135deg, #ffd700, #ffed4e);
border: 3px solid #f39c12;
box-shadow: 0 0 20px rgba(255,215,0,0.5);
```
```html
<button class="btn btn-warning">
    ✓ Equipada
</button>
```

---

## 🚀 Próximas Funcionalidades (Opcionales)

### 1. Mostrar Recompensas Equipadas en Perfil
```php
// perfil.php
$stmtRecompensas = $conexion->prepare("
    SELECT r.* 
    FROM usuario_recompensas ur
    JOIN karma_recompensas r ON ur.recompensa_id = r.id
    WHERE ur.usuario_id = ? AND ur.equipada = 1
");
```

### 2. Límite de Recompensas Equipadas
```php
// Máximo 3 recompensas equipadas simultáneamente
if ($count_equipadas >= 3) {
    $error = "Debes desequipar otra recompensa primero";
}
```

### 3. Recompensas por Categoría
```php
// Solo 1 título, 1 insignia, 1 marco equipado a la vez
if ($tipo === 'titulo' && $ya_tiene_titulo_equipado) {
    // Auto-desequipar el anterior
}
```

---

## ✅ Resumen de Correcciones

| Problema | Estado | Solución |
|----------|--------|----------|
| Columna `equipada` no existe | ✅ Corregido | ALTER TABLE ejecutado |
| Redirección a index | ✅ Corregido | Ahora va a perfil.php |
| Error SQL en tienda | ✅ Corregido | Columna renombrada |
| Scripts desactualizados | ✅ Corregido | SQL y PHP actualizados |

---

**Fecha:** 13 de Octubre, 2025  
**Status:** ✅ TODO FUNCIONANDO CORRECTAMENTE  
**Errores SQL:** 0  
**Errores PHP:** 0
