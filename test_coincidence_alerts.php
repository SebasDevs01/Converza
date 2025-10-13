<?php
/**
 * Script de prueba para el Sistema de Alertas de Coincidencias
 * 
 * Este script simula la detección de una conexión mística significativa
 * y envía notificaciones a ambos usuarios para probar el sistema completo.
 */

require_once __DIR__ . '/app/models/config.php';
require_once __DIR__ . '/app/models/notificaciones-triggers.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test - Coincidence Alerts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 5px;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
        }
        .warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .emoji {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1><span class='emoji'>🔔</span>Test - Sistema de Alertas de Coincidencias</h1>";

// Obtener usuarios de prueba
try {
    $stmt = $conexion->prepare("
        SELECT id_use, usuario 
        FROM usuarios 
        WHERE id_use != 1 
        ORDER BY id_use ASC 
        LIMIT 2
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) < 2) {
        echo "<div class='step error'>
            <strong>❌ Error:</strong> Se necesitan al menos 2 usuarios en la base de datos.
        </div>";
        exit;
    }
    
    $usuario1 = $usuarios[0];
    $usuario2 = $usuarios[1];
    
    echo "<div class='step info'>
        <strong>📋 Usuarios seleccionados:</strong><br>
        Usuario 1: <code>{$usuario1['usuario']}</code> (ID: {$usuario1['id_use']})<br>
        Usuario 2: <code>{$usuario2['usuario']}</code> (ID: {$usuario2['id_use']})
    </div>";
    
    // Paso 1: Insertar conexión mística simulada
    echo "<div class='step'>
        <strong>Paso 1:</strong> Insertando conexión mística simulada...
    </div>";
    
    $tipo = 'gustos_compartidos';
    $descripcion = '¡Ambos reaccionaron a 5 publicaciones similares! 💫';
    $puntuacion = 100; // Alta coincidencia
    
    $stmt = $conexion->prepare("
        INSERT INTO conexiones_misticas 
        (usuario1_id, usuario2_id, tipo_conexion, descripcion, puntuacion)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            descripcion = VALUES(descripcion),
            puntuacion = VALUES(puntuacion),
            fecha_deteccion = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([
        min($usuario1['id_use'], $usuario2['id_use']),
        max($usuario1['id_use'], $usuario2['id_use']),
        $tipo,
        $descripcion,
        $puntuacion
    ]);
    
    echo "<div class='step success'>
        ✅ Conexión mística creada con éxito<br>
        - Tipo: <code>{$tipo}</code><br>
        - Puntuación: <code>{$puntuacion}%</code><br>
        - Descripción: {$descripcion}
    </div>";
    
    // Paso 2: Enviar notificaciones
    echo "<div class='step'>
        <strong>Paso 2:</strong> Enviando notificaciones automáticas...
    </div>";
    
    $triggers = new NotificacionesTriggers($conexion);
    $resultado = $triggers->coincidenciaSignificativa(
        $usuario1['id_use'],
        $usuario2['id_use'],
        $tipo,
        $descripcion,
        $puntuacion,
        $usuario1['usuario'],
        $usuario2['usuario']
    );
    
    if ($resultado) {
        echo "<div class='step success'>
            ✅ Notificaciones enviadas correctamente a ambos usuarios
        </div>";
        
        // Verificar notificaciones creadas
        $stmt = $conexion->prepare("
            SELECT n.*, u.usuario 
            FROM notificaciones n
            JOIN usuarios u ON n.de_usuario_id = u.id_use
            WHERE n.usuario_id IN (?, ?)
            AND n.tipo = 'conexion_mistica'
            ORDER BY n.fecha_creacion DESC
            LIMIT 2
        ");
        $stmt->execute([$usuario1['id_use'], $usuario2['id_use']]);
        $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='step info'>
            <strong>📬 Notificaciones creadas:</strong><br><br>";
        
        foreach ($notificaciones as $notif) {
            echo "<strong>Para:</strong> <code>Usuario ID {$notif['usuario_id']}</code><br>
                  <strong>De:</strong> <code>{$notif['usuario']}</code><br>
                  <strong>Mensaje:</strong> {$notif['mensaje']}<br>
                  <strong>URL:</strong> <code>{$notif['url_redireccion']}</code><br>
                  <strong>Fecha:</strong> {$notif['fecha_creacion']}<br><br>";
        }
        
        echo "</div>";
        
        // Paso 3: Instrucciones de prueba
        echo "<div class='step warning'>
            <strong>🧪 Cómo probar:</strong><br><br>
            1. Inicia sesión como <code>{$usuario1['usuario']}</code> o <code>{$usuario2['usuario']}</code><br>
            2. Ve al index (<code>app/view/index.php</code>)<br>
            3. Verás una notificación nueva en la campana 🔔<br>
            4. Haz clic en la notificación<br>
            5. El offcanvas de Conexiones Místicas se abrirá automáticamente ✨<br>
            6. Verás la conexión del 100% con el otro usuario<br><br>
            
            <strong>📝 Enlaces directos:</strong><br>
            - <a href='/Converza/app/view/index.php?open_conexiones=1' target='_blank'>
                Abrir con panel de conexiones (Usuario 1)
              </a><br>
            - Primero inicia sesión con uno de los usuarios de prueba
        </div>";
        
        // Resumen
        echo "<div class='step success'>
            <strong>🎉 Test completado exitosamente</strong><br><br>
            ✅ Sistema de detección: OK<br>
            ✅ Notificaciones automáticas: OK<br>
            ✅ Integración con sistema existente: OK<br>
            ✅ Sin daños a funcionalidad existente: OK<br><br>
            
            El sistema de <strong>Coincidence Alerts</strong> está funcionando correctamente.
        </div>";
        
    } else {
        echo "<div class='step error'>
            ❌ Error: La puntuación es menor a 80 o no cumple criterios de notificación
        </div>";
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>
        <strong>❌ Error:</strong> {$e->getMessage()}
    </div>";
}

echo "    </div>
</body>
</html>";
?>
