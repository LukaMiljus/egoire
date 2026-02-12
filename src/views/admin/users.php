<?php
declare(strict_types=1);
$title = 'Korisnici';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = inputString('action', '', $_POST);
    $uid = inputInt('user_id', 0, $_POST);
    if ($uid && $action === 'toggle_status') {
        $user = fetchUserById($uid);
        if ($user) {
            updateUserStatus($uid, $user['status'] === 'active' ? 'blocked' : 'active');
            flash('success', 'Status korisnika ažuriran.');
        }
        redirect('/admin/users');
    }
}

$page = inputInt('page', 1);
$filters = [
    'search' => inputString('search'),
];
$filters = array_filter($filters);
$total = countUsers($filters);
$pagination = paginate($total, 20, $page);
$filters['limit'] = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$users = fetchUsers($filters);

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Korisnici <span class="badge"><?= $total ?></span></h1>
</div>

<div class="card mb-4">
    <form method="GET" action="/admin/users" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label>Pretraga</label>
                <input type="text" name="search" placeholder="Ime, email, telefon..." value="<?= htmlspecialchars(inputString('search')) ?>">
            </div>
            <div class="filter-group filter-actions">
                <button type="submit" class="btn btn-primary">Traži</button>
                <a href="/admin/users" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ime</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Loyalty bodovi</th>
                <th>Status</th>
                <th>Registrovan</th>
                <th>Akcije</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                <td><?= (int) ($u['points_balance'] ?? 0) ?></td>
                <td><span class="badge <?= $u['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= $u['status'] === 'active' ? 'Aktivan' : 'Blokiran' ?></span></td>
                <td><?= formatDate($u['created_at']) ?></td>
                <td class="actions-cell">
                    <a href="/admin/user?id=<?= $u['id'] ?>" class="btn btn-sm">Detalji</a>
                    <form method="POST" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-warning' : 'btn-success' ?>"><?= $u['status'] === 'active' ? 'Blokiraj' : 'Aktiviraj' ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr><td colspan="8" class="text-muted text-center">Nema korisnika.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?= renderPagination($pagination, '/admin/users') ?>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
