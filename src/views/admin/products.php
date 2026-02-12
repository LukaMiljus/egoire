<?php
declare(strict_types=1);
$title = 'Proizvodi';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && inputString('action', '', $_POST) === 'delete') {
    requireCsrf();
    $pid = inputInt('product_id', 0, $_POST);
    if ($pid) {
        $db = db();
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM product_categories WHERE product_id = ?")->execute([$pid]);
            $db->prepare("DELETE FROM product_flags WHERE product_id = ?")->execute([$pid]);
            $db->prepare("DELETE FROM product_stock WHERE product_id = ?")->execute([$pid]);
            $db->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$pid]);
            $db->prepare("DELETE FROM cart WHERE product_id = ?")->execute([$pid]);
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([$pid]);
            $db->commit();
            flash('success', 'Proizvod obrisan.');
        } catch (Throwable $e) {
            $db->rollBack();
            flash('error', 'Greška pri brisanju.');
        }
        redirect('/admin/products');
    }
}

$page = inputInt('page', 1);
$filters = [
    'search'   => inputString('search'),
    'brand_id' => inputInt('brand_id') ?: null,
    'active'   => inputString('active') !== '' ? (inputString('active') === '1') : null,
];
$filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

$total = countProducts($filters);
$pagination = paginate($total, 20, $page);
$filters['limit'] = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$products = fetchProducts($filters);
$brands = fetchBrands();

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Proizvodi <span class="badge"><?= $total ?></span></h1>
    <a href="/admin/product/new" class="btn btn-primary">+ Novi proizvod</a>
</div>

<div class="card mb-4">
    <form method="GET" action="/admin/products" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label>Pretraga</label>
                <input type="text" name="search" placeholder="Naziv, SKU..." value="<?= htmlspecialchars(inputString('search')) ?>">
            </div>
            <div class="filter-group">
                <label>Brend</label>
                <select name="brand_id">
                    <option value="">Svi</option>
                    <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= inputInt('brand_id') === (int) $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select name="active">
                    <option value="">Svi</option>
                    <option value="1" <?= inputString('active') === '1' ? 'selected' : '' ?>>Aktivan</option>
                    <option value="0" <?= inputString('active') === '0' ? 'selected' : '' ?>>Neaktivan</option>
                </select>
            </div>
            <div class="filter-group filter-actions">
                <button type="submit" class="btn btn-primary">Filtriraj</button>
                <a href="/admin/products" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Slika</th>
                <th>Naziv</th>
                <th>Brend</th>
                <th>Cena</th>
                <th>Akcijska</th>
                <th>Status</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <?php $imgs = fetchProductImages((int) $p['id']); ?>
                    <?php if (!empty($imgs)): ?>
                    <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>" alt="" class="thumb-sm">
                    <?php else: ?>
                    <span class="no-image">—</span>
                    <?php endif; ?>
                </td>
                <td><a href="/admin/product/edit?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></td>
                <td><?= htmlspecialchars($p['brand_name'] ?? '-') ?></td>
                <td><?= formatPrice((float) $p['price']) ?></td>
                <td><?= $p['sale_price'] ? formatPrice((float) $p['sale_price']) : '-' ?></td>
                <td><span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $p['is_active'] ? 'Aktivan' : 'Neaktivan' ?></span></td>
                <td class="actions-cell">
                    <a href="/admin/product/edit?id=<?= $p['id'] ?>" class="btn btn-sm">Uredi</a>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Obriši proizvod?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
            <tr><td colspan="7" class="text-muted text-center">Nema proizvoda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($pagination, '/admin/products') ?>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
