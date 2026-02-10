<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Neispravan zahtev.'], 405);
}

requireCsrf();
requireRateLimit('newsletter', 3, 300);

$input = json_decode(file_get_contents('php://input'), true);
$email = sanitizeEmail($input['email'] ?? '');

if (!isValidEmail($email)) {
    jsonResponse(['success' => false, 'error' => 'Unesite validnu email adresu.']);
}

$result = marketingSubscribe($email);

if ($result === 'already') {
    jsonResponse(['success' => true, 'message' => 'Već ste prijavljeni na naš newsletter.']);
} elseif ($result) {
    jsonResponse(['success' => true, 'message' => 'Uspešno ste se prijavili na newsletter!']);
} else {
    jsonResponse(['success' => false, 'error' => 'Greška pri prijavi. Pokušajte ponovo.']);
}
