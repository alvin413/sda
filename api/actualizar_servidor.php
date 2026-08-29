<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    writeLog("WARNING", "Intento de acceso sin sesión");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Función para limpiar y validar datos
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Verificar ID
if (empty($_POST['id'])) {
    writeLog("WARNING", "ID de servidor no proporcionado: " . json_encode($_POST));
    echo json_encode(['success' => false, 'error' => 'ID de servidor no proporcionado']);
    exit;
}

$id = (int)$_POST['id'];

// Campos requeridos
$requiredFields = ['hostname', 'no_serie', 'ciudad', 'bunker', 'jaula', 'rack', 'cliente', 'marca', 'modelo'];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        writeLog("WARNING", "Campo obligatorio faltante: $field - Datos: " . json_encode($_POST));
        echo json_encode(['success' => false, 'error' => "El campo $field es obligatorio"]);
        exit;
    }
}

// Validar IP ILO si se proporciona
if (!empty($_POST['ip_ilo']) && !filter_var($_POST['ip_ilo'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    writeLog("WARNING", "IP ILO inválida: " . $_POST['ip_ilo']);
    echo json_encode(['success' => false, 'error' => 'IP ILO inválida. Debe ser una IPv4 válida']);
    exit;
}

// Validar que el servidor existe
$checkStmt = $pdo->prepare("SELECT id FROM servidores WHERE id = ?");
$checkStmt->execute([$id]);
if (!$checkStmt->fetch()) {
    writeLog("WARNING", "El servidor no existe - ID: $id");
    echo json_encode(['success' => false, 'error' => 'El servidor no existe']);
    exit;
}

// Validar No. Serie único (excluyendo el registro actual)
$stmt = $pdo->prepare("SELECT id FROM servidores WHERE no_serie = ? AND id != ?");
$stmt->execute([cleanInput($_POST['no_serie']), $id]);
if ($stmt->fetch()) {
    writeLog("WARNING", "No. Serie duplicado - No. Serie: " . $_POST['no_serie'] . " - ID: $id");
    echo json_encode(['success' => false, 'error' => 'No. Serie ya existe en otro servidor']);
    exit;
}

try {
    $pdo->beginTransaction();

    $passwordField = '';
    $passwordValue = '';
    if (!empty($_POST['ilo_password'])) {
        $passwordField = ', ilo_password = :ilo_password';
        $passwordValue = encryptPassword($_POST['ilo_password']);
    }

    $sql = "UPDATE servidores SET
    ciudad_id = :ciudad_id,
    bunker_id = :bunker_id,
    jaula_id = :jaula_id,
    rack_id = :rack_id,
    unidad_rack = :unidad_rack,
    cliente_id = :cliente_id,
    hostname = :hostname,
    marca_id = :marca_id,
    modelo_id = :modelo_id,
    no_serie = :no_serie,
    cpu = :cpu,
    ip_ilo = :ip_ilo,
    ilo_user = :ilo_user,
    ci = :ci,
    fecha_garantia = :fecha_garantia,
    rfc_alta = :rfc_alta,
    updated_at = NOW()
    $passwordField
    WHERE id = :id";

    $params = [
        ':id' => $id,
        ':ciudad_id' => (int)$_POST['ciudad'],
        ':bunker_id' => (int)$_POST['bunker'],
        ':jaula_id' => (int)$_POST['jaula'],
        ':rack_id' => (int)$_POST['rack'],
        ':unidad_rack' => !empty($_POST['unidad_rack']) ? cleanInput($_POST['unidad_rack']) : null,
        ':cliente_id' => (int)$_POST['cliente'],
        ':hostname' => cleanInput($_POST['hostname']),
        ':marca_id' => (int)$_POST['marca'],
        ':modelo_id' => (int)$_POST['modelo'],
        ':no_serie' => cleanInput($_POST['no_serie']),
        ':cpu' => !empty($_POST['cpu']) ? cleanInput($_POST['cpu']) : null,
        ':ip_ilo' => !empty($_POST['ip_ilo']) ? cleanInput($_POST['ip_ilo']) : null,
        ':ilo_user' => !empty($_POST['ilo_user']) ? cleanInput($_POST['ilo_user']) : null,
        ':ci' => !empty($_POST['ci']) ? cleanInput($_POST['ci']) : null,
        ':fecha_garantia' => !empty($_POST['fecha_garantia']) ? $_POST['fecha_garantia'] : null,
        ':rfc_alta' => !empty($_POST['rfc_alta']) ? strtoupper(cleanInput($_POST['rfc_alta'])) : null,
    ];

    if (!empty($_POST['ilo_password'])) {
        $params[':ilo_password'] = $passwordValue;
    }

    writeLog("QUERY", "Ejecutando UPDATE servidores - SQL: $sql - Parámetros: " . json_encode($params));

    if ($stmt = $pdo->prepare($sql)) {
        $success = $stmt->execute($params);
        if ($success) {
            $pdo->commit();
            writeLog("SERVIDORES", "Servidor actualizado correctamente - ID: $id");
            echo json_encode(['success' => true, 'message' => 'Servidor actualizado correctamente']);
        } else {
            $pdo->rollBack();
            writeLog("ERROR", "Error al actualizar servidor - ID: $id");
            echo json_encode(['success' => false, 'error' => 'Error al actualizar en la base de datos']);
        }
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    writeLog("ERROR", "Excepción PDO al actualizar servidor: " . $e->getMessage() . " - ID: $id");
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
