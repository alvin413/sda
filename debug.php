<?php
require_once __DIR__ . '/config/connection.php';
$t = $_GET['t'];
// Crear conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener la estructura de la tabla alarmas
$sql = "SHOW CREATE TABLE $t";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        // Mostrar los resultados
        $row = $result->fetch_assoc();
        echo "<pre>".htmlspecialchars($row['Create Table'])."</pre>";
    } else {
        echo "La tabla 'alarmas' no existe.";
    }
} else {
    echo "Error al ejecutar la consulta: " . $conn->error;
}

// Cerrar conexión
$conn->close();
?>
