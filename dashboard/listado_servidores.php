<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /alarmas/auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM servidores WHERE estado='activo'");
    $countStmt->execute();
    $totalServidores = $countStmt->fetchColumn();
} catch (PDOException $e) {
    $totalServidores = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestion Alarmas - Listado de Servidores</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
    font-family: 'Inter', sans-serif;
    color: var(--text-primary);
}

.card {
    border-radius: 0.75rem;
    box-shadow: var(--card-shadow);
    border: none;
    transition: var(--transition);
}

.card-header {
    background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
    color: white;
    border-radius: 0.75rem 0.75rem 0 0 !important;
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-responsive {
    overflow-x: auto;
}

#servidoresTable thead th {
    background-color: var(--primary-color);
    color: white;
}

.btn-action { margin: 0 2px; padding: 0.35rem 0.65rem; border-radius: 0.4rem; transition: var(--transition); }
.btn-view { color: var(--primary-color); border: 1px solid var(--primary-color); }
.btn-edit { color: var(--accent-color); border: 1px solid var(--accent-color); }
.btn-alarm { color: var(--warning-color); border: 1px solid var(--warning-color); }
.btn-new { background: white; color: var(--primary-color); border-radius: 0.5rem; padding: 0.5rem 1rem; font-weight: 600; }

.fade-in { animation: fadeIn 0.5s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }


</style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid py-4 fade-in">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4><i class="fas fa-server me-2"></i>Listado de Servidores</h4>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-primary me-3">Total: <?= htmlspecialchars($totalServidores) ?></span>
                <a href="/alarmas/dashboard/nuevo_servidor.php" class="btn btn-new me-2"><i class="fas fa-plus me-1"></i>Nuevo Servidor</a>
                <a href="/alarmas/dashboard/importar_servidores.php" class="btn btn-new"><i class="fas fa-file-import me-2"></i>Importar Servidores</a>
            </div>
        </div>

        <div class="card-body">
            <!-- FILTROS -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Ciudad:</label>
                    <select id="filterCiudad" class="form-select">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Cliente:</label>
                    <select id="filterCliente" class="form-select">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Bunker:</label>
                    <select id="filterBunker" class="form-select">
                        <option value="">Todas</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="servidoresTable" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hostname</th>
                            <th>No. Serie</th>
                            <th>Ubicación</th>
                            <th>Cliente</th>
                            <th>RFC Alta</th>
                            <th>Fecha Alta</th>
                            <th>RFC Baja</th>
                            <th>Fecha Baja</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>


<script>
$(document).ready(function(){
    // Cargar opciones de los selects
        $.getJSON('/alarmas/api/get_ciudades.php', function(data) {
        data.forEach(c => $('#filterCiudad').append(`<option value="${c.id}">${c.nombre}</option>`));
    });
        $.getJSON('/alarmas/api/get_clientes.php', function(data) {
        data.forEach(c => $('#filterCliente').append(`<option value="${c.id}">${c.nombre}</option>`));
    });
        $.getJSON('/alarmas/api/get_bunkers.php', function(data) {
        data.forEach(c => $('#filterBunker').append(`<option value="${c.id}">${c.nombre}</option>`));
    });

    var table = $('#servidoresTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/alarmas/api/data_servidores.php",
            "type": "POST",
            "data": function(d){
                d.ciudad_id = $('#filterCiudad').val();
                d.cliente_id = $('#filterCliente').val();
                d.bunker_id = $('#filterBunker').val();
            }
        },
        "columns": [
            {"data": "id", visible: false},
            {"data": "hostname"},
            {"data": "no_serie"},
            {"data": "ubicacion"},
            {"data": "cliente"},
            {"data": "rfc_alta"},
            {"data": "fecha_alta"},
            {"data": "rfc_baja"},
            {"data": "fecha_baja"},
            { 
            data: null,
            render: function(data, type, row){
                let botones = `
                  <button class="btn-view" data-id="${row.id}"><i class="fas fa-eye"></i></button>
                  <button class="btn-edit" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                `;
                if(row.estado === 'activo'){
                    botones += `<button class="btn-alarm" data-no-serie="${row.no_serie}"><i class="fas fa-bell"></i></button>`;
                }
                return botones;
            }
        }
        ],
        "responsive": true,
        "order": [[0,'ASC']],
        language: { 
        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
        "pageLength": 10
    });

    $('#filterCiudad, #filterCliente, #filterBunker').on('change', function(){ table.ajax.reload(); });

    $(document).on('click', '.btn-view', function(){
        let id = $(this).data('id');
        window.location.href = `ver_servidor.php?id=${id}`;
    });

    $(document).on('click', '.btn-edit', function(){
        let id = $(this).data('id');
        window.location.href = `editar_servidor.php?id=${id}`;
    });

    $(document).on('click', '.btn-alarm', function(){
        let no_serie = $(this).data('no-serie');
        window.location.href = `nueva_alarma.php?no_serie=${no_serie}`;
    });

});
</script>
</body>
</html>
