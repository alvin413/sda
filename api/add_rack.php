<?php
require_once __DIR__ . '/../config/db.php';

// Configurar headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        writeLog('ERROR', 'JSON inválido recibido');
        throw new Exception('Datos JSON inválidos');
    }

    $nombre = mb_strtoupper(trim($input['nombre'] ?? ''), 'UTF-8');
    $jaula_id = isset($input['jaula_id']) ? intval($input['jaula_id']) : null;

    if ($nombre === '') {
        writeLog('WARNING', 'Nombre de rack vacío');
        throw new Exception('El nombre es requerido', 400);
    }

    if (!$jaula_id) {
        writeLog('WARNING', 'Jaula no proporcionada para rack: ' . $nombre);
        throw new Exception('La jaula es requerida', 400);
    }

    // Verificar si el rack ya existe
    $stmt = $pdo->prepare("SELECT id FROM racks WHERE UPPER(nombre) = UPPER(?) AND jaula_id = ?");
    $stmt->execute([$nombre, $jaula_id]);
    writeLog('QUERY', "SELECT id FROM racks WHERE UPPER(nombre) = UPPER(?) AND jaula_id = ? - Parámetros: " . json_encode([$nombre, $jaula_id]));

    if ($stmt->fetch()) {
        writeLog('WARNING', 'El rack "' . $nombre . '" ya existe en la jaula ID ' . $jaula_id);
        throw new Exception('El rack ya existe en esta jaula', 409);
    }

    // Insertar nuevo rack
    $stmt = $pdo->prepare("INSERT INTO racks (nombre, jaula_id) VALUES (?, ?)");
    $stmt->execute([$nombre, $jaula_id]);
    writeLog('QUERY', "INSERT INTO racks (nombre, jaula_id) VALUES (?, ?) - Parámetros: " . json_encode([$nombre, $jaula_id]));

    $id = $pdo->lastInsertId();

    // Obtener nombre de la jaula
    $stmt = $pdo->prepare("SELECT nombre FROM jaulas WHERE id = ?");
    $stmt->execute([$jaula_id]);
    $jaula = $stmt->fetch();
    $jaula_nombre = $jaula ? $jaula['nombre'] : '';

    writeLog('RACKS', 'Rack creado exitosamente - ID: ' . $id . ', Nombre: ' . $nombre . ', Jaula: ' . $jaula_nombre);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Rack creado exitosamente',
        'data' => [
            'id' => $id,
            'nombre' => $nombre,
            'jaula_id' => $jaula_id,
            'jaula_nombre' => $jaula_nombre
        ]
    ]);

} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($code);
    writeLog('ERROR', 'Error al crear rack: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $code
    ]);
}
