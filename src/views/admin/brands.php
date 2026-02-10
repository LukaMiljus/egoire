<?php
declare(strict_types=1);
$title = 'Brendovi';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && inputString('action', '', $_POST) === 'delete') {
    requireCsrf();
    $brandId = inputInt('brand_id', 0, $_POST);
    if ($brandId) {
        $db = db();
        $db->prepare("UPDATE products SET brand_id = NULL WHERE brand_id = ?")->execute([$brandId]);
        $db->prepare("DELETE FROM brands WHERE id = ?")->execute([$brandId]);
        flash('success', 'Brend obrisan.');
        redirect('/admin/brands');
    }
}

$brands = fetchBrands();
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Brendovi <span class="badge"><?= count($brands) ?></span></h1>
    <a href="/admin/brand/new" class="btn btn-primary">+ Novi brend</a>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Logo</th>
                <th>Naziv</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($brands as $b): ?>
            <tr>
                <td>
                    <?php if ($b['logo']): ?>
                    <img src="<?= htmlspecialchars($b['logo']) ?>" alt="" class="thumb-sm">
                    <?php else: ?>
                    <span class="no-image">—</span>
                    <?php endif; ?>
                </td>
                <td><a href="/admin/brand/edit?id=<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></a></td>
                <td class="text-muted"><?= htmlspecialchars($b['slug']) ?></td>
                <td><span class="badge <?= $b['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $b['is_active'] ? 'Aktivan' : 'Neaktivan' ?></span></td>
                <td class="actions-cell">
                    <a href="/admin/brand/edit?id=<?= $b['id'] ?>" class="btn btn-sm">Uredi</a>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Obriši brend?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="brand_id" value="<?= $b['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($brands)): ?>
            <tr><td colspan="5" class="text-muted text-center">Nema brendova.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
