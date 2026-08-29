<?php
session_start();
header('Content-Type: application/json');

// Seguridad
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$inputData = ($requestMethod === 'POST') ? $_POST : $_GET;

// DataTables
$draw = isset($inputData['draw']) ? intval($inputData['draw']) : 0;
$start = isset($inputData['start']) ? intval($inputData['start']) : 0;
$length = isset($inputData['length']) ? intval($inputData['length']) : 10;
$searchValue = isset($inputData['search']['value']) ? trim($inputData['search']['value']) : '';
$orderColumnIndex = isset($inputData['order'][0]['column']) ? intval($inputData['order'][0]['column']) : 0;
$orderDirection = isset($inputData['order'][0]['dir']) ? strtoupper($inputData['order'][0]['dir']) : 'ASC';

// Filtros adicionales
$ciudad_id = isset($inputData['ciudad_id']) && $inputData['ciudad_id'] !== '' ? intval($inputData['ciudad_id']) : null;
$cliente_id = isset($inputData['cliente_id']) && $inputData['cliente_id'] !== '' ? intval($inputData['cliente_id']) : null;
$bunker_id = isset($inputData['bunker_id']) && $inputData['bunker_id'] !== '' ? intval($inputData['bunker_id']) : null;
$jaula_id = isset($inputData['jaula_id']) && $inputData['jaula_id'] !== '' ? intval($inputData['jaula_id']) : null;

// Mapear columnas
$columnsMapping = [
    0 => 's.id',
    1 => 's.hostname',
    2 => 's.no_serie',
    3 => 'ubicacion',
    4 => 'c.nombre',
    5 => 's.rfc_alta',
    6 => 's.created_at',
    7 => 's.rfc_baja',
    8 => 's.disabled_at'
];

$orderColumn = $columnsMapping[$orderColumnIndex] ?? 's.id';
$orderDirection = ($orderDirection === 'DESC') ? 'DESC' : 'ASC';

try {
    // Base query
    $baseQuery = "
        SELECT 
            s.id,
            s.hostname,
            s.no_serie,
            CONCAT(ci.nombre, ' - B', b.nombre, ' - J', j.nombre, ' - ', COALESCE(r.nombre,'Sin rack')) as ubicacion,
            c.nombre as cliente,
            s.rfc_alta,
            DATE_FORMAT(s.created_at,'%d/%m/%Y %H:%i') as fecha_alta,
            s.rfc_baja,
            DATE_FORMAT(s.disabled_at,'%d/%m/%Y %H:%i') as fecha_baja,
            s.estado
        FROM servidores s
        LEFT JOIN ciudades ci ON s.ciudad_id = ci.id
        LEFT JOIN bunkers b ON s.bunker_id = b.id
        LEFT JOIN jaulas j ON s.jaula_id = j.id
        LEFT JOIN racks r ON s.rack_id = r.id
        LEFT JOIN clientes c ON s.cliente_id = c.id
    ";


        // WHERE
    $filters = [
    'ciudad_id' => $ciudad_id,
    'cliente_id' => $cliente_id,
    'bunker_id' => $bunker_id,
    'jaula_id' => $jaula_id
];

$where = ["s.estado='activo'"];
$params = [];

foreach ($filters as $field => $value) {
    if ($value) {
        $where[] = "s.$field = ?";
        $params[] = $value;
    }
}

    if (!empty($searchValue)) {
        $searchFields = ['s.hostname','s.no_serie','ci.nombre','b.nombre','j.nombre','r.nombre','c.nombre','s.rfc_alta','s.rfc_baja'];
        $searchConds = [];
        foreach ($searchFields as $field) {
            $searchConds[] = "$field LIKE ?";
            $params[] = "%$searchValue%";
        }
        $where[] = '(' . implode(' OR ', $searchConds) . ')';
    }

    $whereSQL = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

    // Total records
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM servidores s WHERE s.estado='activo'");
    $stmtTotal->execute();
    $recordsTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

    // Filtered records
    $stmtFiltered = $pdo->prepare("SELECT COUNT(*) as filtered FROM servidores s
        LEFT JOIN ciudades ci ON s.ciudad_id=ci.id
        LEFT JOIN bunkers b ON s.bunker_id=b.id
        LEFT JOIN jaulas j ON s.jaula_id=j.id
        LEFT JOIN racks r ON s.rack_id=r.id
        LEFT JOIN clientes c ON s.cliente_id=c.id
        $whereSQL");
    foreach ($params as $i => $p) { $stmtFiltered->bindValue($i+1,$p); }
    $stmtFiltered->execute();
    $recordsFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['filtered'];

    // Datos
    $stmtData = $pdo->prepare("$baseQuery $whereSQL ORDER BY $orderColumn $orderDirection LIMIT ?, ?");
    $i = 1;
    foreach ($params as $p) { $stmtData->bindValue($i++,$p); }
    $stmtData->bindValue($i++,$start, PDO::PARAM_INT);
    $stmtData->bindValue($i,$length, PDO::PARAM_INT);
    $stmtData->execute();
    $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    $formattedData = [];
    foreach($data as $row) {
        $formattedData[] = [
            'id'=>$row['id'],
            'hostname'=>$row['hostname'],
            'no_serie'=>$row['no_serie'],
            'ubicacion'=>$row['ubicacion'],
            'cliente'=>$row['cliente'],
            'rfc_alta'=>$row['rfc_alta'],
            'fecha_alta'=>$row['fecha_alta'],
            'rfc_baja'=>$row['rfc_baja'],
            'fecha_baja'=>$row['fecha_baja'],
                'estado'=>$row['estado'],
        ];
    }

    echo json_encode([
        'draw'=>$draw,
        'recordsTotal'=>$recordsTotal,
        'recordsFiltered'=>$recordsFiltered,
        'data'=>$formattedData
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Error del servidor']);
}
?>
