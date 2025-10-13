# 🚀 GUÍA RÁPIDA - SISTEMA DE BADGES

## 📖 Uso Básico

### Incluir Badge de Mensajes
```php
<li class="nav-item">
    <?php include __DIR__.'/components/mensajes-badge.php'; ?>
</li>
```

### Incluir Badge de Solicitudes
```php
<li class="nav-item">
    <?php include __DIR__.'/components/solicitudes-badge.php'; ?>
</li>
```

### Incluir Badge de Notificaciones
```php
<li class="nav-item">
    <?php include __DIR__.'/components/notificaciones-widget.php'; ?>
</li>
```

---

## 🔌 APIs Disponibles

### 1. API de Mensajes
```
GET /Converza/app/presenters/mensajes_api.php?action=contar_no_leidos

Respuesta:
{
    "success": true,
    "total": 3
}
```

### 2. API de Solicitudes
```
GET /Converza/app/presenters/solicitudes_api.php?action=contar_pendientes

Respuesta:
{
    "success": true,
    "total": 2
}
```

### 3. API de Notificaciones
```
GET /Converza/app/presenters/notificaciones_api.php?action=obtener

Respuesta:
{
    "success": true,
    "notificaciones": [...],
    "total": 5
}
```

---

## 🎨 Personalización del Badge

### Cambiar Color
```css
.mensajes-badge {
    background: #28a745; /* Verde en lugar de rojo */
}
```

### Cambiar Velocidad de Animación
```css
.mensajes-badge {
    animation: pulse-badge 3s infinite; /* 3s en lugar de 2s */
}
```

### Desactivar Animación
```css
.mensajes-badge {
    animation: none; /* Sin animación */
}
```

### Cambiar Tamaño
```css
.mensajes-badge {
    min-width: 24px;  /* Más grande */
    height: 24px;
    font-size: 12px;
}
```

---

## ⚙️ Configuración Avanzada

### Cambiar Intervalo de Actualización

En `mensajes-badge.php`, línea ~54:
```javascript
// Actualizar cada 5 segundos en lugar de 10
this.intervalId = setInterval(() => this.actualizar(), 5000);
```

### Cambiar Formato del Contador

En `mensajes-badge.php`, línea ~78:
```javascript
actualizarBadge(total) {
    if (total > 0) {
        // Cambiar 99+ a 9+
        this.badge.textContent = total > 9 ? '9+' : total;
        this.badge.style.display = 'flex';
    } else {
        this.badge.style.display = 'none';
    }
}
```

---

## 🐛 Troubleshooting

### Badge no aparece
```javascript
// Abrir consola del navegador (F12)
console.log(window.mensajesBadge); // Debe existir
console.log(window.solicitudesBadge); // Debe existir
```

### API no responde
```javascript
// Probar API directamente
fetch('/Converza/app/presenters/mensajes_api.php?action=contar_no_leidos')
    .then(r => r.json())
    .then(console.log);
```

### Animación no funciona
```javascript
// Verificar que el CSS se cargó
const badge = document.getElementById('mensajes-badge');
console.log(getComputedStyle(badge).animation);
// Debe mostrar: "pulse-badge 2s infinite"
```

### Badge no se actualiza
```javascript
// Verificar intervalo
console.log(window.mensajesBadge.intervalId);
// Debe mostrar un número (ID del intervalo)

// Forzar actualización manual
window.mensajesBadge.actualizar();
```

---

## 🔧 Mantenimiento

### Agregar nuevo tipo de badge

1. **Crear componente:** `app/view/components/nuevo-badge.php`
2. **Crear API:** `app/presenters/nuevo_api.php`
3. **Incluir en navbar:** `<?php include 'components/nuevo-badge.php'; ?>`

### Modificar consulta SQL

Editar en `mensajes_api.php`:
```php
$stmtMensajes = $conexion->prepare("
    SELECT COUNT(*) as total 
    FROM chats 
    WHERE para = :usuario_id 
    AND leido = 0
    -- Agregar más condiciones aquí
");
```

---

## 📊 Monitoreo

### Ver estado de los badges en consola
```javascript
// En consola del navegador
console.table({
    'Mensajes': window.mensajesBadge?.badge?.textContent || '0',
    'Solicitudes': window.solicitudesBadge?.badge?.textContent || '0',
    'Notificaciones': window.notificacionesWidget?.badge?.textContent || '0'
});
```

### Verificar intervalos activos
```javascript
// Detener actualización de mensajes
clearInterval(window.mensajesBadge.intervalId);

// Reiniciar actualización
window.mensajesBadge.init();
```

---

## 🎯 Best Practices

### ✅ DO (Hacer)
- Usar componentes incluidos con `include`
- Dejar actualización cada 10s (buen balance)
- Mantener animación en 2s (no cansa la vista)
- Usar formato 99+ para números grandes

### ❌ DON'T (No Hacer)
- Duplicar código en cada página
- Actualizar muy frecuente (< 5s) → sobrecarga servidor
- Actualizar muy lento (> 30s) → parece que no funciona
- Modificar queries SQL sin probar
- Cambiar estructura de JSON de APIs

---

## 📚 Archivos de Referencia

```
📁 Componentes:
   → app/view/components/mensajes-badge.php
   → app/view/components/solicitudes-badge.php
   → app/view/components/notificaciones-widget.php

📁 APIs:
   → app/presenters/mensajes_api.php
   → app/presenters/solicitudes_api.php
   → app/presenters/notificaciones_api.php

📁 Páginas que los usan:
   → app/view/index.php
   → app/presenters/perfil.php
   → app/presenters/albumes.php

📁 Testing:
   → test_badges_api.html
```

---

## 🔗 Enlaces Útiles

- **Documentación completa:** `SISTEMA_BADGES_UNIFICADO.md`
- **Antes/Después visual:** `ANTES_DESPUES_VISUAL.md`
- **Checklist:** `CHECKLIST_BADGES.md`
- **Resumen ejecutivo:** `CAMBIOS_EJECUTIVO.md`

---

## 💡 Tips & Tricks

### Tip 1: Debug rápido
```javascript
// Ver todos los badges al mismo tiempo
[
    window.mensajesBadge,
    window.solicitudesBadge,
    window.notificacionesWidget
].forEach(badge => badge?.actualizar());
```

### Tip 2: Pausar actualizaciones
```javascript
// Pausar todas las actualizaciones
Object.values(window)
    .filter(v => v?.intervalId)
    .forEach(v => clearInterval(v.intervalId));
```

### Tip 3: Simular notificación
```javascript
// Mostrar badge temporalmente para probar
const badge = document.getElementById('mensajes-badge');
badge.textContent = '5';
badge.style.display = 'flex';
```

---

## 📞 Soporte

Si algo no funciona:

1. ✅ Revisa consola del navegador (F12)
2. ✅ Prueba APIs con `test_badges_api.html`
3. ✅ Verifica que sesión esté activa
4. ✅ Limpia caché del navegador
5. ✅ Revisa documentación completa

---

**Última actualización:** 13/10/2025  
**Versión:** 1.0.0  
**Estado:** ✅ Estable y Funcional
