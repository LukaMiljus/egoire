<?php
declare(strict_types=1);
$title = 'Analitika';

$dateFrom = inputString('date_from') ?: date('Y-m-d', strtotime('-30 days'));
$dateTo = inputString('date_to') ?: date('Y-m-d');

$stats = dashboardStats();
$ordersByDay = ordersByDay($dateFrom, $dateTo);
$revByPeriod = revenueByPeriod($dateFrom, $dateTo);
$topCats = topCategories(10);

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Analitika</h1>
    <a href="/admin/export-orders?date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" class="btn btn-secondary">Izvezi CSV</a>
</div>

<div class="card mb-4">
    <form method="GET" class="filter-form">
        <div class="filter-row">
            <div class="filter-group">
                <label>Od</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="filter-group">
                <label>Do</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="filter-group filter-actions">
                <button type="submit" class="btn btn-primary">Primeni</button>
            </div>
        </div>
    </form>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Prihod (period)</span>
        <span class="stat-value"><?= formatPrice((float) ($revByPeriod['total_revenue'] ?? 0)) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Porudžbine (period)</span>
        <span class="stat-value"><?= (int) ($revByPeriod['total_orders'] ?? 0) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Pros. vrednost</span>
        <span class="stat-value"><?= formatPrice((float) ($revByPeriod['avg_order'] ?? 0)) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Ukupno korisnika</span>
        <span class="stat-value"><?= (int) ($stats['total_users'] ?? 0) ?></span>
    </div>
</div>

<!-- Revenue Chart -->
<div class="card mt-4">
    <h3>Prihod po danima</h3>
    <canvas id="revenueChart" height="80"></canvas>
</div>

<!-- Top Categories -->
<div class="card mt-4">
    <h3>Top kategorije</h3>
    <table class="admin-table">
        <thead>
            <tr><th>Kategorija</th><th>Prodatih kom.</th><th>Prihod</th></tr>
        </thead>
        <tbody>
            <?php foreach ($topCats as $tc): ?>
            <tr>
                <td><?= htmlspecialchars($tc['name']) ?></td>
                <td><?= (int) $tc['total_sold'] ?></td>
                <td><?= formatPrice((float) $tc['total_revenue']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topCats)): ?>
            <tr><td colspan="3" class="text-muted text-center">Nema podataka.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($ordersByDay, 'date')) ?>,
        datasets: [{
            label: 'Prihod (RSD)',
            data: <?= json_encode(array_map(fn($d) => (float) $d['revenue'], $ordersByDay)) ?>,
            backgroundColor: 'rgba(182, 142, 87, 0.6)',
            borderColor: '#b68e57',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { display: false } }
    }
});
</script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
