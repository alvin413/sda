<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

function jsonResponse($success, $message = '', $error = '', $data = []) {
    $response = ['success' => $success];

    if ($success) {
        if (!empty($message)) $response['message'] = $message;
        if (!empty($data)) $response['data'] = $data;
    } else {
        if (!empty($error)) $response['error'] = $error;
    }

    echo json_encode($response);
    exit;
}

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    writeLog('ERROR', 'Acceso no autorizado, sesión inválida');
    jsonResponse(false, '', 'No autorizado');
}

// Validar archivo subido
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    writeLog('ERROR', 'Validación archivo: No se ha subido archivo o error en subida - ' . json_encode($_FILES));
    jsonResponse(false, '', 'No se ha subido ningún archivo o hay un error en la subida');
}

// Validar extensión
$allowed_extensions = ['xlsx', 'xls', 'csv'];
$file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($file_extension, $allowed_extensions)) {
    http_response_code(400);
    writeLog('ERROR', 'Validación extensión: Tipo no permitido - Archivo: ' . $_FILES['file']['name'] . ', Extensión: ' . $file_extension . ', Permitidas: ' . implode(', ', $allowed_extensions));
    jsonResponse(false, '', 'Tipo de archivo no permitido. Solo XLSX, XLS o CSV');
}

// Funciones para leer archivos
function readExcelFile($filePath, $fileName) {
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($ext === 'csv') return readCsvFile($filePath);
    elseif ($ext === 'xls') return readXlsFile($filePath);
    elseif ($ext === 'xlsx') {
        if (!file_exists(__DIR__ . '/../assets/libs/SimpleXLSX.php')) {
            throw new Exception('Para XLSX instale SimpleXLSX o use CSV/XLS');
        }
        require_once '../assets/libs/SimpleXLSX.php';
        use Shuchkin\SimpleXLSX;
        $xlsx = SimpleXLSX::parse($filePath);
        if ($xlsx) return $xlsx->rows();
        throw new Exception('No se pudo parsear XLSX');
    }
    throw new Exception('Formato de archivo no soportado: ' . $ext);
}

function readCsvFile($filePath) {
    $handle = fopen($filePath, 'r');
    if (!$handle) throw new Exception('No se pudo abrir CSV');

    $rows = [];

    // Detectar delimitador y encoding
    $firstLine = fgets($handle);
    rewind($handle);
    $encoding  = mb_detect_encoding($firstLine, ['UTF-8','ISO-8859-1','Windows-1252'], true);
    $delimiter = detectCsvDelimiter($firstLine);

    // Encabezados
    $headers = fgetcsv($handle, 0, $delimiter);
    $headers = array_map(fn($h) => trim(mb_convert_encoding($h, 'UTF-8', $encoding)), $headers);
    $expectedCols = count($headers);

    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        if ($encoding !== 'UTF-8') {
            foreach ($data as &$v) { if ($v !== null) $v = mb_convert_encoding($v,'UTF-8',$encoding); }
        }

        // Crear fila "forzando" que cada encabezado tenga valor
        $row = [];
        foreach ($headers as $i => $colName) {
            $val = $data[$i] ?? null; // Si no existe índice, null
            if ($val !== null) {
                $val = trim($val);
                if ($val === '' || $val === '-') {
                    $val = null;
                }
            }
            $row[$colName] = $val;
        }

        $rows[] = $row;
    }

    fclose($handle);
    return $rows;
}


function readXlsFile($filePath) {
    $content = file_get_contents($filePath);
    if ($content === false) throw new Exception('No se pudo leer XLS');
    if (strpos($content,'<?xml')===0 || strpos($content,'<Workbook')!==false) {
        return parseXmlSpreadsheet($content);
    }
    throw new Exception('Formato XLS binario no soportado. Use XML o CSV');
}

function detectCsvDelimiter($line) {
    $delimiters = [',',';','\t','|'];
    $counts = [];
    foreach ($delimiters as $d) $counts[$d] = count(str_getcsv($line,$d));
    return array_search(max($counts),$counts) ?: ',';
}

