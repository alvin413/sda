<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Capturar parámetros
    $ip      = escapeshellarg($_POST['ip'] ?? '');
    $user    = escapeshellarg($_POST['user'] ?? '');
    $pass    = escapeshellarg($_POST['password'] ?? '');
    $query   = escapeshellarg($_POST['query'] ?? '');
    $disk    = escapeshellarg($_POST['disk'] ?? ''); // Nuevo parámetro opcional

    // Validar datos mínimos
    if (empty(trim($ip, "'")) || empty(trim($user, "'")) || empty(trim($pass, "'")) || empty(trim($query, "'"))) {
        echo json_encode(['error' => 'Faltan parámetros obligatorios.']);
        exit;
    }

    // Ruta al script PowerShell
    $psScriptPath = 'C:\\scripts\\wmiQuery.ps1';

    // Construir comando PowerShell
    $cmd = "powershell -NoProfile -ExecutionPolicy Bypass -File $psScriptPath -ip $ip -user $user -password $pass -query $query";
    if (!empty(trim($disk, "'"))) {
        $cmd .= " -q $disk"; // Enviar el volumen solo si se especifica
    }

    // Ejecutar comando y capturar errores también (2>&1)
    $output = shell_exec("$cmd 2>&1");

    // Manejo de errores de PowerShell
    if (strpos($output, 'ERROR:') === 0) {
        echo json_encode(['error' => trim($output)]);
        exit;
    }

    // Intentar decodificar la salida JSON
    $data = json_decode($output, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'error' => 'Error al decodificar la respuesta JSON del script PowerShell.',
            'detalle' => json_last_error_msg(),
            'salida' => trim($output)
        ]);
        exit;
    }

    // Normalizar salida para que siempre sea un array
    if (!isset($data[0])) {
        $data = [$data];
    }

    echo json_encode($data);
}
?>
