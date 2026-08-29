<?php
require_once __DIR__ . '/../config/db.php';

$jaulaId = $_GET['jaula_id'] ?? null;

header('Content-Type: application/json');

if (!$jaulaId || !is_numeric($jaulaId)) {
    echo json_encode(['activo' => false]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id 
    FROM rondines_racks 
    WHERE jaula_id = ? 
      AND fecha_baja IS NULL
    LIMIT 1
");

$stmt->execute([$jaulaId]);

echo json_encode([
    'activo' => $stmt->fetch() ? true : false
]);