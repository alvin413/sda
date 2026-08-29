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

// Campos obligatorios
$required = [
    'ciudad_id',
    'bunker_id',
    'jaula_id',
    'cliente_id',
    'rfc_solicitante',
    'lider_proyecto',
    'fecha_alta',
    'servicios_contratados',
    'tipo_acceso'
];

// Validación de campos
foreach ($required as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        jsonResponse(false, '', "Falta el campo $field");
    }
}

// Campos opcionales
$observaciones = $_POST['observaciones'] ?? null;
$rack = $_POST['rack'] ?? null;
$tipoAcceso = $_POST['tipo_acceso'];
$llave = $_POST['llave'] ?? null;

// VALIDACIÓN FUERA DEL ARRAY (CORRECTO)
if ($tipoAcceso === 'LLAVE') {
    if (empty($llave)) {
        jsonResponse(false, '', 'La llave es obligatoria para acceso tipo LLAVE');
    }
    if (!preg_match('/^[A-Za-z0-9]+$/', $llave)) {
        jsonResponse(false, '', 'La llave solo puede contener letras y números');
    }
}

try {

    // Preparar parámetros
    $params = [
        'ciudad_id' => $_POST['ciudad_id'],
        'bunker_id' => $_POST['bunker_id'],
        'jaula_id' => $_POST['jaula_id'],
        'rack' => $rack,
        'tipo_acceso' => $tipoAcceso,
        'llave' => $llave,
        'cliente_id' => $_POST['cliente_id'],
        'rfc_solicitante' => $_POST['rfc_solicitante'],
        'lider_proyecto' => $_POST['lider_proyecto'],
        'fecha_alta' => $_POST['fecha_alta'],
        'observaciones' => $observaciones,
        'servicios_contratados' => $_POST['servicios_contratados'],
        'usuario_registro' => $_SESSION['user_id']
    ];

    $query = "INSERT INTO rondines_racks (
        ciudad_id,
        bunker_id,
        jaula_id,
        rack,
        tipo_acceso,
        llave,
        cliente_id,
        rfc_solicitante,
        lider_proyecto,
        fecha_alta,
        observaciones,
        servicios_contratados,
        usuario_registro
    ) VALUES (
        :ciudad_id,
        :bunker_id,
        :jaula_id,
        :rack,
        :tipo_acceso,
        :llave,
        :cliente_id,
        :rfc_solicitante,
        :lider_proyecto,
        :fecha_alta,
        :observaciones,
        :servicios_contratados,
        :usuario_registro
    )";

    $stmt = $pdo->prepare($query);

    if (!$stmt->execute($params)) {
        throw new Exception("No se pudo ejecutar el INSERT");
    }

    writeLog('QUERY', "Consulta ejecutada: $query | Parámetros: " . json_encode($params));

    jsonResponse(true, 'Rondín registrado correctamente', '', [
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
    jsonResponse(false, '', 'Error en la base de datos');

} catch (Exception $e) {
    http_response_code(500);
    writeLog('ERROR', "Error inesperado: " . $e->getMessage());
    jsonResponse(false, '', 'Error inesperado');
}