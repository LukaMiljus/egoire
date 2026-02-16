<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/checkout');
}

requireCsrf();

$cartItems = fetchCartItems();
if (empty($cartItems)) {
    flash('error', 'Korpa je prazna.');
    redirect('/cart');
}

// Collect order data
$email = sanitizeEmail($_POST['email'] ?? '');
$firstName = inputString('first_name', '', $_POST);
$lastName = inputString('last_name', '', $_POST);
$phone = inputString('phone', '', $_POST);
$address = inputString('address', '', $_POST);
$city = inputString('city', '', $_POST);
$postalCode = inputString('postal_code', '', $_POST);
$country = inputString('country', 'Srbija', $_POST);
$note = inputString('note', '', $_POST);
$paymentMethod = inputString('payment_method', 'cod', $_POST);
$giftCardCode = inputString('gift_card_code', '', $_POST);
$useLoyalty = isset($_POST['use_loyalty']);
$loyaltyPoints = inputInt('loyalty_points', 0, $_POST);

// Validate
$errors = [];
if (!$firstName) $errors[] = 'Ime je obavezno.';
if (!$lastName) $errors[] = 'Prezime je obavezno.';
if (!isValidEmail($email)) $errors[] = 'Email je neispravan.';
if (!$phone) $errors[] = 'Telefon je obavezan.';
if (!$address) $errors[] = 'Adresa je obavezna.';
if (!$city) $errors[] = 'Grad je obavezan.';
if (!$postalCode) $errors[] = 'Poštanski broj je obavezan.';

if (!empty($errors)) {
    flash('error', implode(' ', $errors));
    redirect('/checkout');
}

$orderData = [
    'email'           => $email,
    'customer_name'   => $firstName . ' ' . $lastName,
    'phone'           => $phone,
    'customer_note'   => $note,
    'payment_method'  => $paymentMethod,
    'gift_card_code'  => $giftCardCode ?: null,
    'use_loyalty'     => $useLoyalty,
    'loyalty_points'  => $loyaltyPoints,
];

$addressData = [
    'first_name'  => $firstName,
    'last_name'   => $lastName,
    'phone'       => $phone,
    'address'     => $address,
    'city'        => $city,
    'postal_code' => $postalCode,
    'country'     => $country,
];

try {
    $result = createOrder($orderData, $cartItems, $addressData);
    if ($result['success']) {
        redirect('/order-confirmation?order=' . urlencode($result['order_number']));
    } else {
        flash('error', $result['error'] ?? 'Greška pri kreiranju porudžbine.');
        redirect('/checkout');
    }
} catch (Throwable $e) {
    error_log('Checkout error: ' . $e->getMessage());
    flash('error', 'Došlo je do greške. Pokušajte ponovo.');
    redirect('/checkout');
}
