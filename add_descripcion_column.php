<?php
/**
 * Script opcional para agregar columna 'descripcion' a la tabla usuarios
 * Esto permite que los usuarios tengan biografías en su perfil
 */

require_once __DIR__.'/app/models/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Agregar columna descripcion</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>
</head>
<body class='bg-light'>
<div class='container mt-5'>
    <div class='card shadow'>
        <div class='card-header bg-primary text-white'>
            <h3><i class='bi bi-database-add'></i> Agregar Columna 'descripcion'</h3>
        </div>
        <div class='card-body'>";

try {
    // Verificar si la columna ya existe
    $stmt = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'descripcion'");
    
    if ($stmt->rowCount() > 0) {
        echo "<div class='alert alert-info'>
                <h4><i class='bi bi-info-circle'></i> La columna ya existe</h4>
                <p>La columna 'descripcion' ya está presente en la tabla usuarios.</p>
              </div>";
    } else {
        echo "<div class='alert alert-warning'>
                <h4><i class='bi bi-exclamation-triangle'></i> Columna no encontrada</h4>
                <p>La columna 'descripcion' no existe. ¿Deseas agregarla?</p>
              </div>";
        
        // Si se confirmó, ejecutar el ALTER
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
            // Agregar la columna
            $conexion->exec("
                ALTER TABLE usuarios 
                ADD COLUMN descripcion TEXT NULL 
                AFTER sexo
            ");
            
            echo "<div class='alert alert-success'>
                    <h4><i class='bi bi-check-circle'></i> ¡Columna agregada exitosamente!</h4>
                    <p>La columna 'descripcion' ha sido agregada a la tabla usuarios.</p>
                  </div>";
            
            // Actualizar Daily Shuffle para usar descripcion
            echo "<div class='alert alert-info'>
                    <h5>Actualizar Daily Shuffle</h5>
                    <p>Ahora puedes actualizar el archivo <code>daily_shuffle.php</code> para usar la columna descripcion.</p>
                    <p>En la línea 87, cambia:</p>
                    <pre>u.email,\nu.sexo,</pre>
                    <p>Por:</p>
                    <pre>u.descripcion,</pre>
                  </div>";
            
            // Ofrecer opción para actualizar usuarios existentes
            echo "<div class='alert alert-secondary'>
                    <h5>¿Deseas agregar descripciones predeterminadas?</h5>
                    <p>Puedes agregar descripciones automáticas a los usuarios existentes.</p>
                    <a href='?confirm=yes&update=yes' class='btn btn-secondary'>
                        <i class='bi bi-pencil'></i> Agregar descripciones predeterminadas
                    </a>
                  </div>";
            
            if (isset($_GET['update']) && $_GET['update'] === 'yes') {
                $conexion->exec("
                    UPDATE usuarios 
                    SET descripcion = CONCAT('¡Hola! Soy ', nombre, ' y me encanta conectar con nuevas personas en Converza.')
                    WHERE descripcion IS NULL OR descripcion = ''
                ");
                
                $stmt = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE descripcion IS NOT NULL");
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                echo "<div class='alert alert-success'>
                        <h5><i class='bi bi-check-circle'></i> ¡Descripciones agregadas!</h5>
                        <p>Se actualizaron $total usuarios con descripciones predeterminadas.</p>
                      </div>";
            }
            
        } else {
            echo "<div class='mt-3'>
                    <h5>¿Qué hace esta columna?</h5>
                    <p>La columna 'descripcion' permite que los usuarios tengan una biografía o descripción personal en su perfil.</p>
                    <p>Esta descripción se mostrará en:</p>
                    <ul>
                        <li>Daily Shuffle (en lugar de email/sexo)</li>
                        <li>Perfil del usuario</li>
                        <li>Búsquedas de usuarios</li>
                    </ul>
                    
                    <h5 class='mt-4'>Confirmar acción</h5>
                    <p>Click en el botón para agregar la columna:</p>
                    <a href='?confirm=yes' class='btn btn-primary btn-lg'>
                        <i class='bi bi-database-add'></i> Sí, agregar columna 'descripcion'
                    </a>
                    <a href='app/view/index.php' class='btn btn-secondary'>
                        <i class='bi bi-x'></i> No, mantener como está
                    </a>
                  </div>";
        }
    }
    
    // Mostrar estructura actual
    echo "<div class='mt-4'>
            <h5>Estructura actual de 'usuarios':</h5>
            <div class='table-responsive'>
              <table class='table table-sm table-bordered'>";
    
    $stmt = $conexion->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<thead class='table-light'>
            <tr>
                <th>Campo</th>
                <th>Tipo</th>
                <th>Null</th>
                <th>Default</th>
            </tr>
          </thead>
          <tbody>";
    
    foreach ($columns as $col) {
        $highlight = ($col['Field'] === 'descripcion') ? "style='background-color: #d4edda;'" : "";
        echo "<tr $highlight>
                <td><code>{$col['Field']}</code></td>
                <td>{$col['Type']}</td>
                <td>{$col['Null']}</td>
                <td>{$col['Default']}</td>
              </tr>";
    }
    
    echo "  </tbody>
            </table>
          </div>
        </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <h4><i class='bi bi-x-circle'></i> Error</h4>
            <p><strong>Mensaje:</strong> {$e->getMessage()}</p>
          </div>";
}

echo "  <div class='mt-3'>
            <a href='test_daily_shuffle.php' class='btn btn-info'>
                <i class='bi bi-clipboard-check'></i> Ir a Tests
            </a>
            <a href='app/view/index.php' class='btn btn-success'>
                <i class='bi bi-house'></i> Ir a Converza
            </a>
        </div>
    </div>
  </div>
</div>
</body>
</html>";
?>