function parseXmlSpreadsheet($xmlContent) {
    $xmlContent = preg_replace(['/\<\?xml.*?\?\>/','/\<\?mso-application.*?\?\>/'],'',$xmlContent);
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlContent);
    if (!$xml) { $errors = libxml_get_errors(); throw new Exception('Error parsing XML: ' . ($errors[0]->message??'desconocido')); }
    $rows = [];
    $headerIndexMap = [];
    $worksheet = $xml->Worksheet ?? null;
    if (!$worksheet) throw new Exception('No se encontró Worksheet');
    $table = $worksheet->Table ?? null;
    if (!$table) throw new Exception('No se encontró Table');

    $headerRow = $table->Row[0] ?? null;
    if ($headerRow) {
        $curIndex = 0;
        foreach ($headerRow->Cell as $cell) {
            $idx = isset($cell['ss:Index']) ? (int)$cell['ss:Index']-1 : $curIndex;
            $headerIndexMap[$idx] = trim((string)($cell->Data ?? ''));
            $curIndex = $idx+1;
        }
    }
    
    $totalCols = count($headerIndexMap);
    for ($i=1;$i<count($table->Row);$i++) {
        $row = $table->Row[$i];
        $rowData = array_fill(0,$totalCols,null);
        $curIndex = 0;
        foreach ($row->Cell as $cell) {
            $idx = isset($cell['ss:Index'])?(int)$cell['ss:Index']-1:$curIndex;
            $val = isset($cell->Data)?trim((string)$cell->Data):null;
            if ($val==='NULL'||$val==='') $val=null;
            if ($idx<$totalCols) $rowData[$idx]=$val;
            $curIndex=$idx+1;
        }
        if (!empty(array_filter($rowData, fn($v)=>$v!==null && $v!==''))) $rows[]=$rowData;
    }
    if (!empty($headerIndexMap)) array_unshift($rows,array_values($headerIndexMap));
    
    writeLog('DEBUG', 'Datos XML procesados - Total filas: ' . count($rows) . ', Contenido del Documento: ' . json_encode(array_slice($rows, 0, count($rows))));
    return $rows;
}

// Funciones para manejar referencias jerárquicas
function loadReferenceData($pdo,$table,$field,$parentField=null) {
    try {
        $query="SELECT id,$field".($parentField?", $parentField":"")." FROM $table";
        $stmt=$pdo->prepare($query);
        $stmt->execute();
        $result=[];
        while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row[$field])) {
                $key=strtolower($row[$field]);
                if ($parentField && !empty($row[$parentField])) $key.='_'.$row[$parentField];
                $result[$key]=$row['id'];
            }
        }
        return $result;
    } catch (PDOException $e) {
        writeLog('ERROR', 'Error cargando ' . $table . ': ' . $e->getMessage());
        return [];
    }
}

function getHierarchicalId($pdo,&$referenceData,$value,$fieldName,$table,$parentField=null,$parentId=null) {
    if (empty($value)) {
        writeLog('DEBUG', 'Valor vacío para ' . $table . '.' . $fieldName . ' - Valor: ' . $value . ', ParentId: ' . $parentId);
        return null;
    }
    
    $value=trim($value);
    $normValue=strtolower($value);
    $cacheKey = $parentId?$normValue.'_'.$parentId:$normValue;
    
    if (isset($referenceData[$cacheKey])) {
        return $referenceData[$cacheKey];
    }

    try {
        $value = mb_strtoupper($value,'UTF-8');
        writeLog('INFO', 'Insertando nuevo registro - Tabla: ' . $table . ', Valor: ' . $value . ', ParentId: ' . ($parentId ?? 'Ninguno'));

        if ($parentField && $parentId!==null) {
            $stmt=$pdo->prepare("INSERT INTO $table ($fieldName,$parentField) VALUES (:value,:parentId)");
            $stmt->execute(['value'=>$value,'parentId'=>$parentId]);
        } else {
            $stmt=$pdo->prepare("INSERT INTO $table ($fieldName) VALUES (:value)");
            $stmt->execute(['value'=>$value]);
        }
        $newId=$pdo->lastInsertId();
        $referenceData[$cacheKey]=$newId;
        return $newId;
    } catch (PDOException $e) {
        writeLog('WARNING', 'Error en INSERT, intentando SELECT - Error: ' . $e->getMessage());
        
        // fallback: intentar SELECT
        try {
            if ($parentField && $parentId!==null) {
                $stmt=$pdo->prepare("SELECT id FROM $table WHERE $fieldName=:value AND $parentField=:parentId");
                $stmt->execute(['value'=>$value,'parentId'=>$parentId]);
            } else {
                $stmt=$pdo->prepare("SELECT id FROM $table WHERE $fieldName=:value");
                $stmt->execute(['value'=>$value]);
            }
            if ($row=$stmt->fetch()) {
                $referenceData[$cacheKey]=$row['id'];
                writeLog('DEBUG', 'Registro encontrado en SELECT: ' . $row['id']);
                return $row['id'];
            }
        } catch (PDOException $e2) {
            writeLog('ERROR', 'Error también en SELECT: ' . $e2->getMessage());
        }
    }
    return false;
}

