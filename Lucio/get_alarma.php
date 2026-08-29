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

$alarma_id = $_GET['id'] ?? 0;

if (!$alarma_id || !is_numeric($alarma_id)) {
    http_response_code(400);
    writeLog('ERROR', 'ID no válido');
    echo json_encode(['success' => false, 'error' => 'ID no válido']);
    exit;
}

try {
    $query = "SELECT 
            a.*,
            c.nombre as ciudad_nombre,
            b.nombre as bunker_nombre,
            j.nombre as jaula_nombre,
            r.nombre as rack_nombre,
            cl.nombre as cliente_nombre,
            m.nombre as marca_nombre,
            modl.nombre as modelo_nombre,
            ta.nombre as tipo_alarma_nombre,
            ea.nombre as estado_alarma_nombre,
            u.username as usuario_nombre
        FROM alarmas_servidores a
        LEFT JOIN ciudades c ON a.ciudad_id = c.id
        LEFT JOIN bunkers b ON a.bunker_id = b.id
        LEFT JOIN jaulas j ON a.jaula_id = j.id
        LEFT JOIN racks r ON a.rack_id = r.id
        LEFT JOIN clientes cl ON a.cliente_id = cl.id
        LEFT JOIN marcas m ON a.marca_id = m.id
        LEFT JOIN modelos modl ON a.modelo_id = modl.id
        LEFT JOIN tipos_alarma ta ON a.tipo_alarma_id = ta.id
        LEFT JOIN estados_alarma ea ON a.estado_alarma_id = ea.id
        LEFT JOIN usuarios u ON a.usuario_registro = u.id
        WHERE a.id = ?";
    // Consulta para obtener los detalles completos de la alarma

    $stmt = $pdo->prepare($query);
    $stmt->execute([$alarma_id]);
    writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode([$alarma_id]));
    $alarma = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alarma) {
        http_response_code(404);
        writeLog('ERROR', 'Alarma no encontrada');
        echo json_encode(['success' => false, 'error' => 'Alarma no encontrada']);
        exit;
    }else{
        writeLog('SUCCESS', "Alarmas obtenidas correctamente");
    }
    
    echo json_encode(['success' => true, 'data' => $alarma]);
    
} catch (PDOException $e) {
    // Manejo de errores de base de datos
    http_response_code(500);
    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Parámetros: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $query ?? 'N/A',
        json_encode($alarma_id ?? []),
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