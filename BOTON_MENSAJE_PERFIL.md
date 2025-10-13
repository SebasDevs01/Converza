# 💬 Botón "Enviar Mensaje" en Perfil - Como Facebook

## 🎯 Funcionalidad Implementada

Se agregó un botón **"Mensaje"** en el perfil de cada usuario (excepto el propio), que maneja automáticamente todos los permisos de chat según la relación entre usuarios.

---

## 📍 Ubicación del Botón

```
Perfil de Usuario
├── Avatar
├── Nombre y @usuario
├── Contadores (Seguidores, Siguiendo, Publicaciones)
└── Botones de Acción:
    ├── [Seguir/Siguiendo]
    ├── [Agregar Amigo / Solicitud Pendiente]
    └── [💬 Mensaje] ← NUEVO
```

---

## 🔐 Sistema de Permisos (Automático)

El botón **detecta automáticamente** la relación y ajusta el comportamiento:

### ✅ **Caso 1: Amigos Confirmados**
```
Modal se abre:
    → ✅ "Son amigos. Pueden chatear libremente."
    → Textarea habilitado
    → Enviar mensaje → Redirige a chat
```

### ✅ **Caso 2: Seguidores Mutuos (No amigos)**
```
Modal se abre:
    → ✅ "Se siguen mutuamente. Pueden chatear libremente."
    → Textarea habilitado
    → Enviar mensaje → Redirige a chat
```

### ✅ **Caso 3: Solicitud de Mensaje Aceptada**
```
Modal se abre:
    → ✅ "Solicitud de mensaje aceptada. Pueden chatear libremente."
    → Textarea habilitado
    → Enviar mensaje → Redirige a chat
```

### ⚠️ **Caso 4: Sin Relación (Primera vez)**
```
Modal se abre:
    → ⚠️ "Solo puedes enviar 1 mensaje hasta que este usuario lo acepte."
    → Textarea habilitado
    → Enviar mensaje:
        → 📬 "Solicitud de mensaje enviada"
        → Textarea se deshabilita
        → Modal se cierra automáticamente
```

### ⏳ **Caso 5: Ya Tiene Solicitud Pendiente**
```
Modal se abre:
    → ⏳ "Ya enviaste un mensaje a este usuario. Espera a que lo acepte."
    → Muestra el mensaje que ya envió (solo lectura)
    → Textarea deshabilitado
    → Botón "Enviar" deshabilitado
```

### ❌ **Caso 6: Solicitud Rechazada**
```
Modal se abre:
    → ❌ "Este usuario rechazó tu solicitud de mensaje anterior."
    → Textarea deshabilitado
    → Botón "Enviar" deshabilitado
```

---

## 🎨 Interfaz del Modal

```
┌────────────────────────────────────────────┐
│ 💬 Enviar mensaje a @usuario              │
│                                        [X]  │
├────────────────────────────────────────────┤
│                                            │
│ [Alerta de estado: amigos/restricción]    │
│                                            │
│ Mensaje:                                   │
│ ┌────────────────────────────────────────┐ │
│ │                                        │ │
│ │ Escribe tu mensaje aquí...            │ │
│ │                                        │ │
│ └────────────────────────────────────────┘ │
│ 0/500 caracteres                           │
│                                            │
│ [ℹ️ Info sobre permisos/restricciones]    │
│                                            │
├────────────────────────────────────────────┤
│            [Cancelar]  [💬 Enviar]         │
└────────────────────────────────────────────┘
```

---

## 🔄 Flujo de Trabajo

### Escenario A: Usuarios Son Amigos
```
1. Usuario hace clic en "💬 Mensaje"
2. Modal se abre
3. AJAX verifica permisos → "amigos"
4. Muestra: "✅ Son amigos"
5. Usuario escribe mensaje
6. Clic en "Enviar"
7. Mensaje se envía a tabla `chats`
8. Redirige a chat.php con el usuario
```

### Escenario B: Primera Vez (Sin Relación)
```
1. Usuario hace clic en "💬 Mensaje"
2. Modal se abre
3. AJAX verifica permisos → "necesita_solicitud"
4. Muestra: "⚠️ Solo 1 mensaje hasta que acepte"
5. Usuario escribe mensaje: "Hola! Me gustó tu perfil"
6. Clic en "Enviar"
7. Se crea solicitud_mensaje (estado: pendiente)
8. Mensaje guardado en `primer_mensaje`
9. Muestra: "📬 Solicitud enviada"
10. Textarea se deshabilita
11. Modal se cierra automáticamente
```

