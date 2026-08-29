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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog('WARNING', 'Método no permitido');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    writeLog('ERROR', 'JSON inválido recibido');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos']);
    exit;
}

$nombre = trim($input['nombre'] ?? '');
$bunker_id = isset($input['bunker_id']) ? intval($input['bunker_id']) : null;

// Validaciones
if ($nombre === '') {
    writeLog('WARNING', 'Nombre de jaula vacío');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

if (!$bunker_id) {
    writeLog('WARNING', 'ID de bunker no proporcionado');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El bunker es requerido']);
    exit;
}

try {
    // Verificar duplicado
    $stmt = $pdo->prepare("SELECT id FROM jaulas WHERE nombre = ? AND bunker_id = ?");
    $stmt->execute([$nombre, $bunker_id]);
    writeLog('QUERY', "SELECT id FROM jaulas WHERE nombre = ? AND bunker_id = ? - Parámetros: " . json_encode([$nombre, $bunker_id]));

    if ($stmt->fetch()) {
        writeLog('WARNING', 'Jaula "'.$nombre.'" ya existe en el bunker ID ' . $bunker_id);
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'La jaula ya existe en este bunker']);
        exit;
    }

    // Insertar jaula
    $stmt = $pdo->prepare("INSERT INTO jaulas (nombre, bunker_id) VALUES (?, ?)");
    $stmt->execute([$nombre, $bunker_id]);
    writeLog('QUERY', "INSERT INTO jaulas (nombre, bunker_id) VALUES (?, ?) - Parámetros: " . json_encode([$nombre, $bunker_id]));

    $id = $pdo->lastInsertId();

    // Obtener nombre del bunker
    $stmt = $pdo->prepare("SELECT nombre FROM bunkers WHERE id = ?");
    $stmt->execute([$bunker_id]);
    $bunker = $stmt->fetch();
    $bunker_nombre = $bunker ? $bunker['nombre'] : '';

    writeLog('JAULAS', "Jaula creada exitosamente - ID: $id, Nombre: $nombre, Bunker: $bunker_id ($bunker_nombre)");

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Jaula creada exitosamente',
        'data' => [
            'id' => $id,
            'nombre' => $nombre,
            'bunker_id' => $bunker_id,
            'bunker_nombre' => $bunker_nombre
        ]
    ]);

} catch (PDOException $e) {
    writeLog('ERROR', 'Error BD al crear jaula: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    writeLog('ERROR', 'Error general: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
