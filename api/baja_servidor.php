<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}



if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

if (!isset($_POST['rfc_baja']) || empty(trim($_POST['rfc_baja']))) {
    echo json_encode(['success' => false, 'error' => 'RFC de baja requerido']);
    exit;
}

$id = $_POST['id'];
$rfc_baja = trim($_POST['rfc_baja']);

try {
    // Verificar que el servidor existe
    $stmt = $pdo->prepare("SELECT id FROM servidores WHERE id = ?");
    $stmt->execute([$id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Servidor no encontrado']);
        exit;
    }
    
    // Cambiar el estado a "baja" e insertar RFC
    $stmt = $pdo->prepare("UPDATE servidores SET estado = 'baja', rfc_baja = ?, disabled_at = NOW(), updated_at = NOW() WHERE id = ?");
    $stmt->execute([$rfc_baja, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Servidor dado de baja correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}
