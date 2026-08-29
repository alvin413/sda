<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
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

foreach ($catalogos as $cat) {
    $stmt = $pdo->query("SELECT id, nombre FROM {$cat['table']} WHERE nombre <> 'SITIO CLIENTE' ORDER BY nombre");
    ${$cat['id']} = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestion Alarmas - Registrar Nuevo Servidor</title>
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
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: var(--transition);
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        
        .select-dependent {
            background-color: #f8f9fa;
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

<div class="container py-4 fade-in">
    
    <div class="row justify-content-center">
        
        <div class="col-lg-12">
            
            <div class="card">
                
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-server me-2"></i>Registrar Nuevo Servidor</h4>
                </div>

                <div class="card-body">

                    <form id="form-servidor">

                            <!-- Información de Ubicación -->
                            <div class="form-section mb-4">
                                <h5 class="section-title"><i class="fas fa-map-marker-alt me-2"></i>Información de Ubicación</h5>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="sitio_cliente" name="sitio_cliente">
                                <label class="form-check-label" for="sitio_cliente">Sitio Cliente</label>
                            </div>

                            <div class="row g-3">
                                
                                <!-- Ciudad (independiente) -->
                                <div class="col-md-3">
                                    <label for="ciudad" class="form-label required-field">Ciudad</label>
                                    <div class="input-group">
                                        <select class="select2" id="ciudad" name="ciudad" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($ciudad as $item): ?>
                                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" 
                                                onclick="openModal('ciudad', 'Ciudad', 'get_ciudades.php', 'add_ciudad.php')">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Bunker (depende de ciudad) -->
                                <div class="col-md-3">
                                    <label for="bunker" class="form-label required-field">Bunker</label>
                                    <div class="input-group">
                                        <select class="select2 select-dependent" id="bunker" name="bunker" required disabled>
                                            <option value="">Primero seleccione una Ciudad</option>
                                        </select>
                                        <button type="button" class="btn btn-add" 
                                                onclick="openModal('bunker', 'Bunker', 'get_bunkers.php', 'add_bunker.php')"
                                                id="btn-add-bunker" disabled>
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                          
                                <!-- Jaula (depende de bunker) -->
                                <div class="col-md-3">
                                    <label for="jaula" class="form-label required-field">Jaula</label>
                                    <div class="input-group">
                                        <select class="select2 select-dependent" id="jaula" name="jaula" required disabled>
                                            <option value="">Primero seleccione un Bunker</option>
                                        </select>
                                        <button type="button" class="btn btn-add" 
                                                onclick="openModal('jaula', 'Jaula', 'get_jaulas.php', 'add_jaula.php')"
                                                id="btn-add-jaula" disabled>
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Rack (depende de jaula) -->
                                <div class="col-md-3">
                                    <label for="rack" class="form-label required-field">Rack</label>
                                    <div class="input-group">
                                        <select class="select2 select-dependent" id="rack" name="rack" required disabled>
                                            <option value="">Primero seleccione una Jaula</option>
                                        </select>
                                        <button type="button" class="btn btn-add" 
                                                onclick="openModal('rack', 'Rack', 'get_racks.php', 'add_rack.php')"
                                                id="btn-add-rack" disabled>
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div class="row g-3 mt-2">

                                <!-- Cliente (independiente) -->
                                <div class="col-md-6">
                                    <label for="cliente" class="form-label required-field">Cliente</label>
                                    <div class="input-group">
                                        <select class="select2" id="cliente" name="cliente" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($cliente as $item): ?>
                                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" 
                                                onclick="openModal('cliente', 'Cliente', 'get_clientes.php', 'add_cliente.php')">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="rfc_alta" class="form-label required-field">RFC Alta</label>
                                    <input type="text" class="form-control" id="rfc_alta" name="rfc_alta" placeholder="Ej: C00000" required />
                                </div>

                            </div>

                            <div class="row g-3 mt-2">

                                <!-- Unidad de rack -->
                                <div class="col-md-4">
                                    <label for="unidad_rack" class="form-label">Unidad de Rack</label>
                                    <input type="text" class="form-control" id="unidad_rack" name="unidad_rack" placeholder="Ej: 23" />
                                </div>

                        </div>

                    </div>

                        <!-- Hardware -->
                        <div class="form-section mb-4">

                        <h5 class="section-title"><i class="fas fa-microchip me-2"></i>Información del Hardware</h5>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="marca" class="form-label required-field">Marca</label>
                                    <div class="input-group">
                                        <select class="select2" id="marca" name="marca" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($marca as $item): ?>
                                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-add" 
                                                onclick="openModal('marca', 'Marca', 'get_marcas.php', 'add_marca.php')">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>

                            </div>

                            <div class="col-md-6">

                                <label for="modelo" class="form-label required-field">Modelo</label>
                                    <div class="input-group">
                                        <select class="select2 select-dependent" id="modelo" name="modelo" required disabled>
                                            <option value="">Primero seleccione una Marca</option>
                                        </select>
                                        <button type="button" class="btn btn-add" 
                                                onclick="openModal('modelo', 'Modelo', 'get_modelos.php', 'add_modelo.php')"
                                                id="btn-add-modelo" disabled>
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>

                            </div>

                            <div class="col-md-6">

                                <label for="no_serie" class="form-label required-field">No. de Serie</label>
                                <input type="text" class="form-control" id="no_serie" name="no_serie" required placeholder="Ej: CNR12345678" />
                                
                            </div>

                            <div class="col-md-6">

                                <label for="cpu" class="form-label">CPU</label>
                                <input type="text" class="form-control" id="cpu" name="cpu" placeholder="Ej: 2x Intel Xeon Gold 6248R" />
                                
                            </div>

                        </div>

                    </div>

                    <!-- Red -->
                    <div class="form-section mb-4">

                        <h5 class="section-title"><i class="fas fa-network-wired me-2"></i>Información de Red</h5>
                        <div class="row g-3">

                            <div class="col-md-6">

                                <label for="hostname" class="form-label required-field">Hostname</label>
                                <input type="text" class="form-control" id="hostname" name="hostname" required placeholder="Ej: srv-web-prod-01" />

                            </div>
                            
                            <div class="col-md-6">

                                <label for="ip_ilo" class="form-label">IP ILO</label>
                                <input type="text" class="form-control" id="ip_ilo" name="ip_ilo" maxlength="15" pattern="^((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])$" title="Ingrese una IP válida formato IPv4" placeholder="Ej: 192.168.1.100" />

                           </div>
                            
                            <div class="col-md-6">

                                <label for="ilo_user" class="form-label">Usuario ILO</label>
                                <input type="text" class="form-control" id="ilo_user" name="ilo_user" placeholder="Usuario de administración" />

                            </div>
                            
                            <div class="col-md-6">

                                <label for="ilo_password" class="form-label">Password ILO</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="ilo_password" name="ilo_password" placeholder="Contraseña de acceso" />
                                    <button class="btn btn-toggle-password" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Información adicional -->
                    <div class="form-section mb-4">

                        <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Información Adicional</h5>
                        <div class="row g-3">

                            <div class="col-md-6">

                                <label for="ci" class="form-label">CI</label>
                                <input type="text" class="form-control" id="ci" name="ci" placeholder="Número de configuración" />

                            </div>

                            <div class="col-md-6">
                                
                                <label for="fecha_garantia" class="form-label">Fecha Garantía</label>
                                <input type="date" class="form-control" id="fecha_garantia" name="fecha_garantia" />

                            </div>

                        </div>

                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">

                        <a href="/alarmas/dashboard/index.php" class="btn btn-secondary btn-back">
                            <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
                        </a>

                        <button type="submit" class="btn btn-success btn-submit">
                            <i class="fas fa-save me-2"></i> Guardar Servidor
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
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="../assets/js/alerts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function(){
    // Toggle "Sitio Cliente"
$('#sitio_cliente').change(function(){ 
    const isChecked = $(this).is(':checked');

    // Mostrar/ocultar todo el bloque
    $('#ciudad').closest('.col-md-3').toggle(!isChecked);
    $('#bunker').closest('.col-md-3').toggle(!isChecked);
    $('#jaula').closest('.col-md-3').toggle(!isChecked);
    $('#rack').closest('.col-md-3').toggle(!isChecked);

    if (isChecked) {
        // Checkbox marcado → deshabilitar todo
        $('#ciudad, #bunker, #jaula, #rack').prop('disabled', true);
    } else {
        // Checkbox desmarcado → solo habilitar ciudad
        $('#ciudad').prop('disabled', false);
        $('#bunker, #jaula, #rack').prop('disabled', true);
    }
});


    modal = new bootstrap.Modal(document.getElementById('modalAdd'));
    $('.select2').select2({
        width: '100%',
        theme: 'bootstrap-5',
        dropdownParent: $('body'),
        containerCssClass: 'select2-container',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            }
        }
    });
    
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
    
    function setSelectLoading(selector, isLoading) {
        const $select = $(selector);
        if (isLoading) {
            $select.addClass('select-loading').prop('disabled', true);
        } else {
            $select.removeClass('select-loading').prop('disabled', false);
        }
    }
    
    function normalizeItems(responseData){
        return Array.isArray(responseData) ? responseData : (responseData.data || []);
    }
    
    // Mapeo de mensajes personalizados para cada select dependiente
    const dependentMessages = {
        'bunker': 'Primero seleccione una Ciudad',
        'jaula': 'Primero seleccione un Bunker',
        'rack': 'Primero seleccione una Jaula',
        'modelo': 'Primero seleccione una Marca'
    };
    
    // Mapeo de relaciones padre-hijo
    const parentRelations = {
        'bunker': 'ciudad',
        'jaula': 'bunker',
        'rack': 'jaula',
        'modelo': 'marca'
    };
    
    // Mapeo de relaciones hijo-padre (para limpiar selects hijos cuando el padre cambia)
    const childRelations = {
        'ciudad': ['bunker', 'jaula', 'rack'],
        'bunker': ['jaula', 'rack'],
        'jaula': ['rack'],
        'marca': ['modelo']
    };
    
    function resetDependentSelects(parentId) {
        if (childRelations[parentId]) {
            childRelations[parentId].forEach(childId => {
                const message = dependentMessages[childId] || 'Primero seleccione el elemento padre';
                $(`#${childId}`).html(`<option value="">${message}</option>`)
                    .prop('disabled', true)
                    .trigger('change');
                $(`#btn-add-${childId}`).prop('disabled', true);
            });
        }
    }
    
    function reloadChildSelect(selector, url, params, enableAddButton = false){
        const selectId = selector.replace('#', '');
        const parentId = parentRelations[selectId];
        const parentValue = $(`#${parentId}`).val();
        
        // Si no hay parámetro (padre vacío), mostrar mensaje personalizado
        if(!parentValue) {
            const message = dependentMessages[selectId] || 'Primero seleccione el elemento padre';
            
            $(selector).html(`<option value="">${message}</option>`);
            $(selector).prop('disabled', true).trigger('change');
            setSelectLoading(selector, false);
            
            // Deshabilitar botón de agregar si corresponde
            if (enableAddButton) {
                $(`#btn-add-${selectId}`).prop('disabled', true);
            }
            
            // Limpiar selects hijos
            resetDependentSelects(selectId);
            return;
        }
        
        setSelectLoading(selector, true);
        
$.getJSON(url, params, function(response){
            const items = normalizeItems(response);
            const $select = $(selector);
            
            $select.empty();
            if (items.length > 0) {
                $select.append('<option value="">Seleccione...</option>');
                items.forEach(item => $select.append(new Option(item.nombre, item.id)));
                $select.prop('disabled', false);
                
                // Habilitar botón de agregar si corresponde
                if (enableAddButton) {
                    $(`#btn-add-${selectId}`).prop('disabled', false);
                }
            } else {
                $select.append('<option value="">No hay opciones disponibles</option>');
                $select.prop('disabled', true);
                
                // Deshabilitar botón de agregar si corresponde
                if (enableAddButton) {
                    $(`#btn-add-${selectId}`).prop('disabled', false);
                }
            }
            
            $select.trigger('change');
            setSelectLoading(selector, false);
            
            // Limpiar selects hijos
            resetDependentSelects(selectId);
        }).fail(function() {
            $(selector).html('<option value="">Error al cargar opciones</option>');
            $(selector).prop('disabled', true).trigger('change');
            setSelectLoading(selector, false);
            
            // Limpiar selects hijos
            resetDependentSelects(selectId);
        });
    }

    // --- Selects anidados ---
    // Ciudad -> Bunker (y limpia hijos)
    $('#ciudad').on('change', function(){
        const id = $(this).val();
        if (!id) {
            // Si se selecciona la opción por defecto, resetear todos los selects dependientes
            resetDependentSelects('ciudad');
            $('#bunker').prop('disabled', true);
            $('#btn-add-bunker').prop('disabled', true);
            return;
        }
        reloadChildSelect('#bunker','../api/get_bunkers.php',{ ciudad_id: id }, true);
    });

    // Bunker -> Jaula (y limpia hijos)
    $('#bunker').on('change', function(){
        const id = $(this).val();
        if (!id) {
            // Si se selecciona la opción por defecto, resetear todos los selects dependientes
            resetDependentSelects('bunker');
            $('#jaula').prop('disabled', true);
            $('#btn-add-jaula').prop('disabled', true);
            return;
        }
        reloadChildSelect('#jaula','../api/get_jaulas.php',{ bunker_id: id }, true);
    });

    // Jaula -> Rack
    $('#jaula').on('change', function(){
        const id = $(this).val();
        if (!id) {
            // Si se selecciona la opción por defecto, resetear todos los selects dependientes
            resetDependentSelects('jaula');
            $('#rack').prop('disabled', true);
            $('#btn-add-rack').prop('disabled', true);
            return;
        }
        reloadChildSelect('#rack','../api/get_racks.php',{ jaula_id: id }, true);
    });

    // Marca -> Modelo
    $('#marca').on('change', function(){
        const id = $(this).val();
        if (!id) {
            // Si se selecciona la opción por defecto, resetear todos los selects dependientes
            resetDependentSelects('marca');
            $('#modelo').prop('disabled', true);
            $('#btn-add-modelo').prop('disabled', true);
            return;
        }
        reloadChildSelect('#modelo','../api/get_modelos.php',{ marca_id: id }, true);
    });

    // Envío del formulario con AJAX
    $('#form-servidor').submit(function(e){
        e.preventDefault();
        
        // Validar que todos los selects requeridos tengan valor
        const requiredSelects = ['#ciudad', '#bunker', '#jaula', '#rack', '#cliente', '#marca', '#modelo'];
        let isValid = true;

        requiredSelects.forEach(selector => {
            const $el = $(selector);
            if ($el.is(':visible') && !$el.is(':disabled') && !$el.val()) {
                isValid = false;
                $el.closest('.input-group').css('border', '1px solid #e74a3b');
            } else {
                $el.closest('.input-group').css('border', '');
            }
        });
        
        if (!isValid) {
            showHeaderAlert('danger', 'Por favor complete todos los campos requeridos');
            return;
        }
        
        $('.btn-submit').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
        $('.btn-submit').prop('disabled', true);
        
        let formData = $(this).serializeArray();

        // Si está marcado "Sitio Cliente", sobrescribir IDs de ubicación a 1
        if ($('#sitio_cliente').is(':checked')) {
            ['ciudad','bunker','jaula','rack'].forEach(id => {
                // Si ya existía en serializeArray, reemplazar
                const index = formData.findIndex(f => f.name === id);
                if (index > -1) {
                    formData[index].value = 1;
                } else {
                    formData.push({ name: id, value: 1 });
                }
            });
        }

        // Enviar data
        $.ajax({
            url: '../api/add_servidor.php',
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    showHeaderAlert('success', 'Servidor registrado correctamente');
                    setTimeout(function(){
                        window.location.href = '/alarmas/dashboard/index.php';
                    }, 2000);
                } else {
                    showHeaderAlert('danger', response.error || 'Error al guardar el servidor');
                    $('.btn-submit').html('<i class="fas fa-save me-2"></i> Guardar Servidor');
                    $('.btn-submit').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                showHeaderAlert('danger', 'Error en la conexión: ' + error);
                $('.btn-submit').html('<i class="fas fa-save me-2"></i> Guardar Servidor');
                $('.btn-submit').prop('disabled', false);
            }
        });
    });
});

