<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$loyalty = fetchUserLoyalty((int) $user['id']);
$title = 'Moj nalog | Egoire';

$db = db();
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user['id']]);
$orderCount = (int) $stmt->fetchColumn();

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container">
        <div class="account-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="account-content">
                <h1>Dobrodošli, <?= htmlspecialchars($user['first_name']) ?>!</h1>

                <div class="account-stats">
                    <div class="account-stat">
                        <span class="stat-value"><?= $orderCount ?></span>
                        <span class="stat-label">Porudžbina</span>
                    </div>
                    <?php if ($loyalty): ?>
                    <div class="account-stat">
                        <span class="stat-value"><?= (int) $loyalty['points_balance'] ?></span>
                        <span class="stat-label">Loyalty bodova</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card mt-4">
                    <h3>Informacije</h3>
                    <dl class="info-list">
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
