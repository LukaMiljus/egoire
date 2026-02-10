<?php

use Dotenv\Dotenv;

// Pokreni sesiju samo ako već nije aktivna
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenvDir = __DIR__ . '/../config';
if (file_exists($dotenvDir . '/.env')) {
    Dotenv::createImmutable($dotenvDir)->safeLoad();
}

// Uključi potrebne fajlove
require_once __DIR__ . '/libs/helpers.php';
require_once __DIR__ . '/libs/auth.php';
require_once __DIR__ . '/libs/connection.php';
require_once __DIR__ . '/main.php';
?>
