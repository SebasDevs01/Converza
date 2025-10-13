# 🎲 ¿CÓMO FUNCIONA DAILY SHUFFLE? (Explicación Simple)

## 🤔 ¿Qué es Daily Shuffle?

**Daily Shuffle es como Tinder pero para hacer amigos en Converza.**

Cada día, el sistema te muestra 10 personas nuevas que NO son tus amigos, para que puedas conocerlas y agregarlas.

---

## 📱 ¿CÓMO LO USO?

### Paso 1: Abre Daily Shuffle
Hay 2 formas:

**Opción A:** En la página principal (index)
- Mira la barra superior (navbar azul)
- Verás un botón que dice 🔀 "Shuffle"
- Haz click ahí

**Opción B:** En tu perfil
- Ve a tu perfil
- Verás un botón azul que dice 🔀 "Daily Shuffle"
- Haz click ahí

### Paso 2: Ve las personas del día
Se abre un panel lateral (como un cajón que sale desde la derecha) con:
- **10 personas aleatorias** que no son tus amigos
- Cada persona tiene una **foto**, **nombre** y **descripción**

### Paso 3: Decide qué hacer
Para cada persona tienes 2 opciones:

1. **Ver perfil** 👤 
   - Click aquí para ver su perfil completo
   - Ver sus fotos, publicaciones, amigos, etc.
   - Luego puedes regresar al shuffle

2. **Agregar** ➕
   - Click aquí para enviarle una solicitud de amistad
   - Automáticamente se marca como "Ya contactado"
   - La persona recibirá tu solicitud
   - Puedes seguir viendo los demás

### Paso 4: Espera al día siguiente
- Mañana tendrás **10 personas NUEVAS diferentes**
- El shuffle se renueva automáticamente cada día a medianoche

---

## 🎯 EJEMPLO PRÁCTICO

Imagina esto:

```
TÚ eres: Carlos
HOY es: Lunes 12 de Octubre

1. Abres Daily Shuffle
2. Ves estas 10 personas:
   - María
   - Juan
   - Ana
   - Pedro
   - Lucía
   - Diego
   - Carmen
   - Roberto
   - Isabel
   - Fernando

3. Decides:
   - Ver perfil de María → Te gusta, regresas y le das "Agregar"
   - Agregar a Juan directamente
   - Ver perfil de Ana → No te interesa, no haces nada
   - Agregar a Pedro
   - Saltas a Lucía (no haces nada)
   - Agregar a Diego
   - Saltas el resto

RESULTADO:
✅ Enviaste 4 solicitudes de amistad (María, Juan, Pedro, Diego)
📊 Ellos verán tu solicitud en su panel de notificaciones
⏰ Mañana (martes) verás 10 personas DIFERENTES
```

---

## 🔍 ¿QUÉ PERSONAS ME MUESTRA?

El sistema es inteligente y **NO te muestra**:

❌ **A ti mismo** (obvio jaja)  
❌ **Tus amigos actuales** (ya los tienes)  
❌ **Personas con solicitud pendiente** (ya les enviaste)  
❌ **Usuarios que bloqueaste**  
❌ **Usuarios que te bloquearon**  

Entonces te muestra:

✅ **Personas completamente nuevas**  
✅ **Usuarios activos de Converza**  
✅ **Gente que no conoces aún**  
✅ **Personas disponibles para conectar**  

---

## 📊 ¿CÓMO FUNCIONA POR DENTRO? (Técnico pero simple)

### Cada día a medianoche:
1. El sistema **borra** el shuffle de ayer
2. Cuando abres Daily Shuffle:
   - **Busca** usuarios que no sean tus amigos ni bloqueados
   - **Selecciona** 10 al azar (RAND())
   - **Guarda** la lista en la base de datos
   - **Te la muestra** en forma de tarjetas (cards)

### Cuando le das "Agregar":
1. **Envía** solicitud de amistad a esa persona
2. **Marca** en la base de datos que ya contactaste a esa persona
3. **Actualiza** visualmente la tarjeta (opacidad + check verde)
4. Esa persona **desaparece** del shuffle de mañana (porque ya contactaste)

### Al día siguiente:
1. El sistema **genera** otro shuffle nuevo
2. Las personas que contactaste **NO aparecen** de nuevo
3. Ves **10 personas DIFERENTES**

---

## 💡 CASOS DE USO REALES

