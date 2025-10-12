<?php
// Limpiar cualquier output previo
if (ob_get_level()) {
    ob_clean();
}

session_start();
require_once __DIR__.'/../models/config.php';

// Establecer headers antes de cualquier output
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    if (!isset($_SESSION['usuario'])) {
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'upload_voice') {
        
        if (!isset($_FILES['audio'])) {
            echo json_encode(['error' => 'Archivo de audio faltante']);
            exit;
        }
        
        if (!isset($_POST['para'])) {
            echo json_encode(['error' => 'Parámetro para faltante']);
            exit;
        }
        
        $de = $_SESSION['id'];
        $para = intval($_POST['para']);
        $duracion = intval($_POST['duracion'] ?? 0);
        
        if ($para === 0) {
            echo json_encode(['error' => 'ID de usuario destino inválido']);
            exit;
        }

        // Crear directorio para audios si no existe
        $uploadDir = '/Converza/public/voice_messages/';
        $fullUploadDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
        
        if (!file_exists($fullUploadDir)) {
            mkdir($fullUploadDir, 0777, true);
        }
        
        // Generar nombre único para el archivo
        $fileName = 'voice_' . time() . '_' . uniqid() . '.wav';
        $filePath = $fullUploadDir . $fileName;
        
        // Verificar errores de upload
        if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'Error en la subida del archivo: ' . $_FILES['audio']['error']]);
            exit;
        }
        
        // Mover archivo subido
        if (!move_uploaded_file($_FILES['audio']['tmp_name'], $filePath)) {
            echo json_encode(['error' => 'Error al subir el archivo de audio']);
            exit;
        }
        
        // PASO 1: Buscar o crear conversación
        $stmtC = $conexion->prepare("SELECT id_cch FROM c_chats WHERE (de = ? AND para = ?) OR (de = ? AND para = ?) LIMIT 1");
        $stmtC->execute([$de, $para, $para, $de]);
        $conversacion = $stmtC->fetch(PDO::FETCH_ASSOC);
        
        if (!$conversacion) {
            // Crear nueva conversación
            $stmtCreateC = $conexion->prepare("INSERT INTO c_chats (de, para) VALUES (?, ?)");
            $stmtCreateC->execute([$de, $para]);
            $id_cch = $conexion->lastInsertId();
        } else {
            $id_cch = $conversacion['id_cch'];
        }
        
        // PASO 2: Insertar mensaje básico (EXACTAMENTE como el diagnóstico)
        $sql = "INSERT INTO chats (id_cch, de, para, mensaje, fecha, leido) VALUES (?, ?, ?, ?, NOW(), 0)";
        $stmtMsg = $conexion->prepare($sql);
        
        $basicParams = [
            $id_cch,
            $de, 
            $para,
            '[Mensaje de voz]'
        ];
        
        $resultado = $stmtMsg->execute($basicParams);
        
        if (!$resultado) {
            // Eliminar archivo si hubo error
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            echo json_encode(['error' => 'Error en inserción básica: ' . implode(", ", $stmtMsg->errorInfo())]);
            exit;
        }
        
        $messageId = $conexion->lastInsertId();
        
        // PASO 3: Actualizar con campos de voz (EXACTAMENTE como el diagnóstico)
        $updateSql = "UPDATE chats SET tipo_mensaje = ?, archivo_audio = ?, duracion_audio = ? WHERE id_cha = ?";
        $updateStmt = $conexion->prepare($updateSql);
        $updateResult = $updateStmt->execute(['voz', $fileName, $duracion, $messageId]);
        
        if (!$updateResult) {
            // Si falla la actualización, al menos tenemos el mensaje básico
            error_log("Warning: No se pudieron actualizar campos de voz: " . implode(", ", $updateStmt->errorInfo()));
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Mensaje de voz enviado correctamente',
            'archivo' => $fileName,
            'messageId' => $messageId
        ]);
        
    } else {
        echo json_encode(['error' => 'Acción no válida']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['error' => 'Error fatal: ' . $e->getMessage()]);
}
?>