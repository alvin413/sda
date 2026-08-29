<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

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

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    writeLog('ERROR', 'Acceso no autorizado, sesión inválida');
    jsonResponse(false, '', 'No autorizado');
}

try {
    // Obtener datos del POST o JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = $_POST;

    $alarma_id = $input['id'] ?? null;

    if (!$alarma_id) {
        throw new Exception('ID de alarma no válido');
    }

    // Campos permitidos para actualizar
    $campos = [
        'ciudad_id', 'bunker_id', 'jaula_id', 'rack_id', 'cliente_id',
        'marca_id', 'modelo_id', 'no_serie', 'ubicacion_manual', 
        'tipo_alarma_id', 'estado_alarma_id', 'fecha_deteccion', 
        'fecha_resolucion', 'resolucion', 'descripcion', 'caso'
    ];


    $setParts = [];
    $values = [];

    foreach ($campos as $campo) {
    if (isset($input[$campo])) {
        $valor = $input[$campo];

        // Convertir vacío a null
        if ($valor === '') {
            $valor = null;
        }

        if (isset($input['resolucion'])) {
            $input['resolucion'] = mb_strtoupper($input['resolucion'], 'UTF-8');
        }

        // Normalizar fechas
        if (in_array($campo, ['fecha_deteccion', 'fecha_resolucion']) && !empty($valor)) {
            $valor = str_replace('T', ' ', $valor); // 2025-09-18T19:20 -> 2025-09-18 19:20
            if (strlen($valor) === 16) {
                $valor .= ':00'; // agregar segundos si no existen
            }
        }

        $setParts[] = "$campo = ?";
        $values[] = $valor;
    }
}


    if (empty($setParts)) {
        throw new Exception('No hay datos para actualizar');
    }

    // Agregar ID al final de los valores
    $values[] = $alarma_id;

    $sql = "UPDATE alarmas_servidores SET " . implode(', ', $setParts) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    writeLog('QUERY', "Actualización ejecutada: $sql | Valores: " . json_encode($values));

    jsonResponse(true, 'Alarma actualizada correctamente');

} catch (PDOException $e) {
    http_response_code(500);
    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Valores: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $sql ?? 'N/A',
        isset($values) ? json_encode($values) : 'N/A',
        $e->getMessage()
    );
    writeLog('ERROR', "Error en la base de datos: " . $errorContext);
    jsonResponse(false, '', 'Error en la base de datos');
} catch (Exception $e) {
    http_response_code(400);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', $e->getMessage());
}
