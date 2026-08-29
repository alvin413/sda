<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

function jsonResponse($success, $message = '', $error = '', $data = []) {
    $response = ['success' => $success];
    
    if ($success) {
        if (!empty($message)) $response['message'] = $message;
        if (!empty($data)) $response['data'] = $data;
    } else {
        if (!empty($error)) $response['error'] = $error;
    }
    
    echo json_encode($response);
    exit;
}

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    writeLog('ERROR', 'Acceso no autorizado, sesión inválida');
    jsonResponse(false, '', 'No autorizado');
}

// Validar ID de servidor
$servidor_id = $_GET['id'] ?? 0;
if (!$servidor_id || !is_numeric($servidor_id)) {
    http_response_code(400);
    writeLog('ERROR', 'ID no válido');
    jsonResponse(false, '', 'ID no válido');
}

try {
    $query = "
        SELECT s.*, 
               c.nombre AS ciudad, b.nombre AS bunker, j.nombre AS jaula, r.nombre AS rack,
               cl.nombre AS cliente, m.nombre AS marca, mo.nombre AS modelo
        FROM servidores s
        LEFT JOIN ciudades c ON s.ciudad_id = c.id
        LEFT JOIN bunkers b ON s.bunker_id = b.id
        LEFT JOIN jaulas j ON s.jaula_id = j.id
        LEFT JOIN racks r ON s.rack_id = r.id
        LEFT JOIN clientes cl ON s.cliente_id = cl.id
        LEFT JOIN marcas m ON s.marca_id = m.id
        LEFT JOIN modelos mo ON s.modelo_id = mo.id
        WHERE s.id = ?
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$servidor_id]);
    writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode([$servidor_id]));
    $servidor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servidor) {
        http_response_code(404);
        writeLog('ERROR', 'Servidor no encontrado');
        jsonResponse(false, '', 'Servidor no encontrado');
    }

    // Desencriptar contraseña ILO si existe
    $servidor['iloPasswordDecrypted'] = !empty($servidor['ilo_password']) ? decryptPassword($servidor['ilo_password']) : '';

    // Formatear fechas
    $servidor['created_at'] = !empty($servidor['created_at']) ? (new DateTime($servidor['created_at']))->format('d/m/Y H:i') : '';
    $servidor['fecha_garantia'] = !empty($servidor['fecha_garantia']) ? (new DateTime($servidor['fecha_garantia']))->format('d/m/Y') : '';

    jsonResponse(true, '', '', $servidor);

} catch (PDOException $e) {
    http_response_code(500);
    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Parámetros: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $query ?? 'N/A',
        json_encode([$servidor_id]),
        $e->getMessage()
    );
    writeLog('ERROR', "Error en la base de datos: " . $errorContext);
    jsonResponse(false, '', 'Error en la base de datos');
} catch (Exception $e) {
    http_response_code(500);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', 'Error inesperado');
}
