<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

// Evitar warnings que rompen el JSON
error_reporting(E_ALL & ~E_NOTICE);

// Parámetros DataTables
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 0;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$search = $_GET['search']['value'] ?? '';

// Orden
$orderColumn = 0;
$orderDir = 'asc';
if (!empty($_GET['order'][0]['column'])) {
    $orderColumn = intval($_GET['order'][0]['column']);
    $orderDir = $_GET['order'][0]['dir'] ?? 'asc';
}

// Columnas para ordenar
$columns = [
    0 => 'a.id',
    1 => 'a.fecha_deteccion',
    2 => 'cli.nombre',
    3 => 'mar.nombre',
    4 => 'mods.nombre',
    5 => 'a.no_serie',
    6 => 'ta.nombre',
    7 => 'ea.nombre',
    8 => 'a.caso'
];

// Consulta base con joins
$query = "SELECT 
            a.id,
            a.fecha_deteccion,
            cli.nombre AS cliente,
            mar.nombre AS marca,
            mods.nombre AS modelo,
            a.no_serie,
            ta.nombre AS tipo_alarma,
            ea.nombre AS estado_alarma,
            a.caso
          FROM alarmas_servidores a
          LEFT JOIN clientes cli ON a.cliente_id = cli.id
          LEFT JOIN marcas mar ON a.marca_id = mar.id
          LEFT JOIN modelos mods ON a.modelo_id = mods.id
          LEFT JOIN bunkers b ON a.bunker_id = b.id
          LEFT JOIN jaulas j ON a.jaula_id = j.id
          LEFT JOIN racks r ON a.rack_id = r.id
          LEFT JOIN ciudades c ON a.ciudad_id = c.id
          JOIN tipos_alarma ta ON a.tipo_alarma_id = ta.id
          JOIN estados_alarma ea ON a.estado_alarma_id = ea.id";

// Total de alarmas
$totalStmt = $pdo->query("SELECT COUNT(*) FROM alarmas_servidores");
$totalAlarmas = $totalStmt->fetchColumn();

// WHERE búsqueda
$where = '';
$searchParams = [];
if (!empty($search)) {
    $where = " WHERE (cli.nombre LIKE :search OR 
                      mar.nombre LIKE :search OR 
                      mods.nombre LIKE :search OR 
                      ta.nombre LIKE :search OR 
                      ea.nombre LIKE :search OR 
                      a.caso LIKE :search OR 
                      a.no_serie LIKE :search)";
    $searchParams[':search'] = "%$search%";
}

// ORDER BY
$orderBy = " ORDER BY " . ($columns[$orderColumn] ?? 'a.id') . " " . ($orderDir === 'desc' ? 'DESC' : 'ASC');

// Consulta filtrada
$filteredQuery = $query . $where . " $orderBy";
$stmt = $pdo->prepare($filteredQuery);
foreach ($searchParams as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->execute();
$alarmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total filtrado
$totalFilteredStmt = $pdo->prepare("SELECT COUNT(*) FROM alarmas_servidores a
    LEFT JOIN clientes cli ON a.cliente_id = cli.id
    LEFT JOIN marcas mar ON a.marca_id = mar.id
    LEFT JOIN modelos mods ON a.modelo_id = mods.id
    LEFT JOIN bunkers b ON a.bunker_id = b.id
    LEFT JOIN jaulas j ON a.jaula_id = j.id
    LEFT JOIN racks r ON a.rack_id = r.id
    LEFT JOIN ciudades c ON a.ciudad_id = c.id
    JOIN tipos_alarma ta ON a.tipo_alarma_id = ta.id
    JOIN estados_alarma ea ON a.estado_alarma_id = ea.id" . $where);
foreach ($searchParams as $key => $value) {
    $totalFilteredStmt->bindValue($key, $value, PDO::PARAM_STR);
}
$totalFilteredStmt->execute();
$totalFiltered = $totalFilteredStmt->fetchColumn();

// Preparar JSON
$response = [
    "draw" => $draw,
    "recordsTotal" => intval($totalAlarmas),
    "recordsFiltered" => intval($totalFiltered),
    "data" => []
];

foreach ($alarmas as $alarma) {
    $estado = $alarma['estado_alarma'] ?? '';
    $caso = $alarma['caso'] ?? '';

    $response['data'][] = [
        "id" => $alarma['id'] ?? '',
        "fecha" => [
            "display" => isset($alarma['fecha_deteccion']) ? date('d/m/Y H:i', strtotime($alarma['fecha_deteccion'])) : '',
            "sort" => isset($alarma['fecha_deteccion']) ? strtotime($alarma['fecha_deteccion']) : 0
        ],
        "cliente" => htmlspecialchars($alarma['cliente'] ?? 'N/A'),
        "marca" => htmlspecialchars($alarma['marca'] ?? 'N/A'),
        "modelo" => htmlspecialchars($alarma['modelo'] ?? 'N/A'),
        "no_serie" => htmlspecialchars($alarma['no_serie'] ?? 'N/A'),
        "tipo_alarma" => htmlspecialchars($alarma['tipo_alarma'] ?? 'N/A'),
        "estado_alarma" => [
            "display" => $estado,
            "value" => strtolower($estado)
        ],
        "caso" => [
            "display" => $caso,
            "value" => strtolower($caso)
        ],
        "acciones" => '' // lo manejamos desde JS
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
