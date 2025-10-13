# 🔮 CONEXIONES MÍSTICAS - Sistema de Serendipia Digital

## 📋 Descripción
Sistema que detecta y resalta patrones o coincidencias curiosas entre usuarios para fomentar conexiones inesperadas basadas en:
- Gustos compartidos (reacciones similares)
- Intereses comunes (comentarios en las mismas publicaciones)
- Amigos de amigos
- Patrones de actividad (horarios coincidentes)

## 🚀 Instalación

### Paso 1: Crear la tabla en la base de datos
Ejecuta el archivo SQL:
```bash
http://localhost/phpmyadmin
```
Luego ejecuta el contenido de:
```
sql/create_conexiones_misticas.sql
```

### Paso 2: Detectar conexiones (ejecutar manualmente o como cron)
Visita:
```
http://localhost/Converza/detectar_conexiones.php
```

Este script analizará todos los datos y detectará las conexiones místicas.

### Paso 3: Agregar el widget al index
Edita `app/view/index.php` y agrega antes de las publicaciones:

```php
<?php include __DIR__.'/../presenters/widget_conexiones_misticas.php'; ?>
```

Por ejemplo, después de la línea que muestra el formulario de publicar:
```php
<!-- ... formulario de publicar ... -->

<?php include __DIR__.'/../presenters/widget_conexiones_misticas.php'; ?>

<?php include __DIR__.'/../presenters/publicaciones.php'; ?>
```

## 📁 Archivos creados

### Base de datos:
- `sql/create_conexiones_misticas.sql` - Tabla de conexiones

### Backend:
- `app/models/conexiones-misticas-helper.php` - Motor de análisis
- `detectar_conexiones.php` - Script ejecutable

### Frontend:
- `app/presenters/widget_conexiones_misticas.php` - Widget para el feed
- `app/presenters/conexiones_misticas.php` - Página completa de conexiones

## 🎯 Tipos de conexiones detectadas

| Tipo | Descripción | Icono |
|------|-------------|-------|
| `gustos_compartidos` | Usuarios que reaccionan a las mismas publicaciones | 💖 |
| `intereses_comunes` | Usuarios que comentan en las mismas publicaciones | 💬 |
| `amigos_de_amigos` | Usuarios que comparten amigos en común | 👥 |
| `horarios_coincidentes` | Usuarios activos en los mismos horarios | 🕐 |

## 🔄 Automatización (Opcional)

Para actualizar las conexiones automáticamente, crea un cron job:

```bash
# Ejecutar cada 6 horas
0 */6 * * * cd /xampp/htdocs/Converza && php detectar_conexiones.php
```

O en Windows Task Scheduler:
- Programa: `php.exe`
- Argumentos: `C:\xampp\htdocs\Converza\detectar_conexiones.php`
- Frecuencia: Cada 6 horas

## ✨ Características

- ✅ **No rompe nada**: Sistema completamente independiente
- ✅ **Visual atractivo**: Degradados y animaciones
- ✅ **Puntuación**: Cada conexión tiene un nivel de coincidencia (0-100%)
- ✅ **Clickeable**: Hacer clic en una conexión lleva al perfil del usuario
- ✅ **Responsive**: Se adapta a móviles y tablets
- ✅ **Actualizable**: Re-ejecutar el script actualiza las conexiones

## 🎨 Personalización

Puedes ajustar:
- **Umbrales de detección**: En `conexiones-misticas-helper.php`, líneas con `HAVING`
- **Límite de conexiones mostradas**: En `widget_conexiones_misticas.php`, cambiar el `3` en `obtenerConexionesUsuario($_SESSION['id'], 3)`
- **Colores del widget**: En el `<style>` del widget, modifica los gradientes

## 📊 Ejemplo de uso

1. Usuario A y Usuario B reaccionan a 5 publicaciones en común
2. El sistema detecta esta coincidencia
3. Ambos usuarios ven en su feed:
   > 🔮 **Conexiones Místicas**
   > 💖 **Usuario B** (80%)
   > ¡Ambos reaccionaron a 5 publicaciones similares! 💫

## 🐛 Troubleshooting

**Problema**: No aparecen conexiones
- Solución: Ejecutar `detectar_conexiones.php` al menos una vez

**Problema**: Widget no se muestra
- Solución: Verificar que el `include` esté agregado en `index.php`

**Problema**: Error en la tabla
- Solución: Verificar que la tabla `conexiones_misticas` exista en la BD

## 📝 Notas

- El sistema analiza datos históricos, no en tiempo real
- Se recomienda ejecutar el detector cada 6-12 horas
- Las conexiones se actualizan automáticamente al re-ejecutar
- Los usuarios bloqueados NO aparecen en las conexiones
