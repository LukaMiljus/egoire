<?php
declare(strict_types=1);
$title = 'Loyalty program';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $db = db();
    $settings = [
        'points_per_rsd'        => inputFloat('points_per_rsd', 1, $_POST),
        'rsd_per_point'         => inputFloat('rsd_per_point', 1, $_POST),
        'min_redeem_points'     => inputInt('min_redeem_points', 100, $_POST),
        'welcome_bonus'         => inputInt('welcome_bonus', 0, $_POST),
        'silver_threshold'      => inputInt('silver_threshold', 1000, $_POST),
        'gold_threshold'        => inputInt('gold_threshold', 5000, $_POST),
        'platinum_threshold'    => inputInt('platinum_threshold', 15000, $_POST),
        'silver_multiplier'     => inputFloat('silver_multiplier', 1.5, $_POST),
        'gold_multiplier'       => inputFloat('gold_multiplier', 2, $_POST),
        'platinum_multiplier'   => inputFloat('platinum_multiplier', 3, $_POST),
        'is_active'             => isset($_POST['is_active']) ? '1' : '0',
    ];
    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("INSERT INTO loyalty_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, (string) $value]);
    }
    flash('success', 'Loyalty podešavanja sačuvana.');
    redirect('/admin/loyalty');
}

$settings = fetchLoyaltySettings();
$topUsers = topLoyaltyUsers(10);

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Loyalty program</h1>
</div>

<div class="form-grid-2">
    <div class="card">
        <h3>Podešavanja</h3>
        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" <?= ($settings['is_active'] ?? '1') === '1' ? 'checked' : '' ?>>
                    Program aktivan
                </label>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Bodovi po RSD</label>
                    <input type="number" step="0.01" name="points_per_rsd" class="form-control" value="<?= $settings['points_per_rsd'] ?? '0.1' ?>">
                </div>
                <div class="form-group">
                    <label>RSD po bodu</label>
                    <input type="number" step="0.01" name="rsd_per_point" class="form-control" value="<?= $settings['rsd_per_point'] ?? '1' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Min bodova za otkup</label>
                    <input type="number" name="min_redeem_points" class="form-control" value="<?= $settings['min_redeem_points'] ?? '100' ?>">
                </div>
                <div class="form-group">
                    <label>Bonus dobrodošlice</label>
                    <input type="number" name="welcome_bonus" class="form-control" value="<?= $settings['welcome_bonus'] ?? '50' ?>">
                </div>
            </div>

            <h4 class="mt-4">Tier pragovi</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Silver (bodovi)</label>
                    <input type="number" name="silver_threshold" class="form-control" value="<?= $settings['silver_threshold'] ?? '1000' ?>">
                </div>
                <div class="form-group">
                    <label>Silver množilac</label>
                    <input type="number" step="0.1" name="silver_multiplier" class="form-control" value="<?= $settings['silver_multiplier'] ?? '1.5' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Gold (bodovi)</label>
                    <input type="number" name="gold_threshold" class="form-control" value="<?= $settings['gold_threshold'] ?? '5000' ?>">
                </div>
                <div class="form-group">
                    <label>Gold množilac</label>
                    <input type="number" step="0.1" name="gold_multiplier" class="form-control" value="<?= $settings['gold_multiplier'] ?? '2' ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Platinum (bodovi)</label>
                    <input type="number" name="platinum_threshold" class="form-control" value="<?= $settings['platinum_threshold'] ?? '15000' ?>">
                </div>
                <div class="form-group">
                    <label>Platinum množilac</label>
                    <input type="number" step="0.1" name="platinum_multiplier" class="form-control" value="<?= $settings['platinum_multiplier'] ?? '3' ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Sačuvaj</button>
        </form>
    </div>

    <div class="card">
        <h3>Top 10 korisnika</h3>
        <table class="admin-table">
            <thead>
                <tr><th>Korisnik</th><th>Bodovi</th><th>Ukupno zarađeno</th><th>Tier</th></tr>
            </thead>
            <tbody>
                <?php foreach ($topUsers as $u): ?>
                <tr>
                    <td><a href="/admin/user?id=<?= $u['user_id'] ?>"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></a></td>
                    <td><?= (int) $u['points'] ?></td>
                    <td><?= (int) $u['total_earned'] ?></td>
                    <td><span class="badge"><?= ucfirst($u['tier']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($topUsers)): ?>
                <tr><td colspan="4" class="text-muted text-center">Nema korisnika.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
