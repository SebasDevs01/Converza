# ✅ CHECKLIST DE VERIFICACIÓN - SISTEMA DE PERSONALIZACIÓN

## 🎯 ANTES DE EMPEZAR

Abre este checklist y ve marcando ✅ cada punto que pruebes.

---

## 📋 FASE 1: VERIFICAR ARCHIVOS

### Archivos Backend (PHP):
- [ ] `app/models/recompensas-aplicar-helper.php` existe
- [ ] `app/presenters/karma_tienda.php` existe
- [ ] `app/view/index.php` tiene `require_once recompensas-aplicar-helper.php`
- [ ] `app/presenters/perfil.php` tiene soporte de temas
- [ ] `app/presenters/albumes.php` tiene soporte de temas
- [ ] `app/presenters/chat.php` tiene soporte de temas
- [ ] `app/view/admin.php` tiene soporte de temas

### Archivos Frontend (CSS):
- [ ] `public/css/karma-recompensas.css` existe (356 líneas)
- [ ] CSS tiene los 5 marcos definidos
- [ ] CSS tiene las 15+ animaciones @keyframes

### Archivos de Documentación:
- [ ] `SISTEMA_PERSONALIZACION_COMPLETO.md` existe
- [ ] `demo_personalizacion.html` existe
- [ ] `CHECKLIST_PERSONALIZACION.md` existe (este archivo)

---

## 🗄️ FASE 2: VERIFICAR BASE DE DATOS

### Tablas requeridas:
```sql
-- Ejecuta estos comandos en phpMyAdmin o tu cliente MySQL:

-- 1. Verificar tabla karma_recompensas
DESCRIBE karma_recompensas;
-- Debe tener: id, nombre, descripcion, tipo, karma_requerido, icono, activo

-- 2. Verificar tabla usuario_recompensas
DESCRIBE usuario_recompensas;
-- Debe tener: id, usuario_id, recompensa_id, equipada, fecha_desbloqueo

-- 3. Verificar que hay recompensas creadas
SELECT * FROM karma_recompensas;
-- Debe retornar al menos 9 recompensas (4 temas + 5 marcos)

-- 4. Verificar tu karma actual
SELECT karma_total, nivel FROM karma_usuarios WHERE usuario_id = TU_ID;
-- Reemplaza TU_ID con tu ID de usuario
```

**Marcas aquí:**
- [ ] Tabla `karma_recompensas` existe
- [ ] Tabla `usuario_recompensas` existe
- [ ] Hay al menos 4 temas en la BD
- [ ] Hay al menos 5 marcos en la BD
- [ ] Mi karma actual es visible

---

## 🧪 FASE 3: PRUEBAS FUNCIONALES

### A. Probar Tienda de Karma

1. **Acceder a la tienda:**
   ```
   URL: http://localhost/Converza/app/presenters/karma_tienda.php
   ```
   - [ ] La página carga sin errores
   - [ ] Veo mi karma total arriba
   - [ ] Veo mi nivel actual
   - [ ] Hay secciones de Temas y Marcos

2. **Ver estado de recompensas:**
   - [ ] Veo "🔒 Bloqueada" en recompensas sin karma
   - [ ] Veo "Te faltan X puntos" en bloqueadas
   - [ ] Veo botón "Desbloquear" si tengo karma suficiente

### B. Desbloquear Recompensa

**Si tienes < 50 karma, primero gana karma:**
   - [ ] Haz comentarios positivos
   - [ ] Reacciona a publicaciones
   - [ ] Espera a que el badge se actualice

**Con 50+ karma:**
   - [ ] Click en "Desbloquear" en "Tema Oscuro Premium"
   - [ ] Aparece mensaje de éxito
   - [ ] Cambia a "✓ Desbloqueada"
   - [ ] Aparece botón "Equipar"

### C. Equipar Recompensa

- [ ] Click en botón "Equipar"
- [ ] Cambia a "✓ Equipada" (color diferente)
- [ ] La página NO se recarga (es AJAX)

### D. Verificar Tema Aplicado

**Navega a estas páginas y verifica que el tema se ve:**
- [ ] `app/view/index.php` (Feed principal)
- [ ] `app/presenters/perfil.php?id=TU_ID` (Tu perfil)
- [ ] `app/presenters/albumes.php?id=TU_ID` (Álbumes)
- [ ] `app/presenters/chat.php` (Chat)
- [ ] `app/view/admin.php` (Solo si eres admin)

