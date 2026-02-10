<?php

declare(strict_types=1);

/**
 * CSRF Token Protection
 * Generates and validates CSRF tokens to prevent cross-site request forgery.
 */

function csrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    // Regenerate token every 30 minutes
    if (time() - $_SESSION['csrf_token_time'] > 1800) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrfMetaTag(): string
{
    return '<meta name="csrf-token" content="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrfToken(?string $token = null): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    $token = $token ?? ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function requireCsrf(): void
{
    if (!verifyCsrfToken()) {
        http_response_code(403);
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid or expired security token. Please refresh the page.']);
        } else {
            echo 'Nevažeći sigurnosni token. Osvežite stranicu.';
        }
        exit;
    }
}

function isAjaxRequest(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
