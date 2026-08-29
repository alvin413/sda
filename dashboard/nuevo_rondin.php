<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener datos para selects
$catalogos = [
    ['id' => 'ciudad', 'label' => 'Ciudad', 'table' => 'ciudades', 'api_get' => 'get_ciudades.php', 'api_add' => 'add_ciudad.php'],
    ['id' => 'bunker', 'label' => 'Bunker', 'table' => 'bunkers', 'api_get' => 'get_bunkers.php', 'api_add' => 'add_bunker.php'],
    ['id' => 'jaula', 'label' => 'Jaula', 'table' => 'jaulas', 'api_get' => 'get_jaulas.php', 'api_add' => 'add_jaula.php'],
    ['id' => 'cliente', 'label' => 'Cliente', 'table' => 'clientes', 'api_get' => 'get_clientes.php', 'api_add' => 'add_cliente.php']
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
    <title>Nuevo Servicio de Rondín</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">

    <!-- Google Fonts -->
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

/* Estilo para el campo rack como input text con el mismo estilo que los demás */
.rack-input-wrapper {
    display: flex;
    align-items: stretch;
}

.rack-input-wrapper .form-control {
    flex: 1;
    border-radius: 0.5rem 0 0 0.5rem;
    border-right: none;
}

.rack-input-wrapper .btn-add {
    width: 3rem;
    border-radius: 0 0.5rem 0.5rem 0;
    margin-top: 0;
}

/* Estilo para campo rack deshabilitado */
.rack-input-wrapper .form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

</style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="container py-5 fade-in">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Nuevo Servicio de Rondín</h4>
                </div>
                <div class="card-body">
                    <form id="form-rondin">
                        <div class="form-section mb-4 info-card">
                            <h5 class="mb-4 text-primary"><i class="fas fa-map-marker-alt me-2"></i>Información de Ubicación</h5>
                            
                            <div class="row g-3">
                                <?php foreach ($catalogos as $cat): ?>
                                <div class="col-md-6">
                                    <label for="<?= $cat['id'] ?>" class="form-label required-field"><?= $cat['label'] ?></label>
                                    <div class="input-group">
                                        <select class="select2" id="<?= $cat['id'] ?>" name="<?= $cat['id'] ?>_id" required style="flex: 1;">
                                            <option value="">Seleccione...</option>
                                            <?php foreach (${$cat['id']} as $item): ?>
                                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" id="btnAgregar<?= $cat['id'] ?>" class="btn btn-add" 
                                                onclick="openModal('<?= $cat['id'] ?>', '<?= $cat['label'] ?>', '<?= $cat['api_get'] ?>', '<?= $cat['api_add'] ?>')">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <!-- Campo Rack como Input Text con el mismo estilo -->
                                <div class="col-md-6">
                                    <label for="rack" class="form-label">Rack</label>
                                    <div class="rack-input-wrapper">
                                        <input type="text" class="form-control" id="rack" name="rack" 
                                               placeholder="Primero seleccione una jaula"
                                               maxlength="100" disabled>
                                        <button type="button" id="btnAgregarrack" class="btn btn-add" 
                                                onclick="openRackModal()" disabled>
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Seleccione una jaula para habilitar el campo rack</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required-field">Tipo de Acceso</label>

                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="tipo_acceso"
                                                   id="tipoLlave"
                                                   value="LLAVE"
                                                   checked>

                                            <label class="form-check-label" for="tipoLlave">
                                                Llave
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="tipo_acceso"
                                                   id="tipoBiometrico"
                                                   value="BIOMETRICO">

                                            <label class="form-check-label" for="tipoBiometrico">
                                                Biométrico
                                            </label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="tipo_acceso"
                                                   id="tipoTea"
                                                   value="TEA">

                                            <label class="form-check-label" for="tipoTea">
                                                TEA
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6" id="contenedorLlave">
                                    <label for="llave" class="form-label required-field">Llave</label>

                                    <input type="text"
                                           class="form-control"
                                           id="llave"
                                           name="llave"
                                           placeholder="Ingrese el valor correspondiente (solo letras y números)"
                                           pattern="[A-Za-z0-9]+"
                                           title="Solo se permiten letras y números, sin espacios ni caracteres especiales">

                                    <small class="text-muted">
                                        Solo letras y números, sin espacios
                                    </small>
                                </div>

                                
                                <div class="col-md-6">
                                    <label for="fecha_alta" class="form-label required-field">Fecha de Alta</label>
                                    <input type="date" class="form-control" id="fecha_alta" name="fecha_alta" required
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section mb-4 cliente-card">
                            <h5 class="mb-4 text-primary"><i class="fas fa-building me-2"></i>Información del Cliente</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="rfc_solicitante" class="form-label required-field">RFC Solicitante</label>
                                    <input type="text" class="form-control" id="rfc_solicitante" name="rfc_solicitante" required
                                           placeholder="Ingrese el RFC del solicitante" maxlength="20" value="C00000">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="lider_proyecto" class="form-label required-field">Líder de Proyecto</label>
                                    <input type="text" class="form-control" id="lider_proyecto" name="lider_proyecto" required
                                           placeholder="Nombre completo del líder">
                                </div>
                            </div>
                        </div>

                        <div class="form-section other-card">
                            <h5 class="mb-4 text-primary"><i class="fas fa-info-circle me-2"></i>Servicios Contratados</h5>
                            
                            <div class="row g-3 mb-2" id="serviciosContainer">
                                <!-- Aquí se mostrarán los servicios con checkbox -->
                            </div>

                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddServicios">
                                <i class="fas fa-plus me-2"></i> Agregar Servicio</button>
                        </div>

                        <div class="form-section other-card">
                            <h5 class="mb-4 text-primary"><i class="fas fa-info-circle me-2"></i>Información Adicional</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                              placeholder="Detalles adicionales sobre el servicio"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="/alarmas/dashboard/index.php" class="btn btn-secondary px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                            <button type="submit" class="btn btn-success btn-submit">
                                <i class="fas fa-save me-2"></i>Guardar Servicio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para alta de servicios al vuelo -->
<div class="modal fade" id="modalAddServicios" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalTitleServicios">Agregar Servicio</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="nuevoServicio" class="form-label">Nombre del Servicio</label>
          <input type="text" class="form-control" id="nuevoServicio" placeholder="Escribe el nombre">
        </div>
        <div id="modalErrorServicios" class="alert alert-danger mt-2 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary" id="btnGuardarServicios">
          <i class="fas fa-save me-2"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para alta de rack al vuelo -->
<div class="modal fade" id="modalAddRack" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Agregar Rack</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="newRackName" class="form-label required-field">Nombre del Rack</label>
          <input type="text" class="form-control" id="newRackName" placeholder="Ingrese el nombre del rack" maxlength="100">
        </div>
        <div class="mb-3">
          <label for="rackJaulaInfo" class="form-label required-field">Jaula Asignada</label>
          <div class="alert alert-info" id="rackJaulaInfo">
            <i class="fas fa-info-circle me-2"></i>
            <span id="jaulaSeleccionadaTexto">No se ha seleccionado ninguna jaula</span>
          </div>
          <input type="hidden" id="rackJaulaId" value="">
        </div>
        <div id="modalErrorRack" class="alert alert-danger mt-2 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary" id="btnGuardarRack">
          <i class="fas fa-save me-2"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para alta al vuelo (genérico) -->
<div class="modal fade custom-modal" id="modalAdd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalTitle">Agregar</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="modalFieldsContainer"></div>
        <div id="modalError" class="alert alert-danger mt-2 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-2"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary" id="btnGuardar">
          <i class="fas fa-save me-2"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/alerts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function restaurarBoton(btn, originalText) {
        btn.html(originalText);
        btn.prop('disabled', false);
    }
    function finalizarGuardadoExitoso(label, btn, originalText) {
        showHeaderAlert('success', `${label} agregado correctamente`);
        restaurarBoton(btn, originalText)
    }
$(document).ready(function(){
    function actualizarTipoAcceso() {
        const tipo = $('input[name="tipo_acceso"]:checked').val();
        if (tipo === 'LLAVE') {
            $('#contenedorLlave').show();
            $('#llave')
                .prop('required', true)
                .val($('#llave').val());
        } else {
            $('#contenedorLlave').hide();
            $('#llave')
                .prop('required', false)
                .val('');
        }
    }
    // Ejecutar al cargar
    actualizarTipoAcceso();
    // Ejecutar cuando cambie
    $('input[name="tipo_acceso"]').change(function () {
        actualizarTipoAcceso();
    });

    // Inicializar Select2
    $('.select2').select2({
        width: '100%',
        theme: 'bootstrap-5',
        dropdownParent: $('body')
    });

    // -------------------------
    // VALIDACIÓN EN TIEMPO REAL DEL CAMPO LLAVE
    // -------------------------
    $('#llave').on('input', function() {
        let valor = $(this).val();
        let nuevoValor = valor.replace(/[^A-Za-z0-9]/g, '');
        
        if (valor !== nuevoValor) {
            $(this).val(nuevoValor);
            if ($('#llave-warning').length === 0) {
                $(this).after('<div id="llave-warning" class="text-warning small mt-1">Solo se permiten letras y números</div>');
                setTimeout(() => $('#llave-warning').fadeOut(3000), 3000);
            }
        } else {
            $('#llave-warning').remove();
        }
    });

    $('#llave').on('blur', function() {
        let valor = $(this).val();
        const alfanumericoRegex = /^[A-Za-z0-9]+$/;
        
        if (valor && !alfanumericoRegex.test(valor)) {
            let nuevoValor = valor.replace(/[^A-Za-z0-9]/g, '');
            $(this).val(nuevoValor);
            
            if ($('#llave-warning-blur').length === 0) {
                $(this).after('<div id="llave-warning-blur" class="text-danger small mt-1">La llave solo puede contener letras y números, sin espacios</div>');
                setTimeout(() => $('#llave-warning-blur').fadeOut(3000), 3000);
            }
        } else {
            $('#llave-warning-blur').remove();
        }
    });

    // Inicializar selects dependientes
    $('#bunker, #jaula').prop('disabled', true);
    $('#bunker, #jaula').empty().append('<option value="">Seleccione primero...</option>');
    $('#btnAgregarbunker, #btnAgregarjaula').prop('disabled', true);

    // -------------------------
    // Función para cargar bunkers por ciudad
    // -------------------------
    function cargarBunkers(ciudadId) {
        if (!ciudadId) {
            $('#bunker').empty().append('<option value="">Seleccione ciudad primero</option>');
            $('#bunker').prop('disabled', true);
            $('#btnAgregarbunker').prop('disabled', true);
            return;
        }
        
        $('#bunker').prop('disabled', false);
        $('#btnAgregarbunker').prop('disabled', false);
        
        // Mostrar loading
        $('#bunker').empty().append('<option value="">Cargando bunkers...</option>');
        
        $.ajax({
            url: '../api/get_bunkers.php',
            type: 'GET',
            data: { ciudad_id: ciudadId },
            dataType: 'json',
            success: function(data) {
                $('#bunker').empty().append('<option value="">Seleccione bunker...</option>');
                
                if (data && data.length > 0) {
                    $.each(data, function(index, bunker) {
                        $('#bunker').append('<option value="' + bunker.id + '">' + bunker.nombre + '</option>');
                    });
                } else {
                    $('#bunker').append('<option value="">No hay bunkers disponibles</option>');
                }
                
                // Refrescar Select2
                $('#bunker').trigger('change.select2');
            },
            error: function() {
                $('#bunker').empty().append('<option value="">Error al cargar bunkers</option>');
                $('#bunker').prop('disabled', true);
                $('#btnAgregarbunker').prop('disabled', true);
            }
        });
    }
    window.cargarBunkers = cargarBunkers;

    // -------------------------
    // Función para cargar jaulas por bunker
    // -------------------------
    function cargarJaulas(bunkerId) {
        if (!bunkerId) {
            $('#jaula').empty().append('<option value="">Seleccione bunker primero</option>');
            $('#jaula').prop('disabled', true);
            $('#btnAgregarjaula').prop('disabled', true);
            return;
        }
        
        $('#jaula').prop('disabled', false);
        $('#btnAgregarjaula').prop('disabled', false);
        
        // Mostrar loading
        $('#jaula').empty().append('<option value="">Cargando jaulas...</option>');
        
        $.ajax({
            url: '../api/get_jaulas.php',
            type: 'GET',
            data: { bunker_id: bunkerId },
            dataType: 'json',
            success: function(data) {
                $('#jaula').empty().append('<option value="">Seleccione jaula...</option>');
                
                if (data && data.length > 0) {
                    $.each(data, function(index, jaula) {
                        $('#jaula').append('<option value="' + jaula.id + '">' + jaula.nombre + '</option>');
                    });
                } else {
                    $('#jaula').append('<option value="">No hay jaulas disponibles</option>');
                }
                
                // Refrescar Select2
                $('#jaula').trigger('change.select2');
            },
            error: function() {
                $('#jaula').empty().append('<option value="">Error al cargar jaulas</option>');
                $('#jaula').prop('disabled', true);
                $('#btnAgregarjaula').prop('disabled', true);
            }
        });
    }
    window.cargarJaulas = cargarJaulas;

    // -------------------------
    // Función para habilitar/deshabilitar campo rack
    // -------------------------
    function toggleRackField() {
        const jaulaId = $('#jaula').val();
        if (jaulaId) {
            $('#rack').prop('disabled', false);
            $('#btnAgregarrack').prop('disabled', false);
            $('#rack').attr('placeholder', 'Ingrese el nombre del rack');
        } else {
            $('#rack').prop('disabled', true);
            $('#btnAgregarrack').prop('disabled', true);
            $('#rack').val('');
            $('#rack').attr('placeholder', 'Primero seleccione una jaula');
        }
    }

    // -------------------------
    // Eventos de cambio
    // -------------------------
    $('#ciudad').change(function() {
        const ciudadId = $(this).val();
        
        // Limpiar selects dependientes
        $('#bunker, #jaula').val('');
        toggleRackField(); // Deshabilitar rack al cambiar ciudad
        
        if (ciudadId) {
            cargarBunkers(ciudadId);
        } else {
            $('#bunker, #jaula').prop('disabled', true);
            $('#bunker, #jaula').empty().append('<option value="">Seleccione ciudad primero</option>');
            $('#btnAgregarbunker, #btnAgregarjaula').prop('disabled', true);
        }
    });

    $('#bunker').change(function() {
        const bunkerId = $(this).val();
        
        // Limpiar select dependiente
        $('#jaula').val('');
        toggleRackField(); // Deshabilitar rack al cambiar bunker
        
        if (bunkerId) {
            cargarJaulas(bunkerId);
        } else {
            $('#jaula').prop('disabled', true);
            $('#jaula').empty().append('<option value="">Seleccione bunker primero</option>');
            $('#btnAgregarjaula').prop('disabled', true);
        }
    });

    $('#jaula').change(function() {
        toggleRackField(); // Habilitar/deshabilitar rack según selección de jaula
    });

    // -------------------------
    // Servicios Contratados
    // -------------------------
    const serviciosContainer = $('#serviciosContainer');
    const modalServicios = new bootstrap.Modal(document.getElementById('modalAddServicios'));

    function cargarServicios(selectedId = null){
        $.getJSON('../api/get_servicios.php', function(data){
            serviciosContainer.empty();
            data.forEach(servicio => {
                const checked = (servicio.id == selectedId) ? 'checked' : '';
                const checkbox = `<div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input servicioCheck" type="checkbox" value="${servicio.id}" id="servicio${servicio.id}" ${checked}>
                        <label class="form-check-label" for="servicio${servicio.id}">${servicio.nombre}</label>
                    </div>
                </div>`;
                serviciosContainer.append(checkbox);
            });
        });
    }

    // Inicializar servicios
    cargarServicios();

    $('#btnGuardarServicios').click(function(){
        const nombre = $('#nuevoServicio').val().trim();
        if(!nombre){
            $('#modalErrorServicios').removeClass('d-none').text('El nombre del servicio es obligatorio');
            return;
        }

        $.post('../api/add_servicio.php', {nombre}, function(resp){
            if(resp.success){
                $('#modalAddServicios').modal('hide');
                $('#nuevoServicio').val('');
                $('#modalErrorServicios').addClass('d-none');

                // Recargar servicios y seleccionar recién agregado
                if(resp.insertId){
                    cargarServicios(resp.insertId);
                } else {
                    cargarServicios();
                }
            } else {
                $('#modalErrorServicios').removeClass('d-none').text(resp.error || 'Error al guardar el servicio');
            }
        }, 'json');
    });

    function getServiciosSeleccionados(){
        const seleccionados = [];
        $('.servicioCheck:checked').each(function(){
            seleccionados.push($(this).val());
        });
        return seleccionados.join(',');
    }

    // -------------------------
    // Envío del formulario
    // -------------------------
    $('#form-rondin').submit(function(e){
        e.preventDefault();

        // Validación rack (ahora es input text)
        const rackVal = ($('#rack').val() || '').trim();

		if (rackVal && rackVal.length < 2) {
			showHeaderAlert('danger','El nombre del rack debe tener al menos 2 caracteres');
			return;
		}

        // VALIDACIÓN DE LLAVE
        const tipoAcceso = $('input[name="tipo_acceso"]:checked').val();
		let llaveVal = null;
		if (tipoAcceso === 'LLAVE') {
			llaveVal = $('#llave').val().trim();
			if (!llaveVal) {
				showHeaderAlert('danger', 'El campo llave es obligatorio');
				return;
			}
			const alfanumericoRegex = /^[A-Za-z0-9]+$/;
			if (!alfanumericoRegex.test(llaveVal)) {
				showHeaderAlert('danger', 'La llave solo puede contener letras y números');
				return;
			}
			if (llaveVal.length < 3) {
				showHeaderAlert('danger', 'La llave debe tener al menos 3 caracteres');
				return;
			}
			if (llaveVal.length > 50) {
				showHeaderAlert('danger', 'La llave no puede exceder los 50 caracteres');
				return;
			}
		}
        if (llaveVal.length < 3) {
            showHeaderAlert('danger', 'La llave debe tener al menos 3 caracteres');
            return;
        }
        if (llaveVal.length > 50) {
            showHeaderAlert('danger', 'La llave no puede exceder los 50 caracteres');
            return;
        }

        // Validar que se haya seleccionado una jaula
        const jaulaId = $('#jaula').val();
        if(!jaulaId){
            showHeaderAlert('danger','Debe seleccionar una jaula');
            return;
        }

        // IDs de servicios concatenados
        const servicios_ids = getServiciosSeleccionados();
        
        // Crear campo oculto para el rack
		$('input[name="rack"]').remove();
		$('<input>').attr({
			type: 'hidden',
			name: 'rack',
			value: rackVal
		}).appendTo(this);

		// AGREGAR TIPO DE ACCESO
		$('input[name="tipo_acceso"]').remove();
		$('<input>').attr({
			type: 'hidden',
			name: 'tipo_acceso',
			value: tipoAcceso
		}).appendTo(this);
        
        if($('input[name="servicios_contratados"]').length){
            $('input[name="servicios_contratados"]').val(servicios_ids);
        } else {
            $('<input>').attr({
                type: 'hidden',
                name: 'servicios_contratados',
                value: servicios_ids
            }).appendTo(this);
        }

        // Agregar jaula_id al formulario
        $('input[name="jaula_id"]').remove();
        $('<input>').attr({
            type: 'hidden',
            name: 'jaula_id',
            value: jaulaId
        }).appendTo(this);

        // Mostrar spinner
        const btn = $('.btn-submit');
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
        btn.prop('disabled', true);

        // Validar servicio activo
        fetch(`../api/check_rondin_activo.php?jaula_id=${jaulaId}`)
        .then(response => response.json())
        .then(data => {
            if(data.activo){
                showHeaderAlert('danger','Este rack ya tiene un servicio de rondín activo');
                btn.html('<i class="fas fa-save me-2"></i>Guardar Servicio').prop('disabled', false);
            } else {
                // Enviar formulario
                $.ajax({
                    url: '../api/guardar_rondin.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response){
                        if(response.success){
                            showHeaderAlert('success','Servicio de rondín registrado correctamente');
                            setTimeout(function(){
                                window.location.href='listado_rondines.php';
                            },2000);
                        } else {
                            showHeaderAlert('danger', response.error || 'Error al guardar el servicio');
                            btn.html('<i class="fas fa-save me-2"></i>Guardar Servicio').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error){
                        showHeaderAlert('danger','Error en la conexión: '+error);
                        btn.html('<i class="fas fa-save me-2"></i>Guardar Servicio').prop('disabled', false);
                    }
                });
            }
        })
        .catch(error => {
            showHeaderAlert('danger', 'Error al verificar servicio activo: ' + error);
            btn.html('<i class="fas fa-save me-2"></i>Guardar Servicio').prop('disabled', false);
        });
    });

    // Función para abrir modal de agregar rack
    window.openRackModal = function() {
        const jaulaId = $('#jaula').val();
        const jaulaNombre = $('#jaula option:selected').text();
        
        // Verificar que se haya seleccionado una jaula
        if (!jaulaId) {
            showHeaderAlert('warning', 'Primero debe seleccionar una jaula antes de agregar un rack');
            return;
        }
        
        // Actualizar la información en el modal
        $('#rackJaulaId').val(jaulaId);
        $('#jaulaSeleccionadaTexto').text(jaulaNombre);
        
        // Limpiar campos
        $('#newRackName').val('');
        $('#modalErrorRack').addClass('d-none');
        
        // Mostrar modal
        const $modal = new bootstrap.Modal(document.getElementById('modalAddRack'));
        $modal.show();
    }

    // Guardar nuevo rack
    $('#btnGuardarRack').click(function(){
        const nombre = $('#newRackName').val().trim();
        const jaulaId = $('#rackJaulaId').val();
        
        if(!nombre){
            $('#modalErrorRack').removeClass('d-none').text('El nombre del rack es obligatorio');
            return;
        }
        
        if(!jaulaId){
            $('#modalErrorRack').removeClass('d-none').text('Error: No se ha seleccionado ninguna jaula');
            return;
        }
        
        // Mostrar loading en el botón
        const btn = $('#btnGuardarRack');
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');
        btn.prop('disabled', true);
        
        $.ajax({
            url: '../api/add_rack.php',
            type: 'POST',
            data: JSON.stringify({ nombre: nombre, jaula_id: jaulaId }),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(resp){
                if(resp.success){
                    $('#modalAddRack').modal('hide');
                    // Limpiar y llenar el campo rack con el nombre ingresado
                    $('#rack').val(nombre);
                            finalizarGuardadoExitoso('Rack', btn, originalText);
                } else {
                    $('#modalErrorRack').removeClass('d-none').text(resp.message || 'Error al guardar el rack');
                    restaurarBoton(btn, originalText);
                }
            },
            error: function(xhr){
                let errorMsg = 'Error al conectar con el servidor';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                $('#modalErrorRack').removeClass('d-none').text(errorMsg);

                restaurarBoton(btn, originalText);
            },
        });
    });

});
window.openModal = function(campo, label, apiGet, apiAdd) {
    const $modal = $('#modalAdd');
    const $title = $('#modalTitle');
    const $container = $('#modalFieldsContainer');
    const $error = $('#modalError');

    // Configurar modal
    $title.text(`Agregar ${label}`);
    $container.empty();
    $error.addClass('d-none').text('');

    // Determinar el padre según el tipo de campo
    let parentId = null;
    let parentName = null;
    let parentLabel = '';

    if (campo === 'bunker') {
        parentId = $('#ciudad').val();
        parentName = $('#ciudad option:selected').text();
        parentLabel = 'Ciudad';
        
        if (!parentId) {
            showHeaderAlert('warning', `Debe seleccionar una ${parentLabel} antes de agregar un ${label}`);
            return;
        }
    } 
    else if (campo === 'jaula') {
        parentId = $('#bunker').val();
        parentName = $('#bunker option:selected').text();
        parentLabel = 'Bunker';
        
        if (!parentId) {
            showHeaderAlert('warning', `Debe seleccionar un ${parentLabel} antes de agregar una ${label}`);
            return;
        }
    }

    // Construir el contenido del modal
    let modalContent = `
        <div class="mb-3">
            <label for="new_name" class="form-label required-field">Nombre ${label}</label>
            <input type="text" class="form-control" id="new_name" placeholder="Ingrese ${label}" required>
        </div>
    `;

    // Si tiene padre, mostrar la información en un alert
    if (parentId && parentName) {
        modalContent += `
            <div class="mb-3">
                <label class="form-label required-field">${parentLabel} seleccionada:</label>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>${parentName}</strong>
                </div>
                <input type="hidden" id="parent_id_value" value="${parentId}">
            </div>
        `;
    }

    $container.html(modalContent);

    // Mostrar modal
    $modal.modal('show');

    // Guardar
    $('#btnGuardar').off('click').on('click', function() {
        const valor = $('#new_name').val().trim();
        if (!valor) {
            $error.removeClass('d-none').text('El campo no puede estar vacío.');
            return;
        }

        // ---- Determinar si necesita ID padre ----
        let extraData = {};
        let parentIdValue = null;
        
        if (campo === 'bunker') {
            parentIdValue = $('#ciudad').val();
            if (!parentIdValue) {
                $error.removeClass('d-none').text('Debe seleccionar una ciudad antes de agregar un bunker.');
                return;
            }
            extraData.ciudad_id = parentIdValue;
        }
        else if (campo === 'jaula') {
            parentIdValue = $('#parent_id_value').val();
            if (!parentIdValue) {
                $error.removeClass('d-none').text('Debe seleccionar un bunker antes de agregar una jaula.');
                return;
            }
            extraData.bunker_id = parentIdValue;
        }

        // ---- Armar payload ----
        const payload = { nombre: valor, ...extraData };

        // Mostrar loading en el botón
        const btn = $('#btnGuardar');
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Guardando...');
        btn.prop('disabled', true);

        // Guardar vía AJAX
        $.ajax({
            url: '../api/' + apiAdd,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    // Cerrar modal
                    $modal.modal('hide');
                    
                    // Recargar según el tipo de campo
                    if (campo === 'ciudad') {
                        $.getJSON('../api/' + apiGet, function(data) {
                            const $select = $('#' + campo);

                            $select.empty().append('<option value="">Seleccione...</option>');

                            data.forEach(item => {
                                $select.append(new Option(item.nombre, item.id));
                            });

                            $select.val(resp.data.id).trigger('change');

                            finalizarGuardadoExitoso(label, btn, originalText);
                        });
                    }
                    else if (campo === 'bunker') {
                        const ciudadId = $('#ciudad').val();

                        if (ciudadId) {
                            cargarBunkers(ciudadId);

                            setTimeout(function() {
                                $('#bunker').val(resp.data.id).trigger('change');

                                finalizarGuardadoExitoso(label, btn, originalText);
                            }, 500);
                        } else {
                            showHeaderAlert('warning', 'No se pudo recargar el bunker agregado');
                            restaurarBoton(btn, originalText);
                        }
                    }
                    else if (campo === 'jaula') {
                        const bunkerId = $('#bunker').val();
                        if (bunkerId) {
                            // Recargar jaulas usando tu función existente
                            cargarJaulas(bunkerId);
                            
                            // Intentar seleccionar la nueva jaula después de que se cargue
                            setTimeout(function() {
                                // Verificar si el option existe en el select
                                if ($('#jaula option[value="' + resp.data.id + '"]').length > 0) {
                                    $('#jaula').val(resp.data.id).trigger('change');
                                    finalizarGuardadoExitoso(label, btn, originalText);
                                } else {
                                    // Si no existe, esperar un poco más
                                    setTimeout(function() {
                                        if ($('#jaula option[value="' + resp.data.id + '"]').length > 0) {
                                            $('#jaula').val(resp.data.id).trigger('change');
                                        }
                                        finalizarGuardadoExitoso(label, btn, originalText);
                                    }, 500);
                                }
                            }, 800);
                        } else {
                            finalizarGuardadoExitoso(label, btn, originalText);
                        }
                    }
                    else if (campo === 'cliente') {
                        $.getJSON('../api/' + apiGet, function(data) {
                            const $select = $('#cliente');
                            $select.empty().append('<option value="">Seleccione...</option>');
                            data.forEach(item => {
                                $select.append(new Option(item.nombre, item.id));
                            });
                            $select.val(resp.data.id).trigger('change');
                            finalizarGuardadoExitoso(label, btn, originalText);
                        });
                    } else {
                        // Por si acaso, restaurar botón
                        restaurarBoton(btn, originalText)
                    }
                } else {
                    $error.removeClass('d-none').text(resp.message || 'Ocurrió un error al guardar.');
                    restaurarBoton(btn, originalText)
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let msg = `Error al guardar: ${errorThrown}`;
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    msg = jqXHR.responseJSON.message;
                }
                $error.removeClass('d-none').text(msg);
                restaurarBoton(btn, originalText)
            }
        });
    });
}
</script>

</body>
</html>