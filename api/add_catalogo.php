<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$catalogo = $_POST['catalogo'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$padre = $_POST['padre'] ?? null;

if ($nombre === '') {
    writeLog("WARNING", "Nombre vacío para catálogo $catalogo");
    echo json_encode(['success' => false, 'error' => 'Nombre vacío']);
    exit;
}

switch ($catalogo) {
    case 'ciudad':
        $table = 'catalog_ciudad';
        $padre_campo = null;
        break;
    case 'bunker':
        $table = 'catalog_bunker';
        $padre_campo = 'ciudad_id';
        break;
    case 'jaula':
        $table = 'catalog_jaula';
        $padre_campo = 'bunker_id';
        break;
    case 'rack':
        $table = 'catalog_rack';
        $padre_campo = 'jaula_id';
        break;
    case 'cliente':
        $table = 'catalog_cliente';
        $padre_campo = null;
        break;
    case 'marca':
        $table = 'catalog_marca';
        $padre_campo = null;
        break;
    case 'modelo':
        $table = 'catalog_modelo';
        $padre_campo = 'marca_id';
        break;
    default:
        writeLog("WARNING", "Catálogo inválido recibido: $catalogo");
        echo json_encode(['success' => false, 'error' => 'Catálogo inválido']);
        exit;
}

// Validar campo padre si aplica
if ($padre_campo !== null && empty($padre)) {
    writeLog("WARNING", "Falta id de elemento padre para catálogo $catalogo");
    echo json_encode(['success' => false, 'error' => 'Falta id de elemento padre']);
    exit;
}

try {
    // Verificar si ya existe
    if ($padre_campo) {
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE nombre = ? AND $padre_campo = ?");
        $stmt->execute([$nombre, $padre]);
        writeLog("QUERY", "SELECT id FROM $table WHERE nombre = ? AND $padre_campo = ? - Parámetros: " . json_encode([$nombre, $padre]));
    } else {
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE nombre = ?");
        $stmt->execute([$nombre]);
        writeLog("QUERY", "SELECT id FROM $table WHERE nombre = ? - Parámetros: " . json_encode([$nombre]));
    }

    if ($stmt->fetch()) {
        writeLog("WARNING", "Registro duplicado en $catalogo: $nombre");
        echo json_encode(['success' => false, 'error' => 'Ya existe registro con ese nombre']);
        exit;
    }

    // Insertar nuevo registro
    if ($padre_campo) {
        $stmt = $pdo->prepare("INSERT INTO $table (nombre, $padre_campo) VALUES (?, ?)");
        $success = $stmt->execute([$nombre, $padre]);
        writeLog("QUERY", "INSERT INTO $table (nombre, $padre_campo) VALUES (?, ?) - Parámetros: " . json_encode([$nombre, $padre]));
    } else {
        $stmt = $pdo->prepare("INSERT INTO $table (nombre) VALUES (?)");
        $success = $stmt->execute([$nombre]);
        writeLog("QUERY", "INSERT INTO $table (nombre) VALUES (?) - Parámetros: " . json_encode([$nombre]));
    }

    if ($success) {
        $id = $pdo->lastInsertId();
        writeLog("CATALOGOS", "Nuevo registro creado en $catalogo - ID: $id, Nombre: $nombre");
        echo json_encode(['success' => true, 'id' => $id, 'nombre' => $nombre]);
    } else {
        writeLog("ERROR", "Error al insertar registro en $catalogo - Nombre: $nombre");
        echo json_encode(['success' => false, 'error' => 'Error al insertar']);
    }
} catch (PDOException $e) {
    writeLog("ERROR", "Excepción PDO al insertar en $catalogo: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}
