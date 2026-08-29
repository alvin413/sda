<?php
require_once '../assets/libs/SimpleXLSX.php';
use Shuchkin\SimpleXLSX;

// Configuración de base de datos
require_once '../config/db.php';

// Función para obtener o crear registros respetando jerarquía con log
function get_or_create(PDO $pdo, $table, $nameField, $value, $parentField = null, $parentId = null) {
    if ($value === null) return null;
        $value = mb_strtoupper($value);


    // Buscar registro existente
    $sql = "SELECT id FROM $table WHERE $nameField = ?";
    $params = [$value];
    if ($parentField && $parentId) {
        $sql .= " AND $parentField = ?";
        $params[] = $parentId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    if ($row) {
        // Log: registro encontrado
        writeLog('QUERY', "Registro existente en $table encontrado: $value | ID: {$row['id']} | Parámetros: " . json_encode($params));
        return $row['id'];
    }

    // Insertar si no existe
    $fields = $nameField;
    $placeholders = "?";
    if ($parentField && $parentId) {
        $fields .= ", $parentField";
        $placeholders .= ", ?";
    }
    $paramsInsert = [$value];
    if ($parentField && $parentId) $paramsInsert[] = $parentId;

    $stmt = $pdo->prepare("INSERT INTO $table ($fields) VALUES ($placeholders)");
    $stmt->execute($paramsInsert);
    $lastId = $pdo->lastInsertId();

    // Log: registro creado
    writeLog('QUERY', "Registro creado en $table: $value | ID: $lastId | Parámetros: " . json_encode($paramsInsert));

    return $lastId;
}


// Columnas obligatorias
$requiredColumns = ['ciudad','bunker','jaula','cliente','marca','modelo','no_serie','rfc_alta'];

// Procesar archivo XLSX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['file']['tmp_name'];
        if ($xlsx = SimpleXLSX::parse($tmpName)) {
            $header = [];
            $inserted = 0;
            $errors = [];
            $skippedRows = [];

            foreach ($xlsx->rows() as $index => $row) {
                if ($index === 0) {
                    $header = $row;
                    continue; // Saltar encabezados
                }

                // Mapear valores a encabezados y convertir vacíos a NULL
                $data = [];
                foreach ($header as $i => $colName) {
                    $data[$colName] = isset($row[$i]) && trim($row[$i]) !== '' ? trim($row[$i]) : null;
                }

                // Validar columnas obligatorias
                $missingRequired = array_filter($requiredColumns, fn($col) => empty($data[$col]));
                if (!empty($missingRequired)) {
                    $friendlyMissing = [];
                    foreach ($missingRequired as $col) {
                        $friendlyMissing[$col] = "Falta valor obligatorio: $col";
                    }

                    $skippedRows[] = [
                        'row' => $index + 1,
                        'missing_columns' => $friendlyMissing,
                        'no_serie' => $data['no_serie'] ?? null
                    ];
                    continue; // Saltar fila
                }

                try {
                    // Crear referencias obligatorias respetando jerarquía
                    $ciudad_id = get_or_create($pdo, 'ciudades', 'nombre', $data['ciudad']);
                    $bunker_id = get_or_create($pdo, 'bunkers', 'nombre', $data['bunker'], 'ciudad_id', $ciudad_id);
                    $jaula_id = get_or_create($pdo, 'jaulas', 'nombre', $data['jaula'], 'bunker_id', $bunker_id);
                    $cliente_id = get_or_create($pdo, 'clientes', 'nombre', $data['cliente']);
                    $marca_id = get_or_create($pdo, 'marcas', 'nombre', $data['marca']);
                    $modelo_id = get_or_create($pdo, 'modelos', 'nombre', $data['modelo'], 'marca_id', $marca_id);

                    // Validar rack opcional
                    $rack_id = null;
                    if (!empty($data['rack'])) {
                        $rack_id = get_or_create($pdo, 'racks', 'nombre', $data['rack'], 'jaula_id', $jaula_id);
                    }

                    // Si hostname está vacío, usar no_serie
                    if (empty($data['hostname'])) {
                        $data['hostname'] = $data['no_serie'];
                    }

                    // Insertar servidor
                    $query = "
                        INSERT INTO servidores 
                        (ciudad_id, bunker_id, jaula_id, cliente_id, hostname, marca_id, modelo_id, no_serie, rfc_alta, rack_id, unidad_rack, cpu, ip_ilo, ilo_user, ilo_password, ci, fecha_garantia) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ";
                    $params = [
                        $ciudad_id,
                        $bunker_id,
                        $jaula_id,
                        $cliente_id,
                        $data['hostname'],
                        $marca_id,
                        $modelo_id,
                        $data['no_serie'],
                        $data['rfc_alta'],
                        $rack_id,
                        $data['unidad_rack'] ?? null,
                        $data['cpu'] ?? null,
                        $data['ip_ilo'] ?? null,
                        $data['ilo_user'] ?? null,
                        $data['ilo_password'] ?? null,
                        $data['ci'] ?? null,
                        $data['fecha_garantia'] ?? null
                    ];
                    writeLog('QUERY', "Insert ejecutado: $query | Parámetros: " . json_encode($params));
                    $stmt = $pdo->prepare($query);
                    $stmt->execute($params);
                    $inserted++;

                } catch (PDOException $e) {
                    $errorMsg = $e->getMessage();

                    // Mensajes amigables
    if ($e->getCode() == 23000 && str_contains($errorMsg, 'no_serie')) {
        $userFriendly = "El número de serie '{$data['no_serie']}' ya existe en la base de datos.";
    } else if ($e->getCode() == "42S22" && str_contains($errorMsg, 'rack')) {
        $userFriendly = "La columna 'rack' no existe en la tabla servidores.";
    } else {
        $userFriendly = $errorMsg;
    }

                    $errors[] = [
                        'row' => $index + 1,
                        'message' => $userFriendly,
                        'no_serie' => $data['no_serie'] ?? null
                    ];
                }
            }

            echo json_encode([
                'success' => 1,
                'inserted' => $inserted,
                'errors' => $errors,
                'skipped' => $skippedRows,
                'message' => $inserted . " registros insertados correctamente."
            ]);
        } else {
            echo json_encode([
                'success' => 0,
                'errors' => [],
                'message' => "Error al leer XLSX: " . SimpleXLSX::parseError()
            ]);
        }
    } else {
        echo json_encode([
            'success' => 0,
            'errors' => [],
            'message' => "Error al subir el archivo."
        ]);
    }
} else {
    echo json_encode([
        'success' => 0,
        'errors' => [],
        'message' => "No se envió ningún archivo."
    ]);
}
?>
