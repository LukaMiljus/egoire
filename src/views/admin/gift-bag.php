<?php
declare(strict_types=1);
$title = 'Gift Bag';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && inputString('action', '', $_POST) === 'delete') {
    requireCsrf();
    $ruleId = inputInt('rule_id', 0, $_POST);
    if ($ruleId) {
        $db = db();
        $db->prepare("DELETE FROM gift_bag_discounts WHERE rule_id = ?")->execute([$ruleId]);
        $db->prepare("DELETE FROM gift_bag_rules WHERE id = ?")->execute([$ruleId]);
        flash('success', 'Pravilo obrisano.');
        redirect('/admin/gift-bag');
    }
}

$rules = fetchGiftBagRules();
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Gift Bag pravila</h1>
    <a href="/admin/gift-bag/new" class="btn btn-primary">+ Novo pravilo</a>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr><th>Naziv</th><th>Min iznos</th><th>Tip</th><th>Status</th><th>Akcije</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rules as $r): ?>
            <tr>
                <td><a href="/admin/gift-bag/edit?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></a></td>
                <td><?= formatPrice((float) ($r['min_order_value'] ?? 0)) ?></td>
                <td>-</td>
                <td><span class="badge <?= $r['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $r['is_active'] ? 'Aktivno' : 'Neaktivno' ?></span></td>
                <td class="actions-cell">
                    <a href="/admin/gift-bag/edit?id=<?= $r['id'] ?>" class="btn btn-sm">Uredi</a>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Obriši?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="rule_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rules)): ?>
            <tr><td colspan="5" class="text-muted text-center">Nema pravila.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
