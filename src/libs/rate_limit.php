<?php

declare(strict_types=1);

/**
 * Rate Limiting using database or session.
 * Prevents brute force on login, checkout, contact forms.
 */

function rateLimitCheck(string $action, int $maxAttempts = 5, int $windowSeconds = 300): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $key = 'rate_limit_' . $action;
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }

    // Clean old attempts
    $_SESSION[$key] = array_filter(
        $_SESSION[$key],
        static fn(int $timestamp) => ($now - $timestamp) < $windowSeconds
    );

    return count($_SESSION[$key]) < $maxAttempts;
}

function rateLimitRecord(string $action): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $key = 'rate_limit_' . $action;
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }

    $_SESSION[$key][] = time();
}

function rateLimitReset(string $action): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    unset($_SESSION['rate_limit_' . $action]);
}

/**
 * Enforce rate limit – respond with 429 if exceeded.
 */
function requireRateLimit(string $action, int $maxAttempts = 5, int $windowSeconds = 300): void
{
    if (!rateLimitCheck($action, $maxAttempts, $windowSeconds)) {
        http_response_code(429);
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Previše pokušaja. Pokušajte ponovo za nekoliko minuta.']);
        } else {
            echo 'Previše pokušaja. Pokušajte ponovo za nekoliko minuta.';
        }
        exit;
    }
}
