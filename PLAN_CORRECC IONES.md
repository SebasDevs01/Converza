# 🔧 PLAN DE CORRECCIONES COMPLETO

## ✅ Estado Actual
- `save_reaction.php` ya tiene:
  - ✅ Supresión de errores
  - ✅ session_start() con verificación
  - ✅ Todos los require_once al inicio
  - ✅ exit después de JSON

## 📋 TAREAS PENDIENTES

### 1️⃣ **REACCIONES Y KARMA** (Prioridad ALTA)
**Problema**: Error de conexión, aparece al recargar pero no quita karma

**Solución**:
1. ✅ Ya corregido: session_start() duplicado
2. ⏳ Verificar: ¿Se está llamando a `$karmaTriggers->nuevaReaccion()`?
3. ⏳ Probar: Ctrl+Shift+R y clic en 😡

**Archivos**:
- `save_reaction.php` (líneas 185-186)
- `karma-social-triggers.php`
- `karma-social-helper.php` (línea 607 ya corregida: fecha_accion)

---

### 2️⃣ **FILTRAR PUBLICACIONES DEL FEED** (Prioridad ALTA)
**Problema**: Muestra TODAS las publicaciones, debe filtrar por relación

**Debe mostrar SOLO**:
- ✅ Mis propias publicaciones
- ✅ Publicaciones de mis amigos (tabla `amigos` con `estado=1`)
- ✅ Publicaciones de usuarios que sigo (tabla `seguidores`)
- ✅ Publicaciones de usuarios que me siguen (seguimiento mutuo)

**SQL Correcto**:
```sql
SELECT DISTINCT p.* FROM publicaciones p
WHERE p.usuario = :mi_id
OR p.usuario IN (
    -- Amigos confirmados
    SELECT CASE 
        WHEN de = :mi_id THEN para 
        WHEN para = :mi_id THEN de 
    END FROM amigos WHERE estado = 1 AND (de = :mi_id OR para = :mi_id)
)
OR p.usuario IN (
    -- Usuarios que sigo
    SELECT seguido_id FROM seguidores WHERE seguidor_id = :mi_id
)
ORDER BY p.fecha DESC
```

**Archivos a modificar**:
- `app/presenters/publicaciones.php` (query principal)
- O `app/presenters/get_publicaciones.php` si existe

---

### 3️⃣ **BOTÓN AMIGOS EN BUSCADOR** (Prioridad MEDIA)
**Problema**: Muestra "Agregar" aunque ya sean amigos

**Solución**:
1. Consultar tabla `amigos` con `estado=1`
2. Si existe amistad: Mostrar ✅ "Amigos" (deshabilitado)
3. Si NO existe: Mostrar ➕ "Agregar"

**Lógica**:
```php
$stmt = $conexion->prepare("
    SELECT COUNT(*) as es_amigo FROM amigos 
    WHERE estado = 1 
    AND ((de = :yo AND para = :otro) OR (de = :otro AND para = :yo))
");
$stmt->execute([':yo' => $mi_id, ':otro' => $usuario_id]);
$esAmigo = $stmt->fetch()['es_amigo'] > 0;
```

**Archivos a modificar**:
- `app/presenters/buscar.php` o `search.php`
- `app/view/buscador.php` o similar

---

### 4️⃣ **SISTEMA DE PREDICCIONES** ✨ (Prioridad BAJA - Nueva Feature)
**Requisito**: Predicciones divertidas sobre gustos sin comprometer privacidad

**Diseño**:
1. **Tabla `predicciones_usuarios`**:
   ```sql
   CREATE TABLE predicciones_usuarios (
       id INT AUTO_INCREMENT PRIMARY KEY,
       usuario_id INT NOT NULL,
       categoria VARCHAR(50), -- 'musica', 'comida', 'hobbies', 'viajes'
       prediccion TEXT, -- "Probablemente te gusta el rock 🎸"
       confianza ENUM('baja', 'media', 'alta'),
       fecha_generada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       visto TINYINT(1) DEFAULT 0,
       FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use) ON DELETE CASCADE
   );
   ```

2. **Lógica de predicción** (basada en data existente):
   - Analizar palabras clave en publicaciones/comentarios
   - Analizar tipos de reacciones
   - Analizar conexiones místicas
   - NO usar datos sensibles

3. **Ejemplos de predicciones**:
   - "🎮 Probablemente disfrutas los videojuegos"
   - "☕ Parece que eres fan del café"
   - "🌅 Te gustan los atardeceres"
   - "📚 Tienes alma de lector"

4. **Archivos a crear**:
   - `app/models/predicciones-helper.php` (lógica)
   - `app/presenters/get_prediccion.php` (API)
   - `sql/create_predicciones_table.sql` (BD)
   - Componente UI en `perfil.php` o `index.php`

---

## 🎯 ORDEN DE EJECUCIÓN

1. ✅ **Corregir reacciones** (ya hecho, probar con Ctrl+Shift+R)
2. 🔄 **Filtrar publicaciones feed** (siguiente)
3. 🔄 **Botón amigos buscador**
4. 🔄 **Sistema predicciones** (al final)

---

## 🧪 TESTING

### Test Reacciones:
```
1. Ctrl + Shift + R
2. Clic en 😡 me_enoja en publicación 187
3. Verificar: No error, aparece reacción, karma -3
```

### Test Feed:
```
1. Ver feed como usuario A
2. Solo debe aparecer:
   - Mis publicaciones
   - Publicaciones de amigos
   - Publicaciones de usuarios que sigo
```

### Test Buscador:
```
1. Buscar usuario con quien YA soy amigo
2. Debe mostrar: ✅ "Amigos" (no ➕ "Agregar")
```

### Test Predicciones:
```
1. Ir a perfil
2. Ver card "Predicción del día"
3. Mostrar predicción divertida
```

---

## 📌 NOTAS IMPORTANTES

- ⚠️ **NO ROMPER**: Mantener funcionalidad existente
- ⚠️ **PRIVACIDAD**: Predicciones NO deben usar datos sensibles
- ✅ **TESTEAR**: Cada cambio con Ctrl+Shift+R
- ✅ **LOGS**: Verificar `C:\xampp\apache\logs\error.log`
