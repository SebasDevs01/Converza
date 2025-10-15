<?php
session_start();
require_once __DIR__.'/app/models/config.php';

if (!isset($_SESSION['id'])) {
    die("ERROR: Debes estar logueado para usar este diagnóstico.");
}

$usuario_id = (int)$_SESSION['id'];

echo "<h1>🔍 DIAGNÓSTICO DEL FEED</h1>";
echo "<p><strong>Usuario actual:</strong> ID {$usuario_id} - {$_SESSION['usuario']}</p>";
echo "<hr>";

// 1. Verificar publicaciones propias
echo "<h2>1️⃣ Mis Publicaciones</h2>";
$stmt = $conexion->prepare("
    SELECT id_pub, contenido, fecha, usuario 
    FROM publicaciones 
    WHERE usuario = ? 
    ORDER BY id_pub DESC 
    LIMIT 5
");
$stmt->execute([$usuario_id]);
$misPublicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total: <strong>".count($misPublicaciones)."</strong> publicaciones</p>";
if (count($misPublicaciones) > 0) {
    echo "<ul>";
    foreach ($misPublicaciones as $pub) {
        echo "<li>ID: {$pub['id_pub']} - ".substr($pub['contenido'], 0, 50)."... (Usuario: {$pub['usuario']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'>⚠️ No tienes publicaciones.</p>";
}
echo "<hr>";

// 2. Verificar usuarios que sigo
echo "<h2>2️⃣ Usuarios que Sigo</h2>";
$stmt = $conexion->prepare("
    SELECT s.seguido_id, u.usuario, u.nombre 
    FROM seguidores s 
    JOIN usuarios u ON s.seguido_id = u.id_use 
    WHERE s.seguidor_id = ?
");
$stmt->execute([$usuario_id]);
$siguiendo = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total: <strong>".count($siguiendo)."</strong> usuarios seguidos</p>";
if (count($siguiendo) > 0) {
    echo "<ul>";
    foreach ($siguiendo as $seg) {
        echo "<li>ID: {$seg['seguido_id']} - {$seg['usuario']} ({$seg['nombre']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'>⚠️ No sigues a nadie.</p>";
}
echo "<hr>";

// 3. Verificar publicaciones de usuarios que sigo
echo "<h2>3️⃣ Publicaciones de Usuarios que Sigo</h2>";
$stmt = $conexion->prepare("
    SELECT p.id_pub, p.contenido, p.fecha, p.usuario, u.usuario as username 
    FROM publicaciones p 
    JOIN usuarios u ON p.usuario = u.id_use 
    WHERE p.usuario IN (
        SELECT seguido_id 
        FROM seguidores 
        WHERE seguidor_id = ?
    )
    ORDER BY p.id_pub DESC 
    LIMIT 10
");
$stmt->execute([$usuario_id]);
$publicacionesSeguidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total: <strong>".count($publicacionesSeguidos)."</strong> publicaciones de usuarios seguidos</p>";
if (count($publicacionesSeguidos) > 0) {
    echo "<ul>";
    foreach ($publicacionesSeguidos as $pub) {
        echo "<li>ID: {$pub['id_pub']} - {$pub['username']} (ID: {$pub['usuario']}) - ".substr($pub['contenido'], 0, 50)."...</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'>⚠️ Los usuarios que sigues no han publicado nada.</p>";
}
echo "<hr>";

// 4. Verificar mis amigos
echo "<h2>4️⃣ Mis Amigos (Amistad Confirmada)</h2>";
$stmt = $conexion->prepare("
    SELECT 
        CASE 
            WHEN a.de = ? THEN a.para 
            ELSE a.de 
        END as amigo_id,
        u.usuario, u.nombre
    FROM amigos a 
    JOIN usuarios u ON (CASE WHEN a.de = ? THEN a.para ELSE a.de END) = u.id_use
    WHERE (a.de = ? OR a.para = ?) 
    AND a.estado = 1
");
$stmt->execute([$usuario_id, $usuario_id, $usuario_id, $usuario_id]);
$amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total: <strong>".count($amigos)."</strong> amigos</p>";
if (count($amigos) > 0) {
    echo "<ul>";
    foreach ($amigos as $amigo) {
        echo "<li>ID: {$amigo['amigo_id']} - {$amigo['usuario']} ({$amigo['nombre']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'>⚠️ No tienes amigos confirmados.</p>";
}
echo "<hr>";

// 5. Verificar publicaciones de amigos
echo "<h2>5️⃣ Publicaciones de Amigos</h2>";
$stmt = $conexion->prepare("
    SELECT p.id_pub, p.contenido, p.fecha, p.usuario, u.usuario as username 
    FROM publicaciones p 
    JOIN usuarios u ON p.usuario = u.id_use 
    WHERE p.usuario IN (
        SELECT CASE 
            WHEN a.de = ? THEN a.para 
            ELSE a.de 
        END as amigo_id
        FROM amigos a 
        WHERE (a.de = ? OR a.para = ?) 
        AND a.estado = 1
    )
    ORDER BY p.id_pub DESC 
    LIMIT 10
");
$stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
$publicacionesAmigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total: <strong>".count($publicacionesAmigos)."</strong> publicaciones de amigos</p>";
if (count($publicacionesAmigos) > 0) {
    echo "<ul>";
    foreach ($publicacionesAmigos as $pub) {
        echo "<li>ID: {$pub['id_pub']} - {$pub['username']} (ID: {$pub['usuario']}) - ".substr($pub['contenido'], 0, 50)."...</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange;'>⚠️ Tus amigos no han publicado nada.</p>";
}
echo "<hr>";

// 6. Verificar query COMPLETA del feed (la que usa publicaciones.php)
echo "<h2>6️⃣ Query COMPLETA del Feed (publicaciones.php)</h2>";
$filtroBloqueos = "(1=1)"; // Placeholder simple para diagnóstico
$stmt = $conexion->prepare("
    SELECT DISTINCT p.id_pub, p.contenido, p.fecha, p.usuario, u.usuario as username
    FROM publicaciones p 
    JOIN usuarios u ON p.usuario = u.id_use 
    WHERE ($filtroBloqueos) AND (
        p.usuario = :user_id 
        OR p.usuario IN (
            SELECT s.seguido_id 
            FROM seguidores s 
            WHERE s.seguidor_id = :user_id2
        )
        OR p.usuario IN (
            SELECT CASE 
                WHEN a.de = :user_id3 THEN a.para 
                ELSE a.de 
            END as amigo_id
            FROM amigos a 
            WHERE (a.de = :user_id4 OR a.para = :user_id5) 
            AND a.estado = 1
        )
    )
    ORDER BY p.id_pub DESC 
    LIMIT 20
");
$stmt->execute([
    ':user_id' => $usuario_id,
    ':user_id2' => $usuario_id,
    ':user_id3' => $usuario_id,
    ':user_id4' => $usuario_id,
    ':user_id5' => $usuario_id
]);
$feedCompleto = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total: <strong>".count($feedCompleto)."</strong> publicaciones en el feed</p>";
if (count($feedCompleto) > 0) {
    echo "<ul>";
    foreach ($feedCompleto as $pub) {
        echo "<li><strong>ID: {$pub['id_pub']}</strong> - {$pub['username']} (Usuario ID: {$pub['usuario']}) - ".substr($pub['contenido'], 0, 80)."...</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>❌ EL FEED ESTÁ VACÍO - PROBLEMA DETECTADO</p>";
}
echo "<hr>";

// 7. Verificar tabla seguidores completa
echo "<h2>7️⃣ Tabla Seguidores (todas las filas relacionadas contigo)</h2>";
$stmt = $conexion->prepare("
    SELECT * FROM seguidores 
    WHERE seguidor_id = ? OR seguido_id = ?
    ORDER BY id DESC 
    LIMIT 20
");
$stmt->execute([$usuario_id, $usuario_id]);
$seguidoresTabla = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total filas: <strong>".count($seguidoresTabla)."</strong></p>";
if (count($seguidoresTabla) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    
    // Obtener nombres de columnas dinámicamente
    $columnas = array_keys($seguidoresTabla[0]);
    echo "<tr>";
    foreach ($columnas as $col) {
        echo "<th>".htmlspecialchars(ucfirst($col))."</th>";
    }
    echo "</tr>";
    
    foreach ($seguidoresTabla as $row) {
        $destacado = ($row['seguidor_id'] == $usuario_id) ? "style='background-color:#ffffcc;'" : "";
        echo "<tr $destacado>";
        foreach ($columnas as $col) {
            echo "<td>".htmlspecialchars($row[$col] ?? '')."</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><small>* Filas amarillas: Tú eres el seguidor</small></p>";
} else {
    echo "<p style='color:orange;'>⚠️ No hay registros en la tabla seguidores relacionados contigo.</p>";
}
echo "<hr>";

// 8. Verificar tabla amigos completa
echo "<h2>8️⃣ Tabla Amigos (todas las filas relacionadas contigo)</h2>";
$stmt = $conexion->prepare("
    SELECT * FROM amigos 
    WHERE de = ? OR para = ?
    ORDER BY fecha DESC 
    LIMIT 20
");
$stmt->execute([$usuario_id, $usuario_id]);
$amigosTabla = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p>Total filas: <strong>".count($amigosTabla)."</strong></p>";
if (count($amigosTabla) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>De (ID)</th><th>Para (ID)</th><th>Estado</th><th>Fecha</th></tr>";
    foreach ($amigosTabla as $row) {
        $estadoTexto = $row['estado'] == 0 ? "Pendiente" : ($row['estado'] == 1 ? "Confirmado" : "Otro");
        $destacado = ($row['estado'] == 1) ? "style='background-color:#ccffcc;'" : "";
        echo "<tr $destacado>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['de']}</td>";
        echo "<td>{$row['para']}</td>";
        echo "<td><strong>{$estadoTexto}</strong> ({$row['estado']})</td>";
        echo "<td>{$row['fecha']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><small>* Filas verdes: Amistad confirmada (estado = 1)</small></p>";
} else {
    echo "<p style='color:orange;'>⚠️ No hay registros en la tabla amigos relacionados contigo.</p>";
}
echo "<hr>";

echo "<h2>✅ Diagnóstico Completado</h2>";
echo "<p><a href='app/view/index.php' style='padding:10px 20px; background:#0d6efd; color:white; text-decoration:none; border-radius:5px;'>← Volver al Feed</a></p>";
?>
