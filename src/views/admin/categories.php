<?php
declare(strict_types=1);
$title = 'Kategorije';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && inputString('action', '', $_POST) === 'delete') {
    requireCsrf();
    $catId = inputInt('category_id', 0, $_POST);
    if ($catId) {
        $db = db();
        $db->prepare("DELETE FROM product_categories WHERE category_id = ?")->execute([$catId]);
        $db->prepare("UPDATE categories SET parent_id = NULL WHERE parent_id = ?")->execute([$catId]);
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$catId]);
        flash('success', 'Kategorija obrisana.');
        redirect('/admin/categories');
    }
}

$categories = fetchCategories();
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Kategorije <span class="badge"><?= count($categories) ?></span></h1>
    <a href="/admin/category/new" class="btn btn-primary">+ Nova kategorija</a>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Slika</th>
                <th>Naziv</th>
                <th>Slug</th>
                <th>Nadkategorija</th>
                <th>Redosled</th>
                <th>Status</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $catMap = [];
            foreach ($categories as $c) $catMap[$c['id']] = $c['name'];
            foreach ($categories as $c):
            ?>
            <tr>
                <td>
                    <?php if ($c['image']): ?>
                    <img src="<?= htmlspecialchars($c['image']) ?>" alt="" class="thumb-sm">
                    <?php else: ?>
                    <span class="no-image">—</span>
                    <?php endif; ?>
                </td>
                <td><a href="/admin/category/edit?id=<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></a></td>
                <td class="text-muted"><?= htmlspecialchars($c['slug']) ?></td>
                <td><?= $c['parent_id'] ? htmlspecialchars($catMap[$c['parent_id']] ?? '-') : '—' ?></td>
                <td><?= (int) $c['sort_order'] ?></td>
                <td><span class="badge <?= $c['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $c['is_active'] ? 'Aktivna' : 'Neaktivna' ?></span></td>
                <td class="actions-cell">
                    <a href="/admin/category/edit?id=<?= $c['id'] ?>" class="btn btn-sm">Uredi</a>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Obriši kategoriju?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
            <tr><td colspan="7" class="text-muted text-center">Nema kategorija.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
