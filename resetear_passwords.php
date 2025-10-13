<?php
/**
 * Generador de SQL para resetear contraseñas
 * Ejecuta este script y copia el SQL en phpMyAdmin
 */

// Generar hash para contraseña simple: "123456"
$password_simple = password_hash('123456', PASSWORD_DEFAULT);

// Generar hash para contraseña: "password123"
$password_medium = password_hash('password123', PASSWORD_DEFAULT);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Resetear Contraseñas - Converza</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        pre { background: #f8f9fa; padding: 20px; border-radius: 5px; overflow-x: auto; border: 1px solid #ddd; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; color: #c7254e; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>🔐 Resetear Contraseñas de Usuarios - Converza</h2>
        
        <div class='info'>
            <strong>📋 Instrucciones:</strong>
            <ol>
                <li>Copia el SQL que necesites (Opción 1 o 2)</li>
                <li>Ve a <strong>phpMyAdmin</strong></li>
                <li>Selecciona la base de datos <code>converza</code></li>
                <li>Ve a la pestaña <strong>SQL</strong></li>
                <li>Pega el código y haz clic en <strong>Continuar</strong></li>
            </ol>
        </div>

        <h3>Opción 1: Resetear TODOS los usuarios a contraseña simple</h3>
        <div class='warning'>
            <strong>Contraseña que se asignará:</strong> <code>123456</code>
        </div>
        <pre>-- Resetear TODOS los usuarios a contraseña: 123456
UPDATE usuarios SET contrasena = '{$password_simple}';

-- Verificar cambios
SELECT id_use, usuario, nombre, email FROM usuarios;</pre>

        <h3>Opción 2: Resetear TODOS los usuarios a contraseña media</h3>
        <div class='warning'>
            <strong>Contraseña que se asignará:</strong> <code>password123</code>
        </div>
        <pre>-- Resetear TODOS los usuarios a contraseña: password123
UPDATE usuarios SET contrasena = '{$password_medium}';

-- Verificar cambios
SELECT id_use, usuario, nombre, email FROM usuarios;</pre>

        <h3>Opción 3: Resetear usuario específico (cami12)</h3>
        <div class='warning'>
            <strong>Contraseña:</strong> <code>123456</code>
        </div>
        <pre>-- Resetear solo usuario: cami12
UPDATE usuarios SET contrasena = '{$password_simple}' WHERE usuario = 'cami12';</pre>

        <h3>Opción 4: Resetear usuario específico (cami123)</h3>
        <div class='warning'>
            <strong>Contraseña:</strong> <code>123456</code>
        </div>
        <pre>-- Resetear solo usuario: cami123
UPDATE usuarios SET contrasena = '{$password_simple}' WHERE usuario = 'cami123';</pre>

        <h3>Opción 5: Crear usuario de prueba nuevo</h3>
        <div class='info'>
            <strong>Datos del nuevo usuario:</strong><br>
            Usuario: <code>test_user</code><br>
            Contraseña: <code>123456</code><br>
            Email: <code>test@converza.com</code>
        </div>
        <pre>-- Crear nuevo usuario de prueba
INSERT INTO usuarios (nombre, email, usuario, contrasena, fecha_reg, avatar, tipo)
VALUES (
    'Usuario de Prueba',
    'test@converza.com',
    'test_user',
    '{$password_simple}',
    NOW(),
    'defect.jpg',
    'user'
);</pre>

        <div class='info'>
            <h3>🎯 Después de ejecutar el SQL:</h3>
            <p><strong>Para iniciar sesión en Converza:</strong></p>
            <ul>
                <li>Ve a: <code>http://localhost/Converza/app/view/iniciar-sesion.php</code></li>
                <li><strong>Usuario:</strong> El nombre de usuario (ej: cami12, cami123, test_user)</li>
                <li><strong>Contraseña:</strong> Según el SQL que ejecutaste (123456 o password123)</li>
            </ul>
        </div>

        <div class='warning'>
            <h3>⚠️ Verificar usuarios existentes</h3>
            <p>Si no sabes qué usuarios tienes, ejecuta primero:</p>
            <a href='verificar_usuarios.php' target='_blank' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>
                Ver Usuarios Existentes
            </a>
        </div>
    </div>
</body>
</html>";
?>
