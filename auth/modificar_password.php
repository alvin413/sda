<?php
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/db.php';
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: /alarmas/auth/login.php');
    exit;
}

$mensaje = '';
$tipoMensaje = ''; // success o danger

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($passwordConfirm)) {
        $mensaje = 'Por favor complete todos los campos.';
        $tipoMensaje = 'danger';
    } elseif ($password !== $passwordConfirm) {
        $mensaje = 'Las contraseñas no coinciden.';
        $tipoMensaje = 'danger';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $_SESSION['user_id']]);

        writeLog('INFO', "Usuario {$_SESSION['username']} cambió su contraseña");

        $mensaje = 'Contraseña actualizada correctamente.';
        $tipoMensaje = 'success';
		$stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ?, must_change_password = 0, password_last_changed = NOW() WHERE id = ?");
		$stmt->execute([$passwordHash, $_SESSION['user_id']]);
		header("Location: /alarmas/dashboard/index.php");
		exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Contraseña - Sistema de Alarmas</title>
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
            height: 100vh;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container-card {
            max-width: 420px;
            width: 100%;
        }
        .card-custom {
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: none;
            overflow: hidden;
            background: white;
            transition: var(--transition);
        }
        .card-header-custom {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1.75rem 2rem;
            text-align: center;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        .brand-logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
            display: inline-block;
            padding: 1rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
        }
        .card-body-custom {
            padding: 2rem;
        }
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
        .btn-custom {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 0.5rem;
            color: white;
            height: 2.75rem;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(58, 59, 69, 0.2);
        }
        .toggle-password {
            border: 1px solid var(--border-color);
            border-left: none;
            background-color: white;
        }
        .alert {
            border-radius: 0.5rem;
            border: none;
            padding: 0.9rem 1.25rem;
        }
        .alert-success { color: #1cc88a; border-left: 3px solid #1cc88a; background-color: rgba(28, 200, 138, 0.1);}
        .alert-danger { color: #e74a3b; border-left: 3px solid #e74a3b; background-color: rgba(231, 74, 59, 0.1);}
    </style>
</head>
<body>
    <div class="container-card">
        <div class="card-custom">
            <div class="card-header-custom">
                <div class="brand-logo"><i class="fas fa-shield-alt"></i></div>
                <h3 class="mb-0">Modificar Contraseña</h3>
            </div>
            <div class="card-body-custom">
                <?php if ($mensaje): ?>
                    <div class="alert <?= $tipoMensaje === 'success' ? 'alert-success' : 'alert-danger' ?> mb-4">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold text-dark mb-2">Nueva contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese su nueva contraseña" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirm" class="form-label fw-semibold text-dark mb-2">Confirmar contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Confirme su contraseña" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-custom"><i class="fas fa-save me-2"></i>Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.toggle-password').click(function(){
                const input = $(this).siblings('input');
                const icon = $(this).find('i');
                if(input.attr('type') === 'password'){
                    input.attr('type','text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type','password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
</body>
</html>