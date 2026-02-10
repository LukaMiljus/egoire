<?php

declare(strict_types=1);

/**
 * Input Validation & Sanitization Library
 * Prevents XSS, SQL injection, and validates common data types.
 */

function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function sanitizeEmail(string $email): string
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone(string $phone): bool
{
    // Allow +, digits, spaces, dashes, parentheses – 7 to 20 chars
    return (bool) preg_match('/^[\+]?[\d\s\-\(\)]{7,20}$/', trim($phone));
}

function isValidSlug(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
}

function generateSlug(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');

    // Transliterate common Serbian/Latin characters
    $map = [
        'č' => 'c', 'ć' => 'c', 'š' => 's', 'ž' => 'z', 'đ' => 'dj',
        'Č' => 'c', 'Ć' => 'c', 'Š' => 's', 'Ž' => 'z', 'Đ' => 'dj',
        'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss',
    ];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Validate required fields from POST data.
 * Returns array of error messages (empty if valid).
 */
function validateRequired(array $fields, array $data): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $errors[$field] = "$label je obavezno polje.";
        }
    }
    return $errors;
}

/**
 * Validate numeric fields.
 */
function validateNumeric(array $fields, array $data): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (isset($data[$field]) && $data[$field] !== '' && !is_numeric($data[$field])) {
            $errors[$field] = "$label mora biti broj.";
        }
    }
    return $errors;
}

function validatePositiveNumber($value): bool
{
    return is_numeric($value) && (float) $value > 0;
}

function validateMinLength(string $value, int $min): bool
{
    return mb_strlen(trim($value), 'UTF-8') >= $min;
}

function validateMaxLength(string $value, int $max): bool
{
    return mb_strlen(trim($value), 'UTF-8') <= $max;
}

function validatePassword(string $password): array
{
    $errors = [];

    if (mb_strlen($password) < 8) {
        $errors[] = 'Lozinka mora imati najmanje 8 karaktera.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Lozinka mora sadržati bar jedno veliko slovo.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Lozinka mora sadržati bar jedno malo slovo.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Lozinka mora sadržati bar jedan broj.';
    }

    return $errors;
}

/**
 * Validate image upload.
 */
function validateImageUpload(array $file, int $maxSizeMB = 5): array
{
    $errors = [];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Greška pri uploadu fajla.';
        return $errors;
    }

    if (!in_array($file['type'], $allowed, true)) {
        $errors[] = 'Dozvoljeni formati: JPG, PNG, WebP, GIF.';
    }

    if ($file['size'] > $maxSizeMB * 1024 * 1024) {
        $errors[] = "Maksimalna veličina fajla je {$maxSizeMB}MB.";
    }

    // Verify it's actually an image
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'Fajl nije validna slika.';
    }

    return $errors;
}

/**
 * Safe file upload – generates unique name, validates extension.
 * Returns relative path or null on failure.
 */
function uploadImage(array $file, string $subDir = 'products'): ?string
{
    $errors = validateImageUpload($file);
    if ($errors) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowedExts, true)) {
        return null;
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../../public/uploads/' . $subDir;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    return '/uploads/' . $subDir . '/' . $filename;
}

/**
 * Get integer from input, with bounds checking.
 */
function inputInt(string $key, int $default = 0, ?array $source = null): int
{
    $source = $source ?? $_REQUEST;
    $val = $source[$key] ?? $default;
    return (int) $val;
}

/**
 * Get trimmed string from input.
 */
function inputString(string $key, string $default = '', ?array $source = null): string
{
    $source = $source ?? $_REQUEST;
    $val = $source[$key] ?? $default;
    return is_string($val) ? trim($val) : $default;
}

/**
 * Get float from input.
 */
function inputFloat(string $key, float $default = 0.0, ?array $source = null): float
{
    $source = $source ?? $_REQUEST;
    $val = $source[$key] ?? $default;
    return is_numeric($val) ? (float) $val : $default;
}
