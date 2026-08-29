<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /alarmas/dashboard/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Sistema de Alarmas</title>
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
            background: linear-gradient(135deg, var(--secondary-color) 0%, #e9ecef 100%);
            height: 100vh;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: none;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .login-card:hover {
            box-shadow: 0 0.5rem 2rem rgba(58, 59, 69, 0.2);
        }
        
        .login-header {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1.75rem 2rem;
            text-align: center;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        
        .login-body {
            padding: 2rem;
            background-color: white;
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
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid var(--border-color);
            border-right: none;
            padding: 0 1rem;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .btn-login {
            background: linear-gradient(120deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 0.5rem;
            transition: var(--transition);
            height: 2.75rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(58, 59, 69, 0.2);
        }
        
        .toggle-password {
            border: 1px solid var(--border-color);
            border-left: none;
            background-color: white;
            transition: var(--transition);
        }
        
        .toggle-password:hover {
            background-color: var(--secondary-color);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
            padding: 0.9rem 1.25rem;
        }
        
        .alert-danger {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
            border-left: 3px solid var(--danger-color);
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }
            
            .login-header {
                padding: 1.5rem 1.25rem;
            }
            
            .login-body {
                padding: 1.5rem;
            }
            
            .brand-logo {
                font-size: 2rem;
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container fade-in">
        <div class="login-card">
            <div class="login-header">
                <div class="brand-logo">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="mb-0">Sistema de Gestión de Alarmas</h3>
            </div>
            <div class="login-body">
                <form method="POST" action="check_login.php">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div>
                                    <?php 
                                    if ($_GET['error'] === 'invalid') {
                                        echo "correo o contraseña incorrectos";
                                    } elseif ($_GET['error'] === 'empty') {
                                        echo "Por favor complete todos los campos";
                                    } else {
                                        echo "Error al iniciar sesión";
                                    }
                                    ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label for="username" class="form-label fw-semibold text-dark mb-2">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-user text-muted"></i></span>
                            <input type="email" id="username" name="username" class="form-control" placeholder="Ingrese su correo" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold text-dark mb-2">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-login text-white">
                            <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                        </button>
                    </div>
                    
                </form>

                <div class="footer-links">
                    <small>&copy; <?php echo date('Y'); ?> Sistema de Alarmas</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mostrar/ocultar contraseña
            $('.toggle-password').click(function() {
                const icon = $(this).find('i');
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                icon.toggleClass('fa-eye fa-eye-slash');
                
                // Efecto visual al togglear
                $(this).toggleClass('btn-outline-secondary btn-outline-primary');
            });
            
            // Efecto de enfoque en los inputs
            $('.form-control').focus(function() {
                $(this).parent().find('.input-group-text').css({
                    'border-color': '#4e73df',
                    'color': '#4e73df'
                });
            }).blur(function() {
                $(this).parent().find('.input-group-text').css({
                    'border-color': '',
                    'color': ''
                });
            });
        });
    </script>
</body>
</html>