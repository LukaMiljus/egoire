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
$variantId = inputInt('variant_id', 0, $_POST);
$quantity = inputInt('quantity', 1, $_POST);

if (!$productId || $quantity < 1) {
    jsonResponse(['error' => 'Nevalidni podaci'], 400);
}

$product = fetchProductById($productId);
if (!$product || !$product['is_active']) {
    jsonResponse(['error' => 'Proizvod nije dostupan'], 404);
}

// Validate variant exists if provided
if ($variantId) {
    $variants = fetchProductVariants($productId);
    $validVariant = false;
    foreach ($variants as $v) {
        if ((int) $v['id'] === $variantId) {
            $validVariant = true;
            break;
        }
    }
    if (!$validVariant) {
        jsonResponse(['error' => 'Varijanta nije pronađena'], 400);
    }
}

addProductToCart($productId, $quantity, $variantId ?: null);

jsonResponse([
    'success' => true,
    'cart_count' => cartItemCount(),
    'message' => 'Proizvod dodat u korpu',
]);
