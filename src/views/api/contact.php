<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Neispravan zahtev.'], 405);
}

requireCsrf();
requireRateLimit('contact_api', 3, 600);

$input = json_decode(file_get_contents('php://input'), true);
$name    = inputString('name', '', $input ?? []);
$email   = sanitizeEmail($input['email'] ?? '');
$subject = inputString('subject', '', $input ?? []);
$message = inputString('message', '', $input ?? []);

$errors = [];
if (!$name)    $errors[] = 'Ime je obavezno.';
if (!isValidEmail($email)) $errors[] = 'Email je neispravan.';
if (!$subject) $errors[] = 'Naslov je obavezan.';
if (!$message) $errors[] = 'Poruka je obavezna.';

if (!empty($errors)) {
    jsonResponse(['success' => false, 'error' => implode(' ', $errors)]);
}

$result = createContactMessage($name, $email, $subject, $message);

if ($result) {
    jsonResponse(['success' => true, 'message' => 'Vaša poruka je uspešno poslata!']);
} else {
    jsonResponse(['success' => false, 'error' => 'Greška pri slanju. Pokušajte ponovo.']);
}
