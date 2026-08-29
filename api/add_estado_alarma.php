<?php
require_once __DIR__ . '/../config/db.php';

// Configurar headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    writeLog("ERROR", "JSON inválido recibido");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos']);
    exit;
}

$nombre = mb_strtoupper(trim($input['nombre'] ?? ''), 'UTF-8');
if (empty($nombre)) {
    writeLog("WARNING", "Nombre de tipo de alarma vacío");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

try {
    // Verificar duplicado
    $stmt = $pdo->prepare("SELECT id, nombre FROM estados_alarma WHERE UPPER(nombre) = UPPER(?)");
    $stmt->execute([$nombre]);
    writeLog("QUERY", "SELECT id, nombre FROM estados_alarma WHERE UPPER(nombre) = UPPER(?) - Parámetros: " . json_encode([$nombre]));

    if ($stmt->fetch()) {
        writeLog("WARNING", 'Tipo de alarma "'.$nombre.'" ya existe');
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Este tipo de alarma ya existe']);
        exit;
    }

    // Insertar
    $stmt = $pdo->prepare("INSERT INTO estados_alarma (nombre) VALUES (?)");
    $stmt->execute([$nombre]);
    writeLog("QUERY", "INSERT INTO estados_alarma (nombre) VALUES (?) - Parámetros: " . json_encode([$nombre]));

    $id = $pdo->lastInsertId();
    writeLog("ESTADOS_ALARMA", "Tipo de alarma creado exitosamente - ID: $id, Nombre: $nombre");

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Tipo de alarma creado exitosamente',
        'data' => [
            'id' => $id,
            'nombre' => $nombre
        ]
    ]);

} catch (PDOException $e) {
    writeLog("ERROR", "Error BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos', 'error' => $e->getMessage()]);
}
