<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$loyalty = fetchUserLoyalty((int) $user['id']);
$title = 'Moj nalog | Egoire';
$pageStyles = ['/css/account.css'];

$db = db();
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user['id']]);
$orderCount = (int) $stmt->fetchColumn();

require __DIR__ . '/../layout/header.php';
?>

<section class="ac-page">
    <div class="ac-container">
        <div class="ac-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="ac-content">
                <h1 class="ac-title">Dobrodošli, <?= htmlspecialchars($user['first_name']) ?>!</h1>

                <div class="ac-stats">
                    <div class="ac-stat">
                        <span class="ac-stat__value"><?= $orderCount ?></span>
                        <span class="ac-stat__label">Porudžbina</span>
                    </div>
                    <?php if ($loyalty): ?>
                    <div class="ac-stat">
                        <span class="ac-stat__value"><?= (int) $loyalty['points_balance'] ?></span>
                        <span class="ac-stat__label">Loyalty bodova</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="ac-card">
                    <h3 class="ac-card__title">Informacije</h3>
                    <dl class="ac-info-list">
                        <dt>Ime</dt><dd><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></dd>
                        <dt>Email</dt><dd><?= htmlspecialchars($user['email']) ?></dd>
                        <dt>Telefon</dt><dd><?= htmlspecialchars($user['phone'] ?? '-') ?></dd>
                        <dt>Član od</dt><dd><?= formatDate($user['created_at']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
