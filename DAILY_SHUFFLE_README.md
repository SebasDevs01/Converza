# 🎲 Daily Shuffle - Sistema de Descubrimiento de Usuarios

## 📋 Descripción

Daily Shuffle es una funcionalidad que muestra cada día 10 usuarios aleatorios nuevos para conectar. Similar a Tinder o Bumble, permite a los usuarios descubrir personas que no son sus amigos ni están bloqueadas.

## ✨ Características

- 🔄 **Shuffle Diario Automático**: Cada día se genera una nueva lista de 10 usuarios
- 👥 **Filtrado Inteligente**: Excluye amigos actuales, solicitudes pendientes y usuarios bloqueados
- ✅ **Seguimiento de Contactos**: Marca los usuarios ya contactados
- 🎨 **Interfaz Tipo Tinder**: Cards atractivas con animaciones suaves
- 📱 **Totalmente Responsive**: Funciona perfectamente en móvil y escritorio

## 🚀 Instalación

### 1. Crear la tabla en la base de datos

Ejecuta el script de instalación visitando:
```
http://localhost/Converza/setup_daily_shuffle.php
```

O manualmente ejecuta el SQL:
```sql
-- Contenido de sql/create_daily_shuffle_table.sql
CREATE TABLE IF NOT EXISTS daily_shuffle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    usuario_mostrado_id INT NOT NULL,
    fecha_shuffle DATE NOT NULL,
    ya_contactado BOOLEAN DEFAULT FALSE,
    fecha_contacto TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use),
    FOREIGN KEY (usuario_mostrado_id) REFERENCES usuarios(id_use),
    UNIQUE KEY unique_daily_pair (usuario_id, usuario_mostrado_id, fecha_shuffle),
    INDEX idx_usuario_fecha (usuario_id, fecha_shuffle),
    INDEX idx_fecha_shuffle (fecha_shuffle)
);
```

### 2. Archivos necesarios

Ya están creados en tu proyecto:

**Backend:**
- ✅ `app/presenters/daily_shuffle.php` - API que genera y devuelve el shuffle diario
- ✅ `app/presenters/marcar_contacto_shuffle.php` - API para marcar usuarios contactados
- ✅ `sql/create_daily_shuffle_table.sql` - Script SQL de la tabla

**Frontend:**
- ✅ `app/view/index.php` - Botón en navbar agregado
- ✅ `app/view/_navbar_panels.php` - Offcanvas con interfaz completa

## 💻 Uso

### Para Usuarios

1. **Acceder al Daily Shuffle:**
   - Click en el icono 🔀 "Shuffle" en la barra de navegación
   - Se abre un panel lateral con los usuarios del día

2. **Interactuar con usuarios:**
   - **Ver perfil**: Ver el perfil completo del usuario
   - **Agregar**: Enviar solicitud de amistad

3. **Seguimiento:**
   - Los usuarios ya contactados se marcan automáticamente
   - El shuffle se renueva cada día a medianoche

### Para Desarrolladores

#### API Endpoints

**1. Obtener Daily Shuffle**
```javascript
GET /app/presenters/daily_shuffle.php

Response:
{
    "success": true,
    "shuffle": [
        {
            "id": 1,
            "usuario_id": 1,
            "usuario_mostrado_id": 5,
            "fecha_shuffle": "2025-10-12",
            "ya_contactado": false,
            "usuario": "juanperez",
            "nombre": "Juan Pérez",
            "avatar": "avatar.jpg",
            "descripcion": "Amante de la tecnología"
        }
    ],
    "fecha": "2025-10-12",
    "total": 10,
    "nuevo_shuffle": false
}
```

**2. Marcar usuario como contactado**
```javascript
POST /app/presenters/marcar_contacto_shuffle.php
Body: usuario_contactado_id=5

Response:
{
    "success": true,
    "mensaje": "Usuario marcado como contactado en Daily Shuffle"
}
```

## 🎨 Personalización

### Cambiar cantidad de usuarios diarios

Edita `app/presenters/daily_shuffle.php` línea 57:
```php
// Cambiar LIMIT 10 por el número deseado
ORDER BY RAND()
LIMIT 10  // <- Cambiar aquí
```

