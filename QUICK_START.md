# 🚀 INICIO RÁPIDO - DAILY SHUFFLE

## ⚡ 3 Pasos para Activar

### 1️⃣ SETUP (2 minutos)
Abre en tu navegador:
```
http://localhost/Converza/setup_daily_shuffle.php
```
✅ Esto creará la tabla `daily_shuffle` en tu base de datos

---

### 2️⃣ TEST (1 minuto)
Verifica que todo funcione:
```
http://localhost/Converza/test_daily_shuffle.php
```
✅ Deberías ver 7 tests en verde

---

### 3️⃣ ¡ÚSALO! (Instantáneo)
Abre Converza:
```
http://localhost/Converza/app/view/index.php
```
1. Inicia sesión
2. Click en 🔀 **"Shuffle"** en la navbar
3. ¡Descubre nuevas personas!

---

## 📱 ¿Cómo se usa?

### Para usuarios:
1. **Click en el icono Shuffle** 🔀 en la barra superior
2. **Se abre un panel** con 10 usuarios nuevos
3. **Cada usuario tiene 2 opciones:**
   - 👤 **Ver perfil** → Abre su perfil completo
   - ➕ **Agregar** → Envía solicitud de amistad
4. **Cuando agregas a alguien:**
   - ✅ Se marca como "Ya contactado"
   - 📨 Recibe tu solicitud de amistad
5. **Mañana:** Nuevos 10 usuarios automáticamente

---

## 🎯 Características Clave

✅ **10 usuarios aleatorios** cada día  
✅ **Filtros inteligentes** (no muestra amigos ni bloqueados)  
✅ **Interfaz tipo Tinder** (cards atractivas)  
✅ **Totalmente responsive** (móvil y desktop)  
✅ **Seguimiento de contactados**  
✅ **Limpieza automática** de datos antiguos  

---

## 🐛 Si algo no funciona:

### La tabla no existe
```
Ejecuta: http://localhost/Converza/setup_daily_shuffle.php
```

### No aparece el botón Shuffle
```
Verifica que hayas iniciado sesión
Refresca la página (Ctrl + F5)
```

### Error: Column 'descripcion' not found ✅ RESUELTO
```
Este error ya fue corregido en el código.
Si persiste, lee: FIX_DESCRIPCION_ERROR.md
```

### No se muestran usuarios
```
1. Verifica que hay usuarios en la BD
2. Asegúrate de no tener todos como amigos
3. Revisa la consola del navegador (F12)
```

### Error de permisos
```
Verifica que estés logueado
Revisa que tu sesión no haya expirado
```

---

## 📚 Documentación Completa

- **README técnico:** `DAILY_SHUFFLE_README.md`
- **Resumen completo:** `DAILY_SHUFFLE_SUMMARY.md`
- **Preview visual:** `daily_shuffle_preview.html`

---

## 🔗 Links Útiles

| Acción | URL |
|--------|-----|
| **Setup** | http://localhost/Converza/setup_daily_shuffle.php |
| **Tests** | http://localhost/Converza/test_daily_shuffle.php |
| **Preview** | http://localhost/Converza/daily_shuffle_preview.html |
| **App** | http://localhost/Converza/app/view/index.php |

---

## 💡 Tips

### Para probar con datos ficticios:
1. Crea varios usuarios de prueba
2. Inicia sesión con uno
3. Abre Daily Shuffle
4. ¡Los otros usuarios aparecerán!

### Para ver estadísticas:
```sql
SELECT 
    COUNT(*) as total_hoy,
    SUM(ya_contactado) as contactados_hoy
FROM daily_shuffle
WHERE fecha_shuffle = CURDATE();
```

---

## ✨ ¡Listo!

**Ya tienes Daily Shuffle funcionando en Converza** 🎉

Si necesitas ayuda:
- Revisa `DAILY_SHUFFLE_README.md`
- Ejecuta `test_daily_shuffle.php`
- Revisa la consola del navegador (F12)

---

**Desarrollado para Converza**  
Octubre 12, 2025
