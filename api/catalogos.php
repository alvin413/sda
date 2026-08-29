<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$catalogo = $_GET['catalogo'] ?? '';
$padre_id = null;
$padre_campo = null;

switch ($catalogo) {
    case 'ciudad':
        $sql = "SELECT id, nombre FROM catalog_ciudad ORDER BY nombre";
        break;
    case 'bunker':
        $padre_id = $_GET['ciudad_id'] ?? null;
        $padre_campo = 'ciudad_id';
        break;
    case 'jaula':
        $padre_id = $_GET['bunker_id'] ?? null;
        $padre_campo = 'bunker_id';
        break;
    case 'rack':
        $padre_id = $_GET['jaula_id'] ?? null;
        $padre_campo = 'jaula_id';
        break;
    case 'cliente':
        $sql = "SELECT id, nombre FROM catalog_cliente ORDER BY nombre";
        break;
    case 'marca':
        $sql = "SELECT id, nombre FROM catalog_marca ORDER BY nombre";
        break;
    case 'modelo':
        $padre_id = $_GET['marca_id'] ?? null;
        $padre_campo = 'marca_id';
        break;
    default:
        echo json_encode([]);
        exit;
}

if (isset($sql)) {
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
} elseif ($padre_id) {
    $stmt = $pdo->prepare("SELECT id, nombre FROM catalog_$catalogo WHERE $padre_campo = ? ORDER BY nombre");
    $stmt->execute([$padre_id]);
    $rows = $stmt->fetchAll();
} else {
    $rows = [];
}

header('Content-Type: application/json');
echo json_encode($rows);
