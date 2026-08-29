<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Headers de seguridad
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once("../config/db.php");

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listado_servidores.php");
    exit;
}

$id = $_GET['id'];

// Obtener datos del servidor
try {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               c.nombre as ciudad_nombre, b.nombre as bunker_nombre, 
               j.nombre as jaula_nombre, r.nombre as rack_nombre,
               cl.nombre as cliente_nombre, m.nombre as marca_nombre,
               mo.nombre as modelo_nombre
        FROM servidores s
        LEFT JOIN ciudades c ON s.ciudad_id = c.id
        LEFT JOIN bunkers b ON s.bunker_id = b.id
        LEFT JOIN jaulas j ON s.jaula_id = j.id
        LEFT JOIN racks r ON s.rack_id = r.id
        LEFT JOIN clientes cl ON s.cliente_id = cl.id
        LEFT JOIN marcas m ON s.marca_id = m.id
        LEFT JOIN modelos mo ON s.modelo_id = mo.id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $servidor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servidor) {
        header("Location: listado_servidores.php");
        exit;
    }
} catch (PDOException $e) {
    console.error("Error obteniendo servidor: " . $e->getMessage());
    header("Location: listado_servidores.php");
    exit;
}

// Obtener datos para los selects
$catalogos = [
    ['id' => 'ciudad', 'label' => 'Ciudad', 'table' => 'ciudades'],
    ['id' => 'bunker', 'label' => 'Bunker', 'table' => 'bunkers'],
    ['id' => 'jaula', 'label' => 'Jaula', 'table' => 'jaulas'],
    ['id' => 'rack', 'label' => 'Rack', 'table' => 'racks'],
    ['id' => 'cliente', 'label' => 'Cliente', 'table' => 'clientes'],
    ['id' => 'marca', 'label' => 'Marca', 'table' => 'marcas'],
    ['id' => 'modelo', 'label' => 'Modelo', 'table' => 'modelos']
];

// Inicializar arrays para cada catálogo
$ciudad_data = $bunker_data = $jaula_data = $rack_data = $cliente_data = $marca_data = $modelo_data = [];

