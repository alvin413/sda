<?php
require_once __DIR__ . '/config/db.php';

try {
    // 1. Conexión sin DB para crear base
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");

    // 2. Conectar ya con la base creada
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. Leer el archivo schema.sql
    $schema_path = __DIR__ . '/includes/sql/schema.sql';
    if (!file_exists($schema_path)) {
        throw new Exception("Archivo schema.sql no encontrado en: $schema_path");
    }
    $schema = file_get_contents($schema_path);

    // 4. Desactivar temporalmente llaves foráneas
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 5. Ejecutar las sentencias separadas por ';'
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $index => $stmt) {
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                echo "<h3>Error en la sentencia #" . ($index + 1) . ":</h3>";
                echo "<pre>" . htmlspecialchars($stmt) . "</pre>";
                echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                throw $e; // Re-throw to stop execution
            }
        }
    }

    // 6. Reactivar llaves foráneas
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 7. Crear usuario admin si no existe
    $username = 'admin';
    $password = 'admin123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, $password_hash]);
        echo "Usuario admin creado correctamente.<br>";
    } else {
        echo "Usuario admin ya existe.<br>";
    }

    echo "<h1>¡Instalación completada!</h1>";
    echo "<p>Base de datos <strong>" . DB_NAME . "</strong> y tablas creadas correctamente.</p>";

} catch (PDOException $e) {
    echo "<h1>Error en la base de datos</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
} catch (Exception $e) {
    echo "<h1>Error en la instalación</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}
