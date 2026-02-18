<?php
/* ============================================================
   Egoire — Search API
   Matches: product name, slug, brand, category, SKU, description
   ============================================================ */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Neispravan zahtev.'], 405);
}

$query = trim($_GET['q'] ?? '');

// Empty or too-short query
if (mb_strlen($query) < 2) {
    jsonResponse(['success' => true, 'results' => [], 'count' => 0]);
}

$products = fetchProducts([
    'search' => $query,
    'active' => true,
    'limit'  => 12,
]);

$results = [];
foreach ($products as $p) {
    $results[] = [
        'id'         => (int)$p['id'],
        'name'       => htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'),
        'slug'       => $p['slug'],
        'price'      => (float)$p['price'],
        'sale_price' => $p['sale_price'] ? (float)$p['sale_price'] : null,
        'formatted_price' => formatPrice($p['sale_price'] ?: $p['price']),
        'image'      => $p['primary_image'] ?? '/uploads/products/placeholder.jpg',
        'brand'      => htmlspecialchars($p['brand_name'] ?? '', ENT_QUOTES, 'UTF-8'),
        'url'        => '/product/' . $p['slug'],
    ];
}

jsonResponse([
    'success' => true,
    'results' => $results,
    'count'   => count($results),
    'query'   => htmlspecialchars($query, ENT_QUOTES, 'UTF-8'),
]);
