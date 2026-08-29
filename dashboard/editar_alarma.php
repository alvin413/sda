<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$alarma_id = $_GET['id'] ?? 0;

if (!$alarma_id || !is_numeric($alarma_id)) {
    header("Location: /alarmas/dashboard/listado_alarmas.php");
    exit;
}

// Obtener datos para selects (solo para cargar los dropdowns)
$catalogos = [
    ['id' => 'ciudad', 'label' => 'Ciudad', 'table' => 'ciudades'],
    ['id' => 'bunker', 'label' => 'Bunker', 'table' => 'bunkers'],
    ['id' => 'jaula', 'label' => 'Jaula', 'table' => 'jaulas'],
    ['id' => 'rack', 'label' => 'Rack', 'table' => 'racks'],
    ['id' => 'cliente', 'label' => 'Cliente', 'table' => 'clientes'],
    ['id' => 'marca', 'label' => 'Marca', 'table' => 'marcas'],
    ['id' => 'modelo', 'label' => 'Modelo', 'table' => 'modelos'],
    ['id' => 'tipo_alarma', 'label' => 'Tipo de Alarma', 'table' => 'tipos_alarma'],
    ['id' => 'estado_alarma', 'label' => 'Estados de Alarma', 'table' => 'estados_alarma']
];

// Inicializar arrays para cada catálogo
foreach ($catalogos as $cat) {
    try {
        $stmt = $pdo->query("SELECT id, nombre FROM {$cat['table']} ORDER BY nombre");
        ${$cat['id'] . '_data'} = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        ${$cat['id'] . '_data'} = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestion Alarmas - Editar Alarma</title>
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
        
        /* Input group con Select2 mejorado */
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
        
        /* Botón de agregar mejorado */
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
            background: linear-gradient(120deg, var(--success-color), #17a673);
            border: none;
            transition: var(--transition);
        }
        
        .btn-submit:hover {
            background: linear-gradient(120deg, #17a673, #13865c);
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
        
        /* Búsqueda en tiempo real */
        .real-time-search {
            position: relative;
        }
        
        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: var(--card-shadow);
            display: none;
        }
        
        .search-result-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        
        .search-result-item:hover {
            background-color: var(--secondary-color);
        }
        
        .search-result-item i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .no-results, .search-loading {
            padding: 1rem;
            color: var(--text-secondary);
            text-align: center;
        }
        
        .search-loading i {
            margin-right: 0.5rem;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    
        .badge {
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
            border-radius: 0.35rem;
        }
        
        /* Cards especiales */
        .info-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .alarma-card {
            border-left: 4px solid var(--warning-color);
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
            
            .search-results {
                position: relative;
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
        <?php require_once __DIR__ . '/../includes/header.php'; ?>
        <div class="container-fluid py-4 fade-in">
            <div class="alarma-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="alarma-icon">
                            <i class="fas fa-bell"></i><h1 class="h2 mb-2" id="alarma-titulo">Editar Alarma</h1>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="/alarmas/dashboard/index.php" class="btn btn-light btn-back">
                            <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
            <form id="form-alarma">
                <input type="hidden" name="id" id="alarma_id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Cliente</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 md-3">
                                        <label for="cliente" class="form-label required-field">Cliente</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="cliente" name="cliente_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($cliente_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="cliente" data-label="Cliente" data-api-get="get_clientes.php" data-api-add="add_cliente.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-server me-2"></i>Información del Equipo</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 md-3">
                                        <label for="marca" class="form-label required-field">Marca</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="marca" name="marca_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($marca_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="marca" data-label="Marca" data-api-get="get_marcas.php" data-api-add="add_marca.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 md-3">
                                        <label for="modelo" class="form-label required-field">Modelo</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="modelo" name="modelo_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($modelo_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="modelo" data-label="Modelo" data-api-get="get_modelos.php" data-api-add="add_modelo.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 md-3">
                                        <label for="no_serie" class="form-label required-field">Número de Serie</label>
                                        <input type="text" class="form-control" id="no_serie" name="no_serie" placeholder="Ej: CNR12345678" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Ubicación del Equipo</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-6 md-3">
                                        <label for="ciudad" class="form-label required-field">Ciudad</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="ciudad" name="ciudad_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($ciudad_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="ciudad" data-label="Ciudad" data-api-get="get_ciudads.php" data-api-add="add_ciudad.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 md-3">
                                        <label for="bunker" class="form-label required-field">Bunker</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="bunker" name="bunker_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($bunker_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="bunker" data-label="Bunker" data-api-get="get_bunkers.php" data-api-add="add_bunker.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 md-3">
                                        <label for="jaula" class="form-label required-field">Jaula</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="jaula" name="jaula_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($jaula_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="jaula" data-label="Jaula" data-api-get="get_jaulas.php" data-api-add="add_jaula.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 md-3">
                                        <label for="rack" class="form-label required-field">Rack</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="rack" name="rack_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($rack_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="rack" data-label="Rack" data-api-get="get_racks.php" data-api-add="add_rack.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="ubicacion_manual" class="form-label">Unidad de Rack</label>
                                        <input type="text" class="form-control" id="ubicacion_manual" name="ubicacion_manual" placeholder="Unidad de Rack">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Información de la Alarma</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 md-3">
                                        <label for="tipo_alarma" class="form-label required-field">Tipo de Alarma</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="tipo_alarma" name="tipo_alarma_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($tipo_alarma_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="tipo_alarma" data-label="Tipo de Alarma" data-api-get="get_tipo_alarmas.php" data-api-add="add_tipo_alarma.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 md-3">
                                        <label for="estado_alarma" class="form-label required-field">Estado de Alarma</label>
                                        <div class="input-group">
                                            <select class="form-select select2" id="estado_alarma" name="estado_alarma_id" required>
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($estado_alarma_data as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-add" data-modal-type="estado_alarma" data-label="Estado de la Alarma" data-api-get="get_estado_alarmas.php" data-api-add="add_estado_alarma.php">
                                                <i class="fas fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_deteccion" class="form-label">Fecha Detección</label>
                                        <input type="datetime-local" class="form-control" id="fecha_deteccion" name="fecha_deteccion" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_resolucion" class="form-label">Fecha Resolución</label>
                                        <input type="date" class="form-control" id="fecha_resolucion" name="fecha_resolucion">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="descripcion" class="form-label required-field">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required placeholder="Describa la alarma (ej: LED rojo encendido en panel frontal, sonido de alarma audible, etc.)"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="caso" class="form-label">Caso Proveedor</label>
                                        <input type="text" class="form-control" id="caso" name="caso" placeholder="Número de caso o referencia">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="d-flex justify-content-between mt-4 flex-wrap">
                                        <a href="/alarmas/dashboard/ver_alarma.php?id=<?php echo $alarma_id; ?>" class="btn btn-secondary btn-back">
                                        <i class="fas fa-times me-2"></i> Cancelar
                                    </a>
                                    <div>
                                        <button type="submit" class="btn btn-success btn-submit">
                                            <i class="fas fa-save me-2"></i> Actualizar Alarma
                                        </button>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

<!-- jQuery, Bootstrap, Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/alerts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>

<script>
$(document).ready(function(){
    // Event listener para todos los botones de agregar
    $(document).on('click', '.btn-add', function() {
        const $button = $(this);
        const selectId = $button.data('modal-type');
        const label = $button.data('label');
        const apiGet = $button.data('api-get');
        const apiAdd = $button.data('api-add');
        
        openModal(selectId, label, apiGet, apiAdd);
    });
const alarmaId = <?= $alarma_id ?>;
let alarmaData = {};

// Inicializar Select2
setTimeout(function() {
$('.select2').select2({
theme: 'bootstrap-5',
width: '100%',
language: 'es',
dropdownParent: $('body')
});
}, 100);

// Cargar datos de la alarma via AJAX
function cargarAlarma() {
// Mostrar placeholders mientras carga
$('.form-control, .select2').prop('disabled', true);
$('.btn-submit').prop('disabled', true);

$.ajax({
url: '../api/get_alarma.php',
method: 'GET',
data: { id: alarmaId },
dataType: 'json',
success: function(response) {
if (response.success) {
alarmaData = response.data;
if (alarmaData.estado_alarma_nombre === 'Resuelta') {
showHeaderAlert('danger','Esta alarma ya está resuelta');
setTimeout(function(){
window.location.href = '/alarmas/dashboard/listado_alarmas.php';
}, 2000);
}
mostrarDatosAlarma(alarmaData);
$('.form-control, .select2').prop('disabled', false);
$('.btn-submit').prop('disabled', false);
} else {
mostrarError('Error al cargar los datos: ' + (response.error || 'Error desconocido'));
}
},
error: function(xhr, status, error) {
mostrarError('Error de conexión: ' + error);
}
});
}

// Mostrar datos en la interfaz
function mostrarDatosAlarma(data) {
// Llenar campos del formulario
$('#no_serie').val(data.no_serie || '');
$('#hostname').val(data.hostname || '');
$('#ubicacion_manual').val(data.ubicacion_manual || '');
$('#descripcion').val(data.descripcion || '');
$('#caso').val(data.caso || '');

// Llenar selects
$('#ciudad').val(data.ciudad_id || '').trigger('change');
$('#bunker').val(data.bunker_id || '').trigger('change');
$('#jaula').val(data.jaula_id || '').trigger('change');
$('#rack').val(data.rack_id || '').trigger('change');
$('#cliente').val(data.cliente_id || '').trigger('change');
$('#marca').val(data.marca_id || '').trigger('change');
$('#modelo').val(data.modelo_id || '').trigger('change');
$('#tipo_alarma').val(data.tipo_alarma_id || '').trigger('change');
$('#estado_alarma').val(data.estado_alarma_id || '').trigger('change');

// Llenar fechas
if (data.fecha_deteccion) {
const fechaDet = new Date(data.fecha_deteccion);
$('#fecha_deteccion').val(fechaDet.toISOString().slice(0, 16));
}

if (data.fecha_resolucion) {
const fechaRes = new Date(data.fecha_resolucion);
$('#fecha_resolucion').val(fechaRes.toISOString().slice(0, 16));
}

// Actualizar modal de eliminación
$('#modal-no-serie').text(data.no_serie || 'N/A');
}

// Función para mostrar alertas
// Manejo del envío del formulario - ACTUALIZAR ALARMA
$('#form-alarma').submit(function(e){
e.preventDefault();

$('.btn-submit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...');
$('.btn-submit').prop('disabled', true);

$.ajax({
url: '../api/put_alarma.php',
type: 'POST',
data: $(this).serialize(),
dataType: 'json',
success: function(response) {
if(response.success) {
showHeaderAlert('success', 'Alarma actualizada correctamente');
setTimeout(function(){
window.location.href = '/alarmas/dashboard/listado_alarmas.php';
}, 2000);
} else {
showHeaderAlert('danger', response.error || 'Error al actualizar la alarma');
$('.btn-submit').html('<i class="fas fa-save me-2"></i> Actualizar Alarma');
$('.btn-submit').prop('disabled', false);
}
},
error: function(xhr, status, error) {
showHeaderAlert('danger', 'Error en la conexión: ' + error);
$('.btn-submit').html('<i class="fas fa-save me-2"></i> Actualizar Alarma');
$('.btn-submit').prop('disabled', false);
}
});
});

// Eliminar alarma
$('#btn-eliminar').click(function() {
$('#confirmDeleteModal').modal('show');
});

$('#btn-confirmar-eliminar').click(function() {
$(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...')
.prop('disabled', true);

$.ajax({
url: '../api/delete_alarma.php',
type: 'POST',
data: { id: alarmaId },
dataType: 'json',
success: function(response) {
if (response.success) {
showHeaderAlert('success', 'Alarma eliminada correctamente');
setTimeout(function(){
window.location.href = '/alarmas/dashboard/listado_alarmas.php';
}, 2000);
} else {
showHeaderAlert('danger', response.error || 'Error al eliminar la alarma');
$('#btn-confirmar-eliminar').html('<i class="fas fa-trash-alt me-2"></i> Eliminar')
.prop('disabled', false);
$('#confirmDeleteModal').modal('hide');
}
},
error: function() {
showHeaderAlert('danger', 'Error de conexión al intentar eliminar');
$('#btn-confirmar-eliminar').html('<i class="fas fa-trash-alt me-2"></i> Eliminar')
.prop('disabled', false);
}
});
});

// --- Utilidades para selects dependientes ---
function clearSelect(selector){
$(selector).empty().append('<option value="">Seleccione...</option>').trigger('change');
}

function normalizeItems(responseData){
return Array.isArray(responseData) ? responseData : (responseData.data || []);
}

// Función para cargar opciones de selects dependientes
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

// --- Selects anidados ---
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

// --- Funciones para modales ---
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
    
    // Verifica que los elementos existan
    if (!$('#' + selectId).length) {
        console.error('Elemento no encontrado:', selectId);
        alert('Error: No se puede encontrar el elemento ' + selectId);
        return;
    }
    
    // Resto de tu código original para openModal...
    currentSelectId = selectId;
    currentApiGet = '../api/' + apiGet;
    currentApiAdd = '../api/' + apiAdd;

// Limpiar el contenedor
$('#modalFieldsContainer').empty();
$('#btnGuardar').prop('disabled', false);

// Determinar si es un campo sin dependencia
const isIndependentField = ['ciudad', 'cliente', 'marca', 'tipo_alarma', 'estado_alarma'].includes(selectId);

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
placeholder="Ingrese el nuevo dato" 
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

// Iniciar carga de datos
cargarAlarma();
});
</script>
</body>
</html>