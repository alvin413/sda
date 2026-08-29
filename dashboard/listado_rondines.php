<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener todos los servicios de rondín
$query = "SELECT rr.*, 
c.nombre AS ciudad, 
b.nombre AS bunker, 
j.nombre AS jaula,
cl.nombre AS cliente, 
u.username AS usuario,
CONCAT(c.nombre, ' Bunker ', b.nombre, ' Jaula ', j.nombre, ' Rack ', rr.rack) AS ubicacion,
rr.llave,
rr.rack,
GROUP_CONCAT(sr.nombre ORDER BY sr.nombre SEPARATOR ', ') AS servicios_nombres
FROM rondines_racks rr
JOIN ciudades c ON rr.ciudad_id = c.id
JOIN bunkers b ON rr.bunker_id = b.id
JOIN jaulas j ON rr.jaula_id = j.id
JOIN clientes cl ON rr.cliente_id = cl.id
JOIN usuarios u ON rr.usuario_registro = u.id
LEFT JOIN servicios_rondines sr ON FIND_IN_SET(sr.id, rr.servicios_contratados)
GROUP BY rr.id
ORDER BY rr.fecha_baja IS NULL DESC, rr.fecha_alta DESC";



$rondines = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Alarmas - Rondínes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
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
            line-height: 1.6;
        }
        
        .card {
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: none;
            transition: var(--transition);
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
        
        .table-responsive {
            overflow-x: auto;
            border-radius: 0 0 0.75rem 0.75rem;
        }
        
        .badge-activo {
            background-color: var(--success-color);
            color: white;
            padding: 0.5em 0.8em;
            border-radius: 0.35rem;
            font-weight: 500;
        }
        
        .badge-inactivo {
            background-color: var(--danger-color);
            color: white;
            padding: 0.5em 0.8em;
            border-radius: 0.35rem;
            font-weight: 500;
        }
        
        /* Estilos para DataTables */
        .dataTables_wrapper {
            padding: 0 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.5rem 0.75rem;
            margin-left: 0.5rem;
            transition: var(--transition);
        }
        
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.4rem 1.5rem 0.4rem 0.5rem;
            margin: 0 0.5rem;
            transition: var(--transition);
        }
        
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 1em;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin: 0 0.15rem;
            transition: var(--transition);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary-color) !important;
            color: white !important;
            border: 1px solid var(--primary-color);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--secondary-color) !important;
            border: 1px solid var(--border-color);
            color: var(--primary-color) !important;
        }
        
        /* Estilos para la tabla */
        #rondinesTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100% !important;
        }
        
        #rondinesTable thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 1rem 0.75rem;
            border: none;
            position: relative;
        }
        
        #rondinesTable tbody td {
            padding: 0.9rem 0.75rem;
            vertical-align: middle;
            border-color: var(--border-color);
        }
        
        #rondinesTable tbody tr {
            transition: var(--transition);
        }
        
        #rondinesTable tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        /* Botones de acción */
        .btn-action {
            padding: 0.35rem 0.65rem;
            border-radius: 0.4rem;
            transition: var(--transition);
            margin: 0 2px;
        }
        
        .btn-view {
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-view:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-edit {
            color: var(--accent-color);
            border: 1px solid var(--accent-color);
        }
        
        .btn-edit:hover {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-delete {
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }
        
        .btn-delete:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Botón nuevo servicio */
        .btn-new {
            background: white;
            color: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-new:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            background: var(--primary-color);
            color: white;
        }
        
        /* Modal para baja */
        #modalBaja .modal-header {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        
        #modalBaja .modal-content {
            border-radius: 0.75rem;
            border: none;
            box-shadow: var(--card-shadow);
        }
        
        .required-field::after {
            content: "*";
            color: var(--danger-color);
            margin-left: 3px;
        }
        
        /* Alertas */
        .alert {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 0.15rem 0.5rem rgba(0,0,0,0.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-header > div {
                margin-top: 1rem;
                width: 100%;
                display: flex;
                justify-content: space-between;
            }
            
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: none;
                margin-bottom: 1rem;
            }
        }
        
        /* Animación de carga suave */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container-fluid py-4 fade-in">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Servicios de Rondín</h4>
                <a href="nuevo_rondin.php" class="btn btn-new">
                    <i class="fas fa-plus me-2"></i>Nuevo Servicio
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="rondinesTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Ubicación</th>
                                <th>Cliente</th>
                                <th>Líder</th>
                                <th>RFC Alta</th>
                                <th>Fecha Alta</th>
                                <th>RFC Baja</th>
                                <th>Fecha Baja</th>
                                <th>Llave</th> <!-- Nueva columna -->
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rondines as $rondin): ?>
                            <tr>
                                <td><?= htmlspecialchars($rondin['ubicacion'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($rondin['cliente'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($rondin['lider_proyecto'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($rondin['rfc_solicitante'] ?? '-') ?></td>
                                <td data-order="<?= strtotime($rondin['fecha_alta']) ?>">
                                    <?= date('d/m/Y', strtotime($rondin['fecha_alta'])) ?>
                                </td>
                                <td><?= htmlspecialchars($rondin['rfc_baja'] ?? '-') ?></td>
                                <td data-order="<?= $rondin['fecha_baja'] ? strtotime($rondin['fecha_baja']) : 0 ?>">
                                    <?= $rondin['fecha_baja'] ? date('d/m/Y', strtotime($rondin['fecha_baja'])) : '-' ?>
                                </td>
                                <td><?= htmlspecialchars($rondin['llave'] ?? '-') ?></td> <!-- Mostrar llave -->
                                <td>
                                    <?php if (empty($rondin['fecha_baja'])): ?>
                                        <span class="badge badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badnge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
    <button class="btn btn-sm btn-view ver-detalles"
    data-servicios="<?= htmlspecialchars($rondin['servicios_nombres'] ?? '-') ?>"
    data-observaciones="<?= htmlspecialchars($rondin['observaciones'] ?? '-') ?>"
    title="Ver detalles"><i class="fas fa-eye"></i></button>


    <!--<button class="btn btn-sm btn-edit" 
        onclick="window.location.href='editar_rondin.php?id=<?= $rondin['id'] ?>'" 
        title="Editar"><i class="fas fa-edit"></i></button>-->

    <button class="btn btn-sm btn-delete dar-baja" 
        data-id="<?= $rondin['id'] ?>" 
        data-ubicacion="<?= htmlspecialchars($rondin['ubicacion'] ?? '-') ?>" 
        data-rfc-alta="<?= htmlspecialchars($rondin['rfc_solicitante'] ?? '-') ?>" 
        title="Dar de baja"><i class="fas fa-times"></i></button>
</td>

                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para baja -->
<div class="modal fade" id="modalBaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Dar de baja servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBaja">
                    <input type="hidden" id="rondinIdBaja" name="id">
                    <div class="mb-3">
                        <label for="ubicacionBaja" class="form-label">Ubicación</label>
                        <input type="text" class="form-control" id="ubicacionBaja" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="rfcAltaBaja" class="form-label">RFC de Alta</label>
                        <input type="text" class="form-control" id="rfcAltaBaja" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="rfcBaja" class="form-label required-field">RFC de Baja</label>
                        <input type="text" class="form-control" id="rfcBaja" name="rfc_baja" required
                        placeholder="Ingrese RFC diferente al de alta" maxlength="20">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarBaja">Confirmar Baja</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery, Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- Botones de exportación -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<!-- PDFMake para exportación a PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        var table = $('#rondinesTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            responsive: true,
            order: [[4, 'desc']], // Ordenar por fecha de alta descendente por defecto
            columnDefs: [
                {
                    targets: [9], // Columna de acciones
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [4, 5], // Columnas de fecha
                    type: 'date' // Especificar que es de tipo fecha para ordenamiento correcto
                }
            ],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy"></i>',
                    titleAttr: 'Copiar',
                    className: 'btn btn-sm btn-outline-primary'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i>',
                    titleAttr: 'Exportar a Excel',
                    className: 'btn btn-sm btn-outline-success'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i>',
                    titleAttr: 'Exportar a PDF',
                    className: 'btn btn-sm btn-outline-danger'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    titleAttr: 'Imprimir',
                    className: 'btn btn-sm btn-outline-info'
                }
            ]
        });

        // Añadir los botones de exportación al layout
        table.buttons().container().appendTo('.dataTables_wrapper .col-md-6:eq(0)');
        
        // Estilo personalizado para los botones de exportación
        $('.dt-buttons').addClass('btn-group');
        $('.dt-button').removeClass('dt-button').addClass('btn');

        // Variables para el modal de baja
        const modalBaja = new bootstrap.Modal(document.getElementById('modalBaja'));
        let rondinABaja = null;

        // Mostrar modal de baja
        $(document).on('click', '.dar-baja', function() {
            rondinABaja = {
                id: $(this).data('id'),
                ubicacion: $(this).data('ubicacion'),
                rfcAlta: $(this).data('rfc-alta')
            };

            $('#rondinIdBaja').val(rondinABaja.id);
            $('#ubicacionBaja').val(rondinABaja.ubicacion);
            $('#rfcAltaBaja').val(rondinABaja.rfcAlta);
            $('#rfcBaja').val('');
            
            modalBaja.show();
        });

        // Confirmar baja
        $('#confirmarBaja').click(function() {
            const rfcBaja = $('#rfcBaja').val().trim().toUpperCase();
            const rfcAlta = $('#rfcAltaBaja').val().trim().toUpperCase();

            
            // Validar que el RFC de baja sea diferente al de alta
            if (rfcBaja === rfcAlta) {
                alert('El RFC de baja debe ser diferente al RFC de alta');
                return;
            }
            
            if (!rfcBaja) {
                alert('El RFC de baja es requerido');
                return;
            }
            
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...');

            $.ajax({
                url: '../api/baja_rondin.php',
                method: 'POST',
                data: $('#formBaja').serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'listado_rondines.php?success=2';
                    } else {
                        alert('Error: ' + (response.error || 'Error desconocido'));
                        btn.prop('disabled', false).html('Confirmar Baja');
                    }
                },
                error: function() {
                    alert('Error de conexión');
                    btn.prop('disabled', false).html('Confirmar Baja');
                }
            });
        });
    
    // Modal de detalles
const modalDetalles = new bootstrap.Modal(document.getElementById('modalDetalles'));

$(document).on('click', '.ver-detalles', function() {
    const servicios = $(this).data('servicios');
    const observaciones = $(this).data('observaciones');

    $('#modalServicios').text(servicios || '-');
    $('#modalObservaciones').text(observaciones || '-');

    modalDetalles.show();
});
});
</script>
<!-- Modal para ver servicios y descripción -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles del servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Servicios Contratados:</label>
                    <p id="modalServicios" class="border p-2 rounded bg-light"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción:</label>
                    <p id="modalObservaciones" class="border p-2 rounded bg-light"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>