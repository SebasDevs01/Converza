# 🔄 Sistema de Relaciones y Chat - ACTUALIZADO

## 🎯 Problema Resuelto: No Más Duplicados

### ❌ Problema Anterior:
```
Daily Shuffle → Agregar
    ↓
1. Envía solicitud de amistad
2. Sigue automáticamente
    ↓
Si acepta solicitud:
    → Es AMIGO ✅
    → Es SEGUIDOR ✅
    → DUPLICADO en lista de chat ❌❌
```

### ✅ Solución Implementada:
```
Daily Shuffle → Agregar
    ↓
1. Solo envía solicitud de amistad
2. NO sigue automáticamente
    ↓
OPCIÓN A: Acepta solicitud
    → Son AMIGOS ✅
    → NO son seguidores
    → Una sola entrada en chat ✅

OPCIÓN B: Rechaza solicitud
    → NO son amigos
    → Usuario puede seguir manualmente si quiere
    → Pueden ser seguidores mutuos ✅
```

---

## 📊 Jerarquía de Relaciones (Mutuamente Excluyentes)

### 🥇 **PRIORIDAD 1: AMIGOS**
- **Condición**: Solicitud de amistad aceptada (`amigos.estado = 1`)
- **Permisos**: Chat libre ilimitado
- **Exclusión**: Si son amigos, **NO aparecen como seguidores** en el chat

### 🥈 **PRIORIDAD 2: SEGUIDORES MUTUOS**
- **Condición**: Se siguen mutuamente **Y NO son amigos**
- **Permisos**: Chat libre ilimitado
- **Nota**: Si luego se hacen amigos, pasan a Prioridad 1

### 🥉 **PRIORIDAD 3: SOLICITUD DE MENSAJE ACEPTADA**
- **Condición**: Solicitud de mensaje aceptada **Y NO son amigos ni seguidores mutuos**
- **Permisos**: Chat libre
- **Nota**: Si luego se hacen amigos, pasan a Prioridad 1

### ⏳ **SIN RELACIÓN: Sistema de Solicitud**
- **Condición**: No son amigos, no se siguen mutuamente
- **Permisos**: 
  - Puede enviar **SOLO 1 mensaje**
  - El mensaje queda pendiente
  - **NO puede enviar más** hasta que el otro acepte
  - Límite estricto de 1 mensaje

---

## 🚫 Regla del 1 Mensaje (Como TikTok)

### Funcionamiento:
```
Usuario A quiere chatear con Usuario B
(No son amigos, no se siguen mutuamente)
    ↓
1. Usuario A escribe mensaje: "Hola!"
    → Se crea solicitud_mensaje (estado: pendiente)
    → Mensaje guardado en solicitud
    ↓
2. Usuario A intenta escribir otro mensaje
    → ❌ BLOQUEADO
    → Error: "Ya enviaste un mensaje. Espera a que lo acepte."
    ↓
3. Usuario B ve la solicitud
    → Puede ver el mensaje de Usuario A
    → Acepta o Rechaza
    ↓
4A. Usuario B ACEPTA:
    → Mensaje se inserta en el chat
    → Ahora pueden chatear libremente ✅
    
4B. Usuario B RECHAZA:
    → Mensaje descartado
    → Usuario A no puede volver a escribir ❌
```

---

## 🔄 Flujo Completo: Daily Shuffle → Chat

### Escenario 1: Aceptación de Amistad
```
admin1 → Daily Shuffle → Agregar a santi1
    ↓
1. Solicitud de amistad enviada (estado = 0)
2. admin1 NO sigue a santi1 automáticamente
    ↓
santi1 acepta solicitud
    ↓
Son AMIGOS (estado = 1)
    ↓
✅ Pueden chatear libremente
✅ Una sola entrada en lista de chat
✅ Tipo: "amigo"
```

