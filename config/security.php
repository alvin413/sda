<?php
// Configuración de encriptación
define('ENCRYPTION_KEY', 'BD4628CF22C2BAD7718C80399784A0B9');
define('ENCRYPTION_IV', 'F6V0M2ZBDY9S9PYM');

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