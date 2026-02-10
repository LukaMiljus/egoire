<?php

declare(strict_types=1);

$title = 'Dashboard';

$todayStats = dashboardStats('today');
$weekStats = dashboardStats('this_week');
$monthStats = dashboardStats('this_month');

$topProducts = bestSellerProducts(5);
$topCats = topCategories(5);
$recentOrders = fetchOrders(['limit' => 10]);
$ordersChart = ordersByDay(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p class="page-subtitle">Pregled stanja poslovanja</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <span class="stat-label">Porudžbine danas</span>
            <span class="stat-value"><?= $todayStats['total_orders'] ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <span class="stat-label">Porudžbine ove nedelje</span>
            <span class="stat-value"><?= $weekStats['total_orders'] ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <span class="stat-label">Porudžbine ovog meseca</span>
            <span class="stat-value"><?= $monthStats['total_orders'] ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <span class="stat-label">Promet danas</span>
            <span class="stat-value"><?= formatPrice($todayStats['total_revenue']) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <span class="stat-label">Promet ovog meseca</span>
            <span class="stat-value"><?= formatPrice($monthStats['total_revenue']) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-info">
            <span class="stat-label">Novi korisnici danas</span>
            <span class="stat-value"><?= $todayStats['new_users'] ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-label">Ukupno korisnika</span>
            <span class="stat-value"><?= $todayStats['total_users'] ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⭐</div>
        <div class="stat-info">
            <span class="stat-label">Loyalty korisnici</span>
            <span class="stat-value"><?= $todayStats['loyalty_users'] ?></span>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Orders Chart -->
    <div class="card">
        <h3 class="card-title">Porudžbine po danima (poslednih 30 dana)</h3>
        <canvas id="ordersChart" height="250"></canvas>
        <script id="ordersChartData" type="application/json"><?= json_encode($ordersChart) ?></script>
    </div>

    <!-- Top Products -->
    <div class="card">
        <h3 class="card-title">Najprodavaniji proizvodi</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Proizvod</th>
                    <th>Prodato</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProducts as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= (int) $p['total_sold'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($topProducts)): ?>
                <tr><td colspan="2" class="text-muted">Nema podataka</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Categories -->
    <div class="card">
        <h3 class="card-title">Najprodavanije kategorije</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kategorija</th>
                    <th>Promet</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topCats as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['name']) ?></td>
                    <td><?= formatPrice((float) $c['revenue']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($topCats)): ?>
                <tr><td colspan="2" class="text-muted">Nema podataka</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Orders -->
    <div class="card card-full">
        <h3 class="card-title">Poslednje porudžbine</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Br. porudžbine</th>
                    <th>Kupac</th>
                    <th>Status</th>
                    <th>Iznos</th>
                    <th>Datum</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><span class="badge <?= orderStatusClass($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span></td>
                    <td><?= formatPrice((float) $o['total_price']) ?></td>
                    <td><?= formatDateTime($o['created_at']) ?></td>
                    <td><a href="/admin/order?id=<?= $o['id'] ?>" class="btn btn-sm">Detalji</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = JSON.parse(document.getElementById('ordersChartData').textContent);
    if (data.length && document.getElementById('ordersChart')) {
        new Chart(document.getElementById('ordersChart'), {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'Porudžbine',
                    data: data.map(d => d.orders),
                    borderColor: '#7e38bb',
                    backgroundColor: 'rgba(126, 56, 187, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
