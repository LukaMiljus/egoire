<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Neispravan zahtev.'], 405);
}

if (!isAjaxRequest()) {
    jsonResponse(['success' => false, 'error' => 'Neispravan zahtev.'], 400);
}

verifyCsrfToken();

$input = json_decode(file_get_contents('php://input'), true);
$code = trim($input['code'] ?? '');

if (!$code) {
    jsonResponse(['success' => false, 'error' => 'Unesite kod poklon kartice.']);
}

$result = validateGiftCard($code);

if ($result) {
    jsonResponse([
        'success'   => true,
        'code'      => htmlspecialchars($code, ENT_QUOTES, 'UTF-8'),
        'balance'   => (float)$result['balance'],
        'formatted' => formatPrice((float)$result['balance']),
    ]);
} else {
    jsonResponse(['success' => false, 'error' => 'Poklon kartica nije validna ili je iskorišćena.']);
}
