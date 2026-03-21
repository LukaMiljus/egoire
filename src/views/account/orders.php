<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Moje porudžbine | Egoire';
$pageStyles = ['/css/account.css'];

$db = db();
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../layout/header.php';
?>

<section class="ac-page">
    <div class="ac-container">
        <div class="ac-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="ac-content">
                <h1 class="ac-title">Moje porudžbine</h1>

                <?php if ($orders): ?>
                <div class="ac-orders">
                    <?php foreach ($orders as $o): ?>
                    <div class="ac-order">
                        <div class="ac-order__main">
                            <span class="ac-order__number"><?= htmlspecialchars($o['order_number']) ?></span>
                            <span class="ac-order__date"><?= formatDate($o['created_at']) ?></span>
                            <span class="ac-badge ac-badge--<?= htmlspecialchars($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span>
                        </div>
                        <div class="ac-order__right">
                            <span class="ac-order__total"><?= formatPrice((float) $o['total_price']) ?></span>
                            <a href="/account/order?id=<?= $o['id'] ?>" class="ac-order__details-btn">Detalji</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="ac-empty">
                    <div class="ac-empty__icon">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6"/></svg>
                    </div>
                    <p class="ac-empty__text">Nemate porudžbina.</p>
                    <a href="/products" class="ac-empty__btn">Počnite kupovinu</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
