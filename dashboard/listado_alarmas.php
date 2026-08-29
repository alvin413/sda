<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Total de alarmas (solo para mostrar en la interfaz)
$totalStmt = $pdo->query("SELECT COUNT(*) FROM alarmas_servidores");
$totalAlarmas = $totalStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Alarmas - Listado de Alarmas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
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
        
        .badge-total {
            background-color: white;
            color: var(--primary-color);
            font-size: 0.9rem;
            padding: 0.5em 0.9em;
            border-radius: 0.35rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        #alarmasTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100% !important;
        }
        
        #alarmasTable thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 1rem 0.75rem;
            border: none;
            position: relative;
        }
        
        #alarmasTable tbody td {
            padding: 0.9rem 0.75rem;
            vertical-align: middle;
            border-color: var(--border-color);
        }
        
        #alarmasTable tbody tr {
            transition: var(--transition);
        }
        
        #alarmasTable tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        /* Badges para estados */
        .badge-estado {
            padding: 0.5em 0.8em;
            border-radius: 0.35rem;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .badge-activa {
            background-color: var(--danger-color);
            color: white;
        }
        
        .badge-resuelta {
            background-color: var(--success-color);
            color: white;
        }
        
        .badge-en-proceso {
            background-color: var(--warning-color);
            color: white;
        }
        
        .badge-caso {
            padding: 0.4em 0.7em;
            border-radius: 0.3rem;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-caso-abierto {
            background-color: rgba(231, 74, 59, 0.15);
            color: var(--danger-color);
        }
        
        .badge-caso-cerrado {
            background-color: rgba(28, 200, 138, 0.15);
            color: var(--success-color);
        }
        
        .badge-caso-pendiente {
            
            color: var(--warning-color);
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
        
        .btn-resolve {
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }
        
        .btn-resolve:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Botón nueva alarma */
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
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            #alarmasTable th:nth-child(4),
            #alarmasTable td:nth-child(4),
            #alarmasTable th:nth-child(5),
            #alarmasTable td:nth-child(5) {
                display: none;
            }
        }
        
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
            
            #alarmasTable th:nth-child(3),
            #alarmasTable td:nth-child(3),
            #alarmasTable th:nth-child(6),
            #alarmasTable td:nth-child(6) {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            #alarmasTable th:nth-child(2),
            #alarmasTable td:nth-child(2) {
                display: none;
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
        
        /* Modal personalizado */
        .custom-modal .modal-content {
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: none;
        }
        
        .custom-modal .modal-header {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 0.75rem 0.75rem 0 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .custom-modal .modal-title {
            font-weight: 600;
        }
        
        .custom-modal .btn-close {
            filter: invert(1);
        }
        
        .custom-modal .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        
        .custom-modal .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .custom-modal .btn-outline-secondary {
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
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

.custom-alert-overlay, .custom-alert-box {
    z-index: 2000 !important; /* Bootstrap modals están en 1050-1070 */
}

    </style>


    </style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid py-4 fade-in">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Listado de Alarmas</h4>
            <div class="d-flex align-items-center">
                <span class="badge badge-total me-3">Total: <?= $totalAlarmas ?></span>
                <a href="nueva_alarma.php" class="btn btn-new">
                    <i class="fas fa-plus me-2"></i>Nueva Alarma
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="alarmasTable" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>N° Serie</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Caso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <!-- DataTables llenará esta sección vía AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para resolver alarma -->
<div class="modal fade custom-modal" id="resolveAlarmModal" tabindex="-1" aria-labelledby="resolveAlarmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resolveAlarmModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Resolver Alarma
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Deseas resolver esta alarma? Esta acción no se puede deshacer.</p>
                <p><strong>Cliente:</strong> <span id="resolveClienteText"></span></p>
                <p><strong>No. Serie:</strong> <span id="resolveNoSerieText"></span></p>
                <p><strong>Fecha detección:</strong> <span id="resolveFechaDetText"></span></p>

                <div class="mb-3">
                    <label for="resolveText" class="form-label">Resolución</label>
                    <textarea class="form-control" id="resolveText" rows="3"></textarea>
                </div>

                <input type="hidden" id="resolveAlarmId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmResolveBtn">Resolver</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/alerts.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

<script>
// Variable global para almacenar el ID a eliminar
let currentDeleteId = null;

$(document).ready(function() {
    const table = $('#alarmasTable').DataTable({
    responsive: true,
    processing: true,
    serverSide: false,
    ajax: {
        url: '../api/data_alarmas.php',
        type: 'GET',
        cache: false,
        dataSrc: function(json) { return json.data; }
    },
    columns: [
        { data: "id", visible: false },
        { 
            data: "fecha",
            render: function(data, type) {
                return type === 'display' ? data.display : data.sort;
            }
        },
        { data: "cliente" },
        { data: "marca" },
        { data: "modelo" },
        { data: "no_serie" },
        { data: "tipo_alarma" },
        { 
            data: "estado_alarma",
            render: function(data, type) {
                if (type === 'display') {
                    let badgeClass = 'badge-estado ';
                    // Convertimos a minúsculas para evitar problemas de comparación
                    switch(data.value.toLowerCase()) {
                        case 'detectada': badgeClass += 'badge-activa'; break;
                        case 'resuelta': badgeClass += 'badge-resuelta'; break;
                        default: badgeClass += 'badge-secondary';
                    }
                    return `<span class="${badgeClass}">${data.display}</span>`;
                }
                return data.value;
            }
        },
        { 
            data: "caso",
            render: function(data, type) {
                if (type === 'display') {
                    let badgeClass = 'badge-caso ';
                    switch(data.value.toLowerCase()) {
                        case '': badgeClass += 'badge-caso-pendiente'; break;
                        default: badgeClass += 'badge-caso-abierto';
                    }
                    return `<span class="${badgeClass}">${data.display}</span>`;
                }
                return data.value;
            }
        },
        {
            data: null, 
            orderable: false, 
            searchable: false,
            render: function(data, type, row) {
                if (type === 'display') {
                    // Botones de acción
                    let buttons = `
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-action btn-view" data-id="${row.id}" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                    `;

                    // Mostrar botón resolver solo si el estado NO es 'resuelta'
                    if (row.estado_alarma.value.toLowerCase() !== 'resuelta') {
                        buttons += `
                            <button class="btn btn-action btn-edit" data-id="${row.id}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-action btn-resolve" data-id="${row.id}" data-cliente="${row.cliente}" data-no-serie="${row.no_serie}" data-fecha="${row.fecha.display}" title="Resolver">
                                <i class="fas fa-check"></i>
                            </button>
                        `;
                    }

                    buttons += `</div>`;
                    return buttons;
                }
                return '';
            }
        }
    ],
    order: [[1, 'desc']],
    language: { 
        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
    dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
});

    
    // Estilo personalizado para los botones de exportación
    $('.dt-buttons').addClass('btn-group');
    $('.dt-button').removeClass('dt-button').addClass('btn btn-sm btn-outline-primary');
    
    // Delegación de eventos para los botones de acción
    $('#alarmasTable tbody').on('click', '.btn-view', function() {
        const id = $(this).data('id');
        viewRecord(id);
    });
    
    $('#alarmasTable tbody').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        editRecord(id);
    });

    $('#alarmasTable tbody').on('click', '.btn-resolve', function() {
    const id = $(this).data('id');
    const cliente = $(this).data('cliente');
    const noSerie = $(this).data('no-serie');
    const fechaDet = $(this).data('fecha');
    openResolveModal(id, cliente, noSerie, fechaDet);
    //openResolveModal(row.id, row.cliente, row.no_serie, row.fecha.display);

});

});

function openResolveModal(id, cliente, noSerie, fechaDet) {
    document.getElementById('resolveAlarmId').value = id;
    document.getElementById('resolveClienteText').textContent = cliente;
    document.getElementById('resolveNoSerieText').textContent = noSerie;
    document.getElementById('resolveFechaDetText').textContent = fechaDet;

    const modal = new bootstrap.Modal(document.getElementById('resolveAlarmModal'));
    modal.show();
}

document.getElementById('confirmResolveBtn').addEventListener('click', function() {
    const id = document.getElementById('resolveAlarmId').value;
    const resolucion = document.getElementById('resolveText').value;

    if (!resolucion) {
        showHeaderAlert('danger', 'Debes escribir la resolución');
        return;
    }

    // Obtener fecha actual en formato compatible con put_alarma.php
    const fechaActual = new Date();
    const yyyy = fechaActual.getFullYear();
    const mm = String(fechaActual.getMonth() + 1).padStart(2, '0');
    const dd = String(fechaActual.getDate()).padStart(2, '0');
    const hh = String(fechaActual.getHours()).padStart(2, '0');
    const min = String(fechaActual.getMinutes()).padStart(2, '0');
    const fechaFormateada = `${yyyy}-${mm}-${dd}T${hh}:${min}`;

    fetch('/alarmas/api/put_alarma.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            id: id,
            resolucion: resolucion,
            fecha_resolucion: fechaFormateada,
            estado_alarma_id: 2
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showHeaderAlert('success', 'Alarma resuelta correctamente');
            setTimeout(function(){
                window.location.href = '/alarmas/dashboard/listado_alarmas.php';
                }, 2000);
            } else {
            showHeaderAlert('Error: ' + data.error);
        }
    });
});


// Función para ver un registro
function viewRecord(id) {
    // Redirigir a la página de visualización de detalles
    window.location.href = `ver_alarma.php?id=${id}`;
}

// Función para editar un registro
function editRecord(id) {
    // Redirigir a la página de edición
    window.location.href = `editar_alarma.php?id=${id}`;
}
</script>
</body>
</html>