// Función que devuelve los campos requeridos según el select dependiente
function getRequiredFieldsForSelect(selectId) {
    switch (selectId) {
        case 'bunker':
            return ['ciudad'];   // bunker depende de ciudad
        case 'jaula':
            return ['bunker'];   // jaula depende de bunker
        case 'rack':
            return ['jaula'];    // rack depende de jaula
        case 'modelo':
            return ['marca'];    // modelo depende de marca
        default:
            return []; // los independientes no necesitan campos extra
    }
}

// Función para abrir el modal con campos dinámicos
function openModal(selectId, label, apiGet, apiAdd) {
    currentSelectId = selectId;
    currentApiGet = '../api/' + apiGet;
    currentApiAdd = '../api/' + apiAdd;
    
    // Limpiar el contenedor
    $('#modalFieldsContainer').empty();
    
    // Determinar si es un campo sin dependencia
    const isIndependentField = ['ciudad', 'cliente', 'marca'].includes(selectId);
    
    if (!isIndependentField) {
        // Configurar campos adicionales para campos con dependencia
        const requiredFields = getRequiredFieldsForSelect(selectId);
        
        // Agregar campos adicionales dinámicamente
        requiredFields.forEach(field => {
            const fieldValue = $(`#${field}`).val();
            const fieldLabel = $(`label[for="${field}"]`).text().replace(' *', '');
            
            $('#modalFieldsContainer').append(`
                <div class="mb-3">
                    <label class="form-label">${fieldLabel}</label>
                    <input type="text" class="form-control" 
                           value="${fieldValue ? $(`#${field} option:selected`).text() : ''}" 
                           readonly>
                    <input type="hidden" class="additional-field" 
                           name="${field}_id" value="${fieldValue || ''}">
                </div>
            `);
        });
    }
    
    // Campo principal (nombre)
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
    
    // Configurar título y mostrar modal
    $('#modalTitle').text(`Agregar ${label}`);
    $('#modalError').addClass('d-none').text('');
    modal.show();
    
    // Enfocar automáticamente el campo de nombre
    $('#nuevoNombre').trigger('focus');
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
    
    // Guardamos para re-filtrar al refrescar la lista tras el alta
    lastAdditionalDataForAdd = { ...additionalData };
    
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
    // Actualizar el select correspondiente (con el mismo filtro usado en el alta, si aplica)
    let url = currentApiGet;
    const params = new URLSearchParams(lastAdditionalDataForAdd || {});
    if (params.toString()) {
        url += '?' + params.toString();
    }

    return fetch(url)
        .then(response => response.json())
        .then(responseData => {
            const $select = $(`#${currentSelectId}`);
            
            // Guardar el estado de Select2 antes de destruirlo
            const isOpen = $select.data('select2')?.isOpen();
            
            // Destruir y recrear Select2
            $select.select2('destroy');
            $select.empty().append('<option value="">Seleccione...</option>');
            
            // Normalizar respuesta
            const items = Array.isArray(responseData) ? responseData : (responseData.data || []);
            
            if (Array.isArray(items)) {
                items.forEach(item => {
                    $select.append(new Option(item.nombre, item.id));
                });
                
                // Seleccionar el nuevo item si está en data (cuando es respuesta de add)
                if (data.data && data.data.id) {
                    $select.val(data.data.id).trigger('change');
                }
            } else {
                console.error('La respuesta no es un array:', items);
                throw new Error('Formato de datos inválido');
            }
            
            // Reaplicar Select2 con las mismas opciones
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $select.parent()
            });
            
            // Mantener estado del dropdown
            if (isOpen) $select.select2('open');
            
            modal.hide();
            showHeaderAlert('success', `¡${data.message || 'Datos actualizados correctamente'}!`);
        })
        .catch(error => {
            console.error(error);
            showModalError(error.message || 'Error en la solicitud');
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