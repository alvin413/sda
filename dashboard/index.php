<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /alarmas/auth/login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Consulta para contar servidores
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM servidores");
$countStmt->execute();
$totalServidores = $countStmt->fetchColumn();

// CONSULTA MODIFICADA: Servidores por ciudad y bunker
$servidoresPorCiudadYBunkerStmt = $pdo->query("
    SELECT 
        IFNULL(c.nombre, 'Sin ciudad') AS ciudad,
        IFNULL(b.nombre, 'Sin asignar') AS bunker, 
        COUNT(s.id) AS total
    FROM servidores s
    LEFT JOIN bunkers b ON s.bunker_id = b.id
    LEFT JOIN ciudades c ON b.ciudad_id = c.id
    GROUP BY c.nombre, b.nombre
    ORDER BY ciudad, total DESC
");

// Procesar los datos para agrupar por ciudad
$servidoresPorCiudadYBunker = [];
$datos = $servidoresPorCiudadYBunkerStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($datos as $fila) {
    $ciudad = $fila['ciudad'];
    $bunker = $fila['bunker'];
    $total = $fila['total'];
    
    if (!isset($servidoresPorCiudadYBunker[$ciudad])) {
        $servidoresPorCiudadYBunker[$ciudad] = [];
    }
    
    $servidoresPorCiudadYBunker[$ciudad][$bunker] = $total;
}

// Calcular máximo para la barra de progreso (global)
$maxServidoresBunker = 0;
foreach ($servidoresPorCiudadYBunker as $bunkers) {
    $maxBunker = max($bunkers);
    if ($maxBunker > $maxServidoresBunker) {
        $maxServidoresBunker = $maxBunker;
    }
}
if ($maxServidoresBunker === 0) $maxServidoresBunker = 1;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Alarmas - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

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

        .summary-card {
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: none;
            transition: var(--transition);
            height: 100%;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.3);
        }

        .summary-card .card-body {
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .summary-card i {
            position: absolute;
            right: 1.5rem;
            bottom: 1.5rem;
            font-size: 2.5rem;
            opacity: 0.2;
            transition: var(--transition);
            z-index: 1;
        }

        .summary-card:hover i {
            opacity: 0.3;
            transform: scale(1.1);
        }

        .bg-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        }

        .bg-success-gradient {
            background: linear-gradient(135deg, var(--success-color), #17a673);
        }

        .bg-warning-gradient {
            background: linear-gradient(135deg, var(--warning-color), #dda20a);
        }

        .bg-info-gradient {
            background: linear-gradient(135deg, var(--accent-color), #2a96a5);
        }

        .ciudad-section {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem rgba(58, 59, 69, 0.1);
        }

        .ciudad-section:last-child {
            margin-bottom: 0;
        }

        .bunker-card {
            background-color: var(--secondary-color);
            border-radius: 0.5rem;
            padding: 1.25rem;
            transition: var(--transition);
            height: 100%;
            border: 1px solid var(--border-color);
        }

        .bunker-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.35rem 1.5rem rgba(58, 59, 69, 0.1);
        }

        .progress {
            height: 1.25rem;
            border-radius: 0.5rem;
            background-color: rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 0.5rem;
        }

        .badge {
            font-weight: 600;
            padding: 0.5em 0.8em;
            border-radius: 0.35rem;
        }

        .welcome-card {
            border-left: 5px solid var(--primary-color);
            background-color: white;
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
                padding: 1rem;
            }
            
            .summary-card .card-body {
                padding: 1.25rem;
            }
            
            .summary-card i {
                font-size: 2rem;
                right: 1rem;
                bottom: 1rem;
            }
            
            .ciudad-section {
                padding: 1rem;
            }
            
            .bunker-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container-fluid py-4 fade-in">
        <!-- Tarjeta de Bienvenida -->
        <div class="card welcome-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <h4 class="card-title text-primary mb-2">
                            <i class="fas fa-tachometer-alt me-2"></i>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?>
                        </h4>
                        <p class="card-text text-muted mb-0">Panel de control del sistema de gestión de alarmas y servidores</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card summary-card bg-primary-gradient">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Total Servidores</h5>
                        <h2 class="card-text mb-0"><?= $totalServidores ?></h2>
                        <small>Dispositivos registrados</small>
                        <i class="fas fa-server"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa de Bunkers por Ciudad -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Distribución de Servidores por Ciudad y Bunker</h5>
            </div>
            <div class="card-body">
                <?php foreach ($servidoresPorCiudadYBunker as $ciudad => $bunkers): ?>
                    <?php $totalCiudad = array_sum($bunkers); ?>
                    <div class="ciudad-section">
                        <h6 class="text-primary mb-3 fw-semibold">
                            <i class="fas fa-city me-2"></i><?= htmlspecialchars($ciudad) ?><small class="text-muted">(<?= $totalCiudad ?>)</small>
                        </h6>
                        <div class="row">
                            <?php foreach ($bunkers as $bunker => $cantidad): ?>
                            <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
                                <div class="bunker-card">
                                    <h6 class="fw-semibold mb-3 text-dark">
                                        <i class="fas fa-building me-2 text-primary"></i>Bunker:<?= htmlspecialchars($bunker) ?>
                                    </h6>
                                    <div class="progress mb-3">
                                        <div class="progress-bar bg-info" 
                                            role="progressbar" 
                                            style="width: <?= ($cantidad / $totalCiudad) * 100 ?>%"
                                            aria-valuenow="<?= $cantidad ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="<?= $maxServidoresBunker ?>">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">
                                            <i class="fas fa-server me-1"></i><?= $cantidad ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= round(($cantidad / $totalCiudad) * 100, 1) ?>%
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de barras - Servidores por Ciudad
        const ciudadChart = new Chart(
            document.getElementById('ciudadChart').getContext('2d'),
            {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_keys($servidoresPorCiudad)) ?>,
                    datasets: [{
                        label: 'Servidores',
                        data: <?= json_encode(array_values($servidoresPorCiudad)) ?>,
                        backgroundColor: 'rgba(78, 115, 223, 0.7)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            }
        );

        // Gráfico de pastel - Distribución por Cliente
        const clienteChart = new Chart(
            document.getElementById('clienteChart').getContext('2d'),
            {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_keys($servidoresPorCliente)) ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($servidoresPorCliente)) ?>,
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.7)',
                            'rgba(28, 200, 138, 0.7)',
                            'rgba(54, 185, 204, 0.7)',
                            'rgba(246, 194, 62, 0.7)',
                            'rgba(231, 74, 59, 0.7)',
                            'rgba(133, 135, 150, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            }
        );
    });
    </script>
</body>
</html>