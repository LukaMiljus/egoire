<?php
declare(strict_types=1);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

if (!isAjaxRequest() || !verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    jsonResponse(['error' => 'Invalid request'], 403);
}

$productId = inputInt('product_id', 0, $_POST);
$variantLabel = inputString('variant_label', '', $_POST);
$quantity = inputInt('quantity', 1, $_POST);

if (!$productId || $quantity < 1) {
    jsonResponse(['error' => 'Nevalidni podaci'], 400);
}

$product = fetchProductById($productId);
if (!$product || !$product['is_active']) {
    jsonResponse(['error' => 'Proizvod nije dostupan'], 404);
}

// Check stock
if ($variantLabel) {
    $stock = fetchProductStock($productId);
    foreach ($stock as $s) {
        if ($s['variant_label'] === $variantLabel && (int) $s['quantity'] < $quantity) {
            jsonResponse(['error' => 'Nema dovoljno na stanju'], 400);
        }
    }
}

$price = productDisplayPrice($product);
addProductToCart($productId, $quantity, $variantLabel ?: null, $price);

jsonResponse([
    'success' => true,
    'cart_count' => cartItemCount(),
    'message' => 'Proizvod dodat u korpu',
]);
