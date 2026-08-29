<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /alarmas/auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /alarmas/dashboard/index.php");
    exit;
}

$id = $_GET['id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Alarmas - Detalle de Alarma</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-radius: 0.75rem 0.75rem 0 0 !important;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .info-label {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .info-value {
        padding: 0.75rem;
        background-color: white;
        border-radius: 0.5rem;
        border-left: 3px solid var(--primary-color);
        margin-bottom: 1rem;
        min-height: 2.75rem;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .section-title {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
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

    .alarma-header {
        background: linear-gradient(135deg, var(--primary-color));
        color: white;
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        box-shadow: var(--card-shadow);
    }

    .alarma-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }

    .badge-estado {
        padding: 0.5em 0.8em;
        border-radius: 0.35rem;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .badge-detectada {
        background-color: rgba(246, 194, 62, 0.15);
        color: var(--warning-color);
    }

    .badge-reportada {
        background-color: rgba(54, 185, 204, 0.15);
        color: var(--accent-color);
    }

    .badge-resuelta {
        background-color: rgba(28, 200, 138, 0.15);
        color: var(--success-color);
    }

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
        padding: 1rem 1.25rem;
    }

    .info-value {
        padding: 0.6rem;
        min-height: 2.5rem;
    }

    .alarma-header {
        padding: 1.5rem;
        text-align: center;
    }

    .alarma-icon {
        font-size: 2.5rem;
    }
}
</style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid py-4 fade-in">
<!-- Encabezado de la Alarma -->
<div class="alarma-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="alarma-icon">
                <i class="fas fa-bell"></i><h1 class="h2 mb-2" id="alarma-titulo">Cargando...</h1>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="/alarmas/dashboard/index.php" class="btn btn-light btn-back">
                <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-tie me-2"></i>Cliente
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 md-3">
                        <div class="info-label">Cliente</div>
                        <div class="info-value" id="cliente">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-desktop me-2"></i>Información del Equipo
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 md-3">
                        <div class="info-label">Marca</div>
                        <div class="info-value" id="marca">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 md-3">
                        <div class="info-label">Modelo</div>
                        <div class="info-value" id="modelo">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 md-3">
                        <div class="info-label">Número de Serie</div>
                        <div class="info-value" id="no-serie">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Ubicación del Equipo
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Ciudad</div>
                        <div class="info-value" id="ciudad">
                            <i class="fas fa-city me-2 text-primary"></i>
                            <span class="placeholder col-5"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Bunker</div>
                        <div class="info-value" id="bunker">
                            <i class="fas fa-building-shield me-2 text-primary"></i>
                            <span class="placeholder col-5"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Jaula</div>
                        <div class="info-value" id="jaula">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Rack</div>
                        <div class="info-value" id="rack">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Unidad de Rack</div>
                        <div class="info-value" id="unidad-rack">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bell me-2"></i>Información de la Alarma
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 md-3">
                        <div class="info-label">Tipo de Alarma</div>
                        <div class="info-value" id="tipo-alarma">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Estado</div>
                        <div class="info-value" id="estado-alarma">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Fecha Detección</div>
                        <div class="info-value" id="fecha-deteccion">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Fecha Resolución</div>
                        <div class="info-value" id="fecha-resolucion">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Descripción</div>
                        <div class="info-value" id="descripcion">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
					<div class="col-md-6 mb-3">
                        <div class="info-label">Caso Proveedor</div>
                        <div class="info-value" id="caso">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
					<div class="col-md-6 mb-3">
                        <div class="info-label">Resolución</div>
                        <div class="info-value" id="resolucion">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
					</div>
					<div class="col-md-6 mb-3">
						<div class="info-label">IM</div>
                        <div class="info-value" id="im">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Información del Registro
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Registrado por</div>
                        <div class="info-value" id="usuario-registro">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div><div class="col-md-6 mb-3">
                        <div class="info-label">Fecha de Registro</div>
                        <div class="info-value" id="fecha-registro">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="card mt-4" id="acciones-alarmas">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="#" class="btn btn-primary me-2" id="btn-editar">
                            <i class="fas fa-edit me-2"></i>Editar Registro
                        </a>
                        <button class="btn btn-danger" id="btn-eliminar">
                            <i class="fas fa-trash-alt me-2"></i>Eliminar
                        </button>
                    </div>
                </div>
            </div>
    </div>
</div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar esta alarma?</p>
                <div class="alert alert-warning">
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer.
                </div>
                <p><strong>Alarma ID:</strong> <span id="modal-alarma-id"></span></p>
                <p><strong>Número de Serie:</strong> <span id="modal-no-serie"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const alarmaId = <?= $id ?>;
        let alarmaData = {};

