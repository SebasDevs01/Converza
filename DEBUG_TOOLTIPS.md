# 🔍 Script de Debug para Tooltips - Converza

## 📋 Problema Reportado

**Usuario**: meliodas, vane15, santi12  
**Síntomas**:
1. ❌ Tooltips NO aparecen al hacer hover en contadores
2. ❌ Error al comentar: "Ocurrió un problema al guardar el comentario"
3. ⚠️ El comentario SÍ se guarda pero muestra error primero

---

## 🛠️ Script de Debug

### **Paso 1: Abrir Consola del Navegador**
1. Presiona `F12` o `Ctrl+Shift+I`
2. Ve a la pestaña **Console**
3. Copia y pega el siguiente código:

```javascript
// 🔍 DEBUG: Verificar tooltips
console.log('=== VERIFICANDO TOOLTIPS ===');

// 1. Buscar todos los contadores
const reactionCounters = document.querySelectorAll('.reaction-counter');
const commentCounters = document.querySelectorAll('.comment-counter');

console.log(`Encontrados ${reactionCounters.length} contadores de reacciones`);
console.log(`Encontrados ${commentCounters.length} contadores de comentarios`);

// 2. Verificar atributos data-tooltip
reactionCounters.forEach((counter, index) => {
    const tooltip = counter.getAttribute('data-tooltip');
    const computedDisplay = window.getComputedStyle(counter).display;
    console.log(`Reacción ${index + 1}:`, {
        id: counter.id,
        tooltip: tooltip,
        display: computedDisplay,
        visible: counter.offsetParent !== null
    });
});

commentCounters.forEach((counter, index) => {
    const tooltip = counter.getAttribute('data-tooltip');
    const computedDisplay = window.getComputedStyle(counter).display;
    console.log(`Comentario ${index + 1}:`, {
        id: counter.id,
        tooltip: tooltip,
        display: computedDisplay,
        visible: counter.offsetParent !== null
    });
});

// 3. Verificar CSS del pseudo-elemento ::after
const testCounter = reactionCounters[0];
if (testCounter) {
    const afterStyles = window.getComputedStyle(testCounter, '::after');
    console.log('CSS de ::after:', {
        content: afterStyles.content,
        position: afterStyles.position,
        display: afterStyles.display,
        zIndex: afterStyles.zIndex
    });
}

// 4. Test manual de hover
console.log('Haz hover sobre un contador ahora...');
reactionCounters.forEach(counter => {
    counter.addEventListener('mouseenter', function() {
        console.log('🖱️ HOVER detectado en:', {
            id: this.id,
            tooltip: this.getAttribute('data-tooltip'),
            classList: Array.from(this.classList)
        });
    });
});
```

---

## 🔧 Posibles Problemas y Soluciones

### **Problema 1: Tooltips No Aparecen**

#### Causa Posible A: CSS no se está aplicando
```javascript
// Verificar si el CSS está cargado
const styles = Array.from(document.styleSheets)
    .flatMap(sheet => {
        try {
            return Array.from(sheet.cssRules || []);
        } catch(e) {
            return [];
        }
    })
    .filter(rule => rule.selectorText && rule.selectorText.includes('data-tooltip'));

console.log('Reglas CSS de tooltips encontradas:', styles.length);
styles.forEach(rule => console.log(rule.selectorText, rule.style.cssText));
```

**Solución**: Si no encuentra reglas CSS, el problema es que el CSS no se está cargando.

---

#### Causa Posible B: Atributo `data-tooltip` vacío
```javascript
// Verificar contenido de data-tooltip
document.querySelectorAll('.reaction-counter').forEach(counter => {
    const tooltip = counter.getAttribute('data-tooltip');
    if (!tooltip || tooltip.trim() === '' || tooltip === 'Sin reacciones') {
        console.warn('⚠️ Tooltip vacío o sin datos:', counter.id);
    }
});
```

**Solución**: Verificar que `loadReactionsData()` se está ejecutando correctamente.

---

#### Causa Posible C: `z-index` muy bajo
```javascript
// Verificar z-index del tooltip
const counter = document.querySelector('.reaction-counter');
const afterStyles = window.getComputedStyle(counter, '::after');
console.log('z-index del tooltip:', afterStyles.zIndex);
```

**Solución**: El CSS ya tiene `z-index: 9999 !important`, debería ser suficiente.

---

### **Problema 2: Error al Comentar**

#### Verificar respuesta del servidor:
```javascript
// Interceptar fetch de comentarios
const originalFetch = window.fetch;
window.fetch = function(...args) {
    console.log('📤 FETCH interceptado:', args[0]);
    return originalFetch.apply(this, args).then(response => {
        console.log('📥 Respuesta recibida:', response.status, response.statusText);
        return response.clone().text().then(text => {
            console.log('📄 Cuerpo de respuesta:', text);
            return response;
        });
    });
};
```

