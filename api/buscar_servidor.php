<?php
require_once "../config/db.php";

header('Content-Type: application/json');

if (!isset($_GET['no_serie'])) {
    echo json_encode(['success' => false, 'message' => 'Número de serie no proporcionado']);
    exit;
}

$no_serie = trim($_GET['no_serie']);
$servidores = [];
$alarmas = [];

try {
    // Primero buscar en la tabla servidores (si está aquí, está administrado)
    $stmt = $pdo->prepare("SELECT * FROM servidores WHERE no_serie LIKE ? LIMIT 10");
$stmt->execute(["%$no_serie%"]);
$servidores = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($servidores) {
    echo json_encode([
        'success' => true,
        'data' => $servidores,
        'origen' => 'servidores',
        'administrado' => true
    ]);
    exit;
} else {
 $stmt = $pdo->prepare("
    SELECT 
        a.*,
        ta.nombre as tipo_alarma_nombre,
        ea.nombre as estado_alarma_nombre,
        c.nombre as ciudad_nombre,
        b.nombre as bunker_nombre,
        j.nombre as jaula_nombre,
        r.nombre as rack_nombre,
        cli.nombre as cliente_nombre,
        m.nombre as marca_nombre,
        modl.nombre as modelo_nombre
    FROM alarmas_servidores a
    LEFT JOIN tipos_alarma ta ON a.tipo_alarma_id = ta.id
    LEFT JOIN estados_alarma ea ON a.estado_alarma_id = ea.id
    LEFT JOIN ciudades c ON a.ciudad_id = c.id
    LEFT JOIN bunkers b ON a.bunker_id = b.id
    LEFT JOIN jaulas j ON a.jaula_id = j.id
    LEFT JOIN racks r ON a.rack_id = r.id
    LEFT JOIN clientes cli ON a.cliente_id = cli.id
    LEFT JOIN marcas m ON a.marca_id = m.id
    LEFT JOIN modelos modl ON a.modelo_id = modl.id
    WHERE a.no_serie LIKE ?
    ORDER BY a.fecha_deteccion DESC
    LIMIT 10
");
$stmt->execute(["%$no_serie%"]);
$alarmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($alarmas) {
    echo json_encode([
        'success' => true,
        'data' => $alarmas,
        'origen' => 'alarmas_servidores',
        'administrado' => false,
        'message' => 'Encontrado en historial de alarmas'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'data' => [],
        'administrado' => false,
        'message' => 'No encontrado en ninguna tabla'
    ]);
}
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}