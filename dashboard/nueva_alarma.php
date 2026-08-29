<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Validar y sanitizar el parámetro GET
$no_serie_param = isset($_GET['no_serie']) ? trim($_GET['no_serie']) : '';
$no_serie_param = htmlspecialchars($no_serie_param, ENT_QUOTES, 'UTF-8');

define('API_PATH', '../api/');
// Obtener datos para los selects
$catalogos = [
    ['id' => 'ciudad', 'label' => 'Ciudad', 'table' => 'ciudades'],
    ['id' => 'bunker', 'label' => 'Bunker', 'table' => 'bunkers'],
    ['id' => 'jaula', 'label' => 'Jaula', 'table' => 'jaulas'],
    ['id' => 'rack', 'label' => 'Rack', 'table' => 'racks'],
    ['id' => 'cliente', 'label' => 'Cliente', 'table' => 'clientes'],
    ['id' => 'marca', 'label' => 'Marca', 'table' => 'marcas'],
    ['id' => 'modelo', 'label' => 'Modelo', 'table' => 'modelos'],
    ['id' => 'tipo_alarma', 'label' => 'tipo_alarma', 'table' => 'tipos_alarma'],
    ['id' => 'estado_alarma', 'label' => 'estado_alarma', 'table' => 'estados_alarma'],
];

