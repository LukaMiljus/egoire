<?php
declare(strict_types=1);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $input['_token'] ?? '';

if (!isAjaxRequest() || !verifyCsrfToken($csrfToken)) {
    jsonResponse(['error' => 'Invalid request'], 403);
}

$cartId = (int) ($input['cart_id'] ?? 0);
$quantity = (int) ($input['quantity'] ?? 0);

if (!$cartId || $quantity < 1) {
    jsonResponse(['error' => 'Nevalidni podaci'], 400);
}

updateCartItemQuantity($cartId, $quantity);

jsonResponse([
    'success' => true,
    'cart_count' => cartItemCount(),
]);
