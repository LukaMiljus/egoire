<?php
declare(strict_types=1);
$title = 'Porudžbine';

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'change_status') {
    requireCsrf();
    $orderId = inputInt('order_id', 0, $_POST);
    $newStatus = inputString('new_status', '', $_POST);
    if ($orderId && $newStatus) {
        updateOrderStatus($orderId, $newStatus);
        flash('success', 'Status porudžbine je ažuriran.');
        redirect('/admin/orders');
    }
}

$page = inputInt('page', 1);
$filters = [
    'status'         => inputString('status'),
    'search'         => inputString('search'),
    'date_from'      => inputString('date_from'),
    'date_to'        => inputString('date_to'),
    'payment_method' => inputString('payment_method'),
];
if (inputString('user_type') === 'registered') $filters['registered_only'] = true;
if (inputString('user_type') === 'guest')      $filters['guest_only'] = true;

$filters = array_filter($filters);
$total = countOrders($filters);
$pagination = paginate($total, 20, $page);
$filters['limit'] = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$orders = fetchOrders($filters);

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Porudžbine</h1>
    <span class="badge"><?= $total ?> ukupno</span>
</div>

<!-- Filters -->
<div class="card mb-4">
    <form method="GET" action="/admin/orders" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Svi</option>
                    <?php foreach (['new','processing','shipped','delivered','canceled'] as $s): ?>
                    <option value="<?= $s ?>" <?= inputString('status') === $s ? 'selected' : '' ?>><?= orderStatusLabel($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Od datuma</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars(inputString('date_from')) ?>">
            </div>
            <div class="filter-group">
                <label>Do datuma</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars(inputString('date_to')) ?>">
            </div>
            <div class="filter-group">
                <label>Plaćanje</label>
                <select name="payment_method">
                    <option value="">Svi</option>
                    <option value="cod" <?= inputString('payment_method') === 'cod' ? 'selected' : '' ?>>Pouzeće</option>
                    <option value="card" <?= inputString('payment_method') === 'card' ? 'selected' : '' ?>>Kartica</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Tip korisnika</label>
                <select name="user_type">
                    <option value="">Svi</option>
                    <option value="registered" <?= inputString('user_type') === 'registered' ? 'selected' : '' ?>>Registrovani</option>
                    <option value="guest" <?= inputString('user_type') === 'guest' ? 'selected' : '' ?>>Gosti</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Pretraga</label>
                <input type="text" name="search" placeholder="Br, ime, email..." value="<?= htmlspecialchars(inputString('search')) ?>">
            </div>
            <div class="filter-group filter-actions">
                <button type="submit" class="btn btn-primary">Filtriraj</button>
                <a href="/admin/orders" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Br. porudžbine</th>
                <th>Kupac</th>
                <th>Email</th>
                <th>Status</th>
                <th>Plaćanje</th>
                <th>Iznos</th>
                <th>Datum</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><a href="/admin/order?id=<?= $o['id'] ?>" class="link-bold"><?= htmlspecialchars($o['order_number']) ?></a></td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td><?= htmlspecialchars($o['email']) ?></td>
                <td><span class="badge <?= orderStatusClass($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span></td>
                <td><?= $o['payment_method'] === 'cod' ? 'Pouzeće' : ucfirst($o['payment_method']) ?></td>
                <td><?= formatPrice((float) $o['total_price']) ?></td>
                <td><?= formatDateTime($o['created_at']) ?></td>
                <td>
                    <a href="/admin/order?id=<?= $o['id'] ?>" class="btn btn-sm">Detalji</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
            <tr><td colspan="8" class="text-muted text-center">Nema porudžbina.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?= renderPagination($pagination, '/admin/orders') ?>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
