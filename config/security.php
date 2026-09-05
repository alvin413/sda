<?php

// Claves de cifrado almacenadas fuera del repositorio
require_once __DIR__  . '/../sda-secrets/security.php';

if (!defined('ENCRYPTION_KEY')) {
    die('ERROR: ENCRYPTION_KEY no está definida');
}

if (!defined('ENCRYPTION_IV')) {
    die('ERROR: ENCRYPTION_IV no está definida');
}

// Función para encriptar
function encryptPassword($plainPassword) {
    if (empty($plainPassword)) return null;

    $encrypted = openssl_encrypt(
        $plainPassword,
        'AES-256-CBC',
        ENCRYPTION_KEY,
        OPENSSL_RAW_DATA,
        ENCRYPTION_IV
    );

    return base64_encode($encrypted);
}

// Función para desencriptar
function decryptPassword($encryptedPassword) {
    if (empty($encryptedPassword)) return null;

    return openssl_decrypt(
        base64_decode($encryptedPassword),
        'AES-256-CBC',
        ENCRYPTION_KEY,
        OPENSSL_RAW_DATA,
        ENCRYPTION_IV
    );
}
?>
