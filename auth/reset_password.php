<?php
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/db.php';

session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: /alarmas/auth/login.php');
    exit;
}

// Usuarios autorizados
$usuariosAutorizados = [1, 2];

if (!in_array($_SESSION['user_id'], $usuariosAutorizados)) {
    die("Acceso denegado.");
}

$mensaje = '';
$tipoMensaje = '';

// Contraseña temporal
$defaultPassword = 'Cambio123!';

// Obtener usuarios excepto admin principal
$stmt = $pdo->prepare("
    SELECT id, username 
    FROM usuarios
    WHERE id != 1
    ORDER BY username ASC
");

$stmt->execute();

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuarioId = $_POST['usuario_id'] ?? '';

    if (empty($usuarioId)) {

        $mensaje = 'Seleccione un usuario.';
        $tipoMensaje = 'danger';

    } else {

        // Validar usuario
        $stmt = $pdo->prepare("
            SELECT id, username 
            FROM usuarios 
            WHERE id = ? AND id != 1
        ");

        $stmt->execute([$usuarioId]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {

            $mensaje = 'Usuario inválido.';
            $tipoMensaje = 'danger';

        } else {

            // Generar hash
            $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

            // Actualizar contraseña
            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET
                    password_hash = ?,
                    must_change_password = 1,
                    password_last_changed = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $passwordHash,
                $usuario['id']
            ]);

            // Auditoría
            writeLog(
                'WARNING',
                "Usuario {$_SESSION['username']} reestableció la contraseña del usuario {$usuario['username']}"
            );

            $mensaje = "La contraseña del usuario {$usuario['username']} fue reestablecida correctamente.";
            $tipoMensaje = 'success';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetear Contraseña - Sistema de Alarmas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #3a56c0;
            --secondary-color: #f8f9fc;
            --border-color: #e3e8f0;
            --card-shadow: 0 0.35rem 1.5rem rgba(58, 59, 69, 0.15);
            --transition: all 0.3s ease;
        }

        body {
			background: linear-gradient(135deg, var(--secondary-color) 0%, #e9ecef 100%);
			min-height: 100vh;
			font-family: 'Inter', sans-serif;
		}
		
		.fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container-card {
            max-width: 550px;
            width: 100%;
        }

        .card-custom {
			border-radius: 0.75rem;
			border: none;
			overflow: hidden;
			background: white;

			box-shadow:
				0 10px 25px rgba(58, 59, 69, 0.08),
				0 4px 10px rgba(58, 59, 69, 0.05);

			transition: all 0.3s ease;
		}

        .card-header-custom {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1.75rem 2rem;
            text-align: center;
        }

        .brand-logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
            display: inline-block;
            padding: 1rem;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
        }

        .card-body-custom {
            padding: 2rem;
        }

        .alert {
            border-radius: 0.5rem;
            border: none;
        }

        .alert-success {
            color: #1cc88a;
            border-left: 3px solid #1cc88a;
            background-color: rgba(28, 200, 138, 0.1);
        }

        .alert-danger {
            color: #e74a3b;
            border-left: 3px solid #e74a3b;
            background-color: rgba(231, 74, 59, 0.1);
        }

        .btn-custom {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 0.85rem;
            font-weight: 600;
            border-radius: 0.5rem;
            color: white;
            transition: var(--transition);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
        }

        .user-list {
            max-height: 350px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.5rem;
            background: #fafbff;
        }

        .user-item {
            border: 1px solid transparent;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            background: white;
        }

        .user-item:hover {
            border-color: var(--primary-color);
            background: rgba(78,115,223,0.05);
        }

        .password-info {
            background: #f8f9fc;
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-center align-items-start py-4 px-3 fade-in">

	<div class="container-card">

		<div class="card-custom">

			<div class="card-header-custom">
				<div class="brand-logo">
					<i class="fas fa-key"></i>
				</div>

				<h3 class="mb-0">Resetear Contraseña</h3>
			</div>

			<div class="card-body-custom">

				<?php if ($mensaje): ?>
					<div class="alert <?= $tipoMensaje === 'success' ? 'alert-success' : 'alert-danger' ?> mb-4">
						<?= htmlspecialchars($mensaje) ?>
					</div>
				<?php endif; ?>

				<div class="password-info mb-4">
					<strong>Contraseña temporal:</strong><br>
					<?= htmlspecialchars($defaultPassword) ?>
				</div>

				<form method="POST">

					<label class="form-label fw-semibold mb-3">
						Seleccione el usuario
					</label>

					<div class="user-list mb-4">

						<?php foreach ($usuarios as $usuario): ?>

							<label class="user-item d-flex align-items-center">

								<input
									type="radio"
									name="usuario_id"
									value="<?= $usuario['id'] ?>"
									class="form-check-input me-3"
									required
								>

								<div>
									<strong>
										<?= htmlspecialchars($usuario['username']) ?>
									</strong>
								</div>

							</label>

						<?php endforeach; ?>

					</div>

					<div class="d-grid">

						<button
							type="submit"
							class="btn btn-custom"
							onclick="return confirm('¿Seguro que deseas reestablecer la contraseña del usuario seleccionado?');"
						>
							<i class="fas fa-rotate-right me-2"></i>
							Reestablecer Contraseña
						</button>

					</div>

				</form>

			</div>

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