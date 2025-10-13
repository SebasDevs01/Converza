# 🔐 Usuarios y Contraseñas - Converza

## 📋 Información General

### ⚠️ Problema con las Contraseñas
Las contraseñas en Converza están **encriptadas con `password_hash()`** (bcrypt), por lo que **NO es posible ver las contraseñas originales** directamente en la base de datos.

---

## 🛠️ Soluciones Disponibles

### ✅ Opción 1: Verificar Usuarios Existentes

Ejecuta este script para ver todos los usuarios en el sistema:

```
http://localhost/Converza/verificar_usuarios.php
```

**Este script te mostrará:**
- Lista de todos los usuarios
- IDs, nombres de usuario, emails
- Script SQL para resetear contraseñas
- Información sobre usuarios de test (cami12, cami123)

---

### ✅ Opción 2: Generar SQL para Resetear Contraseñas

Ejecuta este script para obtener SQL listo para copiar:

```
http://localhost/Converza/resetear_passwords.php
```

**Este script te dará:**
- SQL para resetear todos los usuarios a `123456`
- SQL para resetear todos los usuarios a `password123`
- SQL para resetear usuarios específicos
- SQL para crear nuevos usuarios de prueba

---

## 🎯 Solución Rápida (3 pasos)

### Paso 1: Ejecuta el Script
```
http://localhost/Converza/verificar_usuarios.php
```

### Paso 2: Copia el SQL Generado
El script te mostrará algo como:
```sql
UPDATE usuarios SET contrasena = '$2y$10$abc123...xyz';
```

### Paso 3: Ejecútalo en phpMyAdmin
1. Abre phpMyAdmin
2. Selecciona base de datos `converza`
3. Ve a pestaña **SQL**
4. Pega el código
5. Click en **Continuar**

---

## 👥 Usuarios de Test Detectados

Según el test ejecutado, tienes estos usuarios:

| ID | Usuario | Rol |
|----|---------|-----|
| 2 | cami12 | user |
| 3 | cami123 | user |

### Para Resetear sus Contraseñas:

#### Opción A: Ambos a contraseña simple (123456)
```sql
UPDATE usuarios SET contrasena = '$2y$10$...' WHERE usuario IN ('cami12', 'cami123');
```
*(Ejecuta `resetear_passwords.php` para obtener el hash completo)*

#### Opción B: Manual en phpMyAdmin
```sql
-- Resetear cami12
UPDATE usuarios SET contrasena = '$2y$10$NEW_HASH_HERE' WHERE usuario = 'cami12';

-- Resetear cami123
UPDATE usuarios SET contrasena = '$2y$10$NEW_HASH_HERE' WHERE usuario = 'cami123';
```

---

## 🆕 Crear Nuevos Usuarios de Prueba

### Método 1: Registro Normal
```
http://localhost/Converza/app/view/registro.php
```
Llena el formulario y crea tu usuario.

### Método 2: SQL Directo
```sql
INSERT INTO usuarios (nombre, email, usuario, contrasena, fecha_reg, avatar, tipo)
VALUES (
    'Usuario Prueba',
    'prueba@test.com',
    'test_user',
    '$2y$10$HASH_DE_PASSWORD',  -- Usa resetear_passwords.php para generarlo
    NOW(),
    'defect.jpg',
    'user'
);
```

---

## 🔑 Contraseñas Recomendadas para Testing

| Contraseña | Uso | Seguridad |
|------------|-----|-----------|
| `123456` | Testing rápido | ⚠️ Muy débil |
| `password123` | Testing general | ⚠️ Débil |
| `Test123!` | Testing seguro | ✅ Media |

---

## 📝 Cómo Generar Hash de Contraseña

### En PHP:
```php
<?php
$password = '123456';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>
```

### Usando Script Incluido:
```
http://localhost/Converza/resetear_passwords.php
```
El script ya tiene hashes pre-generados para:
- `123456`
- `password123`

---

## 🚀 Inicio de Sesión

### URL:
```
http://localhost/Converza/app/view/iniciar-sesion.php
```

### Credenciales (después de resetear):

**Ejemplo con cami12:**
```
Usuario: cami12
Contraseña: 123456  (o la que hayas configurado)
```

**Ejemplo con cami123:**
```
Usuario: cami123
Contraseña: 123456  (o la que hayas configurado)
```

---

## ⚡ Solución Ultra-Rápida

### Si solo quieres probar el sistema YA:

1. **Ejecuta:**
   ```
   http://localhost/Converza/resetear_passwords.php
   ```

2. **Copia el primer SQL que dice:** "Opción 1: Resetear TODOS los usuarios"

3. **Pégalo en phpMyAdmin → SQL → Continuar**

4. **Inicia sesión con:**
   - Usuario: `cami12` (o cualquier usuario que exista)
   - Contraseña: `123456`

---

## 🔍 Verificar que Funcionó

### Después de resetear, verifica:

```sql
-- Ver todos los usuarios
SELECT id_use, usuario, nombre, email, tipo FROM usuarios;

-- Verificar que contrasena cambió (verás un hash diferente)
SELECT id_use, usuario, LEFT(contrasena, 20) as hash_preview FROM usuarios;
```

---

## 📚 Resumen de Scripts Creados

| Script | URL | Propósito |
|--------|-----|-----------|
| **verificar_usuarios.php** | `http://localhost/Converza/verificar_usuarios.php` | Ver usuarios existentes + SQL para reseteo |
| **resetear_passwords.php** | `http://localhost/Converza/resetear_passwords.php` | Generar SQL para resetear contraseñas |

---

## 🎉 Resultado Final

Después de usar estos scripts:

✅ Podrás ver todos los usuarios en el sistema  
✅ Tendrás SQL listo para resetear contraseñas  
✅ Podrás iniciar sesión con credenciales conocidas  
✅ Podrás probar el sistema de Coincidence Alerts  

---

**Fecha:** 13 de Octubre, 2025  
**Scripts creados:** `verificar_usuarios.php` y `resetear_passwords.php`  
**Estado:** ✅ Listos para usar
