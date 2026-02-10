<?php

declare(strict_types=1);

use Dotenv\Dotenv;

// Secure session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', '3600');
    session_start();
}

// Regenerate session ID periodically to prevent fixation
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
} elseif (time() - $_SESSION['_created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenvDir = __DIR__ . '/../config';
if (file_exists($dotenvDir . '/.env')) {
    Dotenv::createImmutable($dotenvDir)->safeLoad();
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Core libraries
require_once __DIR__ . '/libs/helpers.php';
require_once __DIR__ . '/libs/validation.php';
require_once __DIR__ . '/libs/csrf.php';
require_once __DIR__ . '/libs/rate_limit.php';
require_once __DIR__ . '/libs/auth.php';
require_once __DIR__ . '/libs/connection.php';
require_once __DIR__ . '/main.php';

