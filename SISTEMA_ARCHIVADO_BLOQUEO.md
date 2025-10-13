# 📁 Sistema de Archivado y Bloqueo - Converza

## 🎯 Funcionalidades Implementadas

### ✅ 1. Sistema de Archivado de Conversaciones

#### Características:
- **Archivar conversación**: Mueve la conversación de "Chats" a "Archivados"
- **Desarchivar conversación**: Restaura la conversación a "Chats" activos
- **Pestaña "Archivados"**: Nueva sección para ver todas las conversaciones archivadas
- **Datos mostrados**: Usuario, avatar, último mensaje, fecha de archivado
- **Confirmación**: Pregunta antes de archivar
- **Redirección automática**: Después de desarchivar, redirige a la pestaña Chats

#### Flujo de Archivado:
1. Usuario hace clic en menú de 3 puntos → "Archivar"
2. Confirmación con diálogo
3. Se mueve a tabla `chats_archivados`
4. Desaparece de la lista de "Chats"
5. Opción de ir a "Archivados"
6. Desde "Archivados" puede desarchivar con un solo clic

#### Archivos Backend:
- **`gestionar_archivo_chat.php`**: Maneja las acciones de archivar/desarchivar
  - POST: `accion=archivar&usuario_id=X`
  - POST: `accion=desarchivar&usuario_id=X`

---

### ✅ 2. Sistema de Bloqueo/Desbloqueo de Usuarios

#### Características:
- **Bloquear usuario**: Desde el menú de 3 puntos o desde el perfil
- **Desbloquear usuario**: Desde la pestaña "Bloqueados" o desde el perfil
- **Pestaña "Bloqueados"**: Nueva sección para gestionar usuarios bloqueados
- **Datos mostrados**: Usuario, avatar, nombre completo, fecha de bloqueo
- **Botón Ver perfil**: Para acceder al perfil del usuario bloqueado
- **Confirmación**: Pregunta antes de bloquear/desbloquear

#### Flujo de Bloqueo:
1. Usuario hace clic en menú de 3 puntos → "Bloquear"
2. Confirmación con diálogo explicativo
3. Se inserta en tabla `bloqueos`
4. Se eliminan amistades y seguimientos
5. Desaparece de la lista de "Chats"

#### Flujo de Desbloqueo:
1. Usuario va a pestaña "Bloqueados"
2. Hace clic en "Desbloquear"
3. Confirmación con diálogo
4. Se elimina de tabla `bloqueos`
5. Desaparece de la lista de "Bloqueados"
6. Usuario puede volver a interactuar

#### Archivos Backend:
- **`bloquear_usuario.php`**: Maneja el bloqueo de usuarios
  - POST: `usuario_id=X`
- **`desbloquear_usuario.php`**: Maneja el desbloqueo de usuarios
  - POST: `usuario_id=X`

---

## 📊 Estructura de Base de Datos

### Tabla: `chats_archivados`
```sql
CREATE TABLE IF NOT EXISTS chats_archivados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    chat_con_usuario_id INT NOT NULL,
    fecha_archivado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_use),
    FOREIGN KEY (chat_con_usuario_id) REFERENCES usuarios(id_use),
    UNIQUE KEY unique_archivo (usuario_id, chat_con_usuario_id)
);
```

### Tabla: `bloqueos` (ya existente)
```sql
-- Estructura existente
bloqueador_id / usuario_id
bloqueado_id / usuario_bloqueado_id
fecha_bloqueo
```

---

## 🎨 Interfaz de Usuario

### Pestañas del Chat:
1. **Chats** (activas, no archivadas, no bloqueadas)
2. **Solicitudes** (pendientes de mensaje)
3. **Archivados** (conversaciones archivadas)
4. **Bloqueados** (usuarios bloqueados)

### Menú de 3 puntos (Chats activos):
- Ver perfil
- Archivar
- ─────────
- Bloquear (rojo)

### Panel "Archivados":
- Lista de conversaciones archivadas
- Botón "Desarchivar" verde
- Muestra último mensaje y fecha de archivado

### Panel "Bloqueados":
- Lista de usuarios bloqueados
- Botón "Ver perfil" azul
- Botón "Desbloquear" naranja
- Muestra nombre completo y fecha de bloqueo

---