### Caso 1: Usuario nuevo
```
Día 1: Te registras, abres shuffle, ves 10 personas, agregas 5
Día 2: Ves otras 10 diferentes, agregas 3
Día 3: Ves otras 10 diferentes, agregas 2
Total: ¡10 nuevos amigos en 3 días! 🎉
```

### Caso 2: Usuario activo
```
Cada mañana:
1. Abres Converza
2. Click en Shuffle
3. Ves las 10 del día
4. Agregas 1-2 que te interesan
5. Sigues con tu día normal
6. Vas construyendo tu red de amigos poco a poco
```

### Caso 3: Comunidad pequeña
```
Converza tiene 50 usuarios
Tú ya tienes 40 amigos
Daily Shuffle te muestra solo 10 (los disponibles)
Contactas a todos
Mensaje: "¡Eso es todo por hoy!"
Mañana no habrá más shuffle (hasta que se registren más usuarios)
```

---

## 🎨 ¿POR QUÉ ES AZUL AHORA?

Antes era morado, pero **lo cambiamos al azul de Converza** para que:
- ✨ Tenga el mismo estilo que el resto de la app
- 🎨 Sea consistente con los botones y navbar
- 💙 Se vea más profesional y unificado

**Colores usados:**
- Header: Azul gradiente `#0d6efd` → `#0b5ed7`
- Botón Agregar: Azul de Bootstrap `#0d6efd`
- Badge: Azul primario de Bootstrap

---

## ❓ PREGUNTAS FRECUENTES

### ¿Puedo ver el shuffle de ayer?
**No.** Solo se guarda el del día actual. Ayer se borra automáticamente.

### ¿Cuántas veces puedo abrir Daily Shuffle?
**Las que quieras.** Siempre verás las mismas 10 personas del día.

### ¿Si agrego a alguien y luego abro de nuevo el shuffle?
Esa persona **sigue apareciendo** pero con un check verde "✓ Ya contactado" para que sepas que ya le enviaste solicitud.

### ¿Puedo saltar personas sin agregar?
**Sí.** Solo no hagas click en "Agregar". No pasa nada.

### ¿Las personas saben que aparecieron en mi shuffle?
**No.** Es anónimo. Solo sabrán de ti si les envías solicitud.

### ¿Yo aparezco en el shuffle de otras personas?
**Sí.** Así como tú ves 10 personas, otras personas te verán a ti en su shuffle.

### ¿Qué pasa si alguien me agrega desde su shuffle?
Recibes una **solicitud de amistad normal** en tu panel de notificaciones. Puedes aceptar o rechazar.

### ¿Puedo filtrar por edad, género, ubicación?
**No por ahora.** Es 100% aleatorio. Esto podría agregarse en el futuro.

---

## 🚀 BENEFICIOS DE USAR DAILY SHUFFLE

1. **Descubres gente nueva** sin buscar activamente
2. **Amplías tu red** de amigos poco a poco
3. **Es divertido** como un juego diario
4. **No es intrusivo** (opcional, no obligatorio)
5. **Ayuda a la comunidad** a conectarse más
6. **Sencillo** de usar (2 clicks)

---

## 📈 ESTADÍSTICAS INTERESANTES

- 🎲 **10 usuarios nuevos** cada día
- 📅 **365 días** = hasta **3,650 personas nuevas** al año
- 🤝 Si agregas **3 por día** = **1,095 solicitudes** al año
- 📊 Si aceptan **50%** = **547 nuevos amigos** al año

¡Es una forma SÚPER efectiva de crecer tu red! 🎉

---

## 🎓 RESUMEN EN 3 LÍNEAS

1. **Daily Shuffle** te muestra **10 personas nuevas** cada día
2. Puedes **ver su perfil** o **enviarles solicitud** de amistad
3. Mañana verás **10 personas DIFERENTES**

---

## 🎯 ¡AHORA SÍ ENTIENDES!

**Daily Shuffle** = Conocer gente nueva de forma fácil y divertida

🔀 **Shuffle** = Mezclar (como barajar cartas)  
📅 **Daily** = Diario (cada día nuevo)  
👥 **10 usuarios** = Tu selección del día  
➕ **Agregar** = Enviar solicitud de amistad  

---

**¿Listo para probar?**

1. Abre Converza: `http://localhost/Converza/app/view/index.php`
2. Click en 🔀 "Shuffle"
3. ¡Descubre gente nueva!

---

**Fecha:** Octubre 12, 2025  
**Versión:** 1.0  
**Estado:** ✅ Funcionando perfectamente
