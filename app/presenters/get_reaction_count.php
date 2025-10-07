<?php
// Archivo: get_reaction_count.php
// Propósito: Obtener el número total de reacciones por tipo para una publicación específica

require_once '../models/config.php';

// Verificar si se recibió el parámetro necesario
if (isset($_GET['postId'])) {
    $postId = intval($_GET['postId']);

    // Conectar a la base de datos
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Verificar conexión
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
        exit;
    }

    // Consultar el número total de reacciones por tipo
    $stmt = $conn->prepare("SELECT tipo_reaccion, COUNT(*) as total FROM reacciones WHERE id_publicacion = ? GROUP BY tipo_reaccion");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    $reactions = [];
    while ($row = $result->fetch_assoc()) {
        $reactions[] = $row;
    }

    echo json_encode(['success' => true, 'reactions' => $reactions]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Parámetro postId no proporcionado']);
}
?>