## 🔧 Funciones JavaScript Principales

### `archivarConversacion(usuarioId)`
- Envía solicitud a `gestionar_archivo_chat.php` con `accion=archivar`
- Elimina visualmente de la lista de chats
- Actualiza contador del badge
- Pregunta si quiere ver archivados

### `desarchivarConversacion(usuarioId)`
- Envía solicitud a `gestionar_archivo_chat.php` con `accion=desarchivar`
- Elimina visualmente de la lista de archivados
- Redirige a `chat.php` (pestaña Chats)

### `bloquearUsuarioDesdeChat(usuarioId, nombreUsuario)`
- Envía solicitud a `bloquear_usuario.php`
- Elimina visualmente de la lista de chats
- Actualiza contador del badge
- Muestra mensaje de confirmación

### `desbloquearUsuario(usuarioId, nombreUsuario)`
- Envía solicitud a `desbloquear_usuario.php`
- Elimina visualmente de la lista de bloqueados
- Muestra mensaje de confirmación
- Actualiza la lista si queda vacía

---

## 🎯 Flujo de Trabajo Completo

### Escenario 1: Usuario archiva una conversación
1. Usuario está en "Chats"
2. Hace clic en menú (3 puntos) → "Archivar"
3. Confirma la acción
4. ✅ Conversación desaparece de "Chats"
5. Aparece mensaje "¿Quieres ver archivados?"
6. Si acepta → Va a pestaña "Archivados"
7. Ve la conversación archivada con opción de desarchivar

### Escenario 2: Usuario desarchiva una conversación
1. Usuario está en "Archivados"
2. Hace clic en "Desarchivar"
3. ✅ Conversación desaparece de "Archivados"
4. Redirige automáticamente a "Chats"
5. Conversación vuelve a aparecer en "Chats"

### Escenario 3: Usuario bloquea a alguien
1. Usuario está en "Chats"
2. Hace clic en menú (3 puntos) → "Bloquear"
3. Ve diálogo explicativo sobre bloqueo
4. Confirma la acción
5. ✅ Usuario desaparece de "Chats"
6. Se elimina relación de amistad/seguimiento
7. Usuario bloqueado aparece en "Bloqueados"

### Escenario 4: Usuario desbloquea a alguien
1. Usuario va a pestaña "Bloqueados"
2. Ve la lista de usuarios bloqueados
3. Hace clic en "Desbloquear"
4. Confirma la acción
5. ✅ Usuario desaparece de "Bloqueados"
6. Puede volver a seguir/ser amigo del usuario

---

## 🔒 Seguridad y Validaciones

### Backend:
- ✅ Verificación de sesión activa
- ✅ Validación de IDs de usuario
- ✅ Prepared statements (PDO)
- ✅ Try-catch para errores
- ✅ Respuestas JSON estructuradas

### Frontend:
- ✅ Confirmación antes de acciones destructivas
- ✅ Validación de parámetros en JavaScript
- ✅ Animaciones visuales de feedback
- ✅ Notificaciones de éxito/error
- ✅ Actualización dinámica de contadores

---

## 📱 Compatibilidad

- **Bootstrap 5.3.0**: Tabs, dropdowns, modales
- **Bootstrap Icons**: Iconografía
- **Fetch API**: Solicitudes AJAX
- **Responsive**: Adaptable a móviles

---

## 🐛 Características Anti-duplicación

- **Query principal**: Excluye chats archivados con `NOT EXISTS`
- **Índice UNIQUE**: En `chats_archivados` evita duplicados
- **Validación JS**: Previene múltiples clics simultáneos
- **Animaciones**: Feedback visual inmediato

---

## ✨ Mejoras Implementadas

1. **Visibilidad del Chat**: Se oculta automáticamente al cambiar a pestañas no-chat
2. **Redirección inteligente**: Después de desarchivar, lleva a Chats
3. **Contadores dinámicos**: Se actualizan en tiempo real
4. **Listas vacías**: Muestran mensajes informativos
5. **Menú de 3 puntos**: Separador visual entre opciones normales y peligrosas

---

## 🎉 ¡Sistema Completo y Funcional!

Todo el sistema está implementado y listo para usar. Las funcionalidades de archivado y bloqueo están completamente integradas sin afectar el código existente.