### Escenario C: Ya Tiene Solicitud Pendiente
```
1. Usuario hace clic en "💬 Mensaje"
2. Modal se abre
3. AJAX verifica permisos → "necesita_solicitud" + "tiene_solicitud_pendiente"
4. Muestra: "⏳ Ya enviaste un mensaje"
5. Textarea muestra mensaje anterior (deshabilitado)
6. Botón "Enviar" deshabilitado
7. Usuario solo puede cerrar el modal
```

---

## 📂 Archivos Creados/Modificados

### 1. **Modificado**: `app/presenters/perfil.php`
- Agregado botón "💬 Mensaje" junto a botones de amistad
- Agregado modal completo con formulario
- Agregado JavaScript para manejar permisos y envío

### 2. **Nuevo**: `app/presenters/verificar_permisos_chat.php`
- Endpoint AJAX para verificar permisos de chat
- Retorna: puede_chatear, tipo_relacion, necesita_solicitud, tiene_solicitud_pendiente

### 3. **Ya Existente**: `app/presenters/enviar_mensaje_con_permisos.php`
- Maneja el envío real del mensaje o creación de solicitud
- Valida límite de 1 mensaje para usuarios sin relación

---

## 🎯 Comportamiento Visual

### Estados del Botón "Enviar":
```css
Estado Normal:
    [💬 Enviar]  ← Habilitado (azul)

Enviando:
    [⏳ Enviando...]  ← Deshabilitado (spinner)

Después de Enviar:
    - Chat libre: Redirige automáticamente
    - Solicitud: Muestra confirmación y cierra modal
```

### Colores de Alertas:
- 🟢 **Verde** (success): Amigos, seguidores mutuos, solicitud aceptada
- 🟡 **Amarillo** (warning): Solo 1 mensaje permitido, solicitud pendiente
- 🔴 **Rojo** (danger): Solicitud rechazada, error
- 🔵 **Azul** (info): Solicitud creada correctamente

---

## ✅ Ventajas del Sistema

1. **Automático**: Detecta permisos sin que el usuario configure nada
2. **Intuitivo**: Mensajes claros sobre lo que puede hacer
3. **Como Facebook**: Botón de mensaje siempre visible
4. **Previene Spam**: Límite de 1 mensaje si no hay relación
5. **Feedback Claro**: Muestra estado de solicitud pendiente
6. **UX Fluida**: Redirige al chat cuando puede chatear libremente

---

## 🧪 Pruebas Recomendadas

### Caso 1: admin1 → santi1 (Amigos)
```
1. admin1 va al perfil de santi1
2. Clic en "💬 Mensaje"
3. ✅ Debe mostrar: "Son amigos. Pueden chatear libremente."
4. Escribe mensaje y envía
5. ✅ Debe redirigir a chat.php?usuario=santi1
```

### Caso 2: admin1 → usuario3 (Sin relación)
```
1. admin1 va al perfil de usuario3
2. Clic en "💬 Mensaje"
3. ⚠️ Debe mostrar: "Solo 1 mensaje hasta que acepte"
4. Escribe: "Hola! Me gustó tu perfil"
5. Envía
6. ✅ Debe mostrar: "Solicitud enviada"
7. Intenta abrir modal de nuevo
8. ⏳ Debe mostrar mensaje ya enviado (bloqueado)
```

### Caso 3: admin1 → usuario4 (Seguidores mutuos)
```
1. admin1 sigue a usuario4
2. usuario4 sigue a admin1
3. admin1 va al perfil de usuario4
4. Clic en "💬 Mensaje"
5. ✅ Debe mostrar: "Se siguen mutuamente"
6. Puede chatear libremente
```

---

## 📊 Tabla de Estados

| Relación | Puede Enviar | Límite | Redirige a Chat |
|----------|--------------|--------|-----------------|
| Amigos | ✅ Sí | Ilimitado | ✅ Sí |
| Seguidores mutuos | ✅ Sí | Ilimitado | ✅ Sí |
| Solicitud aceptada | ✅ Sí | Ilimitado | ✅ Sí |
| Sin relación (1ª vez) | ✅ Sí | 1 mensaje | ❌ No |
| Solicitud pendiente | ❌ No | 0 mensajes | ❌ No |
| Solicitud rechazada | ❌ No | 0 mensajes | ❌ No |

---

**Autor**: GitHub Copilot  
**Fecha**: Octubre 2025  
**Versión**: 1.0