**El tema debe:**
- [ ] Cambiar el fondo de la página
- [ ] Cambiar colores de cards
- [ ] Verse consistente en todas las páginas

---

## 🖼️ FASE 4: PROBAR MARCOS DE AVATAR

### Desbloquear Marco:

**Con 100+ karma:**
- [ ] Ve a tienda
- [ ] Busca sección "Marcos"
- [ ] Click "Desbloquear" en "Marco Dorado"
- [ ] Click "Equipar"

### Verificar Marco Visible:

- [ ] Ve a tu perfil
- [ ] Tu avatar tiene borde dorado animado
- [ ] El marco tiene efecto de pulso
- [ ] Aparece en comentarios que hagas
- [ ] Aparece en publicaciones

---

## 🏅 FASE 5: PROBAR INSIGNIAS

### Verificar Insignias Automáticas:

- [ ] Ve a tu perfil
- [ ] Debajo de tu nombre ves insignias
- [ ] Las insignias corresponden a tu nivel
- [ ] Tienen colores según nivel (verde, azul, púrpura, etc.)
- [ ] Hover muestra efecto de elevación

### Subir de Nivel:

**Para probar rápido (solo testing):**
```sql
-- Agregar karma temporal
UPDATE karma_usuarios SET karma_total = 300 WHERE usuario_id = TU_ID;
```

- [ ] Recarga tu perfil
- [ ] Las insignias cambian según nuevo nivel
- [ ] Colores más avanzados aparecen

---

## 🎨 FASE 6: PROBAR TODOS LOS TEMAS

