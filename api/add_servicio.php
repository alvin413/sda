<?php
require_once "../config/db.php";
require_once "../config/log.php"; // asegúrate de tener aquí tu función writeLog

header('Content-Type: application/json');

$nombre = trim($_POST['nombre'] ?? '');
if (!$nombre) {
    writeLog('WARNING', 'Nombre vacío al intentar insertar servicio rondines');
    echo json_encode(['success' => false, 'error' => 'Nombre vacío']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO servicios_rondines (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre);

    writeLog('QUERY', "Query ejecutado: INSERT INTO servicios_rondines (nombre) VALUES (?) - Parámetros: [$nombre]");

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        writeLog('ERROR', 'Error al insertar servicio rondines: ' . $conn->error);
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

} catch (Exception $e) {
    writeLog('ERROR', 'Excepción al insertar servicio rondines: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
