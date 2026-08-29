<?php
require_once __DIR__ . '/../config/db.php';

// Configurar headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        writeLog('ERROR', 'JSON inválido recibido');
        throw new Exception('Formato JSON inválido');
    }

    if (empty($data['nombre'])) {
        writeLog('WARNING', 'Nombre de marca vacío');
        throw new Exception('El nombre de la marca es requerido', 400);
    }

    $nombre = mb_strtoupper(trim($data['nombre']), 'UTF-8');

    // Verificar si la marca ya existe
    $stmt = $pdo->prepare("SELECT id, nombre FROM marcas WHERE UPPER(nombre) = UPPER(?)");
    $stmt->execute([$nombre]);
    writeLog('QUERY', "SELECT id, nombre FROM marcas WHERE UPPER(nombre) = UPPER(?) - Parámetros: " . json_encode([$nombre]));

    if ($existing = $stmt->fetch()) {
        writeLog('WARNING', 'La marca "'.$existing['nombre'].'" ya existe');
        throw new Exception('La marca "'.$existing['nombre'].'" ya existe', 409);
    }

    // Insertar nueva marca
    $stmt = $pdo->prepare("INSERT INTO marcas (nombre) VALUES (?)");
    $stmt->execute([$nombre]);
    writeLog('QUERY', "INSERT INTO marcas (nombre) VALUES (?) - Parámetros: " . json_encode([$nombre]));

    $newId = $pdo->lastInsertId();
    writeLog('MARCAS', 'Marca creada exitosamente - ID: ' . $newId . ', Nombre: ' . $nombre);

    echo json_encode([
        'success' => true,
        'message' => 'Marca creada exitosamente',
        'data' => [
            'id' => $newId,
            'nombre' => $nombre
        ]
    ]);
    exit;

} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($code);
    writeLog('ERROR', 'Error al crear marca: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $code
    ]);
    exit;
}
