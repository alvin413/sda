<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Solo validar sesión, no CSRF
if (!isset($_SESSION['user_id'])) {
    header("Location: /alarmas/auth/login.php");
    exit;
}

// Función segura para obtener datos de referencia
function getReferenceValues($pdo, $table, $field) {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        
        if (!$stmt->fetch()) {
            return ["Ejemplo 1", "Ejemplo 2", "Ejemplo 3"];
        }
        
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
        $stmt->execute([$field]);
        
        if (!$stmt->fetch()) {
            return ["Ejemplo 1", "Ejemplo 2", "Ejemplo 3"];
        }
        
        $stmt = $pdo->prepare("SELECT $field FROM $table ORDER BY $field LIMIT 5");
        $stmt->execute();
        
        $values = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $values[] = $row[$field];
        }
        
        if (empty($values)) {
            return ["Ejemplo 1", "Ejemplo 2", "Ejemplo 3"];
        }
        
        return $values;
        
    } catch (PDOException $e) {
        return ["Ejemplo 1", "Ejemplo 2", "Ejemplo 3"];
    }
}

// Obtener datos de referencia
$ciudades = getReferenceValues($pdo, 'ciudades', 'ciudad');
$bunkers = getReferenceValues($pdo, 'bunkers', 'bunker');
$jaulas = getReferenceValues($pdo, 'jaulas', 'jaula');
$clientes = getReferenceValues($pdo, 'clientes', 'cliente');
$marcas = getReferenceValues($pdo, 'marcas', 'marca');
$modelos = getReferenceValues($pdo, 'modelos', 'modelo');

// Crear XML con encoding explícito y estructura más robusta
$xmlContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Created>' . date('c') . '</Created>
  <Version>16.00</Version>
 </DocumentProperties>
 
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Arial" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="Header">
   <Font ss:FontName="Arial" ss:Size="11" ss:Color="#000000" ss:Bold="1"/>
   <Interior ss:Color="#E6E6FA" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="HeaderObligatorio">
   <Font ss:FontName="Arial" ss:Size="11" ss:Color="#FF0000" ss:Bold="1"/>
   <Interior ss:Color="#E6E6FA" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
 </Styles>
 
 <Worksheet ss:Name="Servidores">
  <Table>
   <Column ss:Width="100"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Column ss:Width="100"/>
   <Column ss:Width="120"/>
   <Column ss:Width="150"/>
   <Column ss:Width="100"/>
   <Column ss:Width="120"/>
   <Column ss:Width="120"/>
   <Column ss:Width="120"/>
   <Column ss:Width="120"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   
   <!-- Encabezados reordenados: primero obligatorios, luego opcionales -->
   <Row>
    <!-- Campos obligatorios -->
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">ciudad</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">bunker</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">jaula</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">cliente</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">hostname</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">marca</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">modelo</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">no_serie</Data></Cell>
    <Cell ss:StyleID="HeaderObligatorio"><Data ss:Type="String">rfc_alta</Data></Cell>
    
    <!-- Campos opcionales -->
    <Cell ss:StyleID="Header"><Data ss:Type="String">rack</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">unidad_rack</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">cpu</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">ip_ilo</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">ilo_user</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">ilo_password</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">ci</Data></Cell>
    <Cell ss:StyleID="Header"><Data ss:Type="String">fecha_garantia</Data></Cell>
   </Row>
   
   <!-- Datos de ejemplo -->
   <Row>
    <!-- Campos obligatorios -->
    <Cell><Data ss:Type="String">Querétaro</Data></Cell>
    <Cell><Data ss:Type="String">9</Data></Cell>
    <Cell><Data ss:Type="String">3</Data></Cell>
    <Cell><Data ss:Type="String">Alpura</Data></Cell>
    <Cell><Data ss:Type="String">alpuravxr01.grupoalpura.corp</Data></Cell>
    <Cell><Data ss:Type="String">Dell</Data></Cell>
    <Cell><Data ss:Type="String">VxRail P670F</Data></Cell>
    <Cell><Data ss:Type="String">JPX6ST3</Data></Cell>
    <Cell><Data ss:Type="String">C00000</Data></Cell>
    
    <!-- Campos opcionales -->
    <Cell><Data ss:Type="String">w6</Data></Cell>
    <Cell><Data ss:Type="String">10</Data></Cell>
    <Cell><Data ss:Type="String">2 @ Intel Xeon</Data></Cell>
    <Cell><Data ss:Type="String">172.30.205.181</Data></Cell>
    <Cell><Data ss:Type="String">triaraop</Data></Cell>
    <Cell><Data ss:Type="String">Alpur4.Hw2023</Data></Cell>
    <Cell><Data ss:Type="String">SIS-EQU-SER-06061</Data></Cell>
    <Cell><Data ss:Type="String">' . date('Y-m-d') . '</Data></Cell>
   </Row>   
  </Table>
 </Worksheet>
</Workbook>';

// Forzar descarga
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="template_servidores.xls"');
header('Cache-Control: max-age=0');
header('Content-Transfer-Encoding: binary');
header('Pragma: public');
header('Content-Length: ' . strlen($xmlContent));

echo $xmlContent;
exit;