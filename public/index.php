<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

// ============================================================
// FRONT CONTROLLER – ROUTING
// ============================================================

$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Route map: path => [file, requireAuth]
$routes = [
    // PUBLIC PAGES
    ''                  => ['pages/home', false],
    'products'          => ['pages/products', false],
    'product'           => ['pages/product-detail', false],
    'categories'        => ['pages/categories', false],
    'category'          => ['pages/category', false],
    'brands'            => ['pages/brands', false],
    'brand'             => ['pages/brand', false],
    'gift-bag'          => ['pages/gift-bag', false],
    'gift-card'         => ['pages/gift-card', false],
    'cart'              => ['pages/cart', false],
    'checkout'          => ['pages/checkout', false],
    'order-confirmation' => ['pages/order-confirmation', false],
    'blog'              => ['pages/blog', false],
    'blog-post'         => ['pages/blog-post', false],
    'contact'           => ['pages/contact', false],
    'faq'               => ['pages/faq', false],
    'about'             => ['pages/static-page', false],
    'terms'             => ['pages/static-page', false],
    'privacy'           => ['pages/static-page', false],
    'shipping'          => ['pages/static-page', false],
    'search'            => ['pages/search', false],
    'unsubscribe'       => ['pages/unsubscribe', false],

    // AUTH
    'login'             => ['pages/login', false],
    'register'          => ['pages/register', false],
    'forgot-password'   => ['pages/forgot-password', false],
    'reset-password'    => ['pages/reset-password', false],
    'logout'            => ['pages/logout', false],

    // USER ACCOUNT
    'account'           => ['pages/account/dashboard', true],
    'account/orders'    => ['pages/account/orders', true],
    'account/order'     => ['pages/account/order-detail', true],
    'account/addresses' => ['pages/account/addresses', true],
    'account/loyalty'   => ['pages/account/loyalty', true],
    'account/settings'  => ['pages/account/settings', true],

    // API ENDPOINTS
    'api/cart/add'       => ['api/cart-add', false],
    'api/cart/update'    => ['api/cart-update', false],
    'api/cart/remove'    => ['api/cart-remove', false],
    'api/cart/count'     => ['api/cart-count', false],
    'api/checkout'       => ['api/checkout', false],
    'api/gift-card/validate' => ['api/gift-card-validate', false],
    'api/newsletter'     => ['api/newsletter', false],
    'api/contact'        => ['api/contact', false],
    'api/search'         => ['api/search', false],

    // ADMIN
    'admin'                 => ['admin/dashboard', 'admin'],
    'admin/login'           => ['admin/login', false],
    'admin/logout'          => ['admin/logout', false],
    'admin/dashboard'       => ['admin/dashboard', 'admin'],
    'admin/orders'          => ['admin/orders', 'admin'],
    'admin/order'           => ['admin/order-detail', 'admin'],
    'admin/products'        => ['admin/products', 'admin'],
    'admin/product/new'     => ['admin/product-edit', 'admin'],
    'admin/product/edit'    => ['admin/product-edit', 'admin'],
    'admin/categories'      => ['admin/categories', 'admin'],
    'admin/category/new'    => ['admin/category-edit', 'admin'],
    'admin/category/edit'   => ['admin/category-edit', 'admin'],
    'admin/brands'          => ['admin/brands', 'admin'],
    'admin/brand/new'       => ['admin/brand-edit', 'admin'],
    'admin/brand/edit'      => ['admin/brand-edit', 'admin'],
    'admin/users'           => ['admin/users', 'admin'],
    'admin/user'            => ['admin/user-detail', 'admin'],
    'admin/loyalty'         => ['admin/loyalty', 'admin'],
    'admin/analytics'       => ['admin/analytics', 'admin'],
    'admin/marketing'       => ['admin/marketing', 'admin'],
    'admin/campaign/create' => ['admin/campaign-create', 'admin'],
    'admin/gift-bag'        => ['admin/gift-bag', 'admin'],
    'admin/gift-bag/new'    => ['admin/gift-bag-edit', 'admin'],
    'admin/gift-bag/edit'   => ['admin/gift-bag-edit', 'admin'],
    'admin/gift-cards'      => ['admin/gift-cards', 'admin'],
    'admin/contacts'        => ['admin/contacts', 'admin'],
    'admin/blog'            => ['admin/blog', 'admin'],
    'admin/blog/new'        => ['admin/blog-edit', 'admin'],
    'admin/blog/edit'       => ['admin/blog-edit', 'admin'],
    'admin/pages'           => ['admin/pages', 'admin'],
    'admin/faq'             => ['admin/faq-manage', 'admin'],
    'admin/export/orders'   => ['admin/export-orders', 'admin'],
];

// Match route
$routeParams = [];
$route = $routes[$path] ?? null;

// For static pages, set slug from the path itself
if ($route && in_array($path, ['about', 'terms', 'privacy', 'shipping'])) {
    $routeParams = ['slug' => $path];
}

// Dynamic slug-based routes (e.g. /product/slug, /category/slug)
if (!$route) {
    $dynamicRoutes = [
        'product'  => ['pages/product-detail', false],
        'category' => ['pages/category', false],
        'brand'    => ['pages/brand', false],
        'blog'     => ['pages/blog-post', false],
    ];

    $segments = explode('/', $path);
    if (count($segments) === 2 && isset($dynamicRoutes[$segments[0]])) {
        $route = $dynamicRoutes[$segments[0]];
        $routeParams = ['slug' => $segments[1]];
    }
}

if ($route) {
    [$viewFile, $auth] = $route;

    // Handle authentication requirements
    if ($auth === 'admin') {
        if ($viewFile !== 'admin/login') {
            requireAdmin();
        }
    } elseif ($auth === true) {
        requireUser();
    }

    $viewPath = __DIR__ . '/../src/views/' . $viewFile . '.php';
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        http_response_code(404);
        require __DIR__ . '/../src/views/pages/404.php';
    }
} else {
    // 404
    http_response_code(404);
    $viewPath = __DIR__ . '/../src/views/pages/404.php';
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>404 – Stranica nije pronađena</h1><p><a href="/">Povratak na početnu</a></p></body></html>';
    }
}
