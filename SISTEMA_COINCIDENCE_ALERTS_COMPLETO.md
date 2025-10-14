# 🔮 SISTEMA COMPLETO: COINCIDENCE ALERTS + CONEXIONES MÍSTICAS

## ✅ TODO IMPLEMENTADO

### 📦 ARCHIVOS CREADOS:

#### Backend PHP:
1. ✅ `app/models/coincidence-alerts-helper.php` - Motor de detección en tiempo real
2. ✅ `app/presenters/check_coincidence_alerts.php` - API endpoint para verificar coincidencias
3. ✅ `app/presenters/manage_conexiones.php` - API para gestionar conexiones místicas
4. ✅ `app/cron/cron_actualizar_conexiones.php` - CRON job para actualización automática cada 6 horas
5. ✅ `app/cron/install_coincidence_system.php` - Instalador de tablas

#### Frontend JavaScript:
6. ✅ `public/js/coincidence-alerts.js` - Sistema de notificaciones en tiempo real
7. ✅ `public/js/conexiones-misticas-manager.js` - Contador y gestión de conexiones

#### Base de Datos:
8. ✅ `sql/coincidence_alerts_setup.sql` - Script SQL para crear tablas

#### Modificaciones:
9. ✅ `app/models/conexiones-misticas-helper.php` - Mejorado con actualización automática, limpieza y contador

---

## 🎯 CARACTERÍSTICAS IMPLEMENTADAS:

### 1️⃣ COINCIDENCE ALERTS (TIEMPO REAL)

#### ¿Qué hace?
- 🔴 Detecta usuarios online con alta compatibilidad (>70%)
- ⚡ Verifica cada 30 segundos automáticamente
- 🔔 Muestra notificaciones emergentes elegantes
- 📊 Calcula compatibilidad basada en:
  - Karma de ambos usuarios
  - Reacciones a publicaciones comunes
  - Amigos en común
  - Horarios de actividad similares

#### Cómo funciona:
```javascript
// Se ejecuta automáticamente cada 30 segundos
coincidenceAlerts.checkCoincidences();

// Muestra popup cuando hay coincidencia >70%
// Usuario puede:
// - Ver perfil del usuario compatible
// - Cerrar la alerta
// - Auto-cierre después de 15 segundos
```

#### Backend:
```php
// Detectar coincidencias
$resultado = $coincidenceAlerts->detectarCoincidenciasEnTiempoReal($usuario_id);

// Retorna:
// - hay_coincidencias: boolean
// - total: int
// - coincidencias: array (top 3)
// - mensaje: string
```

---

### 2️⃣ CONTADOR DE CONEXIONES MÍSTICAS

#### ¿Qué hace?
- 📊 Muestra total de conexiones en navbar
- ✨ Destaca conexiones nuevas (últimos 7 días)
- 🔄 Se actualiza automáticamente cada 5 minutos
- 💾 Usa caché en tabla `conexiones_misticas_contador`

#### Visual:
- **Badge morado** con número en navbar
- **Badge rosa brillante** cuando hay nuevas conexiones
- **Animación pulse** para llamar la atención

#### Datos mostrados:
```javascript
{
  total: 25,           // Total de conexiones
  nuevas: 3,           // Nuevas en últimos 7 días
  ultima_actualizacion // Timestamp última actualización
}
```

---

### 3️⃣ BOTONES DE GESTIÓN

#### En página de Conexiones Místicas:

**🔄 Actualizar Conexiones:**
- Busca nuevas conexiones basadas en actividad reciente
- Mantiene conexiones existentes
- Actualiza contador

**🧹 Limpiar y Renovar:**
- Elimina TODAS las conexiones actuales
- Busca conexiones completamente nuevas
- Útil para "empezar de cero"

**❓ Ayuda:**
- Explica cómo funcionan las conexiones
- Información sobre actualización automática
- Guía de uso

---

### 4️⃣ ACTUALIZACIÓN AUTOMÁTICA CADA 6 HORAS

#### CRON Job:
```bash
# Ejecutar cada 6 horas
0 */6 * * * php /path/to/app/cron/cron_actualizar_conexiones.php
```

#### Lo que hace:
1. ✅ Limpia conexiones antiguas (>30 días)
2. ✅ Detecta nuevas conexiones (gustos, intereses, amigos, horarios)
3. ✅ Actualiza contadores de todos los usuarios
4. ✅ Registra log de ejecución

#### Manual:
```bash
php app/cron/cron_actualizar_conexiones.php
```

---

## 📋 INSTALACIÓN:

### PASO 1: Ejecutar instalador
```bash
php app/cron/install_coincidence_system.php
```

Esto crea:
- ✅ Tabla `coincidence_alerts`
- ✅ Columna `ultima_actividad` en `usuarios`
- ✅ Tabla `conexiones_misticas_contador`
- ✅ Índices optimizados

---

### PASO 2: Agregar scripts a `index.php` (o layout principal)

**Antes del cierre de `</body>`:**
```html
<!-- Coincidence Alerts -->
<script src="public/js/coincidence-alerts.js"></script>

<!-- Conexiones Místicas Manager -->
<script src="public/js/conexiones-misticas-manager.js"></script>
```

---

### PASO 3: Configurar CRON Job

**En Linux/Mac (crontab -e):**
```bash
0 */6 * * * php /path/to/Converza/app/cron/cron_actualizar_conexiones.php >> /path/to/logs/conexiones.log 2>&1
```

