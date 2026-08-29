<?php
require_once __DIR__ . '/../config/db.php';

// Configurar headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

// Permitir opciones preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Obtener datos del request (JSON)
    $data = json_decode(file_get_contents('php://input'), true);

    // Validar JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        writeLog("ERROR", "Formato JSON inválido");
        throw new Exception('Formato JSON inválido', 400);
    }

    // Validaciones
    if (empty($data['nombre'])) {
        writeLog("WARNING", "Nombre de ciudad no proporcionado");
        throw new Exception('El nombre de la ciudad es requerido', 400);
    }

    $nombre = mb_strtoupper(trim($data['nombre']), 'UTF-8');

    // Verificar si la ciudad ya existe
    $stmt = $pdo->prepare("SELECT id, nombre FROM ciudades WHERE UPPER(nombre) = UPPER(?)");
    $stmt->execute([$nombre]);
    writeLog("QUERY", "SELECT id, nombre FROM ciudades WHERE UPPER(nombre) = UPPER(?) - Parámetros: " . json_encode([$nombre]));

    if ($existing = $stmt->fetch()) {
        writeLog("WARNING", 'La ciudad "'.$existing['nombre'].'" ya existe');
        throw new Exception('La ciudad "'.$existing['nombre'].'" ya existe', 409);
    }

    // Insertar nueva ciudad
    $stmt = $pdo->prepare("INSERT INTO ciudades (nombre) VALUES (?)");
    $stmt->execute([$nombre]);
    writeLog("QUERY", "INSERT INTO ciudades (nombre) VALUES (?) - Parámetros: " . json_encode([$nombre]));

    $newId = $pdo->lastInsertId();
    writeLog("CIUDADES", "Ciudad creada exitosamente - ID: $newId, Nombre: $nombre");

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Ciudad creada exitosamente',
        'data' => [
            'id' => $newId,
            'nombre' => $nombre
        ]
    ]);
    exit;

} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    writeLog("ERROR", "Excepción: " . $e->getMessage() . " - Código: $code");
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => $code
    ]);
    exit;
}
