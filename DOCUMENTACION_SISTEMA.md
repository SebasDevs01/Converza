# 📚 DOCUMENTACIÓN COMPLETA DEL SISTEMA CONVERZA

**Última actualización:** Octubre 2025  
**Versión del Sistema:** 2.0

---

## 📑 TABLA DE CONTENIDOS

1. [Introducción](#introducción)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Sistema de Karma](#sistema-de-karma)
4. [Sistema de Badges y Notificaciones](#sistema-de-badges-y-notificaciones)
5. [Sistema de Personalización](#sistema-de-personalización)
6. [Sistema de Chat y Mensajería](#sistema-de-chat-y-mensajería)
7. [Sistema de Conexiones Místicas](#sistema-de-conexiones-místicas)
8. [Daily Shuffle](#daily-shuffle)
9. [Coincidence Alerts](#coincidence-alerts)
10. [Guías de Uso](#guías-de-uso)
11. [Solución de Problemas](#solución-de-problemas)

---

## 🎯 INTRODUCCIÓN

**Converza** es una red social moderna que combina interacciones tradicionales con sistemas gamificados de karma, recompensas visuales y conexiones inteligentes entre usuarios.

### Características Principales

- 🎖️ **Sistema de Karma**: Sistema de puntos que recompensa interacciones positivas
- 🏆 **Badges Animados**: Notificaciones en tiempo real con efectos visuales
- 🎨 **Personalización Total**: Marcos, temas, colores, íconos y stickers
- 💬 **Chat Avanzado**: Sistema de mensajería con permisos y archivado
- 🔮 **Conexiones Místicas**: Detección automática de afinidad entre usuarios
- 🎲 **Daily Shuffle**: Sistema de descubrimiento diario de nuevos contactos
- ⚠️ **Coincidence Alerts**: Notificaciones de compatibilidad en tiempo real

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Estructura de Carpetas

```
Converza/
├── app/
│   ├── models/          # Lógica de negocio y helpers
│   │   ├── config.php
│   │   ├── socialnetwork-lib.php
│   │   ├── karma-social-helper.php
│   │   ├── karma-social-triggers.php
│   │   ├── notificaciones-helper.php
│   │   ├── notificaciones-triggers.php
│   │   ├── recompensas-aplicar-helper.php
│   │   ├── conexiones-misticas-helper.php
│   │   ├── conexiones-misticas-usuario-helper.php
│   │   ├── chat-permisos-helper.php
│   │   ├── bloqueos-helper.php
│   │   └── tema-global-aplicar.php
│   │
│   ├── presenters/      # Controladores y APIs
│   │   ├── login.php
│   │   ├── registro.php
│   │   ├── perfil.php
│   │   ├── chat.php
│   │   ├── karma_tienda.php
│   │   ├── daily_shuffle.php
│   │   ├── conexiones_misticas.php
│   │   ├── mensajes_api.php
│   │   ├── solicitudes_api.php
│   │   ├── notificaciones_api.php
│   │   ├── get_karma.php
│   │   ├── get_karma_social.php
│   │   └── check_karma_notification.php
│   │
│   └── view/            # Vistas y componentes
│       ├── index.php
│       ├── admin.php
│       ├── _navbar_panels.php
│       └── components/
│           ├── mensajes-badge.php
│           ├── solicitudes-badge.php
│           ├── notificaciones-widget.php
│           ├── karma-navbar-badge.php
│           ├── karma-navbar-button.php
│           ├── karma-social-widget.php
│           ├── karma-notification-widget.php
│           └── conexiones-badge.php
│
├── public/              # Recursos públicos
│   ├── css/
│   ├── js/
│   ├── avatars/
│   ├── publicaciones/
│   └── voice_messages/
│
├── sql/                 # Scripts de base de datos
├── dist/                # Assets compilados
└── bootstrap/           # Framework CSS
```

### Base de Datos

**Tablas Principales:**

- `usuarios` - Información de usuarios y karma
- `publicaciones` - Posts y contenido
- `comentarios` - Comentarios en publicaciones
- `chats` - Mensajes privados
- `amigos` - Relaciones de amistad y solicitudes
- `notificaciones` - Sistema de notificaciones
- `usuario_recompensas` - Recompensas desbloqueadas
- `usuario_personalizacion` - Items equipados
- `karma_social_log` - Historial de karma
- `karma_notificaciones` - Notificaciones de karma
- `conexiones_misticas` - Conexiones detectadas
- `daily_shuffle_contactos` - Contactos del shuffle diario
- `chats_archivados` - Chats archivados por usuario
- `solicitudes_mensaje` - Permisos para mensajes

---

## 🎖️ SISTEMA DE KARMA

### Visión General

El sistema de karma es el núcleo gamificado de Converza. Recompensa a los usuarios por interacciones positivas y regula el acceso a características premium.

### Cómo Ganar Karma

| Acción | Karma | Descripción |
|--------|-------|-------------|
| 🎉 **Registro** | +50 | Bienvenida al unirse |
| ❤️ **Publicación con Like** | +5 | Tu publicación recibe un like |
| 💬 **Publicación con Comentario** | +3 | Tu publicación recibe un comentario |
| ➕ **Dar Like** | +1 | Das like a una publicación |
| 💭 **Comentar** | +2 | Comentas en una publicación |
| 🤝 **Aceptar Amistad** | +10 | Aceptas una solicitud de amistad |
| ✅ **Amistad Aceptada** | +10 | Tu solicitud es aceptada |
| 🔮 **Conexión Mística** | +15 | Se detecta conexión especial con otro usuario |

### Cómo Gastar Karma

**Tienda de Recompensas:**

```
🖼️ Marcos de Avatar      → 100-500 Karma
🎨 Temas de Perfil       → 150-400 Karma
⭐ Íconos Especiales     → 50-200 Karma
🌈 Colores de Nombre     → 80-300 Karma
😊 Stickers de Perfil    → 30-150 Karma
🏅 Insignias (automáticas según nivel)
```

### Sistema de Niveles

```
Nivel 1: Novato        →     0 - 99 Karma
Nivel 2: Aprendiz      →   100 - 299 Karma
Nivel 3: Entusiasta    →   300 - 599 Karma
Nivel 4: Experto       →   600 - 999 Karma
Nivel 5: Maestro       →  1000 - 1999 Karma
Nivel 6: Leyenda       →  2000+ Karma
```

### Características del Sistema

#### ✅ Auto-Equipado Inteligente

Cuando desbloqueas una recompensa:
- **Marcos, Temas, Íconos, Colores**: Se equipan automáticamente (reemplazando el anterior)
- **Stickers**: Se añaden sin desequipar los anteriores
- **Insignias**: Se otorgan automáticamente según nivel

#### 🔔 Notificaciones de Karma

El sistema notifica en tiempo real:
- Ganancias de karma con descripción de la acción
- Cambios de nivel
- Nuevas insignias desbloqueadas
- Popup animado con efectos visuales

#### 📊 Widget de Karma Social

Muestra en la navbar:
- Karma actual del usuario
- Nivel y barra de progreso
- Contador animado de cambios
- Acceso rápido a la tienda

### Archivos Relacionados

**Backend:**
- `app/models/karma-social-helper.php` - Funciones principales
- `app/models/karma-social-triggers.php` - Triggers automáticos
- `app/presenters/get_karma.php` - API de consulta
- `app/presenters/get_karma_social.php` - API de karma social
- `app/presenters/check_karma_notification.php` - Verificar notificaciones

**Frontend:**
- `app/view/components/karma-navbar-badge.php` - Badge en navbar
- `app/view/components/karma-social-widget.php` - Widget expandido
- `app/view/components/karma-notification-widget.php` - Popup de notificaciones
- `app/presenters/karma_tienda.php` - Tienda de recompensas

---

## 🏆 SISTEMA DE BADGES Y NOTIFICACIONES

### Badges Animados

Sistema unificado de badges pulsantes en la navbar que actualizan en tiempo real.

#### Tipos de Badges

**1. 💬 Mensajes**
```php
Ubicación: app/view/components/mensajes-badge.php
API: app/presenters/mensajes_api.php
Función: Muestra mensajes no leídos
Actualización: Cada 5 segundos
```

**2. 🤝 Solicitudes de Amistad**
```php
Ubicación: app/view/components/solicitudes-badge.php
API: app/presenters/solicitudes_api.php
Función: Muestra solicitudes pendientes
Actualización: Cada 8 segundos
```

**3. 🔔 Notificaciones**
```php
Ubicación: app/view/components/notificaciones-widget.php
API: app/presenters/notificaciones_api.php
Función: Todas las notificaciones del sistema
Actualización: Cada 10 segundos
```

**4. 🔮 Conexiones Místicas**
```php
Ubicación: app/view/components/conexiones-badge.php
API: app/presenters/get_conexiones_misticas.php
Función: Nuevas conexiones detectadas
Actualización: Cada 15 segundos
```

#### Características

✅ **Actualización Automática**: Sin recargar la página  
✅ **Animación de Pulso**: Atrae atención visual  
✅ **Contador Dinámico**: Aumenta/disminuye en tiempo real  
✅ **Offcanvas Integrado**: Panel lateral con detalles  
✅ **Optimizado**: Previene múltiples requests simultáneos

### Sistema de Notificaciones

#### Tipos de Notificaciones

- 👍 Likes en publicaciones
- 💬 Comentarios en publicaciones
- 🤝 Solicitudes de amistad
- ✅ Solicitudes aceptadas
- 🔮 Nuevas conexiones místicas
- 🎖️ Ganancias de karma
- 📈 Cambios de nivel
- 🎉 Recompensas desbloqueadas

#### Archivos Relacionados

- `app/models/notificaciones-helper.php` - Funciones de notificaciones
- `app/models/notificaciones-triggers.php` - Triggers automáticos
- `app/presenters/notificaciones_api.php` - API REST

---

## 🎨 SISTEMA DE PERSONALIZACIÓN

### Tipos de Recompensas

#### 🖼️ Marcos de Avatar

Bordes decorativos que rodean el avatar del usuario.

**Disponibles:**
- Marco Dorado (100 Karma)
- Marco de Neón Cyan (150 Karma)
- Marco Arcoíris Animado (300 Karma)
- Marco de Fuego (400 Karma)
- Marco Galáctico (500 Karma)

**Aplicación:**
- Auto-equipa al desbloquear
- Visible en avatar de perfil, publicaciones y comentarios
- CSS: Clases `.frame-gold`, `.frame-neon-cyan`, etc.

#### 🎨 Temas de Perfil

Esquemas de colores para el fondo del perfil.

**Disponibles:**
- Tema Nocturno (150 Karma)
- Tema Atardecer (200 Karma)
- Tema Océano (250 Karma)
- Tema Bosque (300 Karma)
- Tema Futurista (400 Karma)

**Aplicación:**
- Auto-equipa al desbloquear
- Gradientes de fondo en página de perfil
- CSS inline generado dinámicamente

#### ⭐ Íconos Especiales

Íconos que aparecen junto al nombre del usuario.

**Disponibles:**
- ⭐ Estrella (50 Karma)
- 👑 Corona (100 Karma)
- 💎 Diamante (150 Karma)
- 🔥 Fuego (120 Karma)
- ⚡ Rayo (80 Karma)

**Aplicación:**
- Auto-equipa al desbloquear
- Aparece en nombre de usuario en todo el sitio

#### 🌈 Colores de Nombre

Efectos de color y gradiente para el nombre de usuario.

**Disponibles:**
- Dorado (80 Karma)
- Neón Cyan (100 Karma)
- Arcoíris Animado (200 Karma)
- Fuego (150 Karma)
- Galaxia (250 Karma)
- Esmeralda (120 Karma)

**Aplicación:**
- Auto-equipa al desbloquear
- CSS con clases especiales
- Puede incluir animaciones

#### 😊 Stickers de Perfil

Stickers decorativos en la sección del perfil.

**Disponibles:**
- 😎 Cool (30 Karma)
- 🎉 Fiesta (40 Karma)
- 💪 Fuerte (50 Karma)
- 🌟 Estrella (35 Karma)
- ❤️ Corazón (45 Karma)

**Aplicación:**
- Se añaden sin desequipar anteriores
- Múltiples stickers pueden estar activos
- Aparecen en sección específica del perfil

#### 🏅 Insignias

Distintivos basados en nivel de karma (automáticas).

**Niveles:**
- 🥉 Bronce (Nivel 1-2)
- 🥈 Plata (Nivel 3-4)
- 🥇 Oro (Nivel 5-6)
- 💎 Diamante (Nivel 7+)

**Aplicación:**
- Se otorgan automáticamente según nivel
- No se compran, se ganan
- Aparecen en el perfil del usuario

### Previews Visuales en Tienda

Cada recompensa muestra un preview animado antes de comprar:

```
🖼️ Marcos      → Avatar con marco aplicado + rotación
🎨 Temas       → Caja con colores reales + gradiente
⭐ Íconos      → "Tu Nombre" + ícono + flotación
🌈 Colores     → "Tu Nombre" con gradiente + pulso
😊 Stickers    → 2 stickers reales + hover animado
🏅 Insignias   → 3 estrellas + escalado animado
```

### Archivos Relacionados

**Backend:**
- `app/models/recompensas-aplicar-helper.php` - Aplicar recompensas
- `app/presenters/karma_tienda.php` - Tienda completa

**Frontend:**
- `public/css/karma-recompensas.css` - Estilos de recompensas

---

## 💬 SISTEMA DE CHAT Y MENSAJERÍA

### Características Principales

#### ✅ Sistema de Permisos

**Modos de privacidad:**
- **Abierto**: Cualquiera puede enviar mensajes
- **Amigos**: Solo amigos pueden enviar mensajes
- **Solicitud**: Requiere solicitud antes de enviar mensaje

**Flujo con Solicitudes:**
1. Usuario intenta enviar mensaje
2. Si el destinatario requiere solicitud:
   - Se muestra formulario de solicitud
   - Usuario envía solicitud con mensaje personalizado
   - Destinatario recibe notificación
3. Si se acepta:
   - Se crea conversación
   - Ambos pueden chatear libremente
4. Si se rechaza:
   - Se notifica al solicitante

#### 📁 Chats Archivados

Los usuarios pueden archivar conversaciones:
- No aparecen en lista principal
- Se accede mediante botón especial
- Se pueden desarchivar en cualquier momento
- Los archivados son individuales (un usuario archiva, el otro no necesariamente)

#### 🔇 Sistema de Bloqueo

- Usuarios bloqueados no pueden enviar mensajes
- No pueden ver el perfil del bloqueador
- No aparecen en búsquedas
- Las conversaciones existentes quedan ocultas

#### 🎤 Mensajes de Voz

- Grabación directa desde el chat
- Almacenamiento en `public/voice_messages/`
- Reproductor integrado
- Compatible con mobile

#### ⚛️ Reacciones a Mensajes

- Emojis rápidos en mensajes
- Guardar/quitar reacciones
- Ver quién reaccionó

### Archivos Relacionados

**Backend:**
- `app/models/chat-permisos-helper.php` - Lógica de permisos
- `app/models/bloqueos-helper.php` - Sistema de bloqueos
- `app/presenters/chat.php` - Interfaz de chat
- `app/presenters/enviar_mensaje_con_permisos.php` - Enviar mensaje
- `app/presenters/gestionar_solicitud_mensaje.php` - Gestionar solicitud
- `app/presenters/gestionar_archivo_chat.php` - Archivar/desarchivar
- `app/presenters/verificar_permisos_chat.php` - Verificar permisos
- `app/presenters/verificar_conversacion_existente.php` - Verificar conversación
- `app/presenters/verificar_nuevos_mensajes.php` - Polling de mensajes
- `app/presenters/iniciar_chat.php` - Iniciar conversación

---

## 🔮 SISTEMA DE CONEXIONES MÍSTICAS

### Concepto

Las **Conexiones Místicas** son un sistema automático que detecta afinidad entre usuarios basándose en múltiples criterios.

### Criterios de Detección

El sistema analiza:

1. **Interacciones Mutuas**:
   - Likes y comentarios cruzados
   - Frecuencia de interacción
   - Reciprocidad

2. **Intereses Comunes**:
   - Publicaciones similares
   - Temas en común
   - Álbumes compartidos

3. **Actividad Temporal**:
   - Patrones de horario
   - Días activos coincidentes

4. **Red Social**:
   - Amigos en común
   - Círculos compartidos

### Niveles de Conexión

```
🌟 Débil      → 1-2 criterios coincidentes
⭐⭐ Media   → 3-4 criterios coincidentes
⭐⭐⭐ Fuerte → 5+ criterios coincidentes
```

### Funcionamiento

1. **Detección Automática**:
   - Script cron ejecuta análisis periódico
   - O trigger manual: `detectar_conexiones.php`

2. **Notificación**:
   - Badge en navbar se actualiza
   - Notificación push
   - +15 Karma por conexión detectada

3. **Visualización**:
   - Offcanvas con lista de conexiones
   - Porcentaje de afinidad
   - Razón de la conexión

4. **Acciones**:
   - Ver perfil de la conexión
   - Enviar mensaje
   - Enviar solicitud de amistad

### Archivos Relacionados

**Backend:**
- `app/models/conexiones-misticas-helper.php` - Lógica de detección
- `app/models/conexiones-misticas-usuario-helper.php` - Helpers de usuario
- `app/presenters/conexiones_misticas.php` - Vista principal
- `app/presenters/get_conexiones_misticas.php` - API
- `app/presenters/marcar_conexiones_vistas.php` - Marcar como visto

**Frontend:**
- `app/view/components/conexiones-badge.php` - Badge en navbar
- `app/presenters/widget_conexiones_misticas.php` - Widget

---

## 🎲 DAILY SHUFFLE

### Concepto

**Daily Shuffle** es un sistema de descubrimiento diario que presenta usuarios aleatorios con los que aún no hay conexión.

### Características

#### 🎯 Usuarios Sugeridos

- 5 usuarios aleatorios por día
- Excluye amigos actuales
- Excluye usuarios bloqueados
- Se renueva cada 24 horas

#### 🔄 Renovación Automática

```
Hora de reset: 00:00 (medianoche)
Usuarios nuevos: 5 perfiles aleatorios
Criterios: Activos en últimos 30 días
```

#### ⚡ Acciones Rápidas

Para cada usuario sugerido:
- ✅ Enviar solicitud de amistad
- 💬 Enviar mensaje (si permisos lo permiten)
- 👁️ Ver perfil completo
- ⏭️ Pasar al siguiente

#### 📊 Seguimiento

El sistema registra:
- Usuarios mostrados
- Acciones tomadas (solicitud/mensaje/skip)
- Fecha de última visualización

### Beneficios

- Descubre nuevos usuarios fácilmente
- Amplía tu red social
- Mantiene la plataforma activa
- Genera interacciones orgánicas

### Archivos Relacionados

**Backend:**
- `app/presenters/daily_shuffle.php` - Vista principal
- `app/presenters/enviar_solicitud_shuffle.php` - Enviar solicitud
- `app/presenters/marcar_contacto_shuffle.php` - Marcar contacto

**Tabla:**
- `daily_shuffle_contactos` - Registro de usuarios mostrados

---

## ⚠️ COINCIDENCE ALERTS

### Concepto

Sistema de alertas en tiempo real que notifica cuando hay **alta compatibilidad** con un usuario en línea.

### Funcionamiento

1. **Análisis en Tiempo Real**:
   - Detecta usuarios activos simultáneamente
   - Calcula compatibilidad instantánea
   - Genera alerta si coincidencia > 70%

2. **Criterios de Compatibilidad**:
   - Intereses compartidos
   - Interacciones previas
   - Conexiones mutuas
   - Actividad similar

3. **Notificación**:
   - Popup emergente
   - Indicador visual especial
   - Botón de acción rápida

4. **Acciones**:
   - Ver perfil del usuario compatible
   - Iniciar chat
   - Enviar solicitud de amistad
   - Descartar alerta

### Diferencia con Conexiones Místicas

| Característica | Conexiones Místicas | Coincidence Alerts |
|----------------|---------------------|-------------------|
| **Timing** | Análisis periódico (batch) | Tiempo real |
| **Criterio** | Análisis profundo histórico | Compatibilidad instantánea |
| **Activación** | Automática (cron) | Cuando ambos están online |
| **Propósito** | Conexiones a largo plazo | Oportunidad inmediata |

### Archivos Relacionados

**Backend:**
- `app/presenters/test_coincidence_alerts.php` - Sistema de detección

---

## 📖 GUÍAS DE USO

### Para Usuarios Finales

#### Ganar Karma Rápidamente

1. **Publica contenido de calidad**: Más likes = más karma
2. **Interactúa con otros**: Comenta y da likes
3. **Acepta solicitudes de amistad**: +10 karma por amistad
4. **Busca conexiones místicas**: +15 karma por conexión

#### Personalizar tu Perfil

1. Ve a **Tienda de Karma**
2. Revisa los **previews animados**
3. Desbloquea con karma
4. **Auto-equipado**: Se aplica automáticamente
5. Ve a tu perfil para ver los cambios

#### Usar el Chat

**Si el usuario tiene modo abierto:**
- Click en "Mensaje" → Escribe y envía

**Si requiere solicitud:**
1. Click en "Mensaje"
2. Aparece formulario de solicitud
3. Escribe mensaje personalizado
4. Espera aprobación
5. Una vez aprobado, chatea libremente

**Archivar conversaciones:**
1. Abre el chat
2. Click en menú de 3 puntos
3. "Archivar chat"
4. Accede a archivados desde botón especial

#### Descubrir Nuevos Usuarios

**Daily Shuffle:**
1. Ve a "Daily Shuffle" en navbar
2. Revisa los 5 perfiles del día
3. Envía solicitudes de amistad
4. O inicia conversación directamente

**Conexiones Místicas:**
1. Revisa el badge 🔮 en navbar
2. Click para ver conexiones detectadas
3. Explora perfiles compatibles
4. Ganas +15 karma automáticamente

### Para Desarrolladores

#### Agregar Nuevo Tipo de Recompensa

1. **Base de datos**:
```sql
ALTER TABLE usuario_recompensas ADD COLUMN nuevo_tipo_id INT;
ALTER TABLE usuario_personalizacion ADD COLUMN nuevo_tipo VARCHAR(50);
```

2. **Helper** (`recompensas-aplicar-helper.php`):
```php
function aplicarNuevoTipo($userId, $itemId) {
    // Lógica de aplicación
}
```

3. **Vista** (`karma_tienda.php`):
```php
// Agregar sección en tienda
// Incluir preview animado
```

4. **CSS**:
```css
.nuevo-tipo-effect {
    /* Efectos visuales */
}
```

#### Agregar Nuevo Criterio de Karma

1. **Identificar acción** (ej: compartir publicación)

2. **Agregar trigger** (`karma-social-triggers.php`):
```php
function aumentarKarmaCompartir($userId) {
    aumentarKarma($userId, 3, 'compartir_publicacion', 
                  'Compartiste una publicación');
}
```

3. **Llamar desde acción** (ej: `compartir.php`):
```php
require_once __DIR__ . '/../models/karma-social-triggers.php';
aumentarKarmaCompartir($_SESSION['user_id']);
```

4. **Documentar** en tabla de acciones

#### Crear Nuevo Badge

1. **Crear componente** (`app/view/components/nuevo-badge.php`):
```php
<div class="badge-container" id="nuevo-badge">
    <span class="badge pulse" id="nuevo-badge-count">0</span>
</div>
<script>
    function actualizarNuevoBadge() {
        fetch('/converza/app/presenters/nuevo_api.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById('nuevo-badge-count').textContent = data.count;
            });
    }
    setInterval(actualizarNuevoBadge, 5000);
</script>
```

2. **Crear API** (`app/presenters/nuevo_api.php`):
```php
header('Content-Type: application/json');
// Lógica para contar
echo json_encode(['count' => $count]);
```

3. **Incluir en navbar** (`_navbar_panels.php`):
```php
require_once __DIR__ . '/components/nuevo-badge.php';
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Badges No Actualizan

**Síntomas:**
- Contador no cambia
- No aparecen notificaciones nuevas

**Soluciones:**
1. Verificar JavaScript en consola (F12)
2. Revisar que APIs respondan correctamente
3. Limpiar caché del navegador
4. Verificar permisos de archivos

### Karma No Se Actualiza

**Síntomas:**
- Acciones no suman karma
- Contador congelado

**Soluciones:**
1. Verificar triggers en `karma-social-triggers.php`
2. Revisar log de karma en base de datos
3. Verificar columna `karma` en tabla `usuarios`
4. Comprobar llamadas a funciones de karma

### Recompensas No Se Aplican

**Síntomas:**
- Desbloqueas pero no ves efecto
- CSS no se aplica

**Soluciones:**
1. Verificar tabla `usuario_personalizacion`
2. Limpiar caché CSS
3. Revisar funciones en `recompensas-aplicar-helper.php`
4. Verificar que karma se descuente correctamente

### Chat No Funciona

**Síntomas:**
- Mensajes no se envían
- No se ven mensajes nuevos

**Soluciones:**
1. Verificar permisos de usuario
2. Revisar `chat-permisos-helper.php`
3. Comprobar polling de mensajes
4. Verificar sesión activa

### Conexiones Místicas No Se Detectan

**Síntomas:**
- Badge siempre en 0
- No hay conexiones

**Soluciones:**
1. Ejecutar manualmente `detectar_conexiones.php`
2. Verificar criterios de detección
3. Revisar datos de interacciones en BD
4. Comprobar que hay suficiente actividad

### Daily Shuffle No Se Renueva

**Síntomas:**
- Mismos usuarios todos los días
- No aparecen nuevos perfiles

**Soluciones:**
1. Verificar timestamp de última renovación
2. Revisar lógica de reset en `daily_shuffle.php`
3. Limpiar tabla `daily_shuffle_contactos`
4. Verificar query de usuarios activos

---

## 📊 MÉTRICAS Y ESTADÍSTICAS

### Rendimiento del Sistema

- **Badges**: Actualización cada 5-15 segundos sin sobrecargar servidor
- **Karma**: Cálculo instantáneo con triggers optimizados
- **Notificaciones**: Push en < 1 segundo
- **Chat**: Polling optimizado cada 3 segundos
- **Conexiones Místicas**: Análisis batch nocturno

### Uso Esperado

- **Usuarios Activos Diarios**: Sistema soporta 1000+ usuarios simultáneos
- **Mensajes**: ~100-500 mensajes por minuto
- **Notificaciones**: ~50-200 notificaciones por minuto
- **Karma**: ~20-100 transacciones por minuto

---

## 🚀 FUTURAS MEJORAS

### En Desarrollo

- [ ] Sistema de logros y trofeos
- [ ] Modo oscuro global
- [ ] Chat grupal
- [ ] Video llamadas
- [ ] Stories temporales
- [ ] Eventos y calendario social

### Sugerencias

- [ ] Machine learning para mejores conexiones
- [ ] Integración con redes sociales externas
- [ ] App móvil nativa
- [ ] Gamificación avanzada
- [ ] Sistema de moderación automático

---

## 📞 SOPORTE

Para problemas técnicos:
1. Revisar esta documentación
2. Verificar logs en consola del navegador
3. Revisar logs del servidor PHP
4. Contactar al equipo de desarrollo

---

**¡Gracias por usar Converza!** 🎉

