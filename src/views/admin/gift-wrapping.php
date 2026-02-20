<?php
declare(strict_types=1);
$title = 'Poklon pakovanja';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && inputString('action', '', $_POST) === 'delete') {
    requireCsrf();
    $wrapId = inputInt('wrap_id', 0, $_POST);
    if ($wrapId) {
        deleteGiftWrapping($wrapId);
        flash('success', 'Poklon pakovanje obrisano.');
        redirect('/admin/gift-wrapping');
    }
}

$options = fetchGiftWrappingOptions(false);
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Poklon pakovanja</h1>
    <a href="/admin/gift-wrapping/new" class="btn btn-primary">+ Novo pakovanje</a>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Slika</th>
                <th>Naziv</th>
                <th>Cena</th>
                <th>Status</th>
                <th>Redosled</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($options as $o): ?>
            <tr>
                <td>
                    <?php if (!empty($o['image'])): ?>
                    <img src="<?= htmlspecialchars($o['image']) ?>" alt="" class="thumb-sm">
                    <?php else: ?>
                    <span class="no-image">—</span>
                    <?php endif; ?>
                </td>
                <td><a href="/admin/gift-wrapping/edit?id=<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></a></td>
                <td><?= formatPrice((float)$o['price']) ?></td>
                <td><span class="badge <?= $o['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $o['is_active'] ? 'Aktivno' : 'Neaktivno' ?></span></td>
                <td><?= $o['sort_order'] ?></td>
                <td class="actions-cell">
                    <a href="/admin/gift-wrapping/edit?id=<?= $o['id'] ?>" class="btn btn-sm">Uredi</a>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Obriši?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="wrap_id" value="<?= $o['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($options)): ?>
            <tr><td colspan="6" class="text-muted text-center">Nema poklon pakovanja.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
