# 📱 Sistema de Chat Flexible - Estilo TikTok

## 🎯 Objetivo
Implementar un sistema de chat más flexible que permita comunicación entre usuarios según diferentes niveles de relación, similar a TikTok.

## 🔐 Niveles de Permiso de Chat

### 1. ✅ **Amigos Confirmados** (Máxima prioridad)
- **Condición**: Solicitud de amistad aceptada (`amigos.estado = 1`)
- **Permisos**: Chat libre, sin restricciones
- **Cómo llegar**: Usuario A envía solicitud → Usuario B acepta

### 2. ✅ **Seguidores Mutuos** (NUEVO)
- **Condición**: Ambos usuarios se siguen mutuamente
- **Permisos**: Chat libre, sin necesidad de ser amigos
- **Cómo llegar**: 
  - Usuario A sigue a Usuario B
  - Usuario B sigue a Usuario A
  - Automáticamente pueden chatear

### 3. ⏳ **Solicitud de Mensaje** (NUEVO - Estilo TikTok)
- **Condición**: No son amigos ni se siguen mutuamente
- **Permisos**: 
  - Usuario A puede escribir un mensaje
  - El mensaje queda **pendiente** hasta que Usuario B lo acepte
  - Usuario B recibe notificación de "Solicitud de mensaje"
  - Usuario B puede aceptar o rechazar
- **Cómo funciona**:
  ```
  Usuario A escribe mensaje → Queda pendiente
       ↓
  Usuario B recibe solicitud
       ↓
  Usuario B acepta → Ahora pueden chatear
  Usuario B rechaza → Mensaje descartado
  ```

## 📊 Flujo del Sistema

```
┌─────────────────────────────────────┐
│ Usuario A quiere chatear con B     │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ ¿Son amigos confirmados?            │
│ (amigos.estado = 1)                 │
└─────────────────────────────────────┘
      SÍ ↓                    NO ↓
┌─────────────┐         ┌─────────────────────────────┐
│ Chat libre  │         │ ¿Se siguen mutuamente?      │
│     ✅      │         │ (seguidores mutuos)         │
└─────────────┘         └─────────────────────────────┘
                          SÍ ↓              NO ↓
                    ┌─────────────┐   ┌──────────────────────┐
                    │ Chat libre  │   │ Solicitud de mensaje │
                    │     ✅      │   │        ⏳            │
                    └─────────────┘   └──────────────────────┘
                                             ↓
                                      ┌──────────────────┐
                                      │ Usuario B decide │
                                      └──────────────────┘
                                       Acepta ↓   Rechaza ↓
                                      ┌─────────┐ ┌─────────┐
                                      │ Chat ✅ │ │ Bloqueo │
                                      └─────────┘ └─────────┘
```

## 🗄️ Nueva Tabla: `solicitudes_mensaje`

```sql
CREATE TABLE solicitudes_mensaje (
    id INT PRIMARY KEY AUTO_INCREMENT,
    de INT NOT NULL,                    -- Usuario que envía
    para INT NOT NULL,                  -- Usuario que recibe
    estado ENUM('pendiente', 'aceptada', 'rechazada'),
    primer_mensaje TEXT,                -- Mensaje inicial
    fecha_solicitud TIMESTAMP,
    fecha_respuesta TIMESTAMP,
    UNIQUE (de, para)                   -- Una solicitud por pareja
);
```

## 📂 Archivos Creados

### 1. **Helper de Permisos**: `app/models/chat-permisos-helper.php`
- `verificarPermisoChat()` - Verifica si dos usuarios pueden chatear
- `tieneSolicitudMensajePendiente()` - Verifica solicitudes pendientes
- `crearSolicitudMensaje()` - Crea nueva solicitud
- `aceptarSolicitudMensaje()` - Acepta solicitud
- `rechazarSolicitudMensaje()` - Rechaza solicitud
- `obtenerSolicitudesMensajePendientes()` - Lista solicitudes

### 2. **Endpoint Envío**: `app/presenters/enviar_mensaje_con_permisos.php`
- Verifica permisos antes de enviar
- Envía mensaje directo si tiene permiso
- Crea solicitud si no tiene permiso

### 3. **Endpoint Gestión**: `app/presenters/gestionar_solicitud_mensaje.php`
- Acepta solicitudes de mensaje
- Rechaza solicitudes de mensaje
- Inserta primer mensaje al aceptar

### 4. **Chat Actualizado**: `app/presenters/chat.php`
- Modificado para mostrar:
  - Amigos confirmados
  - Seguidores mutuos
  - Solicitudes aceptadas

## 🚀 Instalación

1. Ejecutar script de setup:
```
http://localhost/Converza/setup_solicitudes_mensaje.php
```

2. Verificar creación de tabla `solicitudes_mensaje`

3. El sistema está listo para usar

## 💡 Ventajas del Sistema

✅ **Más flexible**: No necesitas ser amigos para chatear  
✅ **Privacidad**: Control sobre quién puede escribirte  
✅ **Como TikTok**: Sistema moderno de solicitudes  
✅ **Seguidores mutuos**: Comunicación fluida entre seguidores  
✅ **Sin spam**: Mensajes pendientes hasta aceptación  

## 🔄 Integración con Daily Shuffle

Cuando usas Daily Shuffle y das "Agregar":
1. ✅ Envía solicitud de amistad
2. ✅ Sigue al usuario automáticamente
3. ⏳ Si el otro usuario te sigue de vuelta → **Chat libre inmediato** (seguidores mutuos)
4. ⏳ Si acepta tu amistad → **Chat libre** (amigos)
5. ⏳ Si ninguna condición → Puedes enviar **solicitud de mensaje**

## 📱 Ejemplo de Uso

### Caso 1: Seguidores Mutuos
```
admin1 sigue a santi1 (Daily Shuffle)
santi1 sigue a admin1
→ ✅ Pueden chatear inmediatamente
```

### Caso 2: Solicitud de Mensaje
```
admin1 quiere chatear con usuario3
admin1 no sigue a usuario3
usuario3 no sigue a admin1
→ admin1 escribe mensaje
→ ⏳ Queda pendiente
→ usuario3 recibe notificación
→ usuario3 acepta
→ ✅ Ahora pueden chatear
```

## 🎨 UI Sugerida

### Panel de Solicitudes de Mensaje
```
┌────────────────────────────────────┐
│ 📬 Solicitudes de Mensaje (2)     │
├────────────────────────────────────┤
│ 👤 usuario1                        │
│ "Hola! Me gustó tu perfil..."     │
│ [Aceptar] [Rechazar]              │
├────────────────────────────────────┤
│ 👤 usuario2                        │
│ "Hey! Quiero conocerte..."        │
│ [Aceptar] [Rechazar]              │
└────────────────────────────────────┘
```

## ⚠️ Notas Importantes

- Las solicitudes de mensaje son **únicas por pareja** (no se pueden duplicar)
- Al rechazar una solicitud, el otro usuario puede volver a intentar
- Los mensajes pendientes se insertan en el chat solo al aceptar
- El sistema es **retrocompatible** con el chat actual

---

**Autor**: GitHub Copilot  
**Fecha**: Octubre 2025  
**Versión**: 1.0
