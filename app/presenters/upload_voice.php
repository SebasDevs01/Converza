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

    switch ($action) {
    case 'upload_voice':
        // Debug: registrar datos recibidos (comentado para evitar output)
        // error_log('Upload voice - Datos recibidos: ' . print_r($_POST, true));
        // error_log('Upload voice - Archivos: ' . print_r($_FILES, true));
        
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
        
        // Validar que son amigos
        $stmtAmigos = $conexion->prepare("
            SELECT id_ami FROM amigos 
            WHERE ((de = :de AND para = :para) OR (de = :para AND para = :de)) 
            AND estado = 1
        ");
        $stmtAmigos->execute([':de' => $de, ':para' => $para]);
        
        if (!$stmtAmigos->fetch()) {
            echo json_encode(['error' => 'No tienes permiso para enviar mensajes a este usuario']);
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
        if (move_uploaded_file($_FILES['audio']['tmp_name'], $filePath)) {
            try {
                // Buscar o crear conversación
                $stmtC = $conexion->prepare("
                    SELECT id_cch FROM c_chats 
                    WHERE (de = :de AND para = :para) OR (de = :para AND para = :de)
                    LIMIT 1
                ");
                $stmtC->execute([':de' => $de, ':para' => $para]);
                $conversacion = $stmtC->fetch(PDO::FETCH_ASSOC);
                
                if (!$conversacion) {
                    // Crear nueva conversación (sin campo fecha que no existe)
                    $stmtCreateC = $conexion->prepare("
                        INSERT INTO c_chats (de, para) 
                        VALUES (:de, :para)
                    ");
                    $stmtCreateC->execute([':de' => $de, ':para' => $para]);
                    $id_cch = $conexion->lastInsertId();
                } else {
                    $id_cch = $conversacion['id_cch'];
                }
                
                // Insertar mensaje de voz - USANDO LA LÓGICA QUE FUNCIONÓ EN EL DIAGNÓSTICO
                try {
                    // PASO 1: Insertar mensaje básico (igual que el diagnóstico exitoso)
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
                        throw new Exception("Error en inserción básica: " . implode(", ", $stmtMsg->errorInfo()));
                    }
                    
                    $messageId = $conexion->lastInsertId();
                    
                    // PASO 2: Actualizar con campos de voz (igual que el diagnóstico exitoso)
                    $updateSql = "UPDATE chats SET tipo_mensaje = ?, archivo_audio = ?, duracion_audio = ? WHERE id_cha = ?";
                    $updateStmt = $conexion->prepare($updateSql);
                    $updateResult = $updateStmt->execute(['voz', $fileName, $duracion, $messageId]);
                    
                    if (!$updateResult) {
                        throw new Exception("Error actualizando campos de voz: " . implode(", ", $updateStmt->errorInfo()));
                    }
                    
                    if (!$resultado) {
                        throw new Exception("Error al insertar mensaje: " . implode(", ", $stmtMsg->errorInfo()));
                    }
                    
                } catch (Exception $e) {
                    throw new Exception("Error en INSERT: " . $e->getMessage());
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Mensaje de voz enviado correctamente',
                    'archivo' => $fileName
                ]);
                
            } catch (Exception $e) {
                // Eliminar archivo si hubo error en BD
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Error al subir el archivo de audio']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['error' => 'Error fatal: ' . $e->getMessage()]);
}