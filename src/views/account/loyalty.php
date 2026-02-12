<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Loyalty | Egoire';
$loyalty = fetchUserLoyalty((int) $user['id']);
$transactions = fetchLoyaltyTransactions((int) $user['id'], 30);
$settings = fetchLoyaltySettings();

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container">
        <div class="account-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="account-content">
                <h1>Loyalty program</h1>

                <?php if ($loyalty): ?>
                <div class="loyalty-summary">
                    <div class="loyalty-card">
                        <div class="lc-points"><?= (int) $loyalty['points_balance'] ?> <small>bodova</small></div>
                        <div class="lc-value"><?= formatPrice((float) $loyalty['points_balance'] * (float) ($settings['rsd_per_point'] ?? 1)) ?></div>
                    </div>
                    <div class="loyalty-info">
                        <p>Ukupno zarađeno: <strong><?= (int) $loyalty['total_earned'] ?></strong> bodova</p>
                        <p>Ukupno potrošeno: <strong><?= (int) $loyalty['total_spent'] ?></strong> bodova</p>
                    </div>
                </div>

                <div class="card mt-4">
                    <h3>Istorija transakcija</h3>
                    <table class="admin-table">
                        <thead><tr><th>Datum</th><th>Tip</th><th>Bodovi</th><th>Opis</th></tr></thead>
                        <tbody>
                            <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td><?= formatDate($t['created_at']) ?></td>
                                <td><?= htmlspecialchars($t['type']) ?></td>
                                <td class="<?= (int) $t['points'] > 0 ? 'text-success' : 'text-danger' ?>"><?= (int) $t['points'] > 0 ? '+' : '' ?><?= (int) $t['points'] ?></td>
                                <td><?= htmlspecialchars($t['description'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($transactions)): ?>
                            <tr><td colspan="4" class="text-muted text-center">Nema transakcija.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>Niste još u loyalty programu.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
