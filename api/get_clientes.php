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

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    writeLog('ERROR', 'Acceso no autorizado, sesión inválida');
    jsonResponse(false, '', 'No autorizado');
}

try {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $isListadoServidores = strpos($referer, '/alarmas/dashboard/listado_servidores.php') !== false;
    
    if ($isListadoServidores) {
        $query = "SELECT DISTINCT c.id, c.nombre FROM clientes c INNER JOIN servidores s ON c.id = s.cliente_id ORDER BY c.nombre ASC";
    } else {
        $query = "SELECT id, nombre FROM clientes ORDER BY nombre ASC";
    }

    $stmt = $pdo->query($query);
    writeLog('QUERY', "Consulta ejecutada: $query");
    $clientes = $stmt->fetchAll();
    echo json_encode($clientes);

} catch (PDOException $e) {
    // Manejo de errores de base de datos
    http_response_code(500);
    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Parámetros: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $query ?? 'N/A',
        $e->getMessage()
    );
    writeLog('ERROR', "Error en la base de datos: " . $errorContext);
    jsonResponse(false, '', 'Error en la base de datos');
} catch (Exception $e) {
    // Manejo de errores genéricos
    http_response_code(500);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', 'Error inesperado');
}