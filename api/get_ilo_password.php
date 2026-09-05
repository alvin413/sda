<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

function jsonResponse($success, $message = '', $error = '', $data = []) {
    $response = ['success' => $success];

    if ($success) {
        if (!empty($message)) {
            $response['message'] = $message;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }
    } else {
        if (!empty($error)) {
            $response['error'] = $error;
        }
    }

    echo json_encode($response);
    exit;
}

// Validar sesión

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    writeLog(
        'ERROR',
        'Intento de acceso no autorizado a credencial ILO'
    );

    jsonResponse(false, '', 'No autorizado');
}

// Validar ID
 
$servidor_id = $_GET['id'] ?? 0;

if (!$servidor_id || !is_numeric($servidor_id)) {
    http_response_code(400);

    writeLog(
        'ERROR',
        'ID de servidor no válido al solicitar contraseña ILO'
    );

    jsonResponse(false, '', 'ID no válido');
}

try {

     // Obtener únicamente la contraseña cifrada
    $query = "
        SELECT id, ilo_password
        FROM servidores
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$servidor_id]);

    $servidor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servidor) {
        http_response_code(404);

        writeLog(
            'ERROR',
            "Servidor no encontrado al solicitar contraseña ILO. ID: $servidor_id"
        );

        jsonResponse(false, '', 'Servidor no encontrado');
    }

     // No hay contraseña configurada
    if (empty($servidor['ilo_password'])) {

        writeLog(
            'INFO',
            "Solicitud de contraseña ILO sin credencial configurada. " .
            "UsuarioID: " . ($_SESSION['user_id'] ?? 'desconocido') .
            " | ServidorID: $servidor_id"
        );

        jsonResponse(true, '', '', [
            'password' => ''
        ]);
    }

     // Descifrar contraseña
    $password = decryptPassword($servidor['ilo_password']);

    if ($password === false || $password === null) {

        writeLog(
            'ERROR',
            "No fue posible descifrar contraseña ILO. " .
            "UsuarioID: " . ($_SESSION['user_id'] ?? 'desconocido') .
            " | ServidorID: $servidor_id"
        );

        http_response_code(500);

        jsonResponse(
            false,
            '',
            'No fue posible obtener la contraseña'
        );
    }

     // Registrar acceso a credencial sensible
    writeLog(
        'INFO',
        "Contraseña ILO consultada. " .
        "UsuarioID: " . ($_SESSION['user_id'] ?? 'desconocido') .
        " | ServidorID: $servidor_id"
    );

     // Devolver únicamente la contraseña
    jsonResponse(true, '', '', [
        'password' => $password
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    writeLog(
        'ERROR',
        "Error de BD al obtener contraseña ILO. " .
        "UsuarioID: " . ($_SESSION['user_id'] ?? 'desconocido') .
        " | ServidorID: $servidor_id" .
        " | Error: " . $e->getMessage()
    );

    jsonResponse(
        false,
        '',
        'Error en la base de datos'
    );

} catch (Exception $e) {

    http_response_code(500);

    writeLog(
        'ERROR',
        "Error inesperado al obtener contraseña ILO. " .
        "UsuarioID: " . ($_SESSION['user_id'] ?? 'desconocido') .
        " | ServidorID: $servidor_id"
    );

    jsonResponse(
        false,
        '',
        'Error inesperado'
    );
}
