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
    $marca_id = isset($input['marca_id']) ? intval($input['marca_id']) : null;

    if ($nombre === '') {
        writeLog('WARNING', 'Nombre de modelo vacío');
        throw new Exception('El nombre es requerido', 400);
    }

    if (!$marca_id) {
        writeLog('WARNING', 'Marca no proporcionada para modelo: ' . $nombre);
        throw new Exception('La marca es requerida', 400);
    }

    // Verificar si el modelo ya existe
    $stmt = $pdo->prepare("SELECT id FROM modelos WHERE UPPER(nombre) = UPPER(?) AND marca_id = ?");
    $stmt->execute([$nombre, $marca_id]);
    writeLog('QUERY', "SELECT id FROM modelos WHERE UPPER(nombre) = UPPER(?) AND marca_id = ? - Parámetros: " . json_encode([$nombre, $marca_id]));

    if ($stmt->fetch()) {
        writeLog('WARNING', 'El modelo "' . $nombre . '" ya existe en la marca ID ' . $marca_id);
        throw new Exception('El modelo ya existe en esta marca', 409);
    }

    // Insertar nuevo modelo
    $stmt = $pdo->prepare("INSERT INTO modelos (nombre, marca_id) VALUES (?, ?)");
    $stmt->execute([$nombre, $marca_id]);
    writeLog('QUERY', "INSERT INTO modelos (nombre, marca_id) VALUES (?, ?) - Parámetros: " . json_encode([$nombre, $marca_id]));

    $id = $pdo->lastInsertId();

    // Obtener nombre de la marca
    $stmt = $pdo->prepare("SELECT nombre FROM marcas WHERE id = ?");
    $stmt->execute([$marca_id]);
    $marca = $stmt->fetch();
    $marca_nombre = $marca ? $marca['nombre'] : '';

    writeLog('MODELOS', 'Modelo creado exitosamente - ID: ' . $id . ', Nombre: ' . $nombre . ', Marca: ' . $marca_nombre);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Modelo creado exitosamente',
        'data' => [
            'id' => $id,
            'nombre' => $nombre,
            'marca_id' => $marca_id,
            'marca_nombre' => $marca_nombre
        ]
    ]);

} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($code);
    writeLog('ERROR', 'Error al crear modelo: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $code
    ]);
}
