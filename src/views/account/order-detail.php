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

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container">
        <div class="account-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="account-content">
                <a href="/account/orders" class="btn btn-secondary btn-sm">&larr; Nazad</a>
                <h1>Porudžbina <?= htmlspecialchars($order['order_number']) ?></h1>

                <div class="order-status-bar mb-4">
                    <span class="badge <?= orderStatusClass($order['status']) ?>"><?= orderStatusLabel($order['status']) ?></span>
                    <span class="text-muted"><?= formatDateTime($order['created_at']) ?></span>
                </div>

                <div class="card mb-4">
                    <h3>Stavke</h3>
                    <?php foreach ($items as $item): ?>
                    <div class="order-item-row">
                        <span><?= htmlspecialchars($item['product_name']) ?></span>
                        <span>× <?= (int) $item['quantity'] ?></span>
                        <span><?= formatPrice((float) $item['unit_price'] * (int) $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="order-item-row total">
                        <span>Ukupno</span>
                        <span></span>
                        <span><strong><?= formatPrice((float) $order['total_price']) ?></strong></span>
                    </div>
                </div>

                <?php if ($address): ?>
                <div class="card">
                    <h3>Adresa dostave</h3>
                    <p>
                        <?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?><br>
                        <?= htmlspecialchars($address['address']) ?><br>
                        <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['postal_code']) ?><br>
                        <?= htmlspecialchars($address['phone'] ?? '') ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
