<?php
declare(strict_types=1);
$id = inputInt('id');
if (!$id) redirect('/admin/users');
$user = fetchUserById($id);
if (!$user) redirect('/admin/users');

// Handle loyalty adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = inputString('action', '', $_POST);
    if ($action === 'add_loyalty') {
        $pts = inputInt('points', 0, $_POST);
        $reason = inputString('reason', '', $_POST);
        if ($pts != 0 && $reason) {
            addLoyaltyPoints($id, $pts, $pts > 0 ? 'admin_add' : 'admin_remove', null, $reason);
            flash('success', 'Loyalty bodovi ažurirani.');
            redirect('/admin/user?id=' . $id);
        }
    }
    if ($action === 'toggle_status') {
        updateUserStatus($id, $user['status'] === 'active' ? 'blocked' : 'active');
        flash('success', 'Status ažuriran.');
        redirect('/admin/user?id=' . $id);
    }
}

$loyalty = fetchUserLoyalty($id);
$loyaltyTransactions = fetchLoyaltyTransactions($id, 20);
$addresses = fetchUserAddresses($id);

// User orders
$db = db();
$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$id]);
$userOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Korisnik: ' . $user['first_name'] . ' ' . $user['last_name'];
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <div>
        <a href="/admin/users" class="btn btn-secondary btn-sm">&larr; Nazad</a>
        <h1><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
    </div>
    <form method="POST" class="inline-form">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="toggle_status">
        <button type="submit" class="btn <?= $user['status'] === 'active' ? 'btn-warning' : 'btn-success' ?>"><?= $user['status'] === 'active' ? 'Blokiraj' : 'Aktiviraj' ?></button>
    </form>
</div>

<div class="order-detail-grid">
    <div class="card">
        <h3>Informacije</h3>
        <dl class="info-list">
            <dt>Email</dt><dd><?= htmlspecialchars($user['email']) ?></dd>
            <dt>Telefon</dt><dd><?= htmlspecialchars($user['phone'] ?? '-') ?></dd>
            <dt>Status</dt><dd><span class="badge <?= $user['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= $user['status'] === 'active' ? 'Aktivan' : 'Blokiran' ?></span></dd>
            <dt>Email verifikovan</dt><dd><?= $user['email_verified'] ? 'Da' : 'Ne' ?></dd>
            <dt>Registrovan</dt><dd><?= formatDateTime($user['created_at']) ?></dd>
        </dl>
    </div>

    <div class="card">
        <h3>Loyalty program</h3>
        <?php if ($loyalty): ?>
        <dl class="info-list">
            <dt>Bodovi</dt><dd><strong><?= (int) ($loyalty['points_balance'] ?? 0) ?></strong></dd>
            <dt>Ukupno zarađeno</dt><dd><?= (int) ($loyalty['total_earned'] ?? 0) ?></dd>
            <dt>Ukupno potrošeno</dt><dd><?= (int) ($loyalty['total_spent'] ?? 0) ?></dd>
        </dl>
        <?php else: ?>
        <p class="text-muted">Nije u loyalty programu.</p>
        <?php endif; ?>

        <form method="POST" class="mt-3">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_loyalty">
            <div class="form-row">
                <div class="form-group">
                    <label>Bodovi (+/-)</label>
                    <input type="number" name="points" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Razlog</label>
                    <input type="text" name="reason" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Primeni</button>
        </form>
    </div>
</div>

<!-- Addresses -->
<div class="card mt-4">
    <h3>Adrese</h3>
    <?php if ($addresses): ?>
    <div class="address-grid">
        <?php foreach ($addresses as $addr): ?>
        <div class="address-card">
            <p><strong><?= htmlspecialchars($addr['first_name'] . ' ' . $addr['last_name']) ?></strong></p>
            <p><?= htmlspecialchars($addr['address']) ?></p>
            <p><?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['postal_code']) ?></p>
            <p><?= htmlspecialchars($addr['country']) ?></p>
            <p><?= htmlspecialchars($addr['phone'] ?? '') ?></p>
            <?php if ($addr['is_default']): ?><span class="badge badge-info">Podrazumevana</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-muted">Nema sačuvanih adresa.</p>
    <?php endif; ?>
</div>

<!-- Orders -->
<div class="card mt-4">
    <h3>Porudžbine</h3>
    <table class="admin-table">
        <thead>
            <tr><th>Br.</th><th>Status</th><th>Iznos</th><th>Datum</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($userOrders as $o): ?>
            <tr>
                <td><a href="/admin/order?id=<?= $o['id'] ?>"><?= htmlspecialchars($o['order_number']) ?></a></td>
                <td><span class="badge <?= orderStatusClass($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span></td>
                <td><?= formatPrice((float) $o['total_price']) ?></td>
                <td><?= formatDate($o['created_at']) ?></td>
                <td><a href="/admin/order?id=<?= $o['id'] ?>" class="btn btn-sm">Detalji</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($userOrders)): ?>
            <tr><td colspan="5" class="text-muted text-center">Nema porudžbina.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Loyalty Transactions -->
<div class="card mt-4">
    <h3>Loyalty transakcije</h3>
    <table class="admin-table">
        <thead>
            <tr><th>Datum</th><th>Tip</th><th>Bodovi</th><th>Opis</th></tr>
        </thead>
        <tbody>
            <?php foreach ($loyaltyTransactions as $lt): ?>
            <tr>
                <td><?= formatDateTime($lt['created_at']) ?></td>
                <td><?= htmlspecialchars($lt['type']) ?></td>
                <td class="<?= (int) $lt['points'] > 0 ? 'text-success' : 'text-danger' ?>"><?= (int) $lt['points'] > 0 ? '+' : '' ?><?= (int) $lt['points'] ?></td>
                <td><?= htmlspecialchars($lt['description'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($loyaltyTransactions)): ?>
            <tr><td colspan="4" class="text-muted text-center">Nema transakcija.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
