# 🎮 Usuario de Prueba - Tienda de Karma

## 🔐 Credenciales de Acceso

```
👤 Usuario: testingtienda
🔑 Contraseña: Testing2025!
💎 Karma disponible: 50,000 puntos
📧 Email: testingtienda@converza.test
```

---

## 🌐 URLs de Acceso

- **Login:** `http://localhost/Converza/`
- **Tienda:** `http://localhost/Converza/karma_tienda.php`
- **Perfil:** `http://localhost/Converza/perfil.php?usuario=testingtienda`

---

## 🛍️ Recompensas Disponibles para Probar

### 🖼️ Marcos de Perfil (5 marcos)
- **Marco Dorado** - 50 karma
- **Marco Neón** - 100 karma
- **Marco Arcoíris** - 150 karma
- **Marco Fuego** - 200 karma
- **Marco Estelar** - 200 karma

### 🎨 Temas Personalizados (9 temas)
- **Tema Nocturno** - 100 karma
- **Tema Bosque** - 150 karma
- **Tema Océano** - 150 karma
- **Tema Atardecer** - 200 karma
- **Tema Aurora** - 250 karma
- **Tema Galáctico** - 300 karma
- **Tema Cyberpunk** - 350 karma
- **Tema Místico** - 400 karma
- **Tema Real** - 500 karma

### 🏆 Insignias (6 insignias)
- **Insignia Novato** - 10 karma
- **Insignia Amistoso** - 50 karma
- **Insignia Popular** - 100 karma
- **Insignia Estrella** - 150 karma
- **Insignia Leyenda** - 200 karma
- **Insignia Líder** - 200 karma

### ⭐ Iconos Especiales (6 iconos)
- **Icono Estrella** - 50 karma
- **Icono Corazón** - 75 karma
- **Icono Rayo** - 100 karma
- **Icono Corona** - 125 karma
- **Icono Fuego** - 150 karma
- **Icono Diamante** - 150 karma

### 🌈 Colores de Nombre (9 colores)
- **Nombre Dorado** - 30 karma
- **Nombre Rosa** - 50 karma
- **Nombre Verde** - 50 karma
- **Nombre Morado** - 75 karma
- **Nombre Azul Cielo** - 75 karma
- **Nombre Naranja** - 100 karma
- **Nombre Arcoíris** - 100 karma
- **Nombre Fuego** - 100 karma
- **Nombre Neón** - 100 karma

### 🎁 Packs de Stickers (3 packs)
- **Pack Emojis Clásicos** - 100 karma (10 stickers)
- **Pack Reacciones** - 200 karma (15 stickers)
- **Pack Animales** - 300 karma (20 stickers)

---

## 📊 Resumen

- **Total de recompensas:** 29 únicas
- **Karma total disponible:** 50,000 puntos
- **Karma necesario para todo:** ~4,000 puntos (aproximado)
- **Sobran:** ~46,000 puntos para probar múltiples veces

---

## ✅ Funciones a Probar

### 1. Sistema de Desbloqueo
- ✓ Comprar recompensas con karma
- ✓ Verificar que los puntos se descuentan correctamente
- ✓ Verificar que aparecen como desbloqueadas

### 2. Sistema de Equipar/Desequipar
- ✓ Equipar marcos (reemplaza el anterior)
- ✓ Equipar temas (reemplaza el anterior)
- ✓ Equipar insignias (acumula hasta 3)
- ✓ Equipar iconos (acumula múltiples)
- ✓ Equipar colores (reemplaza el anterior)
- ✓ Equipar stickers (acumula por packs)
- ✓ Desequipar cualquier recompensa

### 3. Vista Previa de Recompensas
- ✓ Preview de marcos con animación
- ✓ Preview de temas con gradientes
- ✓ Preview de insignias con efectos
- ✓ Preview de iconos con brillo
- ✓ Preview de colores con efectos de texto
- ✓ Preview de stickers ampliados

### 4. Visualización en Perfil
- ✓ Ver marco aplicado en avatar
- ✓ Ver tema aplicado en fondo
- ✓ Ver insignias centradas
- ✓ Ver icono junto al nombre
- ✓ Ver color del nombre aplicado
- ✓ Ver stickers en mensajes (cuando implementes chat)

### 5. Persistencia de Datos
- ✓ Recompensas se mantienen tras cerrar sesión
- ✓ Karma se actualiza correctamente
- ✓ Estado equipado/desequipado persiste
- ✓ Historial de compras en karma_social

---

## 🐛 Casos de Prueba Específicos

1. **Comprar con karma insuficiente** → Debe mostrar error
2. **Comprar recompensa ya desbloqueada** → Debe estar deshabilitado
3. **Equipar múltiples insignias** → Máximo 3
4. **Cambiar de tema** → El anterior se desequipa automáticamente
5. **Cerrar sesión y volver** → Todo debe persistir
6. **Ver perfil de otro usuario** → Debe ver tus recompensas equipadas

---

## 📝 Notas Importantes

- El usuario está verificado y listo para usar
- Tiene permisos normales (tipo: 'user')
- Avatar por defecto: `defect.jpg`
- Todas las compras se registran en `karma_social` con `tipo_accion='compra_tienda'`
- Los puntos se descuentan como valores negativos en la tabla

---

## 🔧 Scripts de Utilidad

Si necesitas resetear el usuario:

```sql
-- Eliminar todas las recompensas del usuario
DELETE FROM usuario_recompensas WHERE usuario_id = (SELECT id_use FROM usuarios WHERE usuario = 'testingtienda');

-- Restablecer karma a 50,000
DELETE FROM karma_social WHERE usuario_id = (SELECT id_use FROM usuarios WHERE usuario = 'testingtienda');
INSERT INTO karma_social (usuario_id, tipo_accion, puntos, descripcion) 
VALUES ((SELECT id_use FROM usuarios WHERE usuario = 'testingtienda'), 'regalo_admin', 50000, 'Reset de puntos');
```

---

¡Disfruta probando todas las funcionalidades de la tienda! 🎉