// Procesar archivo
try {
    $rows = readExcelFile($_FILES['file']['tmp_name'], $_FILES['file']['name']);
    if (empty($rows)||count($rows)<2) throw new Exception('Archivo vacío o insuficiente');

    $headers=$rows[0];
    $expected_headers=['ciudad','bunker','jaula','cliente','hostname','marca','modelo','no_serie','rfc_alta'];
    $missing_headers=array_diff($expected_headers,$headers);
    
    writeLog('INFO', 'Validación de encabezados faltantes: ' . json_encode($missing_headers));

    if (!empty($missing_headers)) {
        http_response_code(400);
        jsonResponse(false,'','Faltan encabezados obligatorios: '.implode(', ',$missing_headers));
    }

    $column_map=array_flip($headers);

    // Datos de referencia
    $ciudades = loadReferenceData($pdo,'ciudades','nombre');
    $bunkers = loadReferenceData($pdo,'bunkers','nombre','ciudad_id');
    $jaulas = loadReferenceData($pdo,'jaulas','nombre','bunker_id');
    $racks = loadReferenceData($pdo,'racks','nombre','jaula_id');
    $clientes = loadReferenceData($pdo,'clientes','nombre');
    $marcas = loadReferenceData($pdo,'marcas','nombre');
    $modelos = loadReferenceData($pdo,'modelos','nombre','marca_id');

    $successCount=0;
    $errors=[];

    writeLog('INFO', 'Iniciando procesamiento de filas - Total filas: ' . (count($rows) - 1));

    for ($i=1;$i<count($rows);$i++) {
        $row_data = $rows[$i];
        
        // Crear array asociativo para logging
        $row_assoc = [];
        foreach ($column_map as $header => $index) {
            if (isset($row_data[$index])) {
                $row_assoc[$header] = $row_data[$index];
            }
        }
        
        writeLog('INFO', 'Procesando fila ' . ($i + 1) . ' - Datos: ' . json_encode($row_assoc));

        try {
            if (empty(array_filter($row_data, fn($v)=>$v!==null && trim($v)!==''))) {
                writeLog('DEBUG', 'Fila ' . ($i + 1) . ' vacía, saltando');
                continue;
            }

            // Campos obligatorios
            $required_fields=['ciudad','bunker','jaula','cliente','hostname','marca','modelo','no_serie','rfc_alta'];
            $missing=[];
            $found_values=[];
            foreach($required_fields as $f) {
                $val=$row_data[$column_map[$f]]??null;
                $found_values[$f] = $val;
                if (empty($val)||$val==='-') $missing[]=$f;
            }
            
            writeLog('INFO', 'Validación campos obligatorios fila ' . ($i + 1) . ' - Valores encontrados: ' . json_encode($found_values) . ', Campos faltantes: ' . json_encode($missing));

            if (!empty($missing)) throw new Exception('Faltan campos obligatorios: '.implode(', ',$missing));

            $no_serie=$row_data[$column_map['no_serie']];
            writeLog('INFO', 'Verificando número de serie único: ' . $no_serie);
            
            $check=$pdo->prepare("SELECT id FROM servidores WHERE no_serie=:no_serie");
            $check->execute(['no_serie'=>$no_serie]);
            if ($check->fetch()) throw new Exception("El número de serie '$no_serie' ya existe");

            // IDs jerárquicos
            $ciudad_val = $row_data[$column_map['ciudad']];
            $ciudad_id=getHierarchicalId($pdo,$ciudades,$ciudad_val,'nombre','ciudades');

            $bunker_val = $row_data[$column_map['bunker']];
            $bunker_id=getHierarchicalId($pdo,$bunkers,$bunker_val,'nombre','bunkers','ciudad_id',$ciudad_id);

            $jaula_val = $row_data[$column_map['jaula']];
            $jaula_id=getHierarchicalId($pdo,$jaulas,$jaula_val,'nombre','jaulas','bunker_id',$bunker_id);

            $rack_val = (isset($column_map['rack']) && $row_data[$column_map['rack']]!=='-') ? $row_data[$column_map['rack']] : null;
            $rack_id = $rack_val ? getHierarchicalId($pdo,$racks,$rack_val,'nombre','racks','jaula_id',$jaula_id) : null;

            $cliente_val = $row_data[$column_map['cliente']];
            $cliente_id=getHierarchicalId($pdo,$clientes,$cliente_val,'nombre','clientes');

            $marca_val = $row_data[$column_map['marca']];
            $marca_id=getHierarchicalId($pdo,$marcas,$marca_val,'nombre','marcas');

            $modelo_val = $row_data[$column_map['modelo']];
            $modelo_id=getHierarchicalId($pdo,$modelos,$modelo_val,'nombre','modelos','marca_id',$marca_id);

            $insert_data=[
                'ciudad_id'=>$ciudad_id,
                'bunker_id'=>$bunker_id,
                'jaula_id'=>$jaula_id,
                'rack_id'=>$rack_id,
                'unidad_rack'=>isset($column_map['unidad_rack']) ? $row_data[$column_map['unidad_rack']] : null,
                'cliente_id'=>$cliente_id,
                'hostname'=>$row_data[$column_map['hostname']],
                'marca_id'=>$marca_id,
                'modelo_id'=>$modelo_id,
                'no_serie'=>$no_serie,
                'rfc_alta'=>$row_data[$column_map['rfc_alta']]
            ];

            $optional_fields=['unidad_rack','cpu','ip_ilo','ilo_user','ilo_password','ci','fecha_garantia'];
            foreach($optional_fields as $f) {
                if(isset($column_map[$f])) {
                    $val=trim($row_data[$column_map[$f]]??'');
                    if($val===''||$val==='-') $val=null;
                    
                    if($f==='ip_ilo' && $val!==null && !filter_var($val,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) {
                        writeLog('WARNING', 'IP iLO inválida: ' . $val);
                        throw new Exception("IP iLO inválida: $val");
                    }
                    if($f==='ilo_password' && $val!==null) {
                        $val_original = $val;
                        $val=encryptPassword($val);
                    }
                    $insert_data[$f]=$val;
                }
            }
            
            $estado_val = isset($column_map['estado']) ? $row_data[$column_map['estado']] : 'activo';
            $insert_data['estado'] = $estado_val;
            
            writeLog('INFO', 'Datos finales para inserción: ' . json_encode($insert_data));

            $stmt=$pdo->prepare("INSERT INTO servidores (".implode(', ',array_keys($insert_data)).") VALUES (:".implode(', :',array_keys($insert_data)).")");
            $stmt->execute($insert_data);
            
            writeLog('INFO', 'Fila insertada correctamente - Fila: ' . ($i + 1) . ', ID insertado: ' . $pdo->lastInsertId() . ', Datos: ' . json_encode($insert_data));
            $successCount++;

        } catch(Exception $e) {
            writeLog('ERROR', 'Error en fila ' . ($i + 1) . ' - Error: ' . $e->getMessage() . ', Datos fila: ' . json_encode($row_assoc));
            $errors[]=['row'=>$i+1,'message'=>$e->getMessage()];
        }
    }

    writeLog('INFO', 'Procesamiento completado - Exitosos: ' . $successCount . ', Errores: ' . count($errors) . ', Detalle errores: ' . json_encode($errors));

    jsonResponse(true,"$successCount registros importados correctamente",'',['errors' => $errors]);

} catch(Exception $e) {
    http_response_code(500);
    writeLog('ERROR', 'Error procesando archivo - Error: ' . $e->getMessage() . ', Archivo: ' . ($_FILES['file']['name'] ?? 'Desconocido') . ', Trace: ' . $e->getTraceAsString());
    jsonResponse(false,'','Error al procesar el archivo: '.$e->getMessage());
}