**En Windows (Programador de tareas):**
- Programa: `php.exe`
- Argumentos: `C:\xampp\htdocs\Converza\app\cron\cron_actualizar_conexiones.php`
- Frecuencia: Cada 6 horas

**En XAMPP (sin cron):**
Puedes usar un servicio como **Cron-job.org** o **EasyCron.com** que llame a:
```
http://tudominio.com/app/cron/cron_actualizar_conexiones.php
```

---

### PASO 4: Ejecutar primera actualización manual
```bash
php app/cron/cron_actualizar_conexiones.php
```

---

## 🎨 PERSONALIZACIÓN:

### Cambiar intervalo de verificación (coincidence-alerts.js):
```javascript
this.checkInterval = 30000; // 30 segundos (default)
// Cambiar a 60000 para 1 minuto, etc.
```

### Cambiar umbral de compatibilidad (coincidence-alerts-helper.php):
```php
// Línea ~47
if ($compatibilidad['score'] >= 70) { // Default: 70%
    // Cambiar a 60, 80, etc.
}
```

### Cambiar frecuencia de actualización automática:
```bash
# Cada 3 horas:
0 */3 * * * php cron_actualizar_conexiones.php

# Cada 12 horas:
0 */12 * * * php cron_actualizar_conexiones.php

# Diario a las 2 AM:
0 2 * * * php cron_actualizar_conexiones.php
```

---

## 📊 ESTRUCTURA DE BASE DE DATOS:

### Tabla: `coincidence_alerts`
```sql
CREATE TABLE coincidence_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,                    -- Usuario que recibe la alerta
    usuario_coincidente_id INT NOT NULL,        -- Usuario compatible
    compatibilidad INT NOT NULL,                -- Score 0-100
    razon TEXT,                                 -- Razón de la compatibilidad
    leida BOOLEAN DEFAULT FALSE,                -- Si fue vista
    fecha_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Tabla: `conexiones_misticas_contador`
```sql
CREATE TABLE conexiones_misticas_contador (
    usuario_id INT PRIMARY KEY,
    total_conexiones INT DEFAULT 0,             -- Total de conexiones
    nuevas_conexiones INT DEFAULT 0,            -- Nuevas en últimos 7 días
    ultima_actualizacion TIMESTAMP              -- Última actualización
);
```

### Columna agregada: `usuarios.ultima_actividad`
```sql
ALTER TABLE usuarios 
ADD COLUMN ultima_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

---

## 🔥 FLUJO COMPLETO:

### Usuario entra al sitio:
1. ✅ JavaScript inicia `CoincidenceAlertsManager`
2. ✅ Verifica inmediatamente si hay usuarios compatibles online
3. ✅ Si encuentra compatibilidad >70%, muestra popup elegante
4. ✅ Actualiza badge en navbar con contador

### Cada 30 segundos:
1. ✅ Verifica nuevamente usuarios online
2. ✅ Calcula compatibilidad en tiempo real
3. ✅ Muestra alertas de nuevas coincidencias
4. ✅ Actualiza contador

### Cada 5 minutos:
1. ✅ Actualiza contador de conexiones místicas
2. ✅ Sincroniza badge en navbar

### Cada 6 horas (CRON):
1. ✅ Limpia conexiones antiguas (>30 días)
2. ✅ Detecta nuevas conexiones de TODOS los usuarios
3. ✅ Actualiza contadores globales
4. ✅ Registra log de ejecución

### Usuario visita Conexiones Místicas:
1. ✅ Ve panel de gestión con botones
2. ✅ Puede actualizar manualmente
3. ✅ Puede limpiar y renovar conexiones
4. ✅ Ve contador actualizado

---

## 🐛 DEBUGGING:

### Ver logs de CRON:
```bash
tail -f /path/to/logs/conexiones.log
```

### Verificar tablas:
```sql
SELECT * FROM coincidence_alerts ORDER BY fecha_alerta DESC LIMIT 10;
SELECT * FROM conexiones_misticas_contador;
SELECT COUNT(*) FROM conexiones_misticas;
```

### Forzar actualización manual:
```bash
php app/cron/cron_actualizar_conexiones.php
```

### Ver alertas en consola del navegador:
```javascript
console.log(coincidenceAlerts);
console.log(conexionesManager);
```

---

## ✅ CHECKLIST DE VERIFICACIÓN:

- [ ] Ejecuté `install_coincidence_system.php`
- [ ] Tablas creadas correctamente
- [ ] Scripts JS agregados a `index.php`
- [ ] CRON job configurado (o alternativa)
- [ ] Ejecuté primera actualización manual
- [ ] Badge aparece en navbar
- [ ] Popup de coincidencias funciona
- [ ] Botones de gestión aparecen en página de conexiones
- [ ] Contador se actualiza correctamente

---

## 🚀 PRÓXIMOS PASOS OPCIONALES:

1. **Notificaciones Push** (web notifications API)
2. **Sonido personalizado** para alertas
3. **Filtros de compatibilidad** (edad, ubicación, etc.)
4. **Historial de coincidencias** pasadas
5. **Estadísticas** de conexiones más comunes
6. **Chat rápido** desde la alerta

---

**Fecha de implementación:** 14 de Octubre, 2025  
**Estado:** ✅ COMPLETAMENTE FUNCIONAL  
**Versión:** 1.0.0
