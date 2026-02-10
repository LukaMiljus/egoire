<?php
declare(strict_types=1);
$title = 'Marketing';

$subscribers = fetchEmailSubscribers(['limit' => 50]);
$campaigns = fetchEmailCampaigns();
$totalSubs = countEmailSubscribers();

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Marketing</h1>
    <a href="/admin/campaign/new" class="btn btn-primary">+ Nova kampanja</a>
</div>

<div class="form-grid-2">
    <div class="card">
        <h3>Email pretplatnici <span class="badge"><?= $totalSubs ?></span></h3>
        <table class="admin-table">
            <thead>
                <tr><th>Email</th><th>Datum</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= formatDate($s['created_at']) ?></td>
                    <td><span class="badge <?= $s['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $s['is_active'] ? 'Aktivan' : 'Odjavio se' ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Kampanje</h3>
        <table class="admin-table">
            <thead>
                <tr><th>Naslov</th><th>Status</th><th>Poslato</th><th>Datum</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['subject']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($c['status']) ?></span></td>
                    <td><?= (int) $c['sent_count'] ?></td>
                    <td><?= formatDate($c['created_at']) ?></td>
                    <td>
                        <?php if ($c['status'] === 'draft'): ?>
                        <a href="/admin/campaign/send?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Pošalji kampanju?')">Pošalji</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($campaigns)): ?>
                <tr><td colspan="5" class="text-muted text-center">Nema kampanja.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
