<?php
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT  id, password_hash, must_change_password, password_last_changed FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Usuario existe: verificamos contraseña
        if (password_verify($password, $user['password_hash'])) {
            session_start();
			session_regenerate_id(true);
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['username'] = $username;

			// Cambio obligatorio de contraseña
			if ((int)$user['must_change_password'] === 1) {

				$_SESSION['must_change_password'] = true;

				writeLog(
					'INFO',
					"Usuario {$username} requiere cambio obligatorio de contraseña"
				);

				header("Location: /alarmas/auth/modificar_password.php");
				exit;
			}

			// Verificar expiración de contraseña
			if (!empty($user['password_last_changed'])) {

				$fechaUltimoCambio = new DateTime($user['password_last_changed']);
				$fechaActual = new DateTime();

				$intervalo = $fechaUltimoCambio->diff($fechaActual);

				if ($intervalo->days >= 90) {

					$_SESSION['must_change_password'] = true;

					writeLog(
						'WARNING',
						"Contraseña expirada para usuario {$username}"
					);

					header("Location: /alarmas/auth/modificar_password.php");
					exit;
				}
			}

			// Si todo está bien, ir al dashboard
			header("Location: /alarmas/dashboard/index.php");
			exit;
        } else {
            writeLog('ERROR', "Login fallido - contraseña incorrecta para username: $username");
            echo "<script>alert('Usuario o contraseña incorrectos'); window.location='login.php';</script>";
            exit;
        }
    } else {
		// Usuario no existe: lo creamos con contraseña genérica
		$defaultPassword = 'Temporal123!'; 
		$passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

		$insertStmt = $pdo->prepare("INSERT INTO usuarios (username, password_hash, must_change_password) VALUES (?, ?, 1)");
		$insertStmt->execute([$username, $passwordHash]);
		$newUserId = $pdo->lastInsertId();

		writeLog('INFO', "Usuario creado automáticamente - user_id: $newUserId, username: $username");

		// Iniciar sesión automáticamente
		session_start();
		session_regenerate_id(true);
		$_SESSION['user_id'] = $newUserId;
		$_SESSION['username'] = $username;
		$_SESSION['must_change_password'] = true;

		// Redirigir al formulario de cambio de contraseña
		header("Location: /alarmas/auth/modificar_password.php");
		exit;
    }
}