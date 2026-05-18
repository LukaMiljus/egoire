<?php

declare(strict_types=1);

if (!function_exists('maintenanceConfig')) {
    function maintenanceConfig(): array
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../../config/maintenance.php';
        }
        return $config;
    }
}

if (!function_exists('isMaintenanceMode')) {
    function isMaintenanceMode(): bool
    {
        return !empty(maintenanceConfig()['enabled']);
    }
}

if (!function_exists('isMaintenanceBypass')) {
    /** Admin prijava omogućava pregled celog sajta tokom održavanja. */
    function isMaintenanceBypass(): bool
    {
        return isAdminAuthenticated();
    }
}

if (!function_exists('isMaintenancePublicPath')) {
    function isMaintenancePublicPath(string $path): bool
    {
        $publicPaths = maintenanceConfig()['public_paths'] ?? [];
        return in_array($path, $publicPaths, true);
    }
}

if (!function_exists('enforceMaintenanceMode')) {
    function enforceMaintenanceMode(string $path): void
    {
        if (!isMaintenanceMode() || isMaintenanceBypass()) {
            return;
        }

        if (isMaintenancePublicPath($path)) {
            return;
        }

        if (isAjaxRequest()) {
            http_response_code(503);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Sajt je u pripremi.']);
            exit;
        }

        header('Location: /coming-soon', true, 302);
        exit;
    }
}

if (!function_exists('serveMaintenanceHome')) {
    /** Početna stranica tokom održavanja = Coming Soon. */
    function serveMaintenanceHome(string $path): bool
    {
        if (!isMaintenanceMode() || isMaintenanceBypass()) {
            return false;
        }

        if ($path !== '' && $path !== 'coming-soon') {
            return false;
        }

        $viewPath = __DIR__ . '/../views/pages/coming-soon.php';
        if (file_exists($viewPath)) {
            require $viewPath;
            return true;
        }

        return false;
    }
}
