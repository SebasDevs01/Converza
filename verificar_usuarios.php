<?php
/**
 * Script para verificar usuarios existentes en Converza
 * Muestra información de usuarios de prueba
 */

require_once __DIR__ . '/app/models/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificar Usuarios - Converza</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .user-card {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .user-info {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
        }
        .password-info {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            margin: 30px 0;
            border-radius: 10px;
        }
        .warning {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        code {
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #c7254e;
        }
        .emoji {
            font-size: 24px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1><span class='emoji'>👥</span>Usuarios de Converza</h1>";

try {
    // Obtener todos los usuarios
    $stmt = $conexion->prepare("
        SELECT 
            id_use,
            nombre,
            email,
            usuario,
            fecha_reg,
            avatar,
            sexo,
            tipo,
            verificado
        FROM usuarios
        ORDER BY id_use ASC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) === 0) {
        echo "<div class='warning'>
            <strong>⚠️ No hay usuarios en la base de datos</strong><br><br>
            Necesitas crear usuarios primero. Puedes:
            <ul>
                <li>Registrarte en: <code>/Converza/app/view/registro.php</code></li>
                <li>O insertar usuarios manualmente en MySQL</li>
            </ul>
        </div>";
    } else {
        echo "<div class='info'>
            <strong>📊 Total de usuarios encontrados:</strong> " . count($usuarios) . "
        </div>";
        
        echo "<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($usuarios as $user) {
            $tipoIcon = $user['tipo'] === 'admin' ? '👑' : '👤';
            echo "<tr>
                <td><strong>{$user['id_use']}</strong></td>
                <td><code>{$user['usuario']}</code></td>
                <td>{$user['nombre']}</td>
                <td>{$user['email']}</td>
                <td>{$tipoIcon} {$user['tipo']}</td>
                <td>{$user['fecha_reg']}</td>
            </tr>";
        }
        
        echo "</tbody></table>";
        
        // Información sobre contraseñas
        echo "<div class='password-info'>
            <h3><span class='emoji'>🔐</span>Sobre las Contraseñas</h3>
            <p><strong>Las contraseñas están encriptadas con password_hash()</strong></p>
            <p>No es posible ver las contraseñas originales porque están hasheadas con bcrypt.</p>
            
            <h4>Opciones:</h4>
            <ol>
                <li><strong>Si recuerdas la contraseña:</strong> Simplemente inicia sesión normal</li>
                <li><strong>Si NO recuerdas la contraseña:</strong> Usa el script de reseteo más abajo</li>
                <li><strong>Para crear nuevos usuarios de prueba:</strong> Regístrate en <code>/Converza/app/view/registro.php</code></li>
            </ol>
        </div>";
        
        // Script para resetear contraseña
        echo "<div class='warning'>
            <h3><span class='emoji'>🔧</span>Resetear Contraseña de Usuario</h3>
            <p>Si necesitas cambiar la contraseña de algún usuario, ejecuta este SQL en phpMyAdmin:</p>
            <pre style='background: white; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
        
        foreach ($usuarios as $user) {
            $nuevaPassword = 'password123'; // Contraseña de ejemplo
            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            echo "\n-- Para usuario: {$user['usuario']}\nUPDATE usuarios SET contrasena = '{$hash}' WHERE id_use = {$user['id_use']};\n-- Nueva contraseña: password123\n";
        }
        
        echo "</pre>
            <p><strong>⚠️ Importante:</strong> Después de ejecutar el UPDATE, podrás iniciar sesión con:</p>
            <ul>
                <li><strong>Usuario:</strong> (el nombre de usuario mostrado arriba)</li>
                <li><strong>Contraseña:</strong> <code>password123</code></li>
            </ul>
        </div>";
        
        // Usuarios específicos del test
        $cami12 = array_filter($usuarios, fn($u) => $u['usuario'] === 'cami12');
        $cami123 = array_filter($usuarios, fn($u) => $u['usuario'] === 'cami123');
        
        if ($cami12 || $cami123) {
            echo "<div class='info'>
                <h3><span class='emoji'>🧪</span>Usuarios de Test Detectados</h3>";
            
            if ($cami12) {
                $u = array_values($cami12)[0];
                echo "<div class='user-card'>
                    <h4>Usuario: {$u['usuario']}</h4>
                    <div class='user-info'>
                        <span class='label'>ID:</span>
                        <span class='value'>{$u['id_use']}</span>
                        
                        <span class='label'>Nombre:</span>
                        <span class='value'>{$u['nombre']}</span>
                        
                        <span class='label'>Email:</span>
                        <span class='value'>{$u['email']}</span>
                        
                        <span class='label'>Tipo:</span>
                        <span class='value'>{$u['tipo']}</span>
                    </div>
                </div>";
            }
            
            if ($cami123) {
                $u = array_values($cami123)[0];
                echo "<div class='user-card'>
                    <h4>Usuario: {$u['usuario']}</h4>
                    <div class='user-info'>
                        <span class='label'>ID:</span>
                        <span class='value'>{$u['id_use']}</span>
                        
                        <span class='label'>Nombre:</span>
                        <span class='value'>{$u['nombre']}</span>
                        
                        <span class='label'>Email:</span>
                        <span class='value'>{$u['email']}</span>
                        
                        <span class='label'>Tipo:</span>
                        <span class='value'>{$u['tipo']}</span>
                    </div>
                </div>";
            }
            
            echo "</div>";
        }
        
        // Crear script de reseteo rápido
        echo "<div class='info'>
            <h3><span class='emoji'>⚡</span>Reseteo Rápido con password123</h3>
            <p>Copia y pega esto en phpMyAdmin (SQL) para resetear TODOS los usuarios a <code>password123</code>:</p>
            <pre style='background: white; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
        
        $quickHash = password_hash('password123', PASSWORD_DEFAULT);
        echo "UPDATE usuarios SET contrasena = '{$quickHash}';\n-- Ahora TODOS los usuarios pueden iniciar sesión con: password123";
        
        echo "</pre>
        </div>";
    }
    
} catch (Exception $e) {
    echo "<div class='warning'>
        <strong>❌ Error:</strong> {$e->getMessage()}
    </div>";
}

echo "    </div>
</body>
</html>";
?>
