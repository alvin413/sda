<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

if (!isset($_SESSION['user_id'])) {
    writeLog('WARNING', 'Intento de insertar servidor sin sesión iniciada');
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Función para limpiar y validar datos
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Campos requeridos
$requiredFields = ['hostname', 'no_serie', 'ciudad', 'bunker', 'jaula', 'rack', 'cliente', 'marca', 'modelo', 'rfc_alta'];

// Verificar campos requeridos
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        writeLog('WARNING', "Campo obligatorio vacío: $field");
        echo json_encode(['success' => false, 'error' => "El campo $field es obligatorio"]);
        exit;
    }
}

// Validar IP ILO si se proporciona
if (!empty($_POST['ip_ilo']) && !filter_var($_POST['ip_ilo'], FILTER_VALIDATE_IP)) {
    writeLog('WARNING', "IP ILO inválida: " . $_POST['ip_ilo']);
    echo json_encode(['success' => false, 'error' => 'IP ILO inválida']);
    exit;
}

// Validar No. Serie único
$stmt = $pdo->prepare("SELECT id FROM servidores WHERE no_serie = ?");
$stmt->execute([cleanInput($_POST['no_serie'])]);
if ($stmt->fetch()) {
    writeLog('WARNING', "No. Serie duplicado: " . $_POST['no_serie']);
    echo json_encode(['success' => false, 'error' => 'No. Serie ya existe']);
    exit;
}

try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO servidores (
        ciudad_id, bunker_id, jaula_id, rack_id, unidad_rack,
        cliente_id, hostname, marca_id, modelo_id, no_serie,
        cpu, ip_ilo, ilo_user, ilo_password, ci, fecha_garantia, rfc_alta
    ) VALUES (
        :ciudad_id, :bunker_id, :jaula_id, :rack_id, :unidad_rack,
        :cliente_id, :hostname, :marca_id, :modelo_id, :no_serie,
        :cpu, :ip_ilo, :ilo_user, :ilo_password, :ci, :fecha_garantia, :rfc_alta
    )";

    writeLog('QUERY', "Query preparado para insertar servidor");

    $stmt = $pdo->prepare($sql);

    $ilo_password_encrypted = !empty($_POST['ilo_password']) 
        ? encryptPassword($_POST['ilo_password']) 
        : null;

    $params = [
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
        ':ilo_password' => $ilo_password_encrypted,
        ':ci' => !empty($_POST['ci']) ? cleanInput($_POST['ci']) : null,
        ':fecha_garantia' => !empty($_POST['fecha_garantia']) ? $_POST['fecha_garantia'] : null,
        ':rfc_alta' => strtoupper(cleanInput($_POST['rfc_alta']))
    ];

    if ($stmt->execute($params)) {
        $pdo->commit();
        writeLog('QUERY', "Servidor insertado correctamente, ID: " . $pdo->lastInsertId());
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } else {
        $pdo->rollBack();
        writeLog('ERROR', 'Error al guardar servidor en la base de datos');
        echo json_encode(['success' => false, 'error' => 'Error al guardar en la base de datos']);
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    writeLog('ERROR', 'Excepción PDO al insertar servidor: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