### Tema 1: Oscuro Premium (50 karma)
- [ ] Desbloqueado
- [ ] Equipado
- [ ] Fondo oscuro (#1a1a2e)
- [ ] Cards semi-transparentes
- [ ] Texto claro visible

### Tema 2: Galaxy (100 karma)
- [ ] Desbloqueado
- [ ] Equipado
- [ ] Fondo negro con estrellas
- [ ] Animación de estrellas funciona
- [ ] Efecto vidrio en cards

### Tema 3: Sunset (150 karma)
- [ ] Desbloqueado
- [ ] Equipado
- [ ] Degradado naranja/rosa/amarillo
- [ ] Cards con sombras coloridas
- [ ] Ambiente cálido

### Tema 4: Neon (200 karma)
- [ ] Desbloqueado
- [ ] Equipado
- [ ] Fondo negro profundo
- [ ] Bordes cyan brillantes
- [ ] Glow effects visibles
- [ ] Títulos con text-shadow

---

## 🖼️ FASE 7: PROBAR TODOS LOS MARCOS

### Marco 1: Dorado (100 karma)
- [ ] Desbloqueado y equipado
- [ ] Borde dorado con degradado
- [ ] Animación de pulso cada 3s
- [ ] Box-shadow dorado visible

### Marco 2: Diamante (200 karma)
- [ ] Desbloqueado y equipado
- [ ] Borde azul cristalino
- [ ] Partícula ✨ arriba-derecha
- [ ] Efecto de destello

### Marco 3: Fuego (300 karma)
- [ ] Desbloqueado y equipado
- [ ] Borde rojo/naranja
- [ ] Emojis 🔥 animados
- [ ] Efecto de llamas ondulantes

### Marco 4: Arcoíris (400 karma)
- [ ] Desbloqueado y equipado
- [ ] Borde con 7 colores
- [ ] Rotación continua de colores
- [ ] Animación suave

### Marco 5: Legendario (500 karma)
- [ ] Desbloqueado y equipado
- [ ] Borde dorado con múltiples sombras
- [ ] Corona 👑 flotando arriba
- [ ] Partículas ✨ animadas
- [ ] Pulso épico continuo

---

## 📱 FASE 8: PROBAR RESPONSIVE

### En Móvil (o emulador):
- [ ] Tienda se ve correcta
- [ ] Marcos no se rompen
- [ ] Insignias se adaptan
- [ ] Temas funcionan igual
- [ ] Botones son clickeables

### Tamaños a probar:
- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Móvil (375x667)

---

## 🔄 FASE 9: PROBAR CAMBIO DE RECOMPENSAS

### Cambiar de Tema:
- [ ] Tema A equipado
- [ ] Click "Equipar" en Tema B
- [ ] Tema A cambia a "Equipar" (desequipado)
- [ ] Tema B cambia a "✓ Equipada"
- [ ] Recargo página → Tema B activo

### Cambiar de Marco:
- [ ] Marco A equipado
- [ ] Click "Equipar" en Marco B
- [ ] Marco A se desequipa automáticamente
- [ ] Marco B se equipa
- [ ] Solo un marco activo a la vez

---

## ⚡ FASE 10: PROBAR RENDIMIENTO

### Tiempos de Carga:
- [ ] Tienda carga en < 2 segundos
- [ ] Desbloqueo responde en < 1 segundo
- [ ] Equipar responde instantáneamente
- [ ] Cambio de página mantiene tema sin delay

### Animaciones:
- [ ] No hay lag en animaciones
- [ ] Pulso de marcos es suave
- [ ] Hover de insignias es fluido
- [ ] Rotación de arcoíris no traba

---

## 🐛 FASE 11: VERIFICAR ERRORES COMUNES

### Errores a revisar:

1. **Console del navegador (F12):**
   - [ ] No hay errores 404
   - [ ] No hay errores JavaScript
   - [ ] No hay errores CSS

2. **Logs de PHP:**
   - [ ] No hay warnings en error_log
   - [ ] No hay notices de variables undefined

3. **Base de Datos:**
   - [ ] Registros se crean correctamente
   - [ ] Foreign keys funcionan
   - [ ] No hay duplicados

### Casos extremos:

- [ ] ¿Qué pasa si desbloqueo sin karma? → Debe fallar
- [ ] ¿Qué pasa si equipo sin desbloquear? → Debe fallar
- [ ] ¿Qué pasa si dos usuarios equipan el mismo tema? → OK
- [ ] ¿Qué pasa si cierro sesión? → Pierde personalización (OK)
- [ ] ¿Qué pasa si otro usuario ve mi perfil? → Ve MI tema en MI perfil

---

## 🎉 FASE 12: DEMO COMPLETA

### Archivo Demo HTML:
```
URL: http://localhost/Converza/demo_personalizacion.html
```

- [ ] La página demo carga
- [ ] Veo los 5 marcos animados
- [ ] Veo las 6 insignias con colores
- [ ] Veo los 4 temas en preview
- [ ] Estadísticas son correctas (4, 5, 6, 15+)
- [ ] Links a tienda funcionan

---

## 📊 RESUMEN FINAL

### Contadores:

**Total de checks posibles:** ~150

**Mis checks completados:** _____

**Porcentaje:** _____ %

### Estado del Sistema:

- [ ] ✅ **100% Funcional** - Todo funciona perfectamente
- [ ] ⚠️ **Funcional con Bugs** - Funciona pero hay problemas menores
- [ ] ❌ **No Funcional** - Hay problemas graves

### Notas adicionales:

```
Escribe aquí cualquier bug encontrado o sugerencia:

1. 

2. 

3. 

```

---

## 🚀 PRÓXIMOS PASOS

Una vez que TODO esté ✅:

1. **Documentar bugs encontrados** (si hay)
2. **Ajustar lo que sea necesario**
3. **Hacer backup de la BD**
4. **Celebrar** 🎉 - ¡Sistema completo!

---

## 📞 SOPORTE

Si algo no funciona:

1. **Verificar PHP errors:**
   ```
   tail -f /xampp/logs/php_error_log
   ```

2. **Verificar tablas BD:**
   ```sql
   SHOW TABLES LIKE '%recompensas%';
   ```

3. **Verificar archivos:**
   ```
   Buscar "recompensas-aplicar-helper" en workspace
   ```

4. **Console del navegador:**
   ```
   F12 → Console → Buscar errores
   ```

---

## ✅ FIRMA DE COMPLETADO

**Sistema verificado por:** _________________

**Fecha:** _________________

**Estado:** ⭐⭐⭐⭐⭐

**Comentarios:**
```



```

---

*Checklist v1.0 - Sistema de Personalización Converza*
