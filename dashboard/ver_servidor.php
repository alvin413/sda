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
    <title>Gestion Alarmas - Detalle del Servidor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
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
        
        .card:hover {
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
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
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 2;
            color: var(--text-secondary);
            transition: var(--transition);
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .password-input {
            padding-right: 40px !important;
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
        
        .btn-danger-custom {
            background: linear-gradient(120deg, var(--danger-color), #c23b2b);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: var(--transition);
        }
        
        .btn-danger-custom:hover {
            background: linear-gradient(120deg, #c23b2b, #a33224);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .server-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }
        
        .server-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
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
            
            .server-header {
                padding: 1.5rem;
                text-align: center;
            }
            
            .server-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php';
    
    if (!empty($message)) {
        echo '<div class="alert alert-'.$alertType.' alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>'.$message.'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
?>

<div class="container-fluid py-4 fade-in">
    <!-- Encabezado del Servidor -->
    <div class="server-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="server-icon">
                    <i class="fas fa-server"></i>
                </div>
                <h1 class="h2 mb-2" id="hostname-titulo">Cargando...</h1>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-fingerprint me-2"></i>Serie:<span id="no_serie-titulo"></span>
                </p>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-building me-2"></i>Cliente:<span id="cliente-titulo"></span>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="/alarmas/dashboard/listado_servidores.php" class="btn btn-light btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-lg-6">
            <!-- Información General -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Hostname</div>
                            <div class="info-value" id="hostname">
                            <i class="fas fa-desktop me-2 text-primary"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Número de Serie</div>
                            <div class="info-value" id="no_serie">
                            <i class="fas fa-barcode me-2 text-primary"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Cliente</div>
                            <div class="info-value" id="cliente">
                            <i class="fas fa-building me-2 text-primary"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Fecha de Registro</div>
                            <div class="info-value" id="created_at">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Estado</div>
                            <div class="info-value" id="estado">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">RFC Alta</div>
                            <div class="info-value" id="rfc_alta">
                            <i class="fas fa-id-card me-2 text-primary"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-6 mb-3" id="rfc_baja_block" style="display:none;">
                            <div class="info-label">RFC Baja</div>
                            <div class="info-value" id="rfc_baja">
                                <i class="fas fa-id-card me-2 text-danger"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ubicación Física -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Ubicación Física</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Ciudad</div>
                            <div class="info-value" id="ciudad">
                            <i class="fas fa-city me-2 text-primary"></i>
                            <span class="placeholder"></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Bunker</div>
                            <div class="info-value" id="bunker">
                            <i class="fas fa-building-shield me-2 text-primary"></i>
                            <span class="placeholder"></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Jaula</div>
                            <div class="info-value" id="jaula">
                                <i class="fas fa-border-all me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Rack</div>
                            <div class="info-value" id="rack">
                                <i class="fas fa-server me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="info-label">Unidad en Rack</div>
                            <div class="info-value" id="unidad_rack">
                                <i class="fas fa-layer-group me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-6">
            <!-- Especificaciones de Hardware -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-microchip me-2"></i>Especificaciones de Hardware</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Marca</div>
                            <div class="info-value" id="marca">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Modelo</div>
                            <div class="info-value" id="modelo">
                            <i class="fas"></i>
                            <span class="placeholder"></span>
                        </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="info-label">Procesador (CPU)</div>
                            <div class="info-value" id="cpu">
                                <i class="fas fa-microchip me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestión y Monitorización -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-network-wired me-2"></i>Gestión y Monitorización</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">IP ILO</div>
                            <div class="info-value" id="ip_ilo">
                                <i class="fas fa-network-wired me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Usuario ILO</div>
                            <div class="info-value" id="ilo_user">
                                <i class="fas fa-user me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="info-label">Contraseña ILO</div>
                            <div class="password-container" id="ilo_password_container">
                                <input type="password" class="form-control info-value password-input" id="ilo_password" value="No configurada" readonly>
                                <span class="password-toggle" id="togglePassword" style="display:none;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">CI</div>
                            <div class="info-value" id="ci">
                                <i class="fas fa-file-alt me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Fecha de Garantía</div>
                            <div class="info-value" id="fecha_garantia">
                                <i class="fas fa-calendar-check me-2 text-primary"></i>
                                <span class="placeholder"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<!-- Acciones -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h5>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2 d-md-flex">
            <a href="#" id="btnEditarServidor" class="btn btn-primary me-2">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <button id="btnMostrarBaja" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#confirmBajaModal" style="display:none;">
                <i class="fas fa-arrow-down me-2"></i>Dar de Baja
            </button>
            <button id="btnMostrarActivar" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmActivarModal" style="display:none;">
                <i class="fas fa-arrow-up me-2"></i>Reactivar
            </button>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Baja -->
<div class="modal fade" id="confirmBajaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Confirmar Baja de Servidor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas dar de baja este servidor?</p>
                <div class="alert alert-info">
                    <strong>Información:</strong> El servidor cambiará a estado "baja" pero permanecerá en el sistema para posible reactivación futura.
                </div>
                <p><strong>Servidor:</strong> <span id="modalBajaHostname"></span></p>
                <p><strong>Número de Serie:</strong> <span id="modalBajaNoSerie"></span></p>

                <div class="mb-3">
                    <label for="rfc_desactivar" class="form-label">RFC para la baja</label>
                    <input type="text" class="form-control" id="rfc_desactivar" name="rfc_desactivar" placeholder="Ingrese RFC">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btnBajaServidor">
                    <i class="fas fa-arrow-down me-2"></i>Dar de Baja
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Reactivación -->
<div class="modal fade" id="confirmActivarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle me-2"></i>Confirmar Reactivación de Servidor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas reactivar este servidor?</p>
                <div class="alert alert-info">
                    <strong>Información:</strong> El servidor cambiará a estado "activo" y volverá a estar disponible en el sistema.
                </div>
                <p><strong>Servidor:</strong> <span id="modalActivarHostname"></span></p>
                <p><strong>Número de Serie:</strong> <span id="modalActivarNoSerie"></span></p>

                <div class="mb-3">
                    <label for="rfc_activar" class="form-label">RFC para la reactivación</label>
                    <input type="text" class="form-control" id="rfc_activar" name="rfc_activar" placeholder="Ingrese RFC">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnActivarServidor">
                    <i class="fas fa-arrow-up me-2"></i>Reactivar
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
    const servidorId = <?= $id ?>;

    // Función para asignar clase al ícono de estado
    function getEstadoClass(estado) {
        switch (estado) {
            case 'activo': return 'text-success';
            case 'mantenimiento': return 'text-warning';
            case 'baja': return 'text-secondary';
            default: return 'text-secondary';
        }
    }

    // Función para mostrar estado en formato legible
    function formatEstado(estado) {
        let texto = estado.replace(/[_-]/g, ' ').toLowerCase();
        texto = texto.replace(/\b\w/g, char => char.toUpperCase());
        return texto;
    }

    // Cargar datos del servidor desde la API
    $.getJSON('../api/get_servidor.php', { id: servidorId }, function(response) {
        if (response.success) {
            const data = response.data;

            $('#hostname-titulo').text(data.hostname || 'N/A');
            $('#no_serie-titulo').text(data.no_serie || 'N/A');
            $('#cliente-titulo').text(data.cliente || 'N/A');
            $('#hostname').text(data.hostname || 'N/A');
            $('#no_serie').text(data.no_serie || 'N/A');
            $('#cliente').text(data.cliente || 'N/A');
            $('#marca').text(data.marca || 'N/A');
            $('#modelo').text(data.modelo || 'N/A');
            $('#cpu').text(data.cpu || 'N/A');
            $('#estado').html(`
                <i class="fas fa-circle me-2 ${getEstadoClass(data.estado)}"></i>
                <span>${formatEstado(data.estado)}</span>
            `);
            $('#rfc_alta').text(data.rfc_alta || 'N/A');
            if (data.estado === 'baja') {
                $('#rfc_baja').text(data.rfc_baja || 'N/A');
                $('#rfc_baja_block').show();
            } else {
                $('#rfc_baja_block').hide();
            }
            $('#ciudad').text(data.ciudad || 'N/A');
            $('#bunker').text(data.bunker || 'N/A');
            $('#jaula').text(data.jaula || 'N/A');
            $('#rack').text(data.rack || 'N/A');
            $('#unidad_rack').text(data.unidad_rack || 'N/A');
            $('#ip_ilo').text(data.ip_ilo || 'N/A');
            $('#ilo_user').text(data.ilo_user || 'N/A');
            $('#ci').text(data.ci || 'N/A');
            $('#fecha_garantia').text(data.fecha_garantia || 'N/A');
            $('#created_at').text(data.created_at || 'N/A');

            // Estado con color e ícono
            $('#estado').html(`
                <i class="fas fa-circle me-2 ${getEstadoClass(data.estado)}"></i>
                <span>${formatEstado(data.estado)}</span>
            `);

            $('#btnEditarServidor').attr('href', `/alarmas/dashboard/editar_servidor.php?id=${data.id}`);

            if (data.estado === 'activo') {
                $('#btnMostrarBaja').show();
            } else if (data.estado === 'baja') {
                $('#btnMostrarActivar').show();
            }

            // Actualizar textos de los modales
            $('#modalBajaHostname').text(data.hostname);
            $('#modalBajaNoSerie').text(data.no_serie);
            $('#modalActivarHostname').text(data.hostname);
            $('#modalActivarNoSerie').text(data.no_serie);

            // Contraseña ILO
            if(data.ilo_password){
                $('#ilo_password').data('password', data.ilo_password).val('••••••••');
                $('#togglePassword').show();
            }
        } else {
            alert('Error al obtener datos del servidor: ' + (response.error || 'Desconocido'));
        }
    });

    // Función para mostrar/ocultar contraseña
    $('#togglePassword').click(function() {
        const passwordField = $('#ilo_password');
        const icon = $(this).find('i');
        const realPassword = passwordField.data('password');
        
        if (passwordField.attr('type') === 'password') {
            if (confirm('ADVERTENCIA: Estás a punto de ver información sensible. ¿Continuar?')) {
                passwordField.attr('type', 'text').val(realPassword);
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
                setTimeout(() => {
                    if (passwordField.attr('type') === 'text') {
                        passwordField.attr('type', 'password').val('••••••••');
                        icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    }
                }, 30000);
            }
        } else {
            passwordField.attr('type', 'password').val('••••••••');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Dar de baja servidor
    $('#btnBajaServidor').click(function() {
        const rfc_baja = $('#rfc_desactivar').val().trim();
        if (!rfc_baja) { alert('Debes ingresar un RFC para la baja'); return; }
        if (confirm('¿Confirmas que deseas dar de baja este servidor?\n\nEl servidor permanecerá en el sistema pero cambiará a estado "baja".')) {
            $(this).html('<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...')
                   .prop('disabled', true);

            $.ajax({
                url: '../api/baja_servidor.php',
                method: 'POST',
                data: { id: servidorId, rfc_baja: rfc_baja },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = `/alarmas/dashboard/ver_servidor.php?id=${servidorId}`;
                    } else {
                        alert('Error al dar de baja: ' + (response.error || 'Error desconocido'));
                        $('#btnBajaServidor').html('<i class="fas fa-arrow-down me-2"></i>Dar de Baja').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Error de conexión al intentar dar de baja');
                    $('#btnBajaServidor').html('<i class="fas fa-arrow-down me-2"></i>Dar de Baja').prop('disabled', false);
                }
            });
        }
    });

    // Reactivar servidor
$('#btnActivarServidor').click(function() {
    if (confirm('¿Confirmas que deseas reactivar este servidor?')) {
        const rfc = $('#rfc_activar').val().trim();
        if (rfc === '') {
            alert('Por favor ingresa el RFC para la reactivación');
            return;
        }

        $(this).html('<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...')
               .prop('disabled', true);

        $.ajax({
            url: '../api/activar_servidor.php',
            method: 'POST',
            data: { 
                id: servidorId, 
                rfc_activar: rfc
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = `/alarmas/dashboard/ver_servidor.php?id=${servidorId}`;
                } else {
                    alert('Error al reactivar: ' + (response.error || 'Error desconocido'));
                    $('#btnActivarServidor').html('<i class="fas fa-arrow-up me-2"></i>Reactivar').prop('disabled', false);
                }
            },
            error: function() {
                alert('Error de conexión al intentar reactivar');
                $('#btnActivarServidor').html('<i class="fas fa-arrow-up me-2"></i>Reactivar').prop('disabled', false);
            }
        });
    }
});


});
</script>
