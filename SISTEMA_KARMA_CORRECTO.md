# 🎯 CONFIGURACIÓN SISTEMA DE KARMA CORRECTO

## 📋 Resumen

Se ha migrado el sistema de karma para usar las **tablas existentes** en lugar de una columna inexistente en `usuarios`.

---

## 🏗️ Arquitectura del Sistema

### Tablas Utilizadas

1. **`karma_social`** - Registra cada acción individual de karma
   - `usuario_id`: ID del usuario que realiza la acción
   - `tipo_accion`: Tipo de acción (ej: `reaccion_me_gusta`)
   - `puntos`: Puntos ganados/perdidos
   - `referencia_id`: ID de la publicación/comentario
   - `referencia_tipo`: Tipo de referencia (`publicacion`, `comentario`)
   - `descripcion`: Descripción legible de la acción
   - `fecha_accion`: Timestamp de la acción

2. **`karma_total_usuarios`** - Mantiene el total acumulado por usuario
   - `usuario_id`: ID del usuario (PRIMARY KEY)
   - `karma_total`: Suma total de puntos
   - `acciones_totales`: Contador de acciones
   - `ultima_accion`: Timestamp de última actualización

3. **`usuarios_con_karma`** - Vista para consultas rápidas
   - Combina datos de usuarios con su karma

### Trigger Automático

```sql
after_karma_social_insert
```
- Se ejecuta automáticamente después de cada INSERT en `karma_social`
- Actualiza `karma_total_usuarios` incrementando:
  - `karma_total` += puntos
  - `acciones_totales` += 1
  - `ultima_accion` = NOW()

---

## 🚀 Pasos de Instalación

### 1️⃣ Ejecutar SQL de Configuración

1. Abre **phpMyAdmin**: http://localhost/phpmyadmin
2. Selecciona tu base de datos
3. Ve a la pestaña **SQL**
4. Abre el archivo: `sql/configurar_sistema_karma.sql`
5. Copia TODO el contenido
6. Pega en phpMyAdmin
7. Click en **"Continuar"**

Este script hará:
- ✅ Crear el trigger `after_karma_social_insert`
- ✅ Inicializar registros en `karma_total_usuarios` para usuarios existentes
- ✅ Recalcular karma desde el historial (si existe)
- ✅ Recrear la vista `usuarios_con_karma`
- ✅ Crear índices para rendimiento

### 2️⃣ Verificar Instalación

Abre en tu navegador:
```
http://localhost/Converza/test_karma_correcto.php
```

Verás un reporte detallado con:
- ✅ Estado de las tablas
- ✅ Estado del trigger
- ✅ Estructura de las tablas
- ✅ Estadísticas del sistema
- ✅ Top 10 usuarios
- ✅ Últimas acciones

**Debe aparecer: "🎉 ¡SISTEMA COMPLETAMENTE FUNCIONAL!"**

---

## 💻 Cambios en el Código

### Archivos Modificados

#### 1. `app/presenters/save_reaction.php`

**ANTES:**
```php
UPDATE usuarios 
SET karma = karma + :puntos 
WHERE id_use = :usuario_id
```

**AHORA:**
```php
INSERT INTO karma_social 
(usuario_id, tipo_accion, puntos, referencia_id, referencia_tipo, descripcion, fecha_accion)
VALUES 
(:usuario_id, :tipo_accion, :puntos, :referencia_id, :referencia_tipo, :descripcion, NOW())
```

El trigger se encarga automáticamente de actualizar `karma_total_usuarios`.

#### 2. `app/presenters/get_karma.php`

**ANTES:**
```php
SELECT karma FROM usuarios WHERE id_use = ?
```

**AHORA:**
```php
SELECT karma_total, acciones_totales 
FROM karma_total_usuarios 
WHERE usuario_id = ?
```

Si el usuario no tiene registro, se crea automáticamente con valores iniciales.

---

## 🔄 Flujo de Funcionamiento

1. **Usuario da reacción** → Frontend llama a `save_reaction.php`
2. **save_reaction.php** → Inserta en `karma_social`:
   ```php
   INSERT INTO karma_social 
   (usuario_id, tipo_accion, puntos, ...)
   VALUES (123, 'reaccion_me_gusta', 5, ...)
   ```
3. **Trigger se activa automáticamente** → Actualiza `karma_total_usuarios`:
   ```sql
   UPDATE karma_total_usuarios
   SET karma_total = karma_total + 5,
       acciones_totales = acciones_totales + 1
   WHERE usuario_id = 123
   ```
4. **save_reaction.php** → Lee el karma actualizado:
   ```php
   SELECT karma_total FROM karma_total_usuarios WHERE usuario_id = 123
   ```
5. **Respuesta JSON** → Frontend actualiza el contador:
   ```json
   {
     "karma_actualizado": {
       "karma": "105",
       "nivel": 2,
       "nivel_titulo": "Aprendiz"
     }
   }
   ```

---

## 📊 Puntos por Reacción

| Reacción | Puntos | Tipo |
|----------|--------|------|
| 👍 Me gusta | +5 | Positivo |
| ❤️ Me encanta | +10 | Positivo |
| 😂 Me divierte | +7 | Positivo |
| 😮 Me asombra | +8 | Positivo |
| 😢 Me entristece | -3 | Negativo |
| 😡 Me enoja | -5 | Negativo |

---

## 🧪 Modo Debug

