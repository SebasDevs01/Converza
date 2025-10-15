# 📍 Ubicación del Método "Enviar Comentario"

## 🎯 Archivo Principal

### **`app/presenters/agregarcomentario.php`**

Este es el archivo que maneja **todo el proceso** de enviar y guardar comentarios.

---

## 📋 Estructura del Archivo

```php
agregarcomentario.php (206 líneas)
├── Configuración inicial (líneas 1-35)
│   ├── Error reporting deshabilitado para JSON limpio
│   ├── Sesión iniciada
│   ├── Conexión a base de datos
│   ├── Sistema de bloqueos
│   ├── Sistema de notificaciones
│   └── Sistema de karma social
│
├── Validación de bloqueo (líneas 36-42)
│   └── Verifica si el usuario está bloqueado
│
├── Proceso POST (líneas 44-189)
│   ├── Validar campos (usuario, comentario, publicación)
│   ├── Validar IDs
│   ├── Verificar existencia de publicación
│   ├── Verificar bloqueo mutuo
│   ├── **INSERTAR COMENTARIO** (línea 70-77) ← AQUÍ SE GUARDA
│   ├── Obtener ID del comentario
│   ├── Crear notificación
│   ├── Registrar karma social
│   └── Respuesta JSON con datos del comentario
│
└── Respuesta JSON (líneas 190-206)
    └── Devuelve resultado en formato JSON
```

---

## 🔑 Método Principal (líneas 70-77)

```php
// Insertar comentario
$stmt = $conexion->prepare("INSERT INTO comentarios (usuario, comentario, publicacion) 
                            VALUES (:usuario, :comentario, :publicacion)");
$stmt->execute([
    ':usuario' => $usuario,
    ':comentario' => $comentario,
    ':publicacion' => $publicacion
]);
```

---

## 📊 Flujo Completo

```
Usuario escribe comentario
        ↓
Formulario HTML (publicaciones.php línea 473)
        ↓
AJAX fetch POST (publicaciones.php línea 658)
        ↓
agregarcomentario.php recibe POST
        ↓
1. Validar sesión
2. Validar datos (usuario, comentario, publicación)
3. Verificar que publicación existe
4. Verificar bloqueos mutuos
5. **INSERTAR EN BD** ← Método principal
6. Obtener ID del comentario
7. Crear notificación al autor de la publicación
8. Registrar karma social (+2 puntos)
9. Obtener karma actualizado
10. Devolver respuesta JSON
        ↓
JavaScript recibe respuesta
        ↓
Comentario aparece instantáneamente en UI
```

---

## 🎯 Archivos Relacionados

### 1. **Frontend - Formulario**
```
app/presenters/publicaciones.php (línea 473)
```
HTML del formulario para escribir comentarios

### 2. **Frontend - JavaScript**
```
app/presenters/publicaciones.php (línea 656-700)
```
Código AJAX que envía el comentario sin recargar página

### 3. **Backend - Procesamiento**
```
app/presenters/agregarcomentario.php ← ARCHIVO PRINCIPAL
```
Procesa el comentario, guarda en BD, crea notificación

### 4. **Modelos - Helpers**
```
app/models/notificaciones-triggers.php
app/models/karma-social-triggers.php
app/models/bloqueos-helper.php
```
Sistemas auxiliares llamados por agregarcomentario.php

---

## 📝 Datos que Maneja

### Entrada (POST):
```php
$_POST['usuario']     // ID del usuario que comenta
$_POST['comentario']  // Texto del comentario
$_POST['publicacion'] // ID de la publicación
```

### Proceso:
```sql
INSERT INTO comentarios (usuario, comentario, publicacion) 
VALUES (14, 'Excelente post!', 123)
```

### Salida (JSON):
```json
{
  "status": "success",
  "message": "Tu comentario ha sido publicado.",
  "comentario": {
    "id": 456,
    "usuario": "sebas#1505",
    "avatar": "foto.jpg",
    "comentario": "Excelente post!",
    "fecha": "2025-10-14 15:30:00"
  },
  "karma_actualizado": {
    "karma": 152,
    "nivel": 3,
    "nivel_titulo": "Conversador Activo",
    "nivel_emoji": "💬"
  }
}
```

---

## 🔧 Funciones Adicionales

### 1. Sistema de Notificaciones (línea 106-119)
```php
$notificacionesTriggers->nuevoComentario(
    $usuario,        // Quién comentó
    $usuario2,       // Dueño de la publicación
    $nombreComentador, // Nombre del comentador
    $publicacion,    // ID de la publicación
    $comentario      // Texto del comentario
);
```

### 2. Sistema de Karma Social (línea 121-124)
```php
$karmaTriggers->nuevoComentario(
    $usuario,      // ID del usuario
    $comentarioId, // ID del comentario
    $comentario    // Texto del comentario
);
```
Otorga +2 puntos de karma automáticamente

### 3. Verificación de Bloqueos (línea 65-68)
```php
$bloqueoInfo = verificarBloqueoMutuo($conexion, $usuario, $pub_data['usuario']);
if ($bloqueoInfo['bloqueado']) {
    throw new Exception("No puedes comentar en esta publicación.");
}
```

---

## 🚀 Características Implementadas

### ✅ Comentarios Instantáneos
- No recarga la página
- Aparecen inmediatamente
- AJAX moderno con fetch()

### ✅ Sistema de Karma
- +2 puntos por comentario
- Actualización automática en navbar
- Respuesta incluye karma actualizado

### ✅ Notificaciones en Tiempo Real
- Notifica al dueño de la publicación
- Solo si el comentario no es del mismo usuario
- Sistema de triggers automático

### ✅ Sistema de Bloqueos
- Verifica bloqueos antes de comentar
- No permite comentar a usuarios bloqueados
- Protege de bloqueos mutuos

### ✅ Validaciones
- Verifica que la publicación existe
- Valida todos los campos
- Sanitiza el input

---

## 🐛 Manejo de Errores

```php
try {
    // Insertar comentario
    // Crear notificación
    // Registrar karma
} catch (PDOException $e) {
    error_log("ERROR PDO: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => 'Error al guardar'];
} catch (Exception $e) {
    error_log("ERROR Exception: " . $e->getMessage());
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}
```

Todos los errores se registran en: `comentarios_debug.log`

---

## 📍 Resumen

### Archivo principal:
```
📁 app/presenters/agregarcomentario.php
```

### Método de inserción:
```
Líneas 70-77
```

### Uso:
```
POST /Converza/app/presenters/agregarcomentario.php
Body: usuario=14&comentario=Hola&publicacion=123
```

### Respuesta:
```json
{"status": "success", "comentario": {...}, "karma_actualizado": {...}}
```

---

**📌 Archivo único que maneja TODO el proceso de comentarios**

*Incluye: Validación, Inserción, Notificaciones, Karma, Bloqueos*
