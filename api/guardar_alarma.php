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

    // Obtener datos del POST
    $tipoAlarmaId = $_POST['tipo_alarma_id'] ?? null;
    $estadoAlarmaId = $_POST['estado_alarma_id'] ?? null;
    $fechaDeteccion = $_POST['fecha_deteccion'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
	$im = $_POST['im'] ?? null;
    $noSerie = $_POST['no_serie'] ?? null;
    $usuarioRegistro = $_SESSION['user_id'];

    // Validaciones básicas
    if (!$tipoAlarmaId || !$estadoAlarmaId || !$fechaDeteccion || empty($descripcion) || empty($noSerie) || empty($im)) {
		throw new Exception('Faltan campos requeridos');
	}

    // Preparar datos para la inserción (CON LOS NUEVOS CAMPOS)
    $params = [
        'servidor_id' => $_POST['servidor_id'] ?? null,
        'no_serie' => $noSerie,
        'ciudad_id' => $_POST['ciudad_id'] ?? null,
        'bunker_id' => $_POST['bunker_id'] ?? null,
        'jaula_id' => $_POST['jaula_id'] ?? null,
        'rack_id' => $_POST['rack_id'] ?? null,
        'cliente_id' => $_POST['cliente_id'] ?? null,
        'marca_id' => $_POST['marca_id'] ?? null,
        'modelo_id' => $_POST['modelo_id'] ?? null,
        'ubicacion_manual' => $_POST['unidad_rack'] ?? null,
        'im' => $im,
		'tipo_alarma_id' => $tipoAlarmaId,
        'estado_alarma_id' => $estadoAlarmaId,
        'fecha_deteccion' => $fechaDeteccion,
        'fecha_resolucion' => null,
        'descripcion' => $descripcion,
        'usuario_registro' => $usuarioRegistro
    ];

    // Insertar en la base de datos (query actualizada con nuevos campos)
	$query = "INSERT INTO alarmas_servidores (
		servidor_id, no_serie, ciudad_id, bunker_id, jaula_id, rack_id, 
		cliente_id, marca_id, modelo_id, ubicacion_manual, im, 
		tipo_alarma_id, estado_alarma_id, 
		fecha_deteccion, fecha_resolucion, descripcion, usuario_registro
	) VALUES (
		:servidor_id, :no_serie, :ciudad_id, :bunker_id, :jaula_id, :rack_id, 
		:cliente_id, :marca_id, :modelo_id, :ubicacion_manual, :im, 
		:tipo_alarma_id, :estado_alarma_id,
		:fecha_deteccion, :fecha_resolucion, :descripcion, :usuario_registro
	)";


    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode($params));

    echo json_encode([
        'success' => true,
        'message' => 'Alarma registrada correctamente',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    $errorContext = sprintf(
        "UsuarioID: %s | Query: %s | Parámetros: %s | Error: %s",
        $_SESSION['user_id'] ?? 'desconocido',
        $query ?? 'N/A',
        isset($params) ? json_encode($params) : 'N/A',
        $e->getMessage()
    );

    writeLog('ERROR', "Error en la base de datos: " . $errorContext);

    // Error 1062 = registro duplicado
    if ($e->errorInfo[1] == 1062) {
        $mensaje = $e->getMessage();
        if (preg_match("/Duplicate entry '([^']+)' for key 'im'/", $mensaje, $matches)) {
            $imDuplicado = $matches[1];
			jsonResponse(false,'','El IM "' . $imDuplicado . '" ya está registrado en el sistema. Por favor, verifica el número de IM.'
            );
        }
        jsonResponse(false,'','El registro que intentas guardar ya existe en el sistema.'
        );
    }
    jsonResponse(false,'','Error en la base de datos. No fue posible registrar la alarma.');
} catch (Exception $e) {
    http_response_code(500);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', 'Error inesperado');
}