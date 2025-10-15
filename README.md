# 🌐 CONVERZA - Red Social Inteligente# 🌐 Converza - Red Social Moderna



[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php)](https://www.php.net/)[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)

[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql)](https://www.mysql.com/)[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)

[![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?logo=javascript)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)[![License](https://img.shields.io/badge/License-Proprietary-red)](LICENSE)

[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> Una red social innovadora que combina interacciones tradicionales con sistemas gamificados de karma, recompensas visuales y conexiones inteligentes entre usuarios.

## 📖 Descripción

---

**Converza** es una red social inteligente con sistema de karma, predicciones de afinidad, recomendaciones personalizadas y un asistente conversacional avanzado.

## ✨ Características Principales

### ✨ Características Principales

### 🎖️ Sistema de Karma

- 🎯 **Sistema de Karma Inteligente**: Puntos por interacciones sociales con análisis semántico- Recompensa automática por interacciones positivas

- 🔮 **Predicciones de Afinidad**: Algoritmo que sugiere conexiones basadas en intereses y comportamiento- Niveles progresivos basados en actividad

- 💬 **Asistente Conversacional**: IA personalizada que aprende de tus interacciones- Tienda de recompensas con items desbloqueables

- 🔔 **Sistema de Notificaciones**: Alertas en tiempo real con sistema de campana- Notificaciones en tiempo real de ganancias

- 📸 **Publicaciones Multimedia**: Soporte para imágenes, reacciones y comentarios

- 👥 **Sistema de Conexiones**: Amigos, seguidores y solicitudes de amistad### 🏆 Badges y Notificaciones

- 🎨 **Marcos de Perfil**: Sistema de marcos temáticos personalizables- Badges animados en navbar con actualización en tiempo real

- 📊 **Feed Inteligente**: Timeline personalizado con algoritmo de relevancia- Sistema unificado de notificaciones

- Contadores pulsantes con efectos visuales

---- Offcanvas integrado con detalles



## 🏗️ Arquitectura del Sistema### 🎨 Personalización Total

- **Marcos de Avatar**: Bordes decorativos personalizables

### 📁 Estructura de Directorios- **Temas de Perfil**: Esquemas de colores para tu página

- **Íconos Especiales**: Distintivos junto a tu nombre

```- **Colores de Nombre**: Efectos de gradiente y animaciones

Converza/- **Stickers**: Decoraciones para tu perfil

├── app/- **Insignias**: Automáticas según tu nivel

│   ├── models/          # Modelos de datos (Usuario, Publicacion, etc.)- **Auto-equipado**: Las recompensas se aplican automáticamente

│   ├── presenters/      # Lógica de negocio (API endpoints)

│   ├── helpers/         # Utilidades y funciones auxiliares### 💬 Chat Avanzado

│   └── views/           # Vistas PHP (HTML + PHP)- Sistema de permisos configurable (abierto/amigos/solicitud)

├── public/- Mensajes de voz integrados

│   ├── css/            # Estilos CSS- Reacciones a mensajes con emojis

│   ├── js/             # JavaScript del cliente- Archivado de conversaciones

│   ├── uploads/        # Archivos subidos (fotos, avatares)- Sistema de bloqueo de usuarios

│   └── index.php       # Punto de entrada

├── sql/                # Scripts SQL de configuración### 🔮 Conexiones Místicas

├── config/             # Archivos de configuración- Detección automática de afinidad entre usuarios

└── vendor/             # Dependencias Composer- Análisis basado en múltiples criterios

```- Notificaciones de nuevas conexiones

- Ganancias de karma por conexiones

### 🗄️ Base de Datos Principal

### 🎲 Daily Shuffle

#### Tablas Core- 5 usuarios nuevos sugeridos cada día

- `usuarios` - Información de usuarios- Sistema de descubrimiento automatizado

- `publicaciones` - Posts del feed- Acciones rápidas (amistad/mensaje/perfil)

- `comentarios` - Comentarios en publicaciones- Renovación diaria automática

- `reacciones` - Reacciones a publicaciones

### ⚠️ Coincidence Alerts

#### Sistema de Karma- Detección de compatibilidad en tiempo real

- `karma_social` - Registro de todas las acciones de karma- Alertas cuando ambos usuarios están online

- `karma_total_usuarios` - Totales acumulados por usuario- Análisis instantáneo de compatibilidad

- **Trigger**: `after_karma_social_insert` - Actualiza automáticamente los totales- Oportunidades de conexión inmediata



#### Sistema de Conexiones---

- `conexiones` - Relaciones entre usuarios (amigos/seguidores)

- `solicitudes_amistad` - Solicitudes pendientes## 🚀 Instalación

- `predicciones_conexiones` - Sugerencias de amistad generadas por IA

### Requisitos

#### Sistema de Notificaciones

- `notificaciones` - Notificaciones de campana- **PHP** 7.4 o superior

- `alertas_coincidencias` - Alertas de nuevas predicciones- **MySQL** 5.7 o superior

- **Apache** con mod_rewrite habilitado

#### Asistente Conversacional- **Composer** (opcional, para dependencias)

- `assistant_sessions` - Sesiones del asistente

- `assistant_messages` - Historial de mensajes### Pasos de Instalación

- `assistant_training` - Datos de entrenamiento

- `assistant_learning` - Aprendizaje del asistente1. **Clonar el repositorio**

   ```bash

#### Sistema de Intereses   git clone https://github.com/tu-usuario/converza.git

- `intereses_disponibles` - Catálogo de intereses   cd converza

- `usuarios_intereses` - Intereses por usuario   ```

- `analisis_intereses` - Análisis de compatibilidad

2. **Configurar la base de datos**

---   ```bash

   # Importar el schema principal

## 🚀 Instalación   mysql -u root -p < sql/converza.sql

   

### Requisitos Previos   # Importar tablas adicionales (ejecutar en orden)

   mysql -u root -p converza < sql/create_karma_social_table.sql

- PHP 8.0 o superior   mysql -u root -p converza < sql/create_notificaciones_table.sql

- MySQL 8.0 o superior   mysql -u root -p converza < sql/create_usuario_recompensas_table.sql

- Servidor web (Apache/Nginx)   mysql -u root -p converza < sql/create_chat_system.sql

- Composer   mysql -u root -p converza < sql/create_conexiones_misticas.sql

   mysql -u root -p converza < sql/create_daily_shuffle_table.sql

### Pasos de Instalación   # ... y demás tablas según necesidad

   ```

1. **Clonar el Repositorio**

```bash3. **Configurar variables de entorno**

git clone https://github.com/tuusuario/converza.git   ```bash

cd converza   # Copiar y editar el archivo .env

```   cp .env.example .env

   ```

2. **Instalar Dependencias**   

```bash   Editar `.env` con tus credenciales:

composer install   ```env

```   DB_HOST=localhost

   DB_NAME=converza

3. **Configurar Base de Datos**   DB_USER=root

```sql   DB_PASS=tu_password

-- Crear base de datos   ```

CREATE DATABASE converza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

4. **Configurar permisos**

-- Importar estructura (archivo principal de BD)   ```bash

mysql -u root -p converza < sql/estructura_bd.sql   chmod 755 public/avatars

   chmod 755 public/publicaciones

-- Configurar sistema de karma   chmod 755 public/voice_messages

mysql -u root -p converza < sql/configurar_sistema_karma.sql   ```

```

5. **Acceder al sistema**

4. **Configurar Conexión**   - Abrir navegador en: `http://localhost/Converza`

Edita `config/database.php`:   - Registrar un usuario nuevo

```php   - ¡Empezar a usar Converza!

<?php

define('DB_HOST', 'localhost');---

define('DB_NAME', 'converza');

define('DB_USER', 'root');## 📁 Estructura del Proyecto

define('DB_PASS', '');

define('DB_CHARSET', 'utf8mb4');```

```Converza/

├── app/

5. **Configurar Permisos**│   ├── models/              # Lógica de negocio

```bash│   │   ├── config.php

chmod 755 public/uploads/│   │   ├── socialnetwork-lib.php

chmod 755 public/uploads/perfiles/│   │   ├── karma-social-helper.php

chmod 755 public/uploads/publicaciones/│   │   ├── notificaciones-helper.php

```│   │   ├── recompensas-aplicar-helper.php

│   │   └── ...

6. **Iniciar Servidor**│   │

```bash│   ├── presenters/          # Controladores y APIs

# XAMPP/WAMP: Inicia Apache y MySQL│   │   ├── login.php

# O usa el servidor integrado de PHP:│   │   ├── registro.php

php -S localhost:8000 -t public│   │   ├── perfil.php

```│   │   ├── chat.php

│   │   ├── karma_tienda.php

7. **Acceder al Sistema**│   │   └── ...

```│   │

http://localhost/converza│   └── view/                # Vistas

# o│       ├── index.php

http://localhost:8000│       ├── components/      # Componentes reutilizables

```│       └── ...

│

---├── public/                  # Assets públicos

│   ├── css/

## 🎮 Características del Sistema│   ├── js/

│   ├── avatars/

### 1. 🎯 Sistema de Karma│   ├── publicaciones/

│   └── voice_messages/

El karma es el sistema de puntos que recompensa las interacciones positivas:│

├── sql/                     # Scripts de base de datos

#### Puntos por Reacciones├── dist/                    # Assets compilados

| Reacción | Puntos | Emoji |├── bootstrap/               # Framework CSS

|----------|--------|-------|├── .env                     # Configuración (no versionar)

| Me encanta | +10 | ❤️ |├── .htaccess                # Configuración Apache

| Me gusta | +5 | 👍 |├── composer.json            # Dependencias PHP

| Me divierte | +8 | 😂 |└── DOCUMENTACION_SISTEMA.md # Documentación completa

| Me asombra | +7 | 😮 |```

| Me entristece | +3 | 😢 |

| Me enoja | -5 | 😡 |---

| No me gusta | -3 | 👎 |

## 📖 Documentación

#### Puntos por Comentarios (Análisis Inteligente)

La documentación completa del sistema está disponible en:

El sistema analiza el contenido del comentario:

**[DOCUMENTACION_SISTEMA.md](DOCUMENTACION_SISTEMA.md)**

- **Comentario Obsceno/Grosero**: -10 puntos

- **Comentario Ofensivo**: -5 puntosIncluye:

- **Comentario Negativo**: -3 puntos- Arquitectura del sistema completa

- **Comentario Neutral**: +4 puntos- Guía de uso de todas las funcionalidades

- **Comentario Positivo**: +8 puntos- API reference para desarrolladores

- **Comentario Muy Positivo**: +12 puntos- Guías para agregar nuevas características

- Solución de problemas comunes

**Ejemplos de Análisis**:

```php---

"¡Excelente publicación! 🎉"     → +12 puntos (muy positivo + emoji)

"Me gusta, buen contenido"        → +8 puntos (positivo)## 🛠️ Tecnologías Utilizadas

"¿Cómo hiciste eso?"              → +4 puntos (pregunta neutral)

"No estoy de acuerdo"             → -3 puntos (negativo)- **Backend**: PHP 7.4+

"Eres un idiota"                  → -10 puntos (obsceno)- **Base de Datos**: MySQL 5.7+

```- **Frontend**: 

  - HTML5, CSS3, JavaScript (ES6+)

#### Niveles de Karma  - Bootstrap 5

  - jQuery

| Nivel | Karma Requerido | Título |  - Font Awesome / Bootstrap Icons

|-------|-----------------|--------|- **Arquitectura**: MVC (Model-View-Presenter)

| 1 | 0-99 | Novato |- **APIs**: REST JSON

| 2 | 100-299 | Aprendiz |

| 3 | 300-599 | Competente |---

| 4 | 600-999 | Experto |

| 5 | 1000+ | Maestro |## 🎯 Casos de Uso



### 2. 🔮 Sistema de Predicciones### Para Usuarios



Algoritmo que sugiere conexiones basadas en:1. **Ganar Karma**: Publica, comenta, da likes y acepta amistades

2. **Personalizar Perfil**: Desbloquea marcos, temas, colores y más

- **Intereses compartidos** (40% peso)3. **Hacer Amigos**: Usa Daily Shuffle o Conexiones Místicas

- **Conexiones mutuas** (30% peso)4. **Chatear**: Configura permisos y chatea con seguridad

- **Nivel de actividad** (20% peso)5. **Descubrir Compatibles**: Recibe alertas de coincidencias

- **Karma similar** (10% peso)

### Para Desarrolladores

**Fórmula de Compatibilidad**:

```1. **Agregar Recompensas**: Sistema modular de recompensas

score = (intereses × 0.4) + (amigos_comunes × 0.3) + (actividad × 0.2) + (karma × 0.1)2. **Crear Badges**: Componentes reutilizables

```3. **Nuevos Criterios Karma**: Sistema de triggers extensible

4. **APIs REST**: Estructura clara para nuevos endpoints

### 3. 💬 Asistente Conversacional5. **Personalización CSS**: Variables y clases modulares



IA personalizada que:---



- 🧠 Aprende de tus interacciones## 🔧 Comandos Útiles

- 📊 Analiza tu contenido y intereses

- 💡 Ofrece recomendaciones personalizadas### Desarrollo

- 🎯 Mejora con cada conversación

```bash

**Características**:# Iniciar servidor de desarrollo (si no usas XAMPP)

- Contexto persistente de conversaciónphp -S localhost:8000 -t public

- Análisis de sentimiento

- Respuestas contextuales# Ver logs de PHP

- Aprendizaje continuotail -f /path/to/php-error.log



### 4. 🔔 Sistema de Notificaciones# Ver logs de Apache

tail -f /path/to/apache/error.log

Notificaciones en tiempo real:```



- 👤 Nueva solicitud de amistad### Base de Datos

- ✅ Solicitud aceptada

- ❤️ Reacción a tu publicación```bash

- 💬 Comentario en tu publicación# Backup de la base de datos

- 🎯 Nueva predicción de afinidadmysqldump -u root -p converza > backup_$(date +%Y%m%d).sql

- ⭐ Cambio de nivel de karma

# Restaurar backup

### 5. 📸 Sistema de Publicacionesmysql -u root -p converza < backup_20251014.sql



- Texto + Imagen# Acceder a MySQL console

- Reacciones múltiplesmysql -u root -p converza

- Comentarios anidados```

- Feed personalizado

- Algoritmo de relevancia---



---## 🐛 Solución de Problemas



## 🛠️ Tecnologías Utilizadas### Badges no actualizan



### Backend```bash

- **PHP 8.0+** - Lenguaje principal# Verificar en consola del navegador (F12)

- **MySQL 8.0+** - Base de datos# Revisar respuesta de APIs

- **Composer** - Gestión de dependencias# Limpiar caché del navegador

```

### Frontend

- **HTML5/CSS3** - Estructura y estilos### Karma no se actualiza

- **JavaScript ES6+** - Lógica del cliente

- **Font Awesome** - Iconos```bash

- **SweetAlert2** - Alertas elegantes# Verificar triggers en karma-social-triggers.php

# Revisar log de karma en base de datos

### Librerías PHP# Comprobar llamadas a funciones

- **PDO** - Capa de abstracción de base de datos```

- **OpenAI API** - IA para asistente conversacional

### Chat no funciona

### Arquitectura

- **MVC Pattern** - Modelo-Vista-Controlador```bash

- **RESTful API** - Endpoints JSON# Verificar permisos de usuario

- **AJAX** - Comunicación asíncrona# Revisar chat-permisos-helper.php

- **Fetch API** - Peticiones HTTP modernas# Comprobar sesión activa

```

---

Para más información, consultar [DOCUMENTACION_SISTEMA.md](DOCUMENTACION_SISTEMA.md) sección "Solución de Problemas".

## 📊 API Endpoints

---

### Autenticación

```## 🤝 Contribuir

POST /app/presenters/login.php

POST /app/presenters/registro.phpEste es un proyecto privado. Si tienes acceso y deseas contribuir:

GET  /app/presenters/logout.php

```1. Crear una branch para tu feature: `git checkout -b feature/nueva-caracteristica`

2. Hacer commit de tus cambios: `git commit -m 'Añadir nueva característica'`

### Publicaciones3. Push a la branch: `git push origin feature/nueva-caracteristica`

```4. Crear un Pull Request

GET    /app/presenters/obtener_publicaciones.php

POST   /app/presenters/crear_publicacion.php---

DELETE /app/presenters/eliminar_publicacion.php

```## 📊 Estado del Proyecto



### Reacciones y Comentarios- ✅ **Sistema de Karma**: Completado y funcional

```- ✅ **Badges Animados**: Completado y funcional

POST /app/presenters/save_reaction.php- ✅ **Sistema de Personalización**: Completado y funcional

POST /app/presenters/agregarcomentario.php- ✅ **Chat con Permisos**: Completado y funcional

```- ✅ **Conexiones Místicas**: Completado y funcional

- ✅ **Daily Shuffle**: Completado y funcional

### Karma- ✅ **Coincidence Alerts**: Completado y funcional

```- 🚧 **App Móvil**: En planificación

GET /app/presenters/obtener_karma_usuario.php?usuario_id={id}- 🚧 **Video Llamadas**: En planificación

```

---

### Conexiones

```## 📞 Soporte

GET  /app/presenters/obtener_conexiones.php

POST /app/presenters/enviar_solicitud.phpPara soporte técnico:

POST /app/presenters/aceptar_solicitud.php1. Revisar la documentación completa

POST /app/presenters/eliminar_amigo.php2. Verificar logs del sistema

```3. Contactar al equipo de desarrollo



### Predicciones---

```

GET /app/presenters/get_predictions.php## 📄 Licencia

POST /app/presenters/generar_predicciones.php

```Este proyecto es privado y propietario. Todos los derechos reservados.



### Asistente---

```

POST /app/presenters/assistant_chat.php## 🎉 Agradecimientos

GET  /app/presenters/get_assistant_session.php

```Gracias a todos los que han contribuido al desarrollo de Converza.



### Notificaciones---

```

GET /app/presenters/obtener_notificaciones.php**Hecho con ❤️ por el equipo de Converza**

POST /app/presenters/marcar_leida.php

```

---

## 🔐 Seguridad

### Implementaciones de Seguridad

- ✅ **Prepared Statements**: Todas las consultas SQL usan PDO preparado
- ✅ **Sanitización de Entrada**: `htmlspecialchars()` en todos los inputs
- ✅ **Validación de Sesión**: Verificación de `$_SESSION` en cada página
- ✅ **CSRF Protection**: Tokens en formularios críticos
- ✅ **Password Hashing**: `password_hash()` con bcrypt
- ✅ **Upload Validation**: Verificación de tipo MIME y extensión
- ✅ **SQL Injection Prevention**: Sin concatenación directa en queries
- ✅ **XSS Prevention**: Escape de salida HTML

### Recomendaciones de Producción

1. **Cambiar credenciales de BD**
2. **Activar HTTPS** (SSL/TLS)
3. **Configurar `.htaccess`** para seguridad
4. **Limitar tamaño de uploads**
5. **Implementar rate limiting**
6. **Activar logs de errores**
7. **Backup automático de BD**

---

## 📝 Configuración Avanzada

### Configurar OpenAI (Asistente)

Edita `config/openai.php`:
```php
<?php
define('OPENAI_API_KEY', 'tu-api-key-aqui');
define('OPENAI_MODEL', 'gpt-4');
define('OPENAI_MAX_TOKENS', 500);
```

### Configurar Karma

Edita `app/helpers/KarmaSocialHelper.php`:
```php
// Modificar puntajes de reacciones
const KARMA_REACCIONES = [
    'me_encanta' => 10,
    'me_gusta' => 5,
    // ...
];

// Modificar niveles
const NIVELES = [
    ['min' => 0, 'max' => 99, 'titulo' => 'Novato'],
    // ...
];
```

### Configurar Predicciones

Edita `app/helpers/PrediccionesHelper.php`:
```php
// Ajustar pesos del algoritmo
const PESO_INTERESES = 0.4;
const PESO_AMIGOS_COMUNES = 0.3;
const PESO_ACTIVIDAD = 0.2;
const PESO_KARMA = 0.1;
```

---

## 🐛 Troubleshooting

### Problema: "Error al conectar con la base de datos"
**Solución**: Verifica credenciales en `config/database.php`

### Problema: "No se pueden subir archivos"
**Solución**: 
```bash
chmod 755 public/uploads/
chown www-data:www-data public/uploads/
```

### Problema: "Karma no se actualiza"
**Solución**: Verifica que el trigger esté creado:
```sql
SHOW TRIGGERS LIKE 'karma_social';
```

### Problema: "Notificaciones no aparecen"
**Solución**: Verifica la tabla `notificaciones` y el JS:
```javascript
// Abre consola del navegador (F12)
// Busca errores en obtener_notificaciones.php
```

### Problema: "Asistente no responde"
**Solución**: Verifica API key de OpenAI y créditos disponibles

---

## 📚 Documentación Adicional

- **LIMPIEZA_SISTEMA.md** - Guía para eliminar archivos de desarrollo

---

## 🤝 Contribuir

Si deseas contribuir:

1. Fork el proyecto
2. Crea una rama: `git checkout -b feature/nueva-caracteristica`
3. Commit cambios: `git commit -m 'Añade nueva característica'`
4. Push a la rama: `git push origin feature/nueva-caracteristica`
5. Abre un Pull Request

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

---

## 👥 Autores

- **Equipo Converza** - Desarrollo principal

---

## 🙏 Agradecimientos

- Font Awesome por los iconos
- OpenAI por la API de IA
- Comunidad PHP por las mejores prácticas

---

## 📞 Soporte

¿Necesitas ayuda? 

- 📧 Email: soporte@converza.com
- 🐛 Issues: [GitHub Issues](https://github.com/tuusuario/converza/issues)

---

<div align="center">

**Hecho con ❤️ por el Equipo Converza**

[⬆ Volver arriba](#-converza---red-social-inteligente)

</div>
