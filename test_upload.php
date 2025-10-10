<?php
if (ob_get_level()) {
    ob_clean();
}

session_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    echo json_encode([
        'test' => 'ok',
        'session_id' => $_SESSION['id'] ?? 'no_session',
        'post_data' => $_POST,
        'files' => $_FILES,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>