// Cargar datos de la alarma
        function cargarAlarma() {
            $.ajax({
                url: '/alarmas/api/get_alarma.php',
                method: 'GET',
                data: { id: alarmaId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alarmaData = response.data;
                        mostrarDatosAlarma(alarmaData);
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
// Encabezado
            $('#alarma-titulo').text(`${data.no_serie}`);

// Información del equipo
            $('#no-serie').html(`<i class="fas fa-barcode me-2 text-primary"></i>${data.no_serie || 'No especificado'}`);
            $('#ciudad').html(`<i class="fas fa-city me-2 text-primary"></i>${data.ciudad_nombre || 'No especificada'}`);
            $('#bunker').html(`<i class="fas fa-building-shield me-2 text-primary"></i>${data.bunker_nombre || 'No especificado'}`);
            $('#jaula').html(`<i class="fas fa-border-all me-2 text-primary"></i>${data.jaula_nombre || 'No especificada'}`);
            $('#rack').html(`<i class="fas fa-server me-2 text-primary"></i>${data.rack_nombre || 'No especificado'}`);
            $('#cliente').html(`<i class="fas fa-building me-2 text-primary"></i>${data.cliente_nombre || 'No especificado'}`);
            $('#marca').html(`<i class="fas fa-tag me-2 text-primary"></i>${data.marca_nombre || 'No especificada'}`);
            $('#modelo').html(`<i class="fas fa-cube me-2 text-primary"></i>${data.modelo_nombre || 'No especificado'}`);
            $('#unidad-rack').html(`<i class="fas fa-layer-group me-2 text-primary"></i>${data.ubicacion_manual || 'No especificada'}`);

// Información de la alarma
            $('#tipo-alarma').html(`<i class="fas fa-bell me-2 text-primary"></i>${data.tipo_alarma_nombre}`);

// Estado con badge de color
            let badgeClass = '';
            let estadoTexto = '';
            if (data.estado_alarma_nombre === 'Detectada') {
                badgeClass = 'badge-detectada';
                estadoTexto = 'Detectada';
            } else if (data.estado_alarma_nombre === 'Reportada') {
                badgeClass = 'badge-reportada';
                estadoTexto = 'Reportada';
            } else if (data.estado_alarma_nombre === 'Resuelta') {
                badgeClass = 'badge-resuelta';
                estadoTexto = 'Resuelta';
            } else {
                estadoTexto = data.estado_alarma_nombre || 'Desconocido';
            }
            $('#estado-alarma').html(`<span class="badge-estado ${badgeClass}">${estadoTexto}</span>`);

// Fechas
            $('#fecha-deteccion').html(`<i class="fas fa-calendar-alt me-2 text-primary"></i>${formatearFecha(data.fecha_deteccion)}`);

            if (data.fecha_resolucion) {
                $('#fecha-resolucion').html(`<i class="fas fa-calendar-check me-2 text-primary"></i>${formatearFecha(data.fecha_resolucion)}`);
            } else {
                $('#fecha-resolucion').html(`<i class="fas fa-calendar-check me-2 text-primary"></i>No resuelta`);
            }

// Descripción
            $('#descripcion').html(data.descripcion || 'No hay descripción');

            // Resolución
            $('#resolucion').html(data.resolucion);
			
			// IM
            $('#im').html(data.im || 'No hay descripción');

// Caso
            if (data.caso) {
                $('#caso').html(`<i class="fas fa-file-alt me-2 text-primary"></i>${data.caso}`);
            } else {
                $('#caso').html(`<i class="fas fa-file-alt me-2 text-primary"></i>No especificado`);
            }

// Información de registro
            $('#usuario-registro').html(`<i class="fas fa-user me-2 text-primary"></i>${data.usuario_nombre || 'Desconocido'}`);
            $('#fecha-registro').html(`<i class="fas fa-clock me-2 text-primary"></i>${formatearFecha(data.created_at)}`);

// Configurar enlace de edición
            $('#btn-editar').attr('href', `/alarmas/dashboard/editar_alarma.php?id=${data.id}`);
            // Ocultar acciones si la alarma está resuelta
            if (data.estado_alarma_nombre === 'Resuelta') {
                $('#acciones-alarmas').hide();
            } else {
                $('#acciones-alarmas').show();
            }
        }

// Formatear fecha
        function formatearFecha(fechaString) {
            if (!fechaString) return 'No especificada';

            const fecha = new Date(fechaString);
            return fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

// Mostrar error
        function mostrarError(mensaje) {
            $('#alarma-titulo').text('Error');
            $('#alarma-subtitulo').html(`<i class="fas fa-exclamation-triangle me-2"></i>${mensaje}`);

// Ocultar placeholders
            $('.info-value').html('<i class="fas fa-times-circle me-2 text-danger"></i>Error al cargar');
        }

// Eliminar alarma
        $('#btn-eliminar').click(function() {
            $('#modal-alarma-id').text(alarmaData.id);
            $('#modal-no-serie').text(alarmaData.no_serie || 'N/A');
            $('#confirmDeleteModal').modal('show');
        });

        $('#btn-confirmar-eliminar').click(function() {
            $(this).html('<span class="spinner-border spinner-border-sm" role="status"></span> Eliminando...')
            .prop('disabled', true);

            $.ajax({
                url: '/alarmas/api/delete_alarma.php',
                method: 'POST',
                data: { id: alarmaId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = '/alarmas/dashboard/index.php?deleted=1';
                    } else {
                        alert('Error al eliminar: ' + (response.error || 'Error desconocido'));
                        $('#btn-confirmar-eliminar').html('<i class="fas fa-trash-alt me-2"></i>Eliminar')
                        .prop('disabled', false);
                        $('#confirmDeleteModal').modal('hide');
                    }
                },
                error: function() {
                    alert('Error de conexión al intentar eliminar');
                    $('#btn-confirmar-eliminar').html('<i class="fas fa-trash-alt me-2"></i>Eliminar')
                    .prop('disabled', false);
                }
            });
        });

// Iniciar carga de datos
        cargarAlarma();
    });
</script>
</body>
</html>