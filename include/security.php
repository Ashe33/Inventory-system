<?php

define('CRYPTO_KEY', 'your-very-strong-secret-key-32chars+');
define('CRYPTO_METHOD', 'AES-256-CBC');

/**
 * Encrypt data (name, email, phone, etc.)
 */
function crypto_encrypt($value) {
    if ($value === null || $value === '') return $value;

    $key = hash('sha256', CRYPTO_KEY);
    $iv = substr(hash('sha256', 'crypto-fixed-iv'), 0, 16);

    $encrypted = openssl_encrypt($value, CRYPTO_METHOD, $key, 0, $iv);

    return base64_encode($encrypted);
}

/**
 * Decrypt data
 */
function crypto_decrypt($value) {
    if ($value === null || $value === '') return $value;

    $key = hash('sha256', CRYPTO_KEY);
    $iv = substr(hash('sha256', 'crypto-fixed-iv'), 0, 16);

    return openssl_decrypt(base64_decode($value), CRYPTO_METHOD, $key, 0, $iv);
}