<?php
/**
 * Script de configuración para Daily Shuffle
 * Ejecuta este archivo una vez para crear la tabla daily_shuffle
 */

require_once __DIR__.'/app/models/config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Daily Shuffle</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
<div class='container mt-5'>
    <div class='card shadow'>
        <div class='card-header bg-primary text-white'>
            <h3><i class='bi bi-shuffle'></i> Configuración Daily Shuffle</h3>
        </div>
        <div class='card-body'>";

try {
    // Leer el archivo SQL
    $sqlFile = __DIR__.'/sql/create_daily_shuffle_table.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("No se encuentra el archivo SQL: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    echo "<div class='alert alert-info'>Ejecutando script SQL...</div>";
    
    // Ejecutar el SQL
    $conexion->exec($sql);
    
    echo "<div class='alert alert-success'>
            <h4>✅ ¡Tabla daily_shuffle creada exitosamente!</h4>
            <p>La funcionalidad Daily Shuffle está lista para usar.</p>
          </div>";
    
    // Verificar que la tabla se creó
    $stmt = $conexion->query("SHOW TABLES LIKE 'daily_shuffle'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='alert alert-success'>
                <strong>Verificación:</strong> La tabla 'daily_shuffle' existe en la base de datos.
              </div>";
        
        // Mostrar estructura de la tabla
        $stmt = $conexion->query("DESCRIBE daily_shuffle");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h5 class='mt-3'>Estructura de la tabla:</h5>";
        echo "<div class='table-responsive'>
                <table class='table table-sm table-bordered'>
                    <thead class='table-light'>
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                        </tr>
                    </thead>
                    <tbody>";
        
        foreach ($columns as $col) {
            echo "<tr>
                    <td><code>{$col['Field']}</code></td>
                    <td>{$col['Type']}</td>
                    <td>{$col['Null']}</td>
                    <td>{$col['Key']}</td>
                    <td>{$col['Default']}</td>
                    <td>{$col['Extra']}</td>
                  </tr>";
        }
        
        echo "    </tbody>
                </table>
              </div>";
    }
    
    echo "<div class='mt-4'>
            <a href='app/view/index.php' class='btn btn-primary'>
                <i class='bi bi-house'></i> Ir al inicio
            </a>
            <a href='app/presenters/daily_shuffle.php' class='btn btn-success'>
                <i class='bi bi-shuffle'></i> Probar Daily Shuffle
            </a>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <h4>❌ Error</h4>
            <p><strong>Mensaje:</strong> {$e->getMessage()}</p>
          </div>";
    
    echo "<div class='mt-3'>
            <a href='javascript:history.back()' class='btn btn-secondary'>Volver</a>
          </div>";
}

echo "    </div>
    </div>
</div>
<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>
</body>
</html>";
?>
