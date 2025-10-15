# 🚀 Instalación Rápida - Converza Assistant

## ✅ Paso 1: Verificar Archivos

Asegúrate de que estos archivos existen:

```
C:\xampp\htdocs\Converza\app\microservices\converza-assistant\
├── api\
│   └── assistant.php
├── engine\
│   ├── IntentClassifier.php
│   ├── ResponseGenerator.php
│   └── ContextManager.php
├── knowledge\
│   ├── karma-kb.json
│   └── reactions-kb.json
├── widget\
│   ├── assistant-widget.html
│   ├── assistant-widget.css
│   └── assistant-widget.js
└── README.md
```

---

## ✅ Paso 2: Incluir Widget en las Páginas

### Opción A: En todas las páginas (recomendado)

Edita `index.php` (o el layout principal):

```php
<?php
// Al final del archivo, antes de </body>
require_once(__DIR__.'/../../microservices/converza-assistant/widget/assistant-widget.html');
?>
```

### Opción B: Solo en páginas específicas

En `publicaciones.php`, `perfil.php`, etc.:

```php
<?php
// Al final del archivo, antes de </body>
require_once(__DIR__.'/../../microservices/converza-assistant/widget/assistant-widget.html');
?>
```

---

## ✅ Paso 3: Pasar el User ID al Widget

En tu archivo principal (donde se carga el usuario):

```php
<script>
    // Hacer disponible el user_id para el widget
    const USER_ID = <?php echo $_SESSION['id'] ?? 0; ?>;
    const sessionUserId = USER_ID; // Compatibilidad
</script>
```

---

## ✅ Paso 4: Probar el Asistente

1. **Abre tu navegador** en `http://localhost/Converza/app/view/index.php`

2. **Verás un botón flotante** 🤖 en la esquina inferior derecha

3. **Haz clic** para abrir el chat

4. **Prueba con estas preguntas**:
   - "¿Cómo gano karma?"
   - "¿Qué son las reacciones?"
   - "¿Qué nivel soy?"
   - "¿Por qué perdí puntos?"

---

## 🎯 Ejemplo de Integración Completa

**Archivo**: `app/view/index.php`

```php
<?php
session_start();
require_once(__DIR__.'/../models/config.php');

// ... tu código existente ...
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Converza - Red Social</title>
    <!-- Bootstrap Icons (requerido) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    
    <!-- Tu contenido existente -->
    
    <!-- User ID para el widget -->
    <script>
        const USER_ID = <?php echo $_SESSION['id'] ?? 0; ?>;
        const sessionUserId = USER_ID;
    </script>
    
    <!-- Incluir widget del asistente -->
    <?php require_once(__DIR__.'/../../microservices/converza-assistant/widget/assistant-widget.html'); ?>
    
</body>
</html>
```

---

## 🔧 Agregar Más Conocimientos

### 1. Crear nuevo archivo JSON en `knowledge/`

**Archivo**: `social-kb.json`

```json
{
  "intents": [
    {
      "intent": "friends_add",
      "keywords": ["amigos", "agregar", "añadir", "solicitud"],
      "questions": [
        "¿Cómo agrego amigos?",
        "¿Cómo envío solicitud de amistad?"
      ],
      "answer": "Para agregar amigos:\n\n1. Ve al perfil del usuario\n2. Haz clic en 'Agregar amigo'\n3. Espera a que acepte tu solicitud\n\n💡 Ganarás +5 puntos de karma por cada amistad.",
      "links": []
    }
  ]
}
```

### 2. El sistema lo cargará automáticamente

No necesitas modificar ningún código PHP. El `IntentClassifier` busca todos los archivos `*-kb.json` en la carpeta `knowledge/`.

---

## 📊 Monitorear Uso (Opcional)

### Crear tabla de logs:

```sql
CREATE TABLE IF NOT EXISTS assistant_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    intent VARCHAR(50),
    confidence DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_intent (intent)
);
```

### Modificar `assistant.php` para loggear:

```php
// Después de clasificar la intención (línea ~75)
$stmt = $conexion->prepare("
    INSERT INTO assistant_logs (user_id, question, intent, confidence) 
    VALUES (?, ?, ?, ?)
");
$stmt->execute([
    $user_id,
    $question,
    $intent['name'],
    $intent['confidence']
]);
```

---

## 🎨 Personalizar Estilos

### Cambiar colores del tema:

En `assistant-widget.css`:

```css
/* Cambiar gradiente principal */
.assistant-toggle-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* Cambia a tus colores: */
    background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%);
}
```

### Cambiar tamaño del botón:

```css
.assistant-toggle-btn {
    width: 60px;  /* Cambia a 70px para más grande */
    height: 60px; /* Cambia a 70px para más grande */
}
```

---

## 🐛 Troubleshooting

### Problema: Widget no aparece

**Solución**:
1. Verifica que Bootstrap Icons esté cargado
2. Revisa la consola del navegador (F12)
3. Asegúrate de que los archivos CSS y JS se cargan correctamente

### Problema: Error "Method not allowed"

**Solución**:
El endpoint `assistant.php` solo acepta POST. Verifica que el fetch use `method: 'POST'`.

### Problema: Respuestas genéricas

**Solución**:
- Agrega más palabras clave en los archivos JSON
- Baja el threshold de confianza en `IntentClassifier.php` (línea ~68):
  ```php
  if ($bestScore < 0.3) {  // Cambiar a 0.2 para ser más flexible
  ```

---

## 📈 Próximas Mejoras

- [ ] Historial de conversaciones persistente
- [ ] Integración con OpenAI para respuestas más naturales
- [ ] Modo voz (speech-to-text)
- [ ] Analytics dashboard
- [ ] Exportar conversación como PDF
- [ ] Widget embebido en otras páginas

---

## 📞 Soporte

Si tienes problemas:
1. Revisa los logs de Apache: `C:\xampp\apache\logs\error.log`
2. Abre consola del navegador (F12) y busca errores
3. Verifica permisos de archivos

---

**¡Listo!** Ahora tienes un asistente inteligente funcionando en Converza 🎉