foreach ($catalogos as $cat) {
    $stmt = $pdo->query("SELECT id, nombre FROM {$cat['table']} ORDER BY nombre");
    ${$cat['id']} = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestion Alarmas - Registrar Nueva Alarma</title>
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
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Registrar Nueva Alarma</h4>
                    </div>
                    <div class="card-body">
                        <form id="form-alarma">
							<!-- Sección de búsqueda por número de serie -->
							<div class="form-section mb-4">
								<h5 class="section-title"><i class="fas fa-search me-2"></i>Buscar Equipo</h5>
								<div class="row g-3">
									<div class="col-md-12">
										<label for="buscar_serie" class="form-label">Buscar por Número de Serie</label>
										<div class="real-time-search position-relative">
											<div class="position-relative">
												<input type="text" id="buscar_serie" class="form-control" placeholder="Buscar número de serie..." value="<?= $no_serie_param ?>">

												<!-- Resultados -->
												<ul id="serie-search-results" class="list-group position-absolute w-100" style="z-index: 1000;"></ul>
											</div>
										</div>
										<div id="serie-result" class="mt-2"></div>
									</div>
								</div>
							</div>

							<!-- Sección de información de la alarma -->
							<div class="form-section alarma-card mb-4">
								<h5 class="section-title"><i class="fas fa-exclamation-triangle me-2"></i>Información de la Alarma</h5>
								<div class="row g-3">
									<div class="col-md-6">
										<label for="tipo_alarma" class="form-label required-field">Tipo de Alarma</label>
										<div class="input-group">
											<select class="form-select select2" id="tipo_alarma" name="tipo_alarma_id" required>
												<option value="">Seleccione una opción...</option>
												<?php if (!empty($tipo_alarma)): ?>
													<?php foreach ($tipo_alarma as $tipo): ?>
														<option value="<?= $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
											<button type="button" id="btnAgregarTipo" class="btn btn-add" 
											onclick="openModal('tipo_alarma', 'Tipo de Alarma', 'get_tipos_alarma.php', 'add_tipo_alarma.php')">
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>

									<div class="col-md-6">
										<label for="estado_alarma" class="form-label required-field">Estado</label>
										<select class="form-select select2" id="estado_alarma" name="estado_alarma_id" required>
											<?php if (!empty($estado_alarma)): ?>
												<?php foreach ($estado_alarma as $estado):
													if ($estado['nombre'] === 'Resuelta') continue; ?>
													<option value="<?= $estado['id'] ?>"><?= htmlspecialchars($estado['nombre']) ?></option>
												<?php endforeach; ?>
											<?php endif; ?>
										</select>
									</div>

									<div class="col-md-6">
										<label for="fecha_deteccion" class="form-label required-field">Fecha Detección</label>
										<input type="datetime-local" class="form-control" id="fecha_deteccion" name="fecha_deteccion" required
										value="<?= date('Y-m-d\TH:i') ?>">
									</div>

									<div class="col-md-6">
										<label for="im" class="form-label required-field">IM</label>
										<input type="text" class="form-control" id="im" name="im" required>
									</div>

									<div class="col-12">
										<label for="descripcion" class="form-label required-field">Descripción</label>
										<textarea class="form-control" id="descripcion" name="descripcion" rows="3" required
										placeholder="Describa la alarma (ej: LED rojo encendido en panel frontal, sonido de alarma audible, etc.)"></textarea>
									</div>
								</div>
							</div>

							<!-- Sección de información del equipo -->
							<div class="form-section info-card mb-4">
								<h5 class="section-title"><i class="fas fa-server me-2"></i>Información del Equipo</h5>

								<div class="row g-3">
								<!-- Ciudad (independiente) -->
									<div class="col-md-6">
										<label for="ciudad" class="form-label required-field">Ciudad</label>
										<div class="input-group">
											<select class="select2" id="ciudad" name="ciudad" required>
												<option value="">Seleccione una Ciudad</option>
												<?php foreach ($ciudad as $item): ?>
													<option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
												<?php endforeach; ?>
											</select>
											<button type="button" id="btnAgregarCiudad" class="btn btn-add" 
											onclick="openModal('ciudad', 'Ciudad', 'get_ciudades.php', 'add_ciudad.php')">
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>
									
									<!-- Bunker (depende de ciudad) -->
									<div class="col-md-6">
										<label for="bunker" class="form-label required-field">Bunker</label>
										<div class="input-group">
											<select class="select2 select-dependent" id="bunker" name="bunker" required disabled>
												<option value="">Primero seleccione una Ciudad</option>
											</select>
											<button type="button" id="btnAgregarBunker" class="btn btn-add" 
											onclick="openModal('bunker', 'Bunker', 'get_bunkers.php', 'add_bunker.php')"
											disabled>
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>

									<!-- Jaula (depende de bunker) -->
									<div class="col-md-6">
										<label for="jaula" class="form-label required-field">Jaula</label>
										<div class="input-group">
											<select class="select2 select-dependent" id="jaula" name="jaula" required disabled>
												<option value="">Primero seleccione un Bunker</option>
											</select>
											<button type="button" id="btnAgregarJaula" class="btn btn-add" 
											onclick="openModal('jaula', 'Jaula', 'get_jaulas.php', 'add_jaula.php')"
											disabled>
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>

									<!-- Rack (depende de jaula) -->
									<div class="col-md-6">
										<label for="rack" class="form-label required-field">Rack</label>
										<div class="input-group">
											<select class="select2 select-dependent" id="rack" name="rack" required disabled>
												<option value="">Primero seleccione una Jaula</option>
											</select>
											<button type="button" id="btnAgregarRack" class="btn btn-add" 
											onclick="openModal('rack', 'Rack', 'get_racks.php', 'add_rack.php')"
											disabled>
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>

									<!-- Cliente (independiente) -->
									<div class="col-md-6">
										<label for="cliente" class="form-label required-field">Cliente</label>
										<div class="input-group">
											<select class="select2" id="cliente" name="cliente" required>
												<option value="">Seleccione un Cliente</option>
												<?php foreach ($cliente as $item): ?>
													<option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
												<?php endforeach; ?>
											</select>
											<button type="button" id="btnAgregarCliente" class="btn btn-add" 
											onclick="openModal('cliente', 'Cliente', 'get_clientes.php', 'add_cliente.php')">
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>

									<!-- Marca (independiente) -->
									<div class="col-md-6">
										<label for="marca" class="form-label required-field">Marca</label>
										<div class="input-group">
											<select class="select2" id="marca" name="marca" required>
												<option value="">Seleccione una Marca</option>
												<?php foreach ($marca as $item): ?>
													<option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
												<?php endforeach; ?>
											</select>
											<button type="button" id="btnAgregarMarca" class="btn btn-add" 
											onclick="openModal('marca', 'Marca', 'get_marcas.php', 'add_marca.php')">
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>

									<!-- Modelo (depende de Marca) -->
									<div class="col-md-6">
										<label for="modelo" class="form-label required-field">Modelo</label>
										<div class="input-group">
											<select class="select2 select-dependent" id="modelo" name="modelo" required disabled>
												<option value="">Primero seleccione una Marca</option>
											</select>
											<button type="button" id="btnAgregarModelo" class="btn btn-add" 
											onclick="openModal('modelo', 'Modelo', 'get_modelos.php', 'add_modelo.php')"
											disabled>
											<i class="fas fa-plus text-white"></i></button>
										</div>
									</div>

									<!-- Unidad de rack -->
									<div class="col-md-6">
										<label for="unidad_rack" class="form-label">Unidad de Rack</label>
										<input type="text" class="form-control" id="unidad_rack" name="unidad_rack" placeholder="Ej: 23" />
									</div>

									<!-- Número de Serie -->
									<div class="col-md-6">
										<label for="no_serie" class="form-label required-field">No. de Serie</label>
										<input type="text" class="form-control" id="no_serie" name="no_serie" required placeholder="Ej: CNR12345678" value="<?= $no_serie_param ?>" />
									</div>

									<!-- Etiqueta -->
									<div class="col-md-6">
										<label for="hostname" class="form-label">Etiqueta</label>
										<input type="text" class="form-control" id="hostname" name="hostname" placeholder="srv-web-prod-01 " />
									</div>
									
								</div>
							</div>

							<!-- Botones de acción -->
							<div class="d-flex justify-content-between mt-4 flex-wrap">
								<a href="/alarmas/dashboard/index.php" class="btn btn-secondary btn-back">
									<i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
								</a>
								<button type="submit" class="btn btn-success btn-submit">
									<i class="fas fa-save me-2"></i> Registrar Alarma</button>
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
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div id="modalFieldsContainer">
						<!-- Campos dinámicos se insertarán aquí -->
					</div>
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
<script src="../assets/js/alerts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        let currentToken = null;

        function generateToken() {
            return Date.now() + Math.random().toString(36).substr(2, 9);
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        const $input = $('#buscar_serie');
        const $results = $('#serie-search-results');

// ----------------- Autocomplete serie -----------------
        $input.on('input', debounce(function () {
            const query = $(this).val().trim();
            if (query.length < 3) {
                $results.hide().empty();
                return;
            }

            $.getJSON(`../api/buscar_servidor.php?no_serie=${encodeURIComponent(query)}`, function(resp) {
                $results.empty();
                if (!resp.success || !resp.data.length) {
                    $results.append(`<li class="list-group-item text-danger">No se encontraron resultados</li>`).show();
                    return;
                }

                resp.data.forEach(item => {
                    $results.append(`
<li class="list-group-item list-group-item-action serie-item"
data-no-serie="${item.no_serie}"
data-ciudad="${item.ciudad_id || ''}"
data-bunker="${item.bunker_id || ''}"
data-jaula="${item.jaula_id || ''}"
data-rack="${item.rack_id || ''}"
data-cliente="${item.cliente_id || ''}"
data-marca="${item.marca_id || ''}"
data-modelo="${item.modelo_id || ''}"
data-unidad_rack="${item.unidad_rack || ''}"
data-hostname="${item.hostname || ''}">
<strong>${item.no_serie}</strong> - ${item.hostname}
</li>
                    `);
                });

                $results.show();
            });
        }, 300));

// ----------------- Click serie -----------------
        $(document).on('click', '.serie-item', function () {
            const $item = $(this);
            console.log($item);
            currentToken = generateToken();
            $input.val($item.data('no-serie'));
            fillSelectsWithToken($item, currentToken);
            $results.hide().empty();
        });

// ----------------- Función principal segura con token -----------------
        async function fillSelectsWithToken($item, token) {
            try {
// --- Ciudad ---
                const ciudadVal = $item.data('ciudad');
                if (ciudadVal) {
                    $('#ciudad').val(ciudadVal).trigger('change.select2');
                    $('#btnAgregarBunker').prop('disabled', false);
                }

                checkCiudadEspecial(ciudadVal);

// --- Bunker ---
                const bunkerVal = $item.data('bunker');
                if (ciudadVal) {
                    await getBunkers(ciudadVal, token);
if (token !== currentToken) return; // cancelar si token cambió
if ($('#bunker option[value="'+bunkerVal+'"]').length) {
    $('#bunker').val(bunkerVal).trigger('change.select2');
    $('#btnAgregarJaula').prop('disabled', false);
}
}

// --- Jaula ---
const jaulaVal = $item.data('jaula');
if (bunkerVal) {
    await getJaulas(bunkerVal, token);
    if (token !== currentToken) return;
    if ($('#jaula option[value="'+jaulaVal+'"]').length) {
        $('#jaula').val(jaulaVal).trigger('change.select2');
        $('#btnAgregarRack').prop('disabled', false);
    }
}

// --- Rack ---
const rackVal = $item.data('rack');
if (jaulaVal) {
    await getRacks(jaulaVal, token);
    if (token !== currentToken) return;
    if ($('#rack option[value="'+rackVal+'"]').length) {
        $('#rack').val(rackVal).trigger('change.select2');
    }
}

// --- Cliente ---
const clienteVal = $item.data('cliente');
if (clienteVal) $('#cliente').val(clienteVal).trigger('change.select2');

// --- Marca y Modelo ---
const marcaVal = $item.data('marca');
const modeloVal = $item.data('modelo');
if (marcaVal) {
    await getModelos(marcaVal, token);
    if (token !== currentToken) return;
    if ($('#marca option[value="'+marcaVal+'"]').length) $('#marca').val(marcaVal).trigger('change.select2');
    if ($('#modelo option[value="'+modeloVal+'"]').length) $('#modelo').val(modeloVal).trigger('change.select2');
    $('#btnAgregarModelo').prop('disabled', false);
}

// --- Otros campos ---
$('#unidad_rack').val($item.data('unidad_rack'));
$('#no_serie').val($item.data('noSerie'));
$('#hostname').val($item.data('hostname'));


// --- Bloquear campos ---
lockFields();

} catch (e) {
    console.error("Error en fillSelectsWithToken:", e);
}
}


// ----------------- Funciones dependientes -----------------
function getBunkers(ciudadId, token) {
    return new Promise((resolve) => {
        const $bunker = $('#bunker');
        if (!ciudadId) { $bunker.prop('disabled', true).empty().append('<option value="">Primero seleccione una Ciudad</option>'); resolve(); return; }
        $.getJSON(`../api/get_bunkers.php?ciudad_id=${ciudadId}`, function(data) {
            if (token !== currentToken) return resolve();
            $bunker.prop('disabled', false).empty().append('<option value="">Seleccione un Bunker</option>');
            data.forEach(item => $bunker.append(new Option(item.nombre, item.id)));
            resolve();
        });
    });
}

function getJaulas(bunkerId, token) {
    return new Promise((resolve) => {
        const $jaula = $('#jaula');
        if (!bunkerId) { $jaula.prop('disabled', true).empty().append('<option value="">Primero seleccione un Bunker</option>'); resolve(); return; }
        $.getJSON(`../api/get_jaulas.php?bunker_id=${bunkerId}`, function(data) {
            if (token !== currentToken) return resolve();
            $jaula.prop('disabled', false).empty().append('<option value="">Seleccione una Jaula</option>');
            data.forEach(item => $jaula.append(new Option(item.nombre, item.id)));
            resolve();
        });
    });
}

function getRacks(jaulaId, token) {
    return new Promise((resolve) => {
        const $rack = $('#rack');
        if (!jaulaId) { $rack.prop('disabled', true).empty().append('<option value="">Primero seleccione una Jaula</option>'); resolve(); return; }
        $.getJSON(`../api/get_racks.php?jaula_id=${jaulaId}`, function(data) {
            if (token !== currentToken) return resolve();
            $rack.prop('disabled', false).empty().append('<option value="">Seleccione un Rack</option>');
            data.forEach(item => $rack.append(new Option(item.nombre, item.id)));
            resolve();
        });
    });
}

function getModelos(marcaId, token) {
    return new Promise((resolve) => {
        const $modelo = $('#modelo');
        if (!marcaId) { $modelo.prop('disabled', true).empty().append('<option value="">Primero seleccione una Marca</option>'); resolve(); return; }
        $.getJSON(`../api/get_modelos.php?marca_id=${marcaId}`, function(data) {
            if (token !== currentToken) return resolve();
            $modelo.prop('disabled', false).empty().append('<option value="">Seleccione un Modelo</option>');
            data.forEach(item => $modelo.append(new Option(item.nombre, item.id)));
            resolve();
        });
    });
}

// --- Eventos de cambio manual de usuario ---
$('#ciudad').on('change', async function() {
    const ciudadId = $(this).val();
    checkCiudadEspecial(ciudadId);
    currentToken = generateToken();
    await getBunkers(ciudadId, currentToken);
    $('#bunker').prop('disabled', !ciudadId);
    $('#btnAgregarBunker').prop('disabled', !ciudadId);
});

$('#bunker').on('change', async function() {
    const bunkerId = $(this).val();
    currentToken = generateToken();
    await getJaulas(bunkerId, currentToken);
    $('#jaula').prop('disabled', !bunkerId);
    $('#btnAgregarJaula').prop('disabled', !bunkerId);
});

$('#jaula').on('change', async function() {
    const jaulaId = $(this).val();
    currentToken = generateToken();
    await getRacks(jaulaId, currentToken);
    $('#rack').prop('disabled', !jaulaId);
    $('#btnAgregarRack').prop('disabled', !jaulaId);
});

$('#marca').on('change', async function() {
    const marcaId = $(this).val();
    currentToken = generateToken();
    await getModelos(marcaId, currentToken);
    $('#modelo').prop('disabled', !marcaId);
    $('#btnAgregarModelo').prop('disabled', !marcaId);
});



// ----------------- Cargar desde GET -----------------
const noSerieFromGET = '<?= $no_serie_param ?>';
if (noSerieFromGET) {
    setTimeout(async () => {
        try {
            const resp = await $.getJSON(`../api/buscar_servidor.php?no_serie=${encodeURIComponent(noSerieFromGET)}`);
            if (resp.success && resp.data.length) {
                const item = resp.data[0];
                currentToken = generateToken();

// Crear <li> virtual con todos los datos
                const $item = itemAsJQuery(item);

// Ciudad
                const ciudadVal = $item.data('ciudad');
                if (ciudadVal) {
                    $('#ciudad').val(ciudadVal).trigger('change.select2');
                    $('#btnAgregarBunker').prop('disabled', false);
                    checkCiudadEspecial(ciudadVal);

                }

// Bunker
                const bunkerVal = $item.data('bunker');
                if (ciudadVal) {
                    await getBunkers(ciudadVal, currentToken);
                    if ($('#bunker option[value="'+bunkerVal+'"]').length) {
                        $('#bunker').val(bunkerVal).trigger('change.select2');
                        $('#btnAgregarJaula').prop('disabled', false);
                    }
                }

// Jaula
                const jaulaVal = $item.data('jaula');
                if (bunkerVal) {
                    await getJaulas(bunkerVal, currentToken);
                    if ($('#jaula option[value="'+jaulaVal+'"]').length) {
                        $('#jaula').val(jaulaVal).trigger('change.select2');
                        $('#btnAgregarRack').prop('disabled', false);
                    }
                }

// Rack
                const rackVal = $item.data('rack');
                if (jaulaVal) {
                    await getRacks(jaulaVal, currentToken);
                    if ($('#rack option[value="'+rackVal+'"]').length) {
                        $('#rack').val(rackVal).trigger('change.select2');
                    }
                }

// Cliente
                const clienteVal = $item.data('cliente');
                if (clienteVal) $('#cliente').val(clienteVal).trigger('change.select2');

// Marca y Modelo
                const marcaVal = $item.data('marca');
                const modeloVal = $item.data('modelo');
                if (marcaVal) {
                    await getModelos(marcaVal, currentToken);
                    if ($('#marca option[value="'+marcaVal+'"]').length) $('#marca').val(marcaVal).trigger('change.select2');
                    if ($('#modelo option[value="'+modeloVal+'"]').length) $('#modelo').val(modeloVal).trigger('change.select2');
                    $('#btnAgregarModelo').prop('disabled', false);
                }

// Otros campos
                $('#unidad_rack').val($item.data('unidad_rack'));
                $('#no_serie').val($item.data('noSerie'));
                $('#hostname').val($item.data('hostname'));

// Bloquear los campos que no deben editarse
                lockFields();
            }
        } catch (e) {
            console.error("Error cargando desde GET:", e);
        }
    }, 500);
}


// ----------------- Función para crear <li> con dataset completo -----------------
function itemAsJQuery(item) {
    const li = document.createElement('li');
    li.dataset.noSerie = item.no_serie || '';
    li.dataset.ciudad = item.ciudad_id || '';
    li.dataset.bunker = item.bunker_id || '';
    li.dataset.jaula = item.jaula_id || '';
    li.dataset.rack = item.rack_id || '';
    li.dataset.cliente = item.cliente_id || '';
    li.dataset.marca = item.marca_id || '';
    li.dataset.modelo = item.modelo_id || '';
    li.dataset.unidad_rack = item.unidad_rack || '';
    li.dataset.hostname = item.hostname || '';
    return $(li);
}



// ----------------- Funciones bloqueo/desbloqueo -----------------
function lockFields() {
    $('#marca,#modelo,#no_serie').prop('disabled', true);
    $('#btnAgregarBunker,#btnAgregarJaula,#btnAgregarRack,#btnAgregarCliente,#btnAgregarModelo').prop('disabled', true);
}

function unlockFields() {
    $('#ciudad,#cliente,#marca,#no_serie').prop('disabled', false);
}

// ----------------- Guardar Alarma via AJAX -----------------
$('#form-alarma').on('submit', function(e) {
e.preventDefault(); // Evita que se recargue la página

const formData = {
    tipo_alarma_id: $('#tipo_alarma').val(),
    estado_alarma_id: $('#estado_alarma').val(),
    fecha_deteccion: $('#fecha_deteccion').val(),
    descripcion: $('#descripcion').val(),
    no_serie: $('#no_serie').val(),
    ciudad_id: $('#ciudad').val(),
    bunker_id: $('#bunker').val(),
    jaula_id: $('#jaula').val(),
    rack_id: $('#rack').val(),
    cliente_id: $('#cliente').val(),
    marca_id: $('#marca').val(),
    modelo_id: $('#modelo').val(),
    unidad_rack: $('#unidad_rack').val(),
    im: $('#im').val()
};

// Validaciones simples
for (const key in formData) {
    if (!formData[key] && key !== 'rack_id' && key !== 'modelo_id' && key !== 'unidad_rack') {
        showHeaderAlert('danger','Por favor, completa todos los campos requeridos.');
        return;
    }
}

$.ajax({
    url: '../api/guardar_alarma.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
        if(response.success) {
            showHeaderAlert('success', 'Alarma registrada correctamente');
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

function checkCiudadEspecial(ciudadId) {
    if (ciudadId === '1' || ciudadId === 1) {
// Ocultar selects y botones dependientes
        $('#ciudad, #bunker, #jaula, #rack').closest('.col-md-6').hide();
        $('#btnAgregarBunker, #btnAgregarJaula, #btnAgregarRack').hide();

// Limpiar valores de selects ocultos
        $('#bunker, #jaula, #rack').val('').trigger('change.select2');
    } else {
// Mostrar selects y botones nuevamente
        $('#ciudad, #bunker, #jaula, #rack').closest('.col-md-6').show();
        $('#btnAgregarBunker, #btnAgregarJaula, #btnAgregarRack').show();
    }
}

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

// Input dinámico
    $container.append(`
<div class="mb-3">
<label for="new_name" class="form-label required-field">Nombre ${label}</label>
<input type="text" class="form-control" id="new_name" placeholder="Ingrese ${label}" required>
</div>
    `);

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
        if (campo === 'bunker') {
            const ciudadId = $('#ciudad').val();
            if (!ciudadId) {
                $error.removeClass('d-none').text('Debe seleccionar una ciudad antes de agregar un bunker.');
                return;
            }
            extraData.ciudad_id = ciudadId;
        }
        if (campo === 'jaula') {
            const bunkerId = $('#bunker').val();
            if (!bunkerId) {
                $error.removeClass('d-none').text('Debe seleccionar un bunker antes de agregar una jaula.');
                return;
            }
            extraData.bunker_id = bunkerId;
        }
        if (campo === 'rack') {
            const jaulaId = $('#jaula').val();
            if (!jaulaId) {
                $error.removeClass('d-none').text('Debe seleccionar una jaula antes de agregar un rack.');
                return;
            }
            extraData.jaula_id = jaulaId;
        }
        if (campo === 'modelo') {
            const marcaId = $('#marca').val();
            if (!marcaId) {
                $error.removeClass('d-none').text('Debe seleccionar una marca antes de agregar un modelo.');
                return;
            }
            extraData.marca_id = marcaId;
        }

// ---- Armar payload ----
        const payload = { nombre: valor, ...extraData };

// Guardar vía AJAX
        $.ajax({
            url: '../api/' + apiAdd,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $modal.modal('hide');

// Recargar opciones del select padre
                    $.getJSON('../api/' + apiGet, function(data) {
                        const $select = $('#' + campo);
                        $select.empty().append('<option value="">Seleccione...</option>');
                        data.forEach(item => $select.append(new Option(item.nombre, item.id)));

// Seleccionar nuevo registro
                        $select.val(resp.data.id).trigger('change');
                        $select.prop('disabled', false);

// Habilitar siguiente botón dependiente
                        if (campo === 'ciudad') $('#btnAgregarBunker').prop('disabled', false);
                        if (campo === 'bunker') $('#btnAgregarJaula').prop('disabled', false);
                        if (campo === 'jaula') $('#btnAgregarRack').prop('disabled', false);
                        if (campo === 'marca') $('#btnAgregarModelo').prop('disabled', false);
                    });

                } else {
                    $error.removeClass('d-none').text(resp.message || 'Ocurrió un error al guardar.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let msg = `No se pudo conectar con el servidor. 
Estado: ${textStatus}, 
                Error: ${errorThrown}`;
                if (jqXHR.responseText) {
                    msg += `\nRespuesta del servidor: ${jqXHR.responseText}`;
                }
                $error.removeClass('d-none').text(msg);
            }
        });
    });
}

</script>
</body>
</html>