El modo DEBUG sigue activo en `save_reaction.php` línea 7:

```php
define('DEBUG_KARMA', true);
```

**Logs que verás en la consola del navegador:**
- 📊 Karma ANTES de actualizar
- 💾 INSERT en karma_social ejecutado
- 📊 Karma DESPUÉS de actualizar
- 🏆 Nivel recalculado
- ✅ karma_actualizado final

**Para desactivar debug:**
```php
define('DEBUG_KARMA', false);
```

---

## ✅ Ventajas del Nuevo Sistema

1. **Historial Completo**
   - Cada acción se registra en `karma_social`
   - Puedes ver todas las acciones de un usuario
   - Puedes hacer auditorías y reportes

2. **Performance**
   - `karma_total_usuarios` es rápido para consultas
   - No necesita contar en tiempo real
   - Índices optimizados

3. **Integridad**
   - Trigger garantiza consistencia
   - No hay riesgo de desincronización
   - Datos atómicos

4. **Escalabilidad**
   - Fácil agregar nuevos tipos de acciones
   - Sistema extensible para recompensas
   - Compatible con `karma_reciente_usuarios` (30 días)

5. **Análisis**
   - Puedes hacer estadísticas complejas
   - Ver evolución del karma en el tiempo
   - Identificar usuarios más activos

---

## 🔧 Troubleshooting

### Problema: "Column 'karma' not found"
**Solución:** Ejecutaste el código viejo. Asegúrate de haber ejecutado `sql/configurar_sistema_karma.sql`

### Problema: "Trigger not found"
**Solución:** El trigger no se creó. Ejecuta manualmente:
```sql
DROP TRIGGER IF EXISTS after_karma_social_insert;
-- (copiar resto del script)
```

### Problema: Karma no se actualiza
1. Verifica que el trigger existe: `SHOW TRIGGERS LIKE 'karma_social'`
2. Verifica logs en consola del navegador (DEBUG_KARMA = true)
3. Verifica que `karma_social` tenga nuevas filas: `SELECT * FROM karma_social ORDER BY id DESC LIMIT 10`

### Problema: Usuario no tiene registro en karma_total_usuarios
**Solución:** El sistema lo crea automáticamente, pero puedes forzarlo:
```sql
INSERT INTO karma_total_usuarios (usuario_id, karma_total, acciones_totales, ultima_accion)
SELECT id_use, 0, 0, NOW()
FROM usuarios
WHERE NOT EXISTS (SELECT 1 FROM karma_total_usuarios WHERE usuario_id = usuarios.id_use);
```

---

## 📈 Consultas Útiles

### Ver karma de un usuario específico
```sql
SELECT * FROM karma_total_usuarios WHERE usuario_id = 123;
```

### Ver historial de acciones de un usuario
```sql
SELECT * FROM karma_social 
WHERE usuario_id = 123 
ORDER BY fecha_accion DESC;
```

### Top 10 usuarios con más karma
```sql
SELECT u.usuario, kt.karma_total, kt.acciones_totales
FROM usuarios u
JOIN karma_total_usuarios kt ON u.id_use = kt.usuario_id
ORDER BY kt.karma_total DESC
LIMIT 10;
```

### Acciones de los últimos 30 días
```sql
SELECT * FROM karma_social 
WHERE fecha_accion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY fecha_accion DESC;
```

### Karma por tipo de acción
```sql
SELECT 
    tipo_accion,
    COUNT(*) as total_acciones,
    SUM(puntos) as puntos_totales,
    AVG(puntos) as promedio_puntos
FROM karma_social
GROUP BY tipo_accion
ORDER BY puntos_totales DESC;
```

---

## 🎯 Testing

Después de configurar el sistema:

1. **Dale una reacción** a cualquier publicación
2. **Abre la consola** del navegador (F12)
3. **Busca los logs** con emoji (🎯, 📊, 💾, etc.)
4. **Verifica** que el contador en el header se actualice

**Resultado esperado:**
```
🎯 Puntos calculados: {tipo_reaccion: "me_gusta", puntos: 5, ...}
📊 Karma ANTES de actualizar: {karma_antes: 100, ...}
💾 INSERT en karma_social ejecutado: {rows_affected: 1, ...}
📊 Karma DESPUÉS de actualizar: {karma_despues: 105, diferencia: 5, trigger_funciono: true}
✅ karma_actualizado final: {karma: "105", nivel: 2, ...}
```

---

## 📝 Checklist de Implementación

- [ ] Ejecutar `sql/configurar_sistema_karma.sql` en phpMyAdmin
- [ ] Verificar en `test_karma_correcto.php` que todo está OK
- [ ] Dar una reacción de prueba
- [ ] Verificar logs en consola del navegador
- [ ] Verificar que el contador se actualiza en el header
- [ ] Verificar que aparece la notificación de karma (+5, +10, etc.)
- [ ] Desactivar DEBUG_KARMA cuando todo funcione
- [ ] Revisar tabla `karma_social` para confirmar que se registran acciones

---

## 🎉 ¡Listo!

El sistema de karma ahora:
- ✅ Usa tablas profesionales y escalables
- ✅ Mantiene historial completo de acciones
- ✅ Se actualiza automáticamente con triggers
- ✅ Tiene índices para performance
- ✅ Es extensible para futuras features

**¡A disfrutar del karma funcionando!** 🚀
