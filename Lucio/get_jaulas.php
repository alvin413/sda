<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

function jsonResponse($success, $message = '', $error = '', $data = []) {
    $response = ['success' => $success];
    
    if ($success) {
        // Respuesta exitosa
        if (!empty($message)) $response['message'] = $message;
        if (!empty($data)) $response['data'] = $data;
    } else {
        // Respuesta de error
        if (!empty($error)) $response['error'] = $error;
    }
    
    echo json_encode($response);
    exit;
}

$bunker_id = isset($_GET['bunker_id']) ? intval($_GET['bunker_id']) : null;

try {

    if ($bunker_id) {
            $query = "SELECT id, nombre FROM jaulas WHERE bunker_id = ? AND nombre <> 'SITIO CLIENTE' ORDER BY nombre ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$bunker_id]);
            writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode($bunker_id));
        } else {
            $query = "SELECT id, nombre FROM jaulas ORDER BY nombre ASC";
            $stmt = $pdo->query($query);
            writeLog('QUERY', "Consulta ejecutada: $query");
        }

    $jaulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    writeLog('SUCCESS', "Jaulas obtenidas correctamente | BunkerID: " . ($bunker_id ?? 'Todos'));
    
    echo json_encode($jaulas);

} catch (PDOException $e) {
    http_response_code(500);
    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Parámetros: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $query ?? 'N/A',
        json_encode($bunker_id ?? []),
        $e->getMessage()
    );
    writeLog('ERROR', "Error en la base de datos: " . $errorContext);
    jsonResponse(false, '', 'Error en la base de datos');
} catch (Exception $e) {
    http_response_code(500);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', 'Error inesperado');
}