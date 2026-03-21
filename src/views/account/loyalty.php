<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Loyalty | Egoire';
$pageStyles = ['/css/account.css'];
$loyalty = fetchUserLoyalty((int) $user['id']);
$transactions = fetchLoyaltyTransactions((int) $user['id'], 30);
$settings = fetchLoyaltySettings();

require __DIR__ . '/../layout/header.php';
?>

<section class="ac-page">
    <div class="ac-container">
        <div class="ac-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="ac-content">
                <h1 class="ac-title">Loyalty program</h1>

                <?php if ($loyalty): ?>
                <div class="ac-loyalty">
                    <div class="ac-loyalty-card">
                        <div class="ac-loyalty-card__points"><?= (int) $loyalty['points_balance'] ?> <small>bodova</small></div>
                        <div class="ac-loyalty-card__value"><?= formatPrice((float) $loyalty['points_balance'] * (float) ($settings['rsd_per_point'] ?? 1)) ?></div>
                    </div>
                    <div class="ac-loyalty-info">
                        <p>Ukupno zarađeno: <strong><?= (int) $loyalty['total_earned'] ?></strong> bodova</p>
                        <p>Ukupno potrošeno: <strong><?= (int) $loyalty['total_spent'] ?></strong> bodova</p>
                    </div>
                </div>

                <div class="ac-card">
                    <h3 class="ac-card__title">Istorija transakcija</h3>
                    <table class="ac-table">
                        <thead><tr><th>Datum</th><th>Tip</th><th>Bodovi</th><th>Opis</th></tr></thead>
                        <tbody>
                            <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td data-label="Datum"><?= formatDate($t['created_at']) ?></td>
                                <td data-label="Tip"><?= htmlspecialchars($t['type']) ?></td>
                                <td data-label="Bodovi" class="<?= (int) $t['points'] > 0 ? 'text-success' : 'text-danger' ?>"><?= (int) $t['points'] > 0 ? '+' : '' ?><?= (int) $t['points'] ?></td>
                                <td data-label="Opis"><?= htmlspecialchars($t['description'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($transactions)): ?>
                            <tr><td colspan="4" style="text-align:center; color:#999;">Nema transakcija.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="ac-empty">
                    <div class="ac-empty__icon">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                    </div>
                    <p class="ac-empty__text">Niste još u loyalty programu.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
