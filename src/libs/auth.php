<?php

declare(strict_types=1);

if (!function_exists('adminConfig')) {
    function adminConfig(): array
    {
        static $config;

        if ($config === null) {
            $path = __DIR__ . '/../../config/admin.php';
            $config = file_exists($path)
                ? require $path
                : [
                    'username'      => 'victoryadmin',
                    'password_hash' => '$2y$10$/F8YqwEIyDVUmRNhSC1W4Omo3jlNSyb4LIceQew7nR6sfkVTG6/Ju',
                ];
        }

        return $config;
    }
}

if (!function_exists('isAdminAuthenticated')) {
    function isAdminAuthenticated(): bool
    {
        return !empty($_SESSION['admin_authenticated']);
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(): void
    {
        if (!isAdminAuthenticated()) {
            header('Location: /admin/login.php');
            exit;
        }
    }
}

if (!function_exists('attemptAdminLogin')) {
    function attemptAdminLogin(string $username, string $password): bool
    {
        $config = adminConfig();

        if (
            hash_equals($config['username'], $username)
            && password_verify($password, $config['password_hash'])
        ) {
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_username']      = $username;
            $_SESSION['admin_logged_in_at']  = time();
            return true;
        }

        return false;
    }
}

if (!function_exists('logoutAdmin')) {
    function logoutAdmin(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
