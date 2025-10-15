<?php
session_start();
require_once(__DIR__ . '/app/models/config.php');
require_once(__DIR__ . '/app/models/conexiones-misticas-helper.php');
require_once(__DIR__ . '/app/models/intereses-helper.php');

echo "<!DOCTYPE html>";
echo "<html lang='es'><head><meta charset='UTF-8'><title>Diagnóstico</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;} .ok{color:green;} .warning{color:orange;} .error{color:red;}</style>";
echo "</head><body>";

echo "<h2>🔍 Diagnóstico de Conexiones Místicas</h2>";

// Verificar sesión
if (!isset($_SESSION['id'])) {
    echo "<p class='error'>❌ No hay sesión activa. Por favor inicia sesión primero.</p>";
    exit();
}

$usuario_id = $_SESSION['id'];
echo "<p class='ok'>✅ Usuario logueado: ID {$usuario_id} ({$_SESSION['usuario']})</p>";
echo "<hr>";

// Paso 1: Intentar generación automática
echo "<h3>Paso 1: Generación Automática de Conexiones</h3>";
$motor = new ConexionesMisticas($conexion);

try {
    $generado = $motor->generarConexionesAutomaticas($usuario_id);
    
    if ($generado) {
        echo "<p class='ok'>✅ Conexiones generadas automáticamente</p>";
    } else {
        echo "<p class='warning'>⚠️ No fue necesario generar (ya tiene conexiones recientes)</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al generar: {$e->getMessage()}</p>";
}

echo "<hr>";

// Paso 2: Obtener Conexiones
echo "<h3>Paso 2: Obtener Conexiones Actuales</h3>";
$conexiones_originales = $motor->obtenerConexionesUsuario($usuario_id, 50);

echo "<p><strong>Total conexiones encontradas:</strong> " . count($conexiones_originales) . "</p>";

if (empty($conexiones_originales)) {
    echo "<p style='color: orange;'>⚠️ No tienes conexiones místicas generadas aún.</p>";
    echo "<p>Esto es normal si:</p>";
    echo "<ul>";
    echo "<li>Eres un usuario nuevo</li>";
    echo "<li>No has interactuado mucho en la plataforma</li>";
    echo "<li>El sistema aún no ha detectado patrones</li>";
    echo "</ul>";
    echo "<p><strong>Solución:</strong> Interactúa más: publica, comenta, da likes, etc.</p>";
} else {
    echo "<p>✅ Conexiones encontradas. Mostrando primeras 3:</p>";
    echo "<pre>";
    for ($i = 0; $i < min(3, count($conexiones_originales)); $i++) {
        $c = $conexiones_originales[$i];
        echo "\n--- Conexión " . ($i + 1) . " ---\n";
        echo "Otro usuario ID: " . $c['otro_id'] . "\n";
        echo "Otro usuario: " . $c['otro_usuario'] . "\n";
        echo "Puntuación original: " . $c['puntuacion'] . "\n";
        echo "Tipo: " . $c['tipo_conexion'] . "\n";
        echo "Descripción: " . $c['descripcion'] . "\n";
    }
    echo "</pre>";
}

echo "<hr>";

// Paso 2: Verificar predicciones del usuario
echo "<h3>Paso 2: Verificar Predicciones Votadas</h3>";
$stmt_pred = $conexion->prepare("
    SELECT categoria, me_gusta 
    FROM predicciones_usuarios 
    WHERE usuario_id = ? AND visto = 1 AND me_gusta IS NOT NULL
");
$stmt_pred->execute([$usuario_id]);
$predicciones_usuario = $stmt_pred->fetchAll(PDO::FETCH_ASSOC);

echo "<p><strong>Predicciones votadas:</strong> " . count($predicciones_usuario) . "</p>";

if (empty($predicciones_usuario)) {
    echo "<p style='color: orange;'>⚠️ No has votado ninguna predicción todavía.</p>";
    echo "<p><strong>Solución:</strong> Abre el offcanvas de 'Predicciones' y vota en las 5 categorías.</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Categoría</th><th>Tu Voto</th></tr>";
    foreach ($predicciones_usuario as $pred) {
        $voto = $pred['me_gusta'] == 1 ? '✅ Me gusta' : '❌ No me gusta';
        echo "<tr><td>{$pred['categoria']}</td><td>{$voto}</td></tr>";
    }
    echo "</table>";
}

echo "<hr>";

// Paso 3: Mejorar conexiones con intereses
if (!empty($conexiones_originales)) {
    echo "<h3>Paso 3: Aplicar InteresesHelper</h3>";
    
    try {
        $interesesHelper = new InteresesHelper($conexion);
        $conexiones_mejoradas = $interesesHelper->mejorarConexionesMisticas($usuario_id, $conexiones_originales);
        
        echo "<p>✅ Conexiones procesadas exitosamente</p>";
        echo "<p><strong>Total después de mejorar:</strong> " . count($conexiones_mejoradas) . "</p>";
        
        echo "<h4>Comparación Primera Conexión:</h4>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Original</th><th>Mejorada (50/50)</th></tr>";
        
        $orig = $conexiones_originales[0];
        $mej = $conexiones_mejoradas[0];
        
        echo "<tr><td>Otro Usuario</td><td>{$orig['otro_usuario']}</td><td>{$mej['otro_usuario']}</td></tr>";
        echo "<tr><td><strong>Score Sistema Místico</strong></td><td>{$orig['puntuacion']}</td><td>" . ($mej['puntuacion_original'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td><strong>Score Predicciones</strong></td><td>-</td><td>" . ($mej['compatibilidad_intereses'] ?? 0) . "%</td></tr>";
        echo "<tr><td><strong>Score Final (50/50)</strong></td><td>{$orig['puntuacion']}</td><td><strong>{$mej['puntuacion']}%</strong></td></tr>";
        echo "<tr><td>Fórmula</td><td>-</td><td>" . ($mej['formula']['explicacion'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td>Intereses Comunes</td><td>-</td><td>" . count($mej['intereses_comunes'] ?? []) . "</td></tr>";
        echo "</table>";
        
        echo "<div style='background: #d1ecf1; padding: 15px; margin-top: 15px; border-left: 4px solid #0dcaf0;'>";
        echo "<h5>📊 Explicación de la Nueva Fórmula:</h5>";
        echo "<p><strong>Score Final = (Sistema Místico × 50%) + (Predicciones × 50%)</strong></p>";
        echo "<ul>";
        echo "<li><strong>Sistema Místico</strong>: Amigos comunes, reacciones similares, comentarios en las mismas publicaciones, patrones de actividad</li>";
        echo "<li><strong>Predicciones</strong>: Compatibilidad basada en gustos e intereses votados</li>";
        echo "<li><strong>Ambos tienen el mismo peso</strong>: 50% cada uno</li>";
        echo "</ul>";
        echo "<p>Ejemplo: Si el sistema místico da 80% y predicciones da 60%, el score final es: (80 × 0.5) + (60 × 0.5) = 70%</p>";
        echo "</div>";
        
        if (!empty($mej['intereses_comunes'])) {
            echo "<p><strong>Intereses compartidos:</strong></p>";
            echo "<ul>";
            foreach ($mej['intereses_comunes'] as $interes) {
                echo "<li>{$interes['emoji']} {$interes['nombre']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>⚠️ No hay intereses en común con este usuario (ambos deben haber votado predicciones)</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al mejorar conexiones: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

echo "<hr>";

// Paso 4: Diagnóstico completo
echo "<h3>Paso 4: Diagnóstico General</h3>";

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Verificación</th><th>Estado</th><th>Acción</th></tr>";

// Check 1: Sesión
echo "<tr>";
echo "<td>Sesión activa</td>";
echo "<td style='color: green;'>✅ OK</td>";
echo "<td>-</td>";
echo "</tr>";

// Check 2: Conexiones
echo "<tr>";
echo "<td>Conexiones Místicas</td>";
if (empty($conexiones_originales)) {
    echo "<td style='color: orange;'>⚠️ Vacío</td>";
    echo "<td>Interactúa más en la plataforma</td>";
} else {
    echo "<td style='color: green;'>✅ " . count($conexiones_originales) . " encontradas</td>";
    echo "<td>-</td>";
}
echo "</tr>";

// Check 3: Predicciones
echo "<tr>";
echo "<td>Predicciones votadas</td>";
if (empty($predicciones_usuario)) {
    echo "<td style='color: orange;'>⚠️ Ninguna</td>";
    echo "<td>Vota predicciones en el offcanvas</td>";
} else {
    echo "<td style='color: green;'>✅ " . count($predicciones_usuario) . " votadas</td>";
    echo "<td>-</td>";
}
echo "</tr>";

// Check 4: InteresesHelper
echo "<tr>";
echo "<td>InteresesHelper</td>";
try {
    $test_helper = new InteresesHelper($conexion);
    echo "<td style='color: green;'>✅ Cargado</td>";
    echo "<td>-</td>";
} catch (Exception $e) {
    echo "<td style='color: red;'>❌ Error</td>";
    echo "<td>Verificar archivo intereses-helper.php</td>";
}
echo "</tr>";

echo "</table>";

echo "<hr>";

echo "<h3>📝 Conclusión</h3>";

if (empty($conexiones_originales)) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
    echo "<h4>⚠️ Las Conexiones Místicas están vacías porque:</h4>";
    echo "<ol>";
    echo "<li>El sistema de Conexiones Místicas aún no ha detectado patrones en tu actividad</li>";
    echo "<li>Esto es normal para usuarios nuevos o con poca interacción</li>";
    echo "</ol>";
    echo "<h4>✅ Para generar conexiones:</h4>";
    echo "<ol>";
    echo "<li>Publica contenido</li>";
    echo "<li>Comenta en publicaciones de otros</li>";
    echo "<li>Da likes a posts</li>";
    echo "<li>Interactúa con diferentes usuarios</li>";
    echo "<li>Espera a que el sistema detecte patrones (se ejecuta periódicamente)</li>";
    echo "</ol>";
    echo "</div>";
} else if (empty($predicciones_usuario)) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #0dcaf0;'>";
    echo "<h4>ℹ️ Las conexiones existen pero sin intereses comunes porque:</h4>";
    echo "<ol>";
    echo "<li>Tienes " . count($conexiones_originales) . " conexiones místicas ✅</li>";
    echo "<li>Pero no has votado predicciones aún ⚠️</li>";
    echo "</ol>";
    echo "<h4>✅ Para ver intereses comunes:</h4>";
    echo "<ol>";
    echo "<li>Abre el offcanvas de 'Predicciones' (icono de estrella en navbar)</li>";
    echo "<li>Vota las 5 predicciones: Música, Comida, Hobbies, Viajes, Personalidad</li>";
    echo "<li>Espera a que otros usuarios también voten sus predicciones</li>";
    echo "<li>Recarga Conexiones Místicas para ver compatibilidad e intereses</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
    echo "<h4>✅ Todo está funcionando correctamente:</h4>";
    echo "<ul>";
    echo "<li>Tienes " . count($conexiones_originales) . " conexiones místicas</li>";
    echo "<li>Has votado " . count($predicciones_usuario) . " predicciones</li>";
    echo "<li>El sistema de intereses está activo</li>";
    echo "</ul>";
    echo "<p><strong>Ve a <a href='/Converza/app/presenters/conexiones_misticas.php'>Conexiones Místicas</a> para ver los resultados</strong></p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/Converza/app/presenters/conexiones_misticas.php'>← Volver a Conexiones Místicas</a></p>";
?>
