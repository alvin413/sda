<?php
require_once __DIR__ . '/../config/db.php';

// Configurar headers para CORS y JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog("WARNING", "Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener el input JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validar JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    writeLog("WARNING", "JSON inválido recibido: " . file_get_contents('php://input'));
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos']);
    exit;
}

$nombre = trim($input['nombre'] ?? '');
$ciudad_id = isset($input['ciudad_id']) ? intval($input['ciudad_id']) : null;

// Validaciones
if ($nombre === '') {
    writeLog("WARNING", "Nombre de bunker vacío");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

if (!$ciudad_id) {
    writeLog("WARNING", "Ciudad de bunker no proporcionada");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La ciudad es requerida']);
    exit;
}

try {
    // Verificar si el bunker ya existe
    $stmt = $pdo->prepare("SELECT id FROM bunkers WHERE nombre = ? AND ciudad_id = ?");
    $stmt->execute([$nombre, $ciudad_id]);
    writeLog("QUERY", "SELECT id FROM bunkers WHERE nombre = ? AND ciudad_id = ? - Parámetros: " . json_encode([$nombre, $ciudad_id]));

    if ($stmt->fetch()) {
        writeLog("WARNING", "Intento de crear bunker duplicado: $nombre en ciudad_id $ciudad_id");
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'El bunker ya existe en esta ciudad']);
        exit;
    }

    // Insertar el nuevo bunker
    $stmt = $pdo->prepare("INSERT INTO bunkers (nombre, ciudad_id) VALUES (?, ?)");
    $stmt->execute([$nombre, $ciudad_id]);
    writeLog("QUERY", "INSERT INTO bunkers (nombre, ciudad_id) VALUES (?, ?) - Parámetros: " . json_encode([$nombre, $ciudad_id]));

    $id = $pdo->lastInsertId();

    // Obtener el nombre de la ciudad para la respuesta
    $stmt = $pdo->prepare("SELECT nombre FROM ciudades WHERE id = ?");
    $stmt->execute([$ciudad_id]);
    writeLog("QUERY", "SELECT nombre FROM ciudades WHERE id = ? - Parámetros: " . json_encode([$ciudad_id]));
    
    $ciudad = $stmt->fetch();
    $ciudad_nombre = $ciudad ? $ciudad['nombre'] : '';

    writeLog("BUNKERS", "Bunker creado exitosamente - ID: $id, Nombre: $nombre, Ciudad: $ciudad_nombre");

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Bunker creado exitosamente',
        'data' => [
            'id' => $id,
            'nombre' => $nombre,
            'ciudad_id' => $ciudad_id,
            'ciudad_nombre' => $ciudad_nombre
        ]
    ]);

} catch (PDOException $e) {
    writeLog("ERROR", "Excepción PDO al crear bunker: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
} catch (Exception $e) {
    writeLog("ERROR", "Excepción al crear bunker: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
