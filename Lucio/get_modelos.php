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

$marca_id = isset($_GET['marca_id']) ? intval($_GET['marca_id']) : null;

try {
    if ($marca_id) {
        $query = "SELECT id, nombre FROM modelos WHERE marca_id = ? ORDER BY nombre ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$marca_id]);
        writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode($marca_id));
    } else {
        $query = "SELECT id, nombre FROM modelos ORDER BY nombre ASC";
        $stmt = $pdo->query($query);
        writeLog('QUERY', "Consulta ejecutada: $query");
    }
    $modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    writeLog('SUCCESS', "Modelos obtenidos correctamente | MarcaID: " . ($marca_id ?? 'Todos'));
    
    echo json_encode($modelos);

} catch (PDOException $e) {
    http_response_code(500);
    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Parámetros: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $query ?? 'N/A',
        json_encode($marca_id ?? []),
        $e->getMessage()
    );
    writeLog('ERROR', "Error en la base de datos: " . $errorContext);
    jsonResponse(false, '', 'Error en la base de datos');
} catch (Exception $e) {
    http_response_code(500);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', 'Error inesperado');
}