foreach ($catalogos as $cat) {
    try {
        $stmt = $pdo->query("SELECT id, nombre FROM {$cat['table']} ORDER BY nombre");
        ${$cat['id'] . '_data'} = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        console.error("Error obteniendo {$cat['table']}: " . $e->getMessage());
        ${$cat['id'] . '_data'} = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestion Alarmas - Editar Servidor</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #3a56c0;
            --secondary-color: #f8f9fc;
            --accent-color: #36b9cc;
            --text-primary: #2e384d;
            --text-secondary: #8798ad;
            --border-color: #e3e8f0;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --card-shadow: 0 0.35rem 1.5rem rgba(58, 59, 69, 0.15);
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: var(--secondary-color);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
        }
        
        .card {
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: none;
            transition: var(--transition);
            margin-bottom: 2rem;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-section {
            background-color: white;
            border-radius: 0.75rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem rgba(58, 59, 69, 0.1);
            transition: var(--transition);
        }
        
        .form-section:hover {
            box-shadow: 0 0.35rem 1.5rem rgba(58, 59, 69, 0.15);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        /* Estilos para el input-group con Select2 */
        .input-group {
            align-items: stretch;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .input-group .select2-container--bootstrap-5 {
            flex: 1 1 auto;
            width: 1px !important;
        }
        
        .input-group .select2-selection {
            height: 100%;
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: 0.5rem 0 0 0.5rem;
            padding: 0.5rem 0.75rem;
            min-height: 2.75rem;
        }
        
        /* Estilos para el botón "+" */
        .btn-add {
            width: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0 0.5rem 0.5rem 0;
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            border: none;
            transition: var(--transition);
        }
        
        .btn-add:hover {
            background: linear-gradient(120deg, var(--primary-dark), #2c4499);
            transform: translateY(-1px);
        }
        
        /* Estilos generales para inputs */
        .form-control {
            height: 2.75rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.6rem 0.9rem;
            font-size: 0.95rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        /* Estilos para el dropdown de Select2 */
        .select2-dropdown {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: var(--card-shadow);
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .btn-submit {
            padding: 0.75rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 0.5rem;
            background: linear-gradient(120deg, var(--warning-color), #dda20a);
            border: none;
            transition: var(--transition);
        }
        
        .btn-submit:hover {
            background: linear-gradient(120deg, #dda20a, #c49209);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .btn-back {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Toggle password */
        .btn-toggle-password {
            border: 1px solid var(--border-color);
            border-left: none;
            border-radius: 0 0.5rem 0.5rem 0;
            background-color: white;
            transition: var(--transition);
        }
        
        .btn-toggle-password:hover {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }
        
        /* Modal styles */
        .modal-content {
            border-radius: 0.75rem;
            border: none;
            box-shadow: var(--card-shadow);
        }
        
        .modal-header {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 0.75rem 0.75rem 0 0;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        
        /* Fade in animation */
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Nuevos estilos para selects dependientes */
        .select-loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .select-loading::after {
            content: "Cargando...";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-style: italic;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-header {
                padding: 1.25rem 1.5rem;
            }
            
            .form-section {
                padding: 1.5rem;
            }
            
            .btn-submit, .btn-back {
                padding: 0.65rem 1.5rem;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .input-group {
                flex-direction: column;
            }
            
            .input-group .select2-container--bootstrap-5 {
                width: 100% !important;
                margin-bottom: 0.5rem;
            }
            
            .input-group .select2-selection {
                border-radius: 0.5rem;
                border-right: 1px solid var(--border-color);
            }
            
            .btn-add {
                width: 100%;
                border-radius: 0.5rem;
                margin-top: 0.5rem;
            }
        }

.custom-alert-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0,0,0,0.3);
    z-index: 1050;
}

.custom-alert-box {
    padding: 1.5rem 2rem;
    border-radius: 0.75rem;
    min-width: 300px;
    max-width: 400px;
    text-align: center;
    font-size: 1rem;
}

</style>
</head>
<body>
<?php 
$headerPath = __DIR__ . '/../includes/header.php';
if (file_exists($headerPath)) {
    require_once $headerPath;
}
?>
<div class="container-fluid py-4 fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Servidor</h4>
                    <a href="listado_servidores.php" class="btn btn-light btn-back">
                        <i class="fas fa-arrow-left me-2"></i> Volver al Listado
                    </a>
                </div>
                <div class="card-body">
                    <form id="form-servidor">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($servidor['id']) ?>">
                        
                        <!-- Información de Ubicación -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-map-marker-alt me-2"></i>Información de Ubicación</h5>
                            
                            <div class="row g-3">
                                <!-- Ciudad -->
                                <div class="col-md-6">
                                    <label for="ciudad" class="form-label required-field">Ciudad</label>
                                    <div class="input-group">
                                        <select class="form-select select2" id="ciudad" name="ciudad" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($ciudad_data as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                    <?= $servidor['ciudad_id'] == $item['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($item['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" onclick="openModal('ciudad', 'Ciudad', 'get_ciudades.php', 'add_ciudad.php')">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Bunker -->
                                <div class="col-md-6">
                                    <label for="bunker" class="form-label required-field">Bunker</label>
                                    <div class="input-group">
                                        <select class="form-select select2" id="bunker" name="bunker" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($bunker_data as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                    <?= $servidor['bunker_id'] == $item['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($item['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" onclick="openModal('bunker', 'Bunker', 'get_bunkers.php', 'add_bunker.php')" id="btn-add-bunker">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Jaula -->
                                <div class="col-md-6">
                                    <label for="jaula" class="form-label required-field">Jaula</label>
                                    <div class="input-group">
                                        <select class="form-select select2" id="jaula" name="jaula" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($jaula_data as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                    <?= $servidor['jaula_id'] == $item['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($item['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" onclick="openModal('jaula', 'Jaula', 'get_jaulas.php', 'add_jaula.php')" id="btn-add-jaula">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Rack -->
                                <div class="col-md-6">
                                    <label for="rack" class="form-label required-field">Rack</label>
                                    <div class="input-group">
                                        <select class="form-select select2" id="rack" name="rack" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($rack_data as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                    <?= $servidor['rack_id'] == $item['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($item['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" onclick="openModal('rack', 'Rack', 'get_racks.php', 'add_rack.php')" id="btn-add-rack">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Cliente -->
                                <div class="col-md-6">
                                    <label for="cliente" class="form-label required-field">Cliente</label>
                                    <div class="input-group">
                                        <select class="form-select select2" id="cliente" name="cliente" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($cliente_data as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                    <?= $servidor['cliente_id'] == $item['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($item['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" onclick="openModal('cliente', 'Cliente', 'get_clientes.php', 'add_cliente.php')">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Unidad de Rack -->
                                <div class="col-md-6">
                                    <label for="unidad_rack" class="form-label">Unidad de Rack</label>
                                    <input type="text" class="form-control" id="unidad_rack" name="unidad_rack" 
                                           value="<?= htmlspecialchars($servidor['unidad_rack'] ?? '') ?>" 
                                           placeholder="Ej: U23" />
                                </div>
                            </div>
                        </div>

                        <!-- Información del Hardware -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-microchip me-2"></i>Información del Hardware</h5>
                            <div class="row g-3">
                                <!-- Marca -->
                                <div class="col-md-6">
                                    <label for="marca" class="form-label required-field">Marca</label>
                                    <div class="input-group">
                                        <select class="form-select select2" id="marca" name="marca" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($marca_data as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                    <?= $servidor['marca_id'] == $item['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($item['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" onclick="openModal('marca', 'Marca', 'get_marcas.php', 'add_marca.php')">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Modelo -->
                                <div class="col-md-6">
                                    <label for="modelo" class="form-label required-field">Modelo</label>
                                    <div class="input-group">
                                        <select class="form-select select2" id="modelo" name="modelo" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($modelo_data as $item): ?>
                                                <option value="<?= $item['id'] ?>" 
                                                    <?= $servidor['modelo_id'] == $item['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($item['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" onclick="openModal('modelo', 'Modelo', 'get_modelos.php', 'add_modelo.php')" id="btn-add-modelo">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="no_serie" class="form-label required-field">No. de Serie</label>
                                    <input type="text" class="form-control" id="no_serie" name="no_serie" 
                                           value="<?= htmlspecialchars($servidor['no_serie']) ?>" 
                                           required placeholder="Ej: CNR12345678" />
                                </div>
                                <div class="col-md-6">
                                    <label for="cpu" class="form-label">CPU</label>
                                    <input type="text" class="form-control" id="cpu" name="cpu" 
                                           value="<?= htmlspecialchars($servidor['cpu'] ?? '') ?>" 
                                           placeholder="Ej: 2x Intel Xeon Gold 6248R" />
                                </div>
                            </div>
                        </div>

                        <!-- Información de Red -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-network-wired me-2"></i>Información de Red</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="hostname" class="form-label required-field">Hostname</label>
                                    <input type="text" class="form-control" id="hostname" name="hostname" 
                                           value="<?= htmlspecialchars($servidor['hostname']) ?>" 
                                           required placeholder="Ej: srv-web-prod-01" />
                                </div>
                                <div class="col-md-6">
                                    <label for="ip_ilo" class="form-label">IP ILO</label>
                                    <input type="text" class="form-control" id="ip_ilo" name="ip_ilo" 
                                           value="<?= htmlspecialchars($servidor['ip_ilo'] ?? '') ?>" 
                                           maxlength="15"
                                           pattern="^((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])$"
                                           title="Ingrese una IP válida formato IPv4" placeholder="Ej: 192.168.1.100" />
                                </div>
                                <div class="col-md-6">
                                    <label for="ilo_user" class="form-label">Usuario ILO</label>
                                    <input type="text" class="form-control" id="ilo_user" name="ilo_user" 
                                           value="<?= htmlspecialchars($servidor['ilo_user'] ?? '') ?>" 
                                           placeholder="Usuario de administración" />
                                </div>
                                <div class="col-md-6">
                                    <label for="ilo_password" class="form-label">Password ILO</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="ilo_password" name="ilo_password" 
                                               value="<?= htmlspecialchars($servidor['ilo_password'] ?? '') ?>" 
                                               placeholder="Contraseña de acceso" />
                                        <button class="btn btn-toggle-password" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="form-section mb-4">
                            <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Información Adicional</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="ci" class="form-label">CI</label>
                                    <input type="text" class="form-control" id="ci" name="ci" 
                                           value="<?= htmlspecialchars($servidor['ci'] ?? '') ?>" 
                                           placeholder="Número de configuración" />
                                </div>
                                <div class="col-md-6">
                                    <label for="rfc_alta" class="form-label">RFC Alta</label>
                                    <input type="text" class="form-control" id="rfc_alta" name="rfc_alta" 
                                           value="<?= htmlspecialchars($servidor['rfc_alta'] ?? '') ?>" 
                                           placeholder="Ej: C00000" />
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_garantia" class="form-label">Fecha Garantía</label>
                                    <input type="date" class="form-control" id="fecha_garantia" name="fecha_garantia" 
                                           value="<?= htmlspecialchars($servidor['fecha_garantia'] ?? '') ?>" />
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between mt-4 flex-wrap">
                            <a href="listado_servidores.php" class="btn btn-secondary btn-back">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning btn-submit">
                                <i class="fas fa-save me-2"></i> Actualizar Servidor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para alta al vuelo -->
<div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Agregar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="modalFieldsContainer"></div>
        <div id="modalError" class="alert alert-danger mt-2 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i> Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardar"><i class="fas fa-save me-2"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery & Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/alerts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>

<script>
$(document).ready(function(){
    // Inicializar Select2 después de un pequeño retraso
    setTimeout(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            language: 'es',
            dropdownParent: $('body') // Cambia esto si necesitas un contenedor específico
        });
    }, 100);
    
    // Mostrar/ocultar contraseña
    $('#togglePassword').click(function(){
        const passwordField = $('#ilo_password');
        const icon = $(this).find('i');
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });

    // --- Utilidades ---
    function clearSelect(selector){
        $(selector).empty().append('<option value="">Seleccione...</option>').trigger('change');
    }
    
    function normalizeItems(responseData){
        return Array.isArray(responseData) ? responseData : (responseData.data || []);
    }
    
    // Función para cargar opciones de selects dependientes (solo para filtrado, no para bloqueo)
    function loadDependentOptions(selector, url, params) {
        $.getJSON(url, params, function(response){
            const items = normalizeItems(response);
            const $select = $(selector);
            const currentValue = $select.val();
            
            $select.empty();
            $select.append('<option value="">Seleccione...</option>');
            
            if (items.length > 0) {
                items.forEach(item => {
                    $select.append(new Option(item.nombre, item.id));
                });
                
                // Mantener el valor seleccionado actual si existe en las nuevas opciones
                if (currentValue && $select.find('option[value="' + currentValue + '"]').length > 0) {
                    $select.val(currentValue).trigger('change');
                }
            }
        }).fail(function() {
            console.error("Error cargando opciones para " + selector);
        });
    }

    // --- Selects anidados (solo para filtrado, no para bloqueo) ---
    // Ciudad -> Bunker
    $('#ciudad').on('change', function(){
        const id = $(this).val();
        if (id) {
            loadDependentOptions('#bunker', '../api/get_bunkers.php', { ciudad_id: id });
        }
    });

    // Bunker -> Jaula
    $('#bunker').on('change', function(){
        const id = $(this).val();
        if (id) {
            loadDependentOptions('#jaula', '../api/get_jaulas.php', { bunker_id: id });
        }
    });

    // Jaula -> Rack
    $('#jaula').on('change', function(){
        const id = $(this).val();
        if (id) {
            loadDependentOptions('#rack', '../api/get_racks.php', { jaula_id: id });
        }
    });

    // Marca -> Modelo
    $('#marca').on('change', function(){
        const id = $(this).val();
        if (id) {
            loadDependentOptions('#modelo', '../api/get_modelos.php', { marca_id: id });
        }
    });

    // Envío del formulario con AJAX
    $('#form-servidor').submit(function(e){
        e.preventDefault();
        
        // Validar que todos los selects requeridos tengan valor
        const requiredSelects = ['#ciudad', '#bunker', '#jaula', '#rack', '#cliente', '#marca', '#modelo'];
        let isValid = true;
        
        requiredSelects.forEach(selector => {
            if (!$(selector).val()) {
                isValid = false;
                $(selector).closest('.input-group').css('border', '1px solid #e74a3b');
            } else {
                $(selector).closest('.input-group').css('border', '');
            }
        });
        
        if (!isValid) {
            showHeaderAlert('danger', 'Por favor complete todos los campos requeridos');
            return;
        }
        
        $('.btn-submit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...');
        $('.btn-submit').prop('disabled', true);
        
        $.ajax({
            url: '../api/actualizar_servidor.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showHeaderAlert('success', 'Servidor actualizado correctamente');
                    setTimeout(function(){
                        window.location.href = 'listado_servidores.php';
                    }, 2000);
                } else {
                    showHeaderAlert('danger', response.error || 'Error al actualizar el servidor');
                    $('.btn-submit').html('<i class="fas fa-save me-2"></i> Actualizar Servidor');
                    $('.btn-submit').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                showHeaderAlert('danger', 'Error en la conexión: ' + error);
                $('.btn-submit').html('<i class="fas fa-save me-2"></i> Actualizar Servidor');
                    $('.btn-submit').prop('disabled', false);
                }
            });
        });
    });

    const modal = new bootstrap.Modal(document.getElementById('modalAdd'));
    let currentSelectId = '';
    let currentApiGet = '';
    let currentApiAdd = '';

    // Función para determinar los campos adicionales requeridos
    function getRequiredFieldsForSelect(selectId) {
        const dependencies = {
            'bunker': ['ciudad'],
            'jaula': ['bunker'],
            'rack': ['jaula'],
            'modelo': ['marca']
        };
        return dependencies[selectId] || [];
    }

    // Función para abrir el modal con campos dinámicos
    function openModal(selectId, label, apiGet, apiAdd) {
        currentSelectId = selectId;
        currentApiGet = '../api/' + apiGet;
        currentApiAdd = '../api/' + apiAdd;
        
        // Limpiar el contenedor
        $('#modalFieldsContainer').empty();
        $('#btnGuardar').prop('disabled', false);
        
        // Determinar si es un campo sin dependencia
        const isIndependentField = ['ciudad', 'cliente', 'marca'].includes(selectId);
        
        if (!isIndependentField) {
            // Configurar campos adicionales para campos con dependencia
            const requiredFields = getRequiredFieldsForSelect(selectId);
            let hasMissingDependencies = false;
            
            // Agregar campos adicionales dinámicamente
            requiredFields.forEach(field => {
                const fieldValue = $(`#${field}`).val();
                const fieldLabel = $(`label[for="${field}"]`).text().replace(' *', '');
                
                if (fieldValue) {
                    $('#modalFieldsContainer').append(`
                        <div class="mb-3">
                            <label class="form-label">${fieldLabel}</label>
                            <input type="text" class="form-control" 
                                   value="${$(`#${field} option:selected`).text()}" 
                                   readonly>
                            <input type="hidden" class="additional-field" 
                                   name="${field}_id" value="${fieldValue}">
                        </div>
                    `);
                } else {
                    // Si no hay valor seleccionado, mostrar mensaje de error
                    $('#modalFieldsContainer').append(`
                        <div class="alert alert-warning">
                            Primero debe seleccionar un ${fieldLabel.toLowerCase()}
                        </div>
                    `);
                    hasMissingDependencies = true;
                }
            });
            
            if (hasMissingDependencies) {
                $('#btnGuardar').prop('disabled', true);
            }
        }
        
        // Solo mostrar campo de nombre si no hay errores
        if ($('#modalFieldsContainer .alert-warning').length === 0) {
            $('#modalFieldsContainer').append(`
                <div class="mb-3">
                    <label for="nuevoNombre" class="form-label">
                        ${isIndependentField ? 'Nombre de la' : 'Nombre del'} ${label}
                    </label>
                    <input type="text" id="nuevoNombre" class="form-control" 
                           placeholder="Ingrese el nombre ${isIndependentField ? 'de la' : 'del'} ${label}" 
                           required autofocus>
                </div>
            `);
        }
        
        // Configurar título y mostrar modal
        $('#modalTitle').text(`Agregar ${label}`);
        $('#modalError').addClass('d-none').text('');
        modal.show();
        
        // Enfocar automáticamente el campo de nombre si está disponible
        setTimeout(() => {
            if ($('#nuevoNombre').length) {
                $('#nuevoNombre').trigger('focus');
            }
        }, 500);
    }

    // Función para guardar los datos
    $('#btnGuardar').click(function(){
        const nombre = $('#nuevoNombre').val().trim();
        const additionalData = {};
        
        // Recoger datos adicionales
        $('.additional-field').each(function() {
            additionalData[$(this).attr('name')] = $(this).val();
        });
        
        // Validación básica
        if (!nombre) {
            showModalError('El nombre es requerido');
            return;
        }
        
        // Validar campos adicionales
        const requiredFields = getRequiredFieldsForSelect(currentSelectId);
        for (const field of requiredFields) {
            if (!additionalData[`${field}_id`]) {
                showModalError(`Debe seleccionar un ${field} primero`);
                return;
            }
        }
        
        // Preparar datos para enviar
        const requestData = {
            nombre: nombre,
            ...additionalData
        };
        
        // Enviar datos como JSON
        fetch(currentApiAdd, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(handleApiResponse)
        .then(handleApiSuccess)
        .catch(handleApiError);
    });

    function handleApiResponse(response) {
        return response.text().then(text => {
            try {
                const data = JSON.parse(text);
                if (!response.ok) {
                    throw new Error(data.message || 'Error en la respuesta del servidor');
                }
                if (!data.success) {
                    throw new Error(data.message || 'Error al guardar los datos');
                }
                return data;
            } catch (e) {
                throw new Error(text || 'Respuesta no válida del servidor');
            }
        });
    }

    function handleApiSuccess(data) {
        return fetch(currentApiGet)
            .then(response => response.json())
            .then(responseData => {
                const $select = $(`#${currentSelectId}`);
                const isOpen = $select.data('select2')?.isOpen();
                
                $select.select2('destroy');
                $select.empty().append('<option value="">Seleccione...</option>');
                
                const items = Array.isArray(responseData) ? responseData : (responseData.data || []);
                
                if (Array.isArray(items)) {
                    items.forEach(item => {
                        $select.append(new Option(item.nombre, item.id));
                    });
                    
                    if (data.data && data.data.id) {
                        $select.val(data.data.id).trigger('change');
                    }
                }
                
                $select.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $select.parent()
                });
                
                if (isOpen) $select.select2('open');
                
                modal.hide();
                showHeaderAlert('success', `¡${data.message || 'Datos actualizados correctamente'}!`);
            })
            .catch(error => {
                console.error('Error al actualizar:', error);
                modal.hide();
                showHeaderAlert('danger', 'Error al actualizar la lista. Por favor recarga la página.');
            });
    }

    function handleApiError(error) {
        console.error('Error completo:', error);
        try {
            const errorData = JSON.parse(error.message);
            showModalError(errorData.message || 'Error en el servidor');
        } catch (e) {
            showModalError(error.message || 'Error en la solicitud');
        }
    }

    function showModalError(message) {
        $('#modalError').removeClass('d-none').text(message).show();
    }

</script>
</body>
</html>