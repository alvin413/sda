<?php
require_once "../config/db.php";

header('Content-Type: application/json');

try {
    // Validar sesión
    session_start();
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('No autorizado');
    }

    // Obtener datos del POST
    $id = $_POST['id'] ?? null;
    $rfc_baja = $_POST['rfc_baja'] ?? null;
    $usuario_baja = $_SESSION['user_id'];

    // Validaciones
    if (!$id) {
        throw new Exception('ID de servicio no proporcionado');
    }

    // Verificar que el servicio existe y está activo
    $stmt = $pdo->prepare("SELECT id FROM rondines_racks WHERE id = ? AND fecha_baja IS NULL");
    $stmt->execute([$id]);
    $rondin = $stmt->fetch();

    if (!$rondin) {
        throw new Exception('El servicio no existe o ya fue dado de baja');
    }

    // Actualizar el registro con solo la fecha de baja
    $stmt = $pdo->prepare("UPDATE rondines_racks 
                          SET fecha_baja = NOW(),
                              usuario_baja = ?,
                              rfc_baja = ?
                          WHERE id = ?");
    $stmt->execute([$usuario_baja,$rfc_baja,$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Servicio dado de baja correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}