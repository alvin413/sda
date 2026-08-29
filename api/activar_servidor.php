<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    writeLog("WARNING", "Intento de acceso sin sesión");
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog("WARNING", "Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Validar ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    writeLog("WARNING", "ID inválido: " . ($_POST['id'] ?? 'N/A'));
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

// Validar RFC
if (!isset($_POST['rfc_activar']) || empty(trim($_POST['rfc_activar']))) {
    writeLog("WARNING", "RFC requerido para reactivación. ID: " . $_POST['id']);
    echo json_encode(['success' => false, 'error' => 'RFC requerido']);
    exit;
}

$id = $_POST['id'];
$rfc = trim($_POST['rfc_activar']);

try {
    // Verificar que el servidor exista
    $query = "SELECT id FROM servidores WHERE id = ?";
    writeLog("QUERY", "Ejecutando: $query - Parámetros: [$id]");

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    if (!$stmt->fetch()) {
        writeLog("WARNING", "Servidor no encontrado. ID: $id");
        echo json_encode(['success' => false, 'error' => 'Servidor no encontrado']);
        exit;
    }

    // Actualizar estado y RFC de reactivación
    $query = "UPDATE servidores 
              SET estado = 'activo', rfc_alta = ?, updated_at = NOW() 
              WHERE id = ?";
    writeLog("QUERY", "Ejecutando: $query - Parámetros: [$rfc, $id]");

    $stmt = $pdo->prepare($query);
    $stmt->execute([$rfc, $id]);

    writeLog("QUERY", "Servidor reactivado correctamente. ID: $id, RFC: $rfc");

    echo json_encode(['success' => true, 'message' => 'Servidor reactivado correctamente']);

} catch (PDOException $e) {
    writeLog("ERROR", "Error en reactivación de servidor: " . $e->getMessage() . " | ID: $id | RFC: $rfc");
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}
