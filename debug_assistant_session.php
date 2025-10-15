<?php
session_start();

echo "<h1>🔍 Diagnóstico de Sesión para Asistente</h1>";

echo "<h2>Datos de Sesión:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Variables del Widget:</h2>";
$id_usuario_widget = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$nombre_usuario_widget = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario';
$foto_perfil_widget = '/Converza/app/static/img/default-avatar.png';

if (isset($_SESSION['foto_perfil']) && !empty($_SESSION['foto_perfil'])) {
    $foto_perfil_widget = '/Converza/' . $_SESSION['foto_perfil'];
}

echo "<ul>";
echo "<li><strong>ID:</strong> " . $id_usuario_widget . "</li>";
echo "<li><strong>Nombre:</strong> " . $nombre_usuario_widget . "</li>";
echo "<li><strong>Foto:</strong> " . $foto_perfil_widget . "</li>";
echo "</ul>";

echo "<h2>window.ASSISTANT_USER_DATA JavaScript:</h2>";
echo "<pre>";
echo "window.ASSISTANT_USER_DATA = {\n";
echo "    id: " . $id_usuario_widget . ",\n";
echo "    nombre: \"" . htmlspecialchars($nombre_usuario_widget, ENT_QUOTES) . "\",\n";
echo "    foto: \"" . htmlspecialchars($foto_perfil_widget, ENT_QUOTES) . "\"\n";
echo "};\n";
echo "</pre>";

echo "<h2>Vista previa de la imagen:</h2>";
echo "<img src='" . $foto_perfil_widget . "' style='width:100px; height:100px; border-radius:50%; object-fit:cover;' onerror='this.style.border=\"3px solid red\"'>";

echo "<h2>Verificar en Consola JS:</h2>";
echo "<script>
console.log('🔍 Diagnóstico Asistente:');
console.log('ASSISTANT_USER_DATA:', window.ASSISTANT_USER_DATA);
window.ASSISTANT_USER_DATA = {
    id: " . $id_usuario_widget . ",
    nombre: \"" . htmlspecialchars($nombre_usuario_widget, ENT_QUOTES) . "\",
    foto: \"" . htmlspecialchars($foto_perfil_widget, ENT_QUOTES) . "\"
};
console.log('ASSISTANT_USER_DATA (actualizado):', window.ASSISTANT_USER_DATA);
</script>";
?>
