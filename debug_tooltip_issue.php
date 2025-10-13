<?php
require_once('app/models/config.php');

// Usuarios a diagnosticar
$usuarios_test = ['santi12', 'meliodas', 'vane15', 'fabian'];

echo "<h2>🔍 Diagnóstico de Tooltips por Usuario</h2>";
echo "<style>
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    .success { background-color: #d4edda; }
    .warning { background-color: #fff3cd; }
    .danger { background-color: #f8d7da; }
    .info { background-color: #d1ecf1; }
</style>";

foreach ($usuarios_test as $username) {
    try {
        // Obtener ID del usuario
        $stmt = $conexion->prepare("SELECT id_use, tipo FROM usuarios WHERE usuario = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "<h3>❌ Usuario '$username' no encontrado</h3>";
            continue;
        }
        
        $userId = $user['id_use'];
        $tipo = $user['tipo'];
        
        echo "<h3>Usuario: <strong>$username</strong> (ID: $userId, Tipo: $tipo)</h3>";
        
        // 1. Verificar si está bloqueado
        $isBlocked = isUserBlocked($userId, $conexion);
        $blockedClass = $isBlocked ? 'danger' : 'success';
        $blockedText = $isBlocked ? '🚫 BLOQUEADO' : '✅ Activo';
        echo "<p class='$blockedClass'>Estado: $blockedText</p>";
        
        // 2. Verificar publicaciones que puede ver
        $sqlPubs = "SELECT p.id_pub, p.contenido, u.usuario as autor, p.usuario as autor_id
                    FROM publicaciones p
                    INNER JOIN usuarios u ON p.usuario = u.id_use
                    WHERE p.usuario NOT IN (
                        SELECT bloqueador_id FROM bloqueos WHERE bloqueado_id = ?
                        UNION
                        SELECT bloqueado_id FROM bloqueos WHERE bloqueador_id = ?
                    ) AND (
                        p.usuario = ?
                        OR p.usuario IN (
                            SELECT seguido_id FROM seguidores WHERE seguidor_id = ?
                        )
                        OR p.usuario IN (
                            SELECT para FROM amigos WHERE de = ? AND estado = 'aceptado'
                        )
                        OR p.usuario IN (
                            SELECT de FROM amigos WHERE para = ? AND estado = 'aceptado'
                        )
                    )
                    ORDER BY p.fecha DESC
                    LIMIT 5";
        
        $stmtPubs = $conexion->prepare($sqlPubs);
        $stmtPubs->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
        $publicaciones = $stmtPubs->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📰 Publicaciones Visibles: " . count($publicaciones) . "</h4>";
        
        if (count($publicaciones) > 0) {
            echo "<table>";
            echo "<tr><th>ID Pub</th><th>Autor</th><th>Contenido (preview)</th><th>¿Es suya?</th></tr>";
            foreach ($publicaciones as $pub) {
                $esSuya = ($pub['autor_id'] == $userId) ? '✅ SÍ' : '❌ NO';
                $contenidoShort = substr($pub['contenido'], 0, 50) . '...';
                echo "<tr>";
                echo "<td>{$pub['id_pub']}</td>";
                echo "<td>{$pub['autor']}</td>";
                echo "<td>$contenidoShort</td>";
                echo "<td>$esSuya</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Probar el botón HTML que se generaría
            echo "<h4>🔘 HTML del Botón de Reacciones:</h4>";
            $testPubId = $publicaciones[0]['id_pub'];
            
            if ($isBlocked) {
                echo "<pre class='danger'>&lt;button class='btn btn-outline-secondary btn-sm disabled' disabled&gt;
    &lt;i class='bi bi-hand-thumbs-up'&gt;&lt;/i&gt; Me gusta
&lt;/button&gt;
&lt;!-- ❌ NO tiene clase 'like-main-btn' --&gt;
&lt;!-- ❌ NO tiene data-post-id --&gt;</pre>";
            } else {
                echo "<pre class='success'>&lt;button class='btn btn-outline-secondary btn-sm like-main-btn' data-post-id='$testPubId'&gt;
    &lt;i class='bi bi-hand-thumbs-up'&gt;&lt;/i&gt; Me gusta
&lt;/button&gt;
&lt;!-- ✅ Tiene clase 'like-main-btn' --&gt;
&lt;!-- ✅ Tiene data-post-id='$testPubId' --&gt;</pre>";
            }
            
            echo "<h4>🎯 Span del Contador:</h4>";
            echo "<pre class='info'>&lt;span class='reaction-counter ms-2' id='reaction_counter_$testPubId' data-tooltip='Sin reacciones'&gt;(0)&lt;/span&gt;
&lt;!-- ✅ SIEMPRE se genera --&gt;
&lt;!-- ✅ Tiene ID: reaction_counter_$testPubId --&gt;</pre>";
            
        } else {
            echo "<p class='warning'>⚠️ Este usuario no puede ver ninguna publicación</p>";
        }
        
        // 3. Verificar amigos
        $stmtAmigos = $conexion->prepare("
            SELECT COUNT(*) as total FROM (
                SELECT para FROM amigos WHERE de = ? AND estado = 'aceptado'
                UNION
                SELECT de FROM amigos WHERE para = ? AND estado = 'aceptado'
            ) as amigos_totales
        ");
        $stmtAmigos->execute([$userId, $userId]);
        $totalAmigos = $stmtAmigos->fetchColumn();
        
        echo "<p class='info'>👥 Total de amigos: <strong>$totalAmigos</strong></p>";
        
        // 4. Verificar seguidores
        $stmtSeguidores = $conexion->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ?");
        $stmtSeguidores->execute([$userId]);
        $totalSeguidos = $stmtSeguidores->fetchColumn();
        
        echo "<p class='info'>📢 Total de usuarios seguidos: <strong>$totalSeguidos</strong></p>";
        
        echo "<hr style='margin: 40px 0;'>";
        
    } catch (Exception $e) {
        echo "<p class='danger'>❌ Error para $username: " . $e->getMessage() . "</p>";
    }
}

// Verificar relaciones entre usuarios
echo "<h2>🔗 Matriz de Relaciones</h2>";
echo "<table>";
echo "<tr><th>Usuario A</th><th>Usuario B</th><th>¿Son amigos?</th><th>¿A sigue a B?</th></tr>";

for ($i = 0; $i < count($usuarios_test); $i++) {
    for ($j = $i + 1; $j < count($usuarios_test); $j++) {
        $userA = $usuarios_test[$i];
        $userB = $usuarios_test[$j];
        
        // Obtener IDs
        $stmtA = $conexion->prepare("SELECT id_use FROM usuarios WHERE usuario = :username");
        $stmtA->execute([':username' => $userA]);
        $idA = $stmtA->fetchColumn();
        
        $stmtB = $conexion->prepare("SELECT id_use FROM usuarios WHERE usuario = :username");
        $stmtB->execute([':username' => $userB]);
        $idB = $stmtB->fetchColumn();
        
        if (!$idA || !$idB) continue;
        
        // Verificar amistad
        $stmtAmigos = $conexion->prepare("
            SELECT COUNT(*) FROM amigos 
            WHERE ((de = ? AND para = ?) OR (de = ? AND para = ?))
            AND estado = 'aceptado'
        ");
        $stmtAmigos->execute([$idA, $idB, $idB, $idA]);
        $sonAmigos = $stmtAmigos->fetchColumn() > 0 ? '✅ SÍ' : '❌ NO';
        
        // Verificar si A sigue a B
        $stmtSigue = $conexion->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $stmtSigue->execute([$idA, $idB]);
        $aSigueB = $stmtSigue->fetchColumn() > 0 ? '✅ SÍ' : '❌ NO';
        
        echo "<tr>";
        echo "<td><strong>$userA</strong></td>";
        echo "<td><strong>$userB</strong></td>";
        echo "<td>$sonAmigos</td>";
        echo "<td>$aSigueB</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<h2>🎯 Conclusión Preliminar</h2>";
echo "<div class='info' style='padding: 15px;'>";
echo "<p><strong>Si todos los usuarios tienen:</strong></p>";
echo "<ul>";
echo "<li>✅ Estado: Activo (no bloqueados)</li>";
echo "<li>✅ Publicaciones visibles</li>";
echo "<li>✅ HTML con clase 'like-main-btn'</li>";
echo "</ul>";
echo "<p><strong>Entonces el problema está en:</strong></p>";
echo "<ol>";
echo "<li>JavaScript no se está ejecutando para algunos usuarios</li>";
echo "<li>Los archivos get_reactions.php o get_comentarios.php están filtrando datos</li>";
echo "<li>Hay cache del navegador que impide actualización del JS</li>";
echo "</ol>";
echo "</div>";
?>
