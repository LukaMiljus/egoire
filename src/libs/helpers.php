<?php

declare(strict_types=1);

/**
 * Render a view/template file with data.
 */
function view(string $filename, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $filePath = __DIR__ . '/../views/' . $filename . '.php';
    if (!file_exists($filePath)) {
        error_log("View not found: $filename");
        return;
    }
    require $filePath;
}

/**
 * Render an admin view.
 */
function adminView(string $filename, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $filePath = __DIR__ . '/../views/admin/' . $filename . '.php';
    if (!file_exists($filePath)) {
        error_log("Admin view not found: $filename");
        return;
    }
    require $filePath;
}

/**
 * Redirect helper with exit.
 */
function redirect(string $url, int $code = 302): void
{
    http_response_code($code);
    header('Location: ' . $url);
    exit;
}

/**
 * JSON response helper.
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Set flash message for next request.
 */
function flash(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'][$type][] = $message;
}

/**
 * Get and clear flash messages.
 */
function getFlash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Check if there are flash messages.
 */
function hasFlash(string $type = ''): bool
{
    if ($type) {
        return !empty($_SESSION['flash'][$type]);
    }
    return !empty($_SESSION['flash']);
}

/**
 * Render flash messages as HTML.
 */
function renderFlash(): string
{
    $flash = getFlash();
    if (empty($flash)) {
        return '';
    }

    $html = '';
    $icons = [
        'success' => '✓',
        'error'   => '✕',
        'warning' => '⚠',
        'info'    => 'ℹ',
    ];

    foreach ($flash as $type => $messages) {
        foreach ($messages as $msg) {
            $icon = $icons[$type] ?? '';
            $html .= '<div class="alert alert-' . htmlspecialchars($type) . '" role="alert">'
                    . '<span class="alert-icon">' . $icon . '</span> '
                    . htmlspecialchars($msg)
                    . '<button class="alert-close" onclick="this.parentElement.remove()">×</button>'
                    . '</div>';
        }
    }

    return $html;
}

/**
 * Format currency in RSD.
 */
function formatPrice(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' RSD';
}

/**
 * Format date for display.
 */
function formatDate(string $date, string $format = 'd.m.Y'): string
{
    return date($format, strtotime($date));
}

/**
 * Format datetime for display.
 */
function formatDateTime(string $datetime, string $format = 'd.m.Y H:i'): string
{
    return date($format, strtotime($datetime));
}

/**
 * Generate a unique order number.
 */
function generateOrderNumber(): string
{
    return 'EG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Truncate text to a given length.
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}

/**
 * Get the current URL path.
 */
function currentPath(): string
{
    return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
}

/**
 * Check if current path matches a pattern.
 */
function isActivePath(string $path): bool
{
    $current = currentPath();
    if ($path === '/') {
        return $current === '/';
    }
    return str_starts_with($current, $path);
}

/**
 * Paginate query results.
 */
function paginate(int $totalItems, int $perPage = 20, int $currentPage = 1): array
{
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total_items'  => $totalItems,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
        'has_prev'     => $currentPage > 1,
        'has_next'     => $currentPage < $totalPages,
    ];
}

/**
 * Render pagination HTML.
 */
function renderPagination(array $pagination, string $baseUrl = ''): string
{
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav class="pagination">';

    if ($pagination['has_prev']) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '" class="pagination-prev">&laquo; Prethodna</a>';
    }

    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        if ($i === $pagination['current_page']) {
            $html .= '<span class="pagination-current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link">' . $i . '</a>';
        }
    }

    if ($pagination['has_next']) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '" class="pagination-next">Sledeća &raquo;</a>';
    }

    $html .= '</nav>';

    return $html;
}

/**
 * Get order status label (localized).
 */
function orderStatusLabel(string $status): string
{
    return match ($status) {
        'new'        => 'Nova',
        'processing' => 'U obradi',
        'shipped'    => 'Poslata',
        'delivered'  => 'Isporučena',
        'canceled'   => 'Otkazana',
        default      => ucfirst($status),
    };
}

/**
 * Get order status badge CSS class.
 */
function orderStatusClass(string $status): string
{
    return match ($status) {
        'new'        => 'badge-info',
        'processing' => 'badge-warning',
        'shipped'    => 'badge-primary',
        'delivered'  => 'badge-success',
        'canceled'   => 'badge-danger',
        default      => 'badge-secondary',
    };
}

/**
 * Get base URL.
 */
function baseUrl(): string
{
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

/**
 * Asset URL with cache busting.
 */
function asset(string $path): string
{
    $fullPath = __DIR__ . '/../../public' . $path;
    $version = file_exists($fullPath) ? filemtime($fullPath) : time();
    return $path . '?v=' . $version;
}

/**
 * Remove a query parameter from the current URL and return the resulting URL.
 */
function removeQueryParam(string $key): string
{
    $params = $_GET;
    unset($params[$key]);
    // For price, remove both price_min and price_max
    if ($key === 'price') {
        unset($params['price_min'], $params['price_max']);
    }
    $qs = http_build_query($params);
    return '/products' . ($qs ? '?' . $qs : '');
}