### Modificar estilos

Los estilos CSS están en `app/view/_navbar_panels.php`:
```css
.shuffle-card {
    background: white;
    border-radius: 20px;
    /* Personaliza aquí */
}
```

### Cambiar colores del gradiente

```css
.bg-gradient-shuffle {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* Cambiar colores aquí */
}
```

## 🔧 Mantenimiento

### Limpiar shuffles antiguos

El sistema limpia automáticamente los shuffles de días anteriores. Si quieres hacerlo manualmente:

```sql
DELETE FROM daily_shuffle WHERE fecha_shuffle < CURDATE();
```

### Ver estadísticas

```sql
-- Usuarios más contactados
SELECT 
    u.usuario,
    COUNT(*) as veces_contactado
FROM daily_shuffle ds
JOIN usuarios u ON ds.usuario_mostrado_id = u.id_use
WHERE ds.ya_contactado = TRUE
GROUP BY ds.usuario_mostrado_id
ORDER BY veces_contactado DESC
LIMIT 10;

-- Shuffle del día
SELECT 
    COUNT(*) as total_hoy,
    SUM(ya_contactado) as contactados_hoy
FROM daily_shuffle
WHERE fecha_shuffle = CURDATE();
```

## 🐛 Troubleshooting

### El shuffle no se muestra

1. Verifica que la tabla existe:
   ```sql
   SHOW TABLES LIKE 'daily_shuffle';
   ```

2. Verifica que hay usuarios disponibles:
   ```sql
   SELECT COUNT(*) FROM usuarios WHERE id_use != 1;
   ```

### Error de permisos

Asegúrate que el usuario tenga sesión activa:
```php
if(!isset($_SESSION['usuario'])) {
    // No autorizado
}
```

### No se generan usuarios

- Verifica que existan usuarios en la BD que no sean amigos
- Revisa la tabla `bloqueos` por posibles bloqueos masivos
- Chequea la tabla `amigos` para ver relaciones existentes

## 📊 Base de Datos

### Esquema de la tabla

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT | ID único del registro |
| `usuario_id` | INT | ID del usuario que recibe el shuffle |
| `usuario_mostrado_id` | INT | ID del usuario mostrado en el shuffle |
| `fecha_shuffle` | DATE | Fecha del shuffle |
| `ya_contactado` | BOOLEAN | Si el usuario fue contactado |
| `fecha_contacto` | TIMESTAMP | Fecha y hora del contacto |
| `created_at` | TIMESTAMP | Fecha de creación del registro |

### Índices

- `PRIMARY KEY (id)` - Llave primaria
- `UNIQUE KEY unique_daily_pair (usuario_id, usuario_mostrado_id, fecha_shuffle)` - Evita duplicados
- `INDEX idx_usuario_fecha (usuario_id, fecha_shuffle)` - Optimiza búsquedas
- `INDEX idx_fecha_shuffle (fecha_shuffle)` - Optimiza limpieza de datos

## 🔐 Seguridad

- ✅ Validación de sesión en todos los endpoints
- ✅ Preparación de consultas SQL (PDO)
- ✅ Escape de HTML en el frontend
- ✅ Verificación de permisos antes de marcar contacto
- ✅ Prevención de duplicados con clave única

## 🚀 Próximas Mejoras

- [ ] Sistema de "Me gusta" mutuo antes de agregar
- [ ] Filtros por edad, género, ubicación
- [ ] Notificaciones cuando hay nuevo shuffle
- [ ] Estadísticas de match rate
- [ ] Compartir perfiles interesantes
- [ ] Modo "Super Like" para destacar solicitud

## 📝 Notas

- El shuffle se regenera automáticamente cada día
- Los shuffles antiguos se eliminan automáticamente
- No se muestran usuarios bloqueados ni que te bloquearon
- No se muestran amigos actuales ni solicitudes pendientes

## 🤝 Contribuir

Si encuentras bugs o tienes sugerencias:
1. Reporta issues en GitHub
2. Crea pull requests con mejoras
3. Sugiere nuevas funcionalidades

---

**Desarrollado para Converza** 🎉
Versión 1.0 - Octubre 2025
