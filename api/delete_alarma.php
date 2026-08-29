<?php
session_start();
require_once "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Para API, recibimos los datos por POST en formato JSON o form-data
$input = json_decode(file_get_contents('php://input'), true);
$alarma_id = $input['id'] ?? $_POST['id'] ?? 0;

if (!$alarma_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID no válido']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM alarmas_servidores WHERE id = ?");
    $success = $stmt->execute([$alarma_id]);
    
    if ($success && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Alarma eliminada correctamente']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Alarma no encontrada o ya eliminada']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}