**Resultado esperado**:
```json
{
    "status": "success",
    "message": "Tu comentario ha sido publicado.",
    "comentario": {
        "id": 123,
        "usuario": "meliodas",
        "avatar": "avatar.jpg",
        "comentario": "holi",
        "fecha": "2025-10-13 10:48:43"
    }
}
```

**Si retorna**:
```json
{
    "status": "error",
    "message": "Ocurrió un problema al guardar el comentario. Por favor, inténtalo de nuevo."
}
```

**Causa**: Error de PDO en `agregarcomentario.php` (verificar logs del servidor).

---

## 🚨 Errores Comunes

### 1. **Funciones no definidas**
```javascript
// Verificar si loadReactionsData existe
if (typeof loadReactionsData === 'undefined') {
    console.error('❌ loadReactionsData NO está definida!');
} else {
    console.log('✅ loadReactionsData está definida');
}
```

### 2. **Fetch falla silenciosamente**
```javascript
// Test manual de fetch
fetch('/Converza/app/presenters/get_reactions.php?postId=1')
    .then(r => r.json())
    .then(data => console.log('Datos de reacciones:', data))
    .catch(err => console.error('Error en fetch:', err));

fetch('/Converza/app/presenters/get_comentarios.php?postId=1')
    .then(r => r.json())
    .then(data => console.log('Datos de comentarios:', data))
    .catch(err => console.error('Error en fetch:', err));
```

### 3. **CORS o ruta incorrecta**
```javascript
// Verificar URL actual
console.log('URL actual:', window.location.href);
console.log('Path actual:', window.location.pathname);

// Construir URL completa
const baseUrl = window.location.origin + '/Converza/app/presenters/';
console.log('Base URL para APIs:', baseUrl);
```

---

## 🔍 Inspección Visual

### **Verificar elemento en DevTools**:
1. Clic derecho en el contador `(1)` o `(2)`
2. Seleccionar **Inspeccionar**
3. Buscar en HTML:
```html
<span class="reaction-counter ms-2" 
      id="reaction_counter_123" 
      data-tooltip="❤️ vane15
😂 meliodas"
      style="display: inline-block; cursor: pointer;">
    (2)
</span>
```

4. Verificar en **Computed Styles** que `::after` tenga:
   - `content`: El texto del tooltip
   - `position`: `absolute`
   - `z-index`: `9999`
   - `display`: `block`

---

## 📊 Resultados Esperados

### **Tooltip de Reacciones**:
```
Hover en (2) →
┌────────────────┐
│ ❤️ vane15      │
│ 😂 meliodas    │
└────────────────┘
```

### **Tooltip de Comentarios**:
```
Hover en (3) →
┌────────────────┐
│ 💬 vane15      │
│ 💬 santi12     │
│ 💬 meliodas    │
└────────────────┘
```

---

## 🎯 Checklist de Verificación

- [ ] Contadores tienen clase `.reaction-counter` o `.comment-counter`
- [ ] Contadores tienen atributo `data-tooltip` con contenido
- [ ] CSS de `::after` está cargado
- [ ] `z-index` del tooltip es `9999`
- [ ] `display` del contador es `inline-block`
- [ ] Fetch a APIs retorna datos correctos
- [ ] `loadReactionsData()` está definida
- [ ] Event listener de hover está activo

---

## 💡 Solución Rápida

Si los tooltips NO funcionan después de verificar todo:

```javascript
// FORZAR tooltips manualmente
document.querySelectorAll('.reaction-counter, .comment-counter').forEach(counter => {
    counter.style.position = 'relative';
    counter.style.display = 'inline-block';
    
    // Agregar tooltip visible al hacer hover
    counter.addEventListener('mouseenter', function(e) {
        const tooltipText = this.getAttribute('data-tooltip');
        if (!tooltipText || tooltipText === 'Sin reacciones' || tooltipText === 'Sin comentarios') {
            return;
        }
        
        // Crear tooltip manual
        const tooltip = document.createElement('div');
        tooltip.className = 'manual-tooltip';
        tooltip.textContent = tooltipText;
        tooltip.style.cssText = `
            position: absolute;
            top: 50%;
            left: 100%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: pre;
            z-index: 9999;
            margin-left: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            pointer-events: none;
        `;
        this.appendChild(tooltip);
    });
    
    counter.addEventListener('mouseleave', function(e) {
        const tooltip = this.querySelector('.manual-tooltip');
        if (tooltip) tooltip.remove();
    });
});

console.log('✅ Tooltips manuales activados');
```

---

**Fecha**: 2025-10-13  
**Status**: 🔍 EN INVESTIGACIÓN
