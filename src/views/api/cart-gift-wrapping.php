<?php
declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

requireCsrf();

$input = json_decode(file_get_contents('php://input'), true);
$giftWrappingId = (int) ($input['gift_wrapping_id'] ?? 0);

if ($giftWrappingId > 0) {
    $option = fetchGiftWrappingById($giftWrappingId);
    if (!$option) {
        echo json_encode(['success' => false, 'error' => 'Gift wrapping option not found']);
        exit;
    }
    $_SESSION['gift_wrapping_id'] = $giftWrappingId;
} else {
    unset($_SESSION['gift_wrapping_id']);
}

echo json_encode(['success' => true, 'gift_wrapping_id' => $giftWrappingId]);
