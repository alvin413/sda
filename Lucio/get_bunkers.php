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

$ciudad_id = isset($_GET['ciudad_id']) ? intval($_GET['ciudad_id']) : null;

try {
    
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $isListadoServidores = strpos($referer, '/alarmas/dashboard/listado_servidores.php') !== false;

    
    if ($isListadoServidores) {
        if ($ciudad_id) {
            $query = "SELECT DISTINCT b.id, b.nombre 
                      FROM bunkers b 
                      INNER JOIN servidores s ON b.id = s.bunker_id 
                      WHERE b.ciudad_id = ? AND b.nombre <> 'SITIO CLIENTE' 
                      ORDER BY b.nombre ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$ciudad_id]);
            writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode([$ciudad_id]));
        } else {
            $query = "SELECT DISTINCT b.id, b.nombre 
                      FROM bunkers b 
                      INNER JOIN servidores s ON b.id = s.bunker_id 
                      WHERE b.nombre <> 'SITIO CLIENTE' 
                      ORDER BY b.nombre ASC";
            $stmt = $pdo->query($query);
            writeLog('QUERY', "Consulta ejecutada: $query");
        }
    } else {
        if ($ciudad_id) {
            $query = "SELECT id, nombre FROM bunkers WHERE ciudad_id = ? AND nombre <> 'SITIO CLIENTE' ORDER BY nombre ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$ciudad_id]);
            writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode([$ciudad_id]));
        } else {
            $query = "SELECT id, nombre FROM bunkers ORDER BY nombre ASC";
            $stmt = $pdo->query($query);
            writeLog('QUERY', "Consulta ejecutada: $query");
        }
    }

    $bunkers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    writeLog('SUCCESS', "Bunkers obtenidos correctamente | CiudadID: " . ($ciudad_id ?? 'Todos'));
    
    echo json_encode($bunkers);

} catch (PDOException $e) {
    http_response_code(500);
    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Parámetros: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $query ?? 'N/A',
        json_encode($ciudad_id ?? []),
        $e->getMessage()
    );
    writeLog('ERROR', "Error en la base de datos: " . $errorContext);
    jsonResponse(false, '', 'Error en la base de datos');
} catch (Exception $e) {
    http_response_code(500);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', 'Error inesperado');
}