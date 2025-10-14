# 🌐 Converza - Red Social Moderna

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red)](LICENSE)

> Una red social innovadora que combina interacciones tradicionales con sistemas gamificados de karma, recompensas visuales y conexiones inteligentes entre usuarios.

---

## ✨ Características Principales

### 🎖️ Sistema de Karma
- Recompensa automática por interacciones positivas
- Niveles progresivos basados en actividad
- Tienda de recompensas con items desbloqueables
- Notificaciones en tiempo real de ganancias

### 🏆 Badges y Notificaciones
- Badges animados en navbar con actualización en tiempo real
- Sistema unificado de notificaciones
- Contadores pulsantes con efectos visuales
- Offcanvas integrado con detalles

### 🎨 Personalización Total
- **Marcos de Avatar**: Bordes decorativos personalizables
- **Temas de Perfil**: Esquemas de colores para tu página
- **Íconos Especiales**: Distintivos junto a tu nombre
- **Colores de Nombre**: Efectos de gradiente y animaciones
- **Stickers**: Decoraciones para tu perfil
- **Insignias**: Automáticas según tu nivel
- **Auto-equipado**: Las recompensas se aplican automáticamente

### 💬 Chat Avanzado
- Sistema de permisos configurable (abierto/amigos/solicitud)
- Mensajes de voz integrados
- Reacciones a mensajes con emojis
- Archivado de conversaciones
- Sistema de bloqueo de usuarios

### 🔮 Conexiones Místicas
- Detección automática de afinidad entre usuarios
- Análisis basado en múltiples criterios
- Notificaciones de nuevas conexiones
- Ganancias de karma por conexiones

### 🎲 Daily Shuffle
- 5 usuarios nuevos sugeridos cada día
- Sistema de descubrimiento automatizado
- Acciones rápidas (amistad/mensaje/perfil)
- Renovación diaria automática

### ⚠️ Coincidence Alerts
- Detección de compatibilidad en tiempo real
- Alertas cuando ambos usuarios están online
- Análisis instantáneo de compatibilidad
- Oportunidades de conexión inmediata

---

## 🚀 Instalación

### Requisitos

- **PHP** 7.4 o superior
- **MySQL** 5.7 o superior
- **Apache** con mod_rewrite habilitado
- **Composer** (opcional, para dependencias)

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tu-usuario/converza.git
   cd converza
   ```

2. **Configurar la base de datos**
   ```bash
   # Importar el schema principal
   mysql -u root -p < sql/converza.sql
   
   # Importar tablas adicionales (ejecutar en orden)
   mysql -u root -p converza < sql/create_karma_social_table.sql
   mysql -u root -p converza < sql/create_notificaciones_table.sql
   mysql -u root -p converza < sql/create_usuario_recompensas_table.sql
   mysql -u root -p converza < sql/create_chat_system.sql
   mysql -u root -p converza < sql/create_conexiones_misticas.sql
   mysql -u root -p converza < sql/create_daily_shuffle_table.sql
   # ... y demás tablas según necesidad
   ```

3. **Configurar variables de entorno**
   ```bash
   # Copiar y editar el archivo .env
   cp .env.example .env
   ```
   
   Editar `.env` con tus credenciales:
   ```env
   DB_HOST=localhost
   DB_NAME=converza
   DB_USER=root
   DB_PASS=tu_password
   ```

4. **Configurar permisos**
   ```bash
   chmod 755 public/avatars
   chmod 755 public/publicaciones
   chmod 755 public/voice_messages
   ```

5. **Acceder al sistema**
   - Abrir navegador en: `http://localhost/Converza`
   - Registrar un usuario nuevo
   - ¡Empezar a usar Converza!

---

## 📁 Estructura del Proyecto

```
Converza/
├── app/
│   ├── models/              # Lógica de negocio
│   │   ├── config.php
│   │   ├── socialnetwork-lib.php
│   │   ├── karma-social-helper.php
│   │   ├── notificaciones-helper.php
│   │   ├── recompensas-aplicar-helper.php
│   │   └── ...
│   │
│   ├── presenters/          # Controladores y APIs
│   │   ├── login.php
│   │   ├── registro.php
│   │   ├── perfil.php
│   │   ├── chat.php
│   │   ├── karma_tienda.php
│   │   └── ...
│   │
│   └── view/                # Vistas
│       ├── index.php
│       ├── components/      # Componentes reutilizables
│       └── ...
│
├── public/                  # Assets públicos
│   ├── css/
│   ├── js/
│   ├── avatars/
│   ├── publicaciones/
│   └── voice_messages/
│
├── sql/                     # Scripts de base de datos
├── dist/                    # Assets compilados
├── bootstrap/               # Framework CSS
├── .env                     # Configuración (no versionar)
├── .htaccess                # Configuración Apache
├── composer.json            # Dependencias PHP
└── DOCUMENTACION_SISTEMA.md # Documentación completa
```

---

## 📖 Documentación

La documentación completa del sistema está disponible en:

**[DOCUMENTACION_SISTEMA.md](DOCUMENTACION_SISTEMA.md)**

Incluye:
- Arquitectura del sistema completa
- Guía de uso de todas las funcionalidades
- API reference para desarrolladores
- Guías para agregar nuevas características
- Solución de problemas comunes

---

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+
- **Frontend**: 
  - HTML5, CSS3, JavaScript (ES6+)
  - Bootstrap 5
  - jQuery
  - Font Awesome / Bootstrap Icons
- **Arquitectura**: MVC (Model-View-Presenter)
- **APIs**: REST JSON

---

## 🎯 Casos de Uso

### Para Usuarios

1. **Ganar Karma**: Publica, comenta, da likes y acepta amistades
2. **Personalizar Perfil**: Desbloquea marcos, temas, colores y más
3. **Hacer Amigos**: Usa Daily Shuffle o Conexiones Místicas
4. **Chatear**: Configura permisos y chatea con seguridad
5. **Descubrir Compatibles**: Recibe alertas de coincidencias

### Para Desarrolladores

1. **Agregar Recompensas**: Sistema modular de recompensas
2. **Crear Badges**: Componentes reutilizables
3. **Nuevos Criterios Karma**: Sistema de triggers extensible
4. **APIs REST**: Estructura clara para nuevos endpoints
5. **Personalización CSS**: Variables y clases modulares

---

## 🔧 Comandos Útiles

### Desarrollo

```bash
# Iniciar servidor de desarrollo (si no usas XAMPP)
php -S localhost:8000 -t public

# Ver logs de PHP
tail -f /path/to/php-error.log

# Ver logs de Apache
tail -f /path/to/apache/error.log
```

### Base de Datos

```bash
# Backup de la base de datos
mysqldump -u root -p converza > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u root -p converza < backup_20251014.sql

# Acceder a MySQL console
mysql -u root -p converza
```

---

## 🐛 Solución de Problemas

### Badges no actualizan

```bash
# Verificar en consola del navegador (F12)
# Revisar respuesta de APIs
# Limpiar caché del navegador
```

### Karma no se actualiza

```bash
# Verificar triggers en karma-social-triggers.php
# Revisar log de karma en base de datos
# Comprobar llamadas a funciones
```

### Chat no funciona

```bash
# Verificar permisos de usuario
# Revisar chat-permisos-helper.php
# Comprobar sesión activa
```

Para más información, consultar [DOCUMENTACION_SISTEMA.md](DOCUMENTACION_SISTEMA.md) sección "Solución de Problemas".

---

## 🤝 Contribuir

Este es un proyecto privado. Si tienes acceso y deseas contribuir:

1. Crear una branch para tu feature: `git checkout -b feature/nueva-caracteristica`
2. Hacer commit de tus cambios: `git commit -m 'Añadir nueva característica'`
3. Push a la branch: `git push origin feature/nueva-caracteristica`
4. Crear un Pull Request

---

## 📊 Estado del Proyecto

- ✅ **Sistema de Karma**: Completado y funcional
- ✅ **Badges Animados**: Completado y funcional
- ✅ **Sistema de Personalización**: Completado y funcional
- ✅ **Chat con Permisos**: Completado y funcional
- ✅ **Conexiones Místicas**: Completado y funcional
- ✅ **Daily Shuffle**: Completado y funcional
- ✅ **Coincidence Alerts**: Completado y funcional
- 🚧 **App Móvil**: En planificación
- 🚧 **Video Llamadas**: En planificación

---

## 📞 Soporte

Para soporte técnico:
1. Revisar la documentación completa
2. Verificar logs del sistema
3. Contactar al equipo de desarrollo

---

## 📄 Licencia

Este proyecto es privado y propietario. Todos los derechos reservados.

---

## 🎉 Agradecimientos

Gracias a todos los que han contribuido al desarrollo de Converza.

---

**Hecho con ❤️ por el equipo de Converza**

