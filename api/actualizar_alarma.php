<?php
session_start();
require_once "../config/db.php";

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    writeLog("WARNING", "Intento de acceso sin sesión");
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Leer datos
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$alarma_id = $data['alarma_id'] ?? 0;
$caso = trim($data['caso'] ?? '');

// Validar ID
if (!$alarma_id) {
    writeLog("WARNING", "ID de alarma no válido: " . json_encode($data));
    echo json_encode(['success' => false, 'error' => 'ID de alarma no válido']);
    exit;
}

try {
    $query = "UPDATE alarmas_servidores SET 
              tipo_alarma_id = ?,
              estado_alarma_id = ?,
              fecha_deteccion = ?,
              descripcion = ?,
              caso = ?,
              updated_at = NOW()
              WHERE id = ?";
    
    $params = [
        $data['tipo_alarma_id'],
        $data['estado_alarma_id'],
        $data['fecha_deteccion'],
        $data['descripcion'],
        $caso,
        $alarma_id
    ];

    writeLog("QUERY", "Ejecutando: $query - Parámetros: " . json_encode($params));

    $stmt = $pdo->prepare($query);
    $success = $stmt->execute($params);

    if ($success) {
        writeLog("ALARMAS", "Alarma actualizada correctamente - ID: $alarma_id");
        echo json_encode(['success' => true, 'message' => 'Alarma actualizada correctamente']);
    } else {
        writeLog("ERROR", "Error al actualizar alarma - ID: $alarma_id");
        echo json_encode(['success' => false, 'error' => 'Error al actualizar la alarma']);
    }
} catch (PDOException $e) {
    writeLog("ERROR", "Error en BD al actualizar alarma: " . $e->getMessage() . " - ID: $alarma_id");
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}
