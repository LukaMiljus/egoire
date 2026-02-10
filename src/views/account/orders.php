<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Moje porudžbine | Egoire';

$db = db();
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container">
        <div class="account-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="account-content">
                <h1>Moje porudžbine</h1>

                <?php if ($orders): ?>
                <div class="orders-list">
                    <?php foreach ($orders as $o): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <strong><?= htmlspecialchars($o['order_number']) ?></strong>
                                <span class="text-muted"><?= formatDate($o['created_at']) ?></span>
                            </div>
                            <span class="badge <?= orderStatusClass($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span>
                        </div>
                        <div class="order-card-body">
                            <span class="order-total"><?= formatPrice((float) $o['total_price']) ?></span>
                            <a href="/account/order?id=<?= $o['id'] ?>" class="btn btn-sm">Detalji</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>Nemate porudžbina.</p>
                    <a href="/products" class="btn btn-primary">Počnite kupovinu</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
