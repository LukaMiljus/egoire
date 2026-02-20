<?php
declare(strict_types=1);
$title = 'Stanje zaliha';

// Handle stock adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && inputString('action', '', $_POST) === 'adjust') {
    requireCsrf();
    $productId = inputInt('product_id', 0, $_POST);
    $adjustment = inputInt('adjustment', 0, $_POST);
    $reason = inputString('reason', '', $_POST);

    if ($productId && $adjustment !== 0) {
        adjustStock($productId, $adjustment, $reason);
        flash('success', 'Stanje ažurirano.');
    } else {
        flash('error', 'Nevažeći parametri.');
    }
    redirect('/admin/inventory');
}

$filters = [
    'search'       => inputString('search'),
    'low_stock'    => inputString('low_stock') === '1',
    'out_of_stock' => inputString('out_of_stock') === '1',
];
$filters = array_filter($filters, fn($v) => $v !== '' && $v !== false);

$inventory = fetchInventory(array_merge($filters, ['limit' => 200]));

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Stanje zaliha <span class="badge"><?= count($inventory) ?></span></h1>
</div>

<div class="card mb-4">
    <form method="GET" action="/admin/inventory" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label>Pretraga</label>
                <input type="text" name="search" placeholder="Naziv, SKU..." value="<?= htmlspecialchars(inputString('search')) ?>">
            </div>
            <div class="filter-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="low_stock" value="1" <?= inputString('low_stock') === '1' ? 'checked' : '' ?>>
                    Samo niske zalihe
                </label>
            </div>
            <div class="filter-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="out_of_stock" value="1" <?= inputString('out_of_stock') === '1' ? 'checked' : '' ?>>
                    Samo bez zaliha
                </label>
            </div>
            <div class="filter-group filter-actions">
                <button type="submit" class="btn btn-primary">Filtriraj</button>
                <a href="/admin/inventory" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Proizvod</th>
                <th>SKU</th>
                <th>Brend</th>
                <th>Trenutno stanje</th>
                <th>Prag</th>
                <th>Status</th>
                <th>Prilagodi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory as $item):
                $qty = (int)($item['quantity'] ?? 0);
                $threshold = (int)($item['low_stock_threshold'] ?? 5);
                $statusClass = $qty <= 0 ? 'badge-danger' : ($qty <= $threshold ? 'badge-warning' : 'badge-success');
                $statusLabel = $qty <= 0 ? 'Nema' : ($qty <= $threshold ? 'Niska' : 'OK');
            ?>
            <tr>
                <td><a href="/admin/product/edit?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></td>
                <td><?= htmlspecialchars($item['sku'] ?? '-') ?></td>
                <td><?= htmlspecialchars($item['brand_name'] ?? '-') ?></td>
                <td><strong><?= $qty ?></strong></td>
                <td><?= $threshold ?></td>
                <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                <td>
                    <form method="POST" class="inline-form" style="display:flex;gap:4px;align-items:center;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="adjust">
                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                        <input type="number" name="adjustment" class="form-control" style="width:80px;padding:4px 6px;" placeholder="+/-" required>
                        <input type="text" name="reason" class="form-control" style="width:120px;padding:4px 6px;" placeholder="Razlog">
                        <button type="submit" class="btn btn-sm btn-primary">Primeni</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($inventory)): ?>
            <tr><td colspan="7" class="text-muted text-center">Nema proizvoda.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