### Escenario 2: Seguimiento Mutuo (Sin Amistad)
```
admin1 sigue a santi1 manualmente
santi1 sigue a admin1 manualmente
    ↓
Son SEGUIDORES MUTUOS
NO son amigos
    ↓
✅ Pueden chatear libremente
✅ Una sola entrada en lista de chat
✅ Tipo: "seguidor_mutuo"
```

### Escenario 3: Sin Relación → Solicitud de Mensaje
```
admin1 quiere chatear con usuario3
NO son amigos
NO se siguen mutuamente
    ↓
admin1 escribe: "Hola! Me gustó tu perfil"
    ↓
📬 Solicitud de mensaje creada
✉️ Mensaje guardado (NO en chat todavía)
    ↓
admin1 intenta escribir otro mensaje:
    ❌ "Ya enviaste un mensaje. Espera a que lo acepte."
    ↓
usuario3 ve solicitud con el mensaje
    ↓
OPCIÓN A: usuario3 acepta
    → Mensaje aparece en chat
    → ✅ Ahora pueden chatear libremente
    
OPCIÓN B: usuario3 rechaza
    → Mensaje descartado
    → ❌ admin1 bloqueado permanentemente
```

---

## 🗂️ Base de Datos

### Tabla: `amigos`
```sql
id_ami | de  | para | estado | fecha
-------|-----|------|--------|----------
1      | 14  | 21   | 0      | 2025-10-12  -- Pendiente
2      | 14  | 22   | 1      | 2025-10-11  -- Aceptada (AMIGOS)
```

### Tabla: `seguidores`
```sql
id | seguidor_id | seguido_id
---|-------------|------------
1  | 14          | 23          -- admin1 sigue a usuario3
2  | 23          | 14          -- usuario3 sigue a admin1
                                -- MUTUOS → Pueden chatear
```

### Tabla: `solicitudes_mensaje`
```sql
id | de | para | estado     | primer_mensaje           | fecha_solicitud
---|----|----- |------------|--------------------------|------------------
1  | 14 | 24   | pendiente  | "Hola! Me gustó..."     | 2025-10-12 10:30
2  | 14 | 25   | aceptada   | "Hey! Quiero conocerte" | 2025-10-11 15:20
3  | 14 | 26   | rechazada  | "Hola"                  | 2025-10-10 12:00
```

---

## ✅ Lista de Verificación del Sistema

### Prevención de Duplicados:
- [x] Si son **AMIGOS**, no aparecen como seguidores
- [x] Query del chat usa `NOT EXISTS` para excluir amigos de seguidores
- [x] Jerarquía clara: Amigos > Seguidores > Solicitud

### Límite de 1 Mensaje:
- [x] Verifica solicitud existente antes de enviar
- [x] Muestra error si ya tiene solicitud pendiente
- [x] Guarda mensaje en `solicitudes_mensaje.primer_mensaje`
- [x] NO inserta en tabla `chats` hasta que acepten

### No Seguimiento Automático:
- [x] Eliminado código de seguimiento automático en Daily Shuffle
- [x] Solo envía solicitud de amistad
- [x] Usuario decide manualmente si seguir

---

## 🔧 Archivos Modificados

| Archivo | Cambio |
|---------|--------|
| `enviar_solicitud_shuffle.php` | Eliminado seguimiento automático |
| `chat-permisos-helper.php` | Jerarquía de prioridades clara |
| `chat.php` | Query anti-duplicados con NOT EXISTS |
| `enviar_mensaje_con_permisos.php` | Límite de 1 mensaje implementado |

---

## 💡 Beneficios del Sistema

✅ **Sin duplicados** - Una persona = Una entrada en chat  
✅ **Jerarquía clara** - Amigos > Seguidores > Solicitudes  
✅ **Sin spam** - Solo 1 mensaje hasta aceptación  
✅ **Como Facebook** - Amigos y seguidores son mutuamente excluyentes  
✅ **Privacidad** - Control total sobre quién puede escribirte  

---

**Autor**: GitHub Copilot  
**Fecha**: Octubre 2025  
**Versión**: 2.0 - SISTEMA CORREGIDO
