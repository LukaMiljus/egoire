<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$orderId = inputInt('id');

$db = db();
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) redirect('/account/orders');

$items = fetchOrderItems($orderId);
$address = fetchOrderAddress($orderId);
$title = 'Porudžbina ' . $order['order_number'] . ' | Egoire';
$pageStyles = ['/css/account.css'];

require __DIR__ . '/../layout/header.php';
?>

<section class="ac-page">
    <div class="ac-container">
        <div class="ac-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="ac-content">
                <a href="/account/orders" class="ac-back-link">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M19 12H5m0 0l7 7m-7-7l7-7"/></svg>
                    Nazad na porudžbine
                </a>
                <h1 class="ac-title">Porudžbina <?= htmlspecialchars($order['order_number']) ?></h1>

                <div class="ac-order-status">
                    <span class="ac-badge ac-badge--<?= htmlspecialchars($order['status']) ?>"><?= orderStatusLabel($order['status']) ?></span>
                    <span class="ac-order__date"><?= formatDateTime($order['created_at']) ?></span>
                </div>

                <div class="ac-card">
                    <h3 class="ac-card__title">Stavke</h3>
                    <?php foreach ($items as $item): ?>
                    <div class="ac-order-item">
                        <span class="ac-order-item__name"><?= htmlspecialchars($item['product_name']) ?></span>
                        <span class="ac-order-item__qty">× <?= (int) $item['quantity'] ?></span>
                        <span class="ac-order-item__price"><?= formatPrice((float) $item['unit_price'] * (int) $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!empty($order['gift_wrapping_name'])): ?>
                    <div class="ac-order-item ac-order-item--gift">
                        <span class="ac-order-item__name">🎁 <?= htmlspecialchars($order['gift_wrapping_name']) ?></span>
                        <span class="ac-order-item__qty"></span>
                        <span class="ac-order-item__price"><?= formatPrice((float) $order['gift_wrapping_price']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="ac-order-total-row">
                        <span>Ukupno</span>
                        <span><?= formatPrice((float) $order['total_price']) ?></span>
                    </div>
                </div>

                <?php if ($address): ?>
                <div class="ac-card">
                    <h3 class="ac-card__title">Adresa dostave</h3>
                    <div class="ac-address-block">
                        <?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?><br>
                        <?= htmlspecialchars($address['address']) ?><br>
                        <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['postal_code']) ?><br>
                        <?= htmlspecialchars($address['phone'] ?? '') ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
