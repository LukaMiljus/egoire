<?php
declare(strict_types=1);
$title = 'Analitika';

/* --- Date Range --- */
$dateFrom = inputString('date_from') ?: date('Y-m-d', strtotime('-30 days'));
$dateTo   = inputString('date_to')   ?: date('Y-m-d');

/* --- Fetch all data --- */
$stats           = dashboardStats();
$ordersByDayData = ordersByDay($dateFrom, $dateTo);
$revAgg          = revenueByPeriodAggregated($dateFrom, $dateTo);
$topCats         = topCategories(10);
$topProducts     = topSellingProducts($dateFrom, $dateTo, 10);
$topBrands       = topSellingBrands($dateFrom, $dateTo, 10);
$statusBreakdown = ordersByStatus($dateFrom, $dateTo);
$paymentMethods  = ordersByPaymentMethod($dateFrom, $dateTo);
$guestVsReg      = guestVsRegisteredOrders($dateFrom, $dateTo);
$monthlyTrend    = monthlyRevenueTrend(12);
$recentOrd       = recentOrders(8);
$lowStock        = lowStockProducts(8);
$newUsersPeriod  = newUsersInPeriod($dateFrom, $dateTo);
$newSubsPeriod   = newSubscribersInPeriod($dateFrom, $dateTo);
$regRate         = conversionRate($dateFrom, $dateTo);

/* --- Previous period comparison --- */
$daysDiff     = max(1, (int) ((strtotime($dateTo) - strtotime($dateFrom)) / 86400));
$prevFrom     = date('Y-m-d', strtotime($dateFrom . " - {$daysDiff} days"));
$prevTo       = date('Y-m-d', strtotime($dateFrom . ' - 1 day'));
$prevRevAgg   = revenueByPeriodAggregated($prevFrom, $prevTo);

$revChange   = ($prevRevAgg['total_revenue'] ?? 0) > 0 ? round((($revAgg['total_revenue'] - $prevRevAgg['total_revenue']) / $prevRevAgg['total_revenue']) * 100, 1) : 0;
$ordChange   = ($prevRevAgg['total_orders'] ?? 0) > 0 ? round((($revAgg['total_orders'] - $prevRevAgg['total_orders']) / $prevRevAgg['total_orders']) * 100, 1) : 0;

/* --- Status map for labels --- */
$statusLabels = [
    'new'        => 'Nove',
    'processing' => 'U obradi',
    'shipped'    => 'Poslate',
    'delivered'  => 'Dostavljene',
    'canceled'   => 'Otkazane',
];
$statusColors = [
    'new'        => '#3B82F6',
    'processing' => '#F59E0B',
    'shipped'    => '#8B5CF6',
    'delivered'  => '#10B981',
    'canceled'   => '#EF4444',
];

require __DIR__ . '/../layout/admin-header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<div class="analytics-page">

    <!-- Header -->
    <div class="analytics-header">
        <div>
            <h1 class="analytics-title">Analitika</h1>
            <p class="analytics-subtitle">Pregled performansi za period <?= htmlspecialchars($dateFrom) ?> — <?= htmlspecialchars($dateTo) ?></p>
        </div>
        <div class="analytics-header-actions">
            <a href="/admin/export-orders?date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" class="btn btn-secondary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Izvezi CSV
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="analytics-filter">
        <form method="GET" class="analytics-filter-form">
            <div class="analytics-filter-group">
                <label>Od</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="form-control">
            </div>
            <div class="analytics-filter-group">
                <label>Do</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="form-control">
            </div>
            <div class="analytics-filter-group analytics-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm">Primeni</button>
            </div>
            <!-- Quick presets -->
            <div class="analytics-presets">
                <a href="?date_from=<?= date('Y-m-d') ?>&date_to=<?= date('Y-m-d') ?>" class="analytics-preset">Danas</a>
                <a href="?date_from=<?= date('Y-m-d', strtotime('-7 days')) ?>&date_to=<?= date('Y-m-d') ?>" class="analytics-preset">7 dana</a>
                <a href="?date_from=<?= date('Y-m-d', strtotime('-30 days')) ?>&date_to=<?= date('Y-m-d') ?>" class="analytics-preset">30 dana</a>
                <a href="?date_from=<?= date('Y-m-d', strtotime('-90 days')) ?>&date_to=<?= date('Y-m-d') ?>" class="analytics-preset">90 dana</a>
                <a href="?date_from=<?= date('Y-01-01') ?>&date_to=<?= date('Y-m-d') ?>" class="analytics-preset">Ova godina</a>
            </div>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="analytics-kpi-grid">
        <div class="analytics-kpi">
            <div class="analytics-kpi__icon analytics-kpi__icon--gold">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div class="analytics-kpi__body">
                <span class="analytics-kpi__label">Ukupan prihod</span>
                <span class="analytics-kpi__value"><?= formatPrice((float) ($revAgg['total_revenue'] ?? 0)) ?></span>
                <?php if ($revChange != 0): ?>
                <span class="analytics-kpi__change <?= $revChange >= 0 ? 'up' : 'down' ?>">
                    <?= $revChange >= 0 ? '↑' : '↓' ?> <?= abs($revChange) ?>%
                    <span>vs preth. period</span>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="analytics-kpi">
            <div class="analytics-kpi__icon analytics-kpi__icon--blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <div class="analytics-kpi__body">
                <span class="analytics-kpi__label">Porudžbine</span>
                <span class="analytics-kpi__value"><?= (int) ($revAgg['total_orders'] ?? 0) ?></span>
                <?php if ($ordChange != 0): ?>
                <span class="analytics-kpi__change <?= $ordChange >= 0 ? 'up' : 'down' ?>">
                    <?= $ordChange >= 0 ? '↑' : '↓' ?> <?= abs($ordChange) ?>%
                    <span>vs preth. period</span>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="analytics-kpi">
            <div class="analytics-kpi__icon analytics-kpi__icon--green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
            </div>
            <div class="analytics-kpi__body">
                <span class="analytics-kpi__label">Prosečna vrednost</span>
                <span class="analytics-kpi__value"><?= formatPrice((float) ($revAgg['avg_order'] ?? 0)) ?></span>
            </div>
        </div>
        <div class="analytics-kpi">
            <div class="analytics-kpi__icon analytics-kpi__icon--purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div class="analytics-kpi__body">
                <span class="analytics-kpi__label">Novi korisnici</span>
                <span class="analytics-kpi__value"><?= $newUsersPeriod ?></span>
            </div>
        </div>
        <div class="analytics-kpi">
            <div class="analytics-kpi__icon analytics-kpi__icon--teal">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div class="analytics-kpi__body">
                <span class="analytics-kpi__label">Novi pretplatnici</span>
                <span class="analytics-kpi__value"><?= $newSubsPeriod ?></span>
            </div>
        </div>
        <div class="analytics-kpi">
            <div class="analytics-kpi__icon analytics-kpi__icon--rose">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div class="analytics-kpi__body">
                <span class="analytics-kpi__label">Registrovani kupci</span>
                <span class="analytics-kpi__value"><?= $regRate ?>%</span>
            </div>
        </div>
    </div>

    <!-- Row: Revenue Chart + Orders by Status -->
    <div class="analytics-grid analytics-grid--2-1">
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Prihod i porudžbine po danima</h3>
            </div>
            <div class="analytics-card__body">
                <canvas id="chart-revenue" height="300"></canvas>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Status porudžbina</h3>
            </div>
            <div class="analytics-card__body analytics-card__body--center">
                <canvas id="chart-status" height="260"></canvas>
            </div>
        </div>
    </div>

    <!-- Row: Monthly Trend + Guest vs Registered + Payment Methods -->
    <div class="analytics-grid analytics-grid--3">
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Mesečni trend (12 meseci)</h3>
            </div>
            <div class="analytics-card__body">
                <canvas id="chart-monthly" height="260"></canvas>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Gost vs Registrovan</h3>
            </div>
            <div class="analytics-card__body analytics-card__body--center">
                <canvas id="chart-guest-reg" height="220"></canvas>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Način plaćanja</h3>
            </div>
            <div class="analytics-card__body analytics-card__body--center">
                <canvas id="chart-payment" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Row: Top Products + Top Categories -->
    <div class="analytics-grid analytics-grid--2">
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Top proizvodi</h3>
            </div>
            <div class="analytics-card__body analytics-card__body--no-padding">
                <div class="table-responsive">
                    <table class="admin-table analytics-table">
                        <thead>
                            <tr><th>#</th><th>Proizvod</th><th>SKU</th><th>Prodato</th><th>Prihod</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $i => $tp): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="analytics-table__product"><?= htmlspecialchars($tp['product_name']) ?></td>
                                <td><code><?= htmlspecialchars($tp['product_sku'] ?? '—') ?></code></td>
                                <td><strong><?= (int) $tp['total_qty'] ?></strong></td>
                                <td><?= formatPrice((float) $tp['total_revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topProducts)): ?>
                            <tr><td colspan="5" class="text-muted text-center">Nema podataka za izabrani period.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Top kategorije</h3>
            </div>
            <div class="analytics-card__body analytics-card__body--no-padding">
                <div class="table-responsive">
                    <table class="admin-table analytics-table">
                        <thead>
                            <tr><th>#</th><th>Kategorija</th><th>Prodato</th><th>Prihod</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topCats as $i => $tc): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($tc['name']) ?></td>
                                <td><strong><?= (int) $tc['items_sold'] ?></strong></td>
                                <td><?= formatPrice((float) $tc['revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topCats)): ?>
                            <tr><td colspan="4" class="text-muted text-center">Nema podataka.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Row: Top Brands + Low Stock -->
    <div class="analytics-grid analytics-grid--2">
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Top brendovi</h3>
            </div>
            <div class="analytics-card__body analytics-card__body--no-padding">
                <div class="table-responsive">
                    <table class="admin-table analytics-table">
                        <thead>
                            <tr><th>#</th><th>Brend</th><th>Prodato</th><th>Prihod</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topBrands as $i => $tb): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($tb['name']) ?></td>
                                <td><strong><?= (int) $tb['items_sold'] ?></strong></td>
                                <td><?= formatPrice((float) $tb['revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topBrands)): ?>
                            <tr><td colspan="4" class="text-muted text-center">Nema podataka.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="analytics-card">
            <div class="analytics-card__header">
                <h3>Nizak nivo zaliha</h3>
                <span class="analytics-badge analytics-badge--warn"><?= count($lowStock) ?> artikala</span>
            </div>
            <div class="analytics-card__body analytics-card__body--no-padding">
                <div class="table-responsive">
                    <table class="admin-table analytics-table">
                        <thead>
                            <tr><th>Proizvod</th><th>SKU</th><th>Na stanju</th><th>Prag</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStock as $ls): ?>
                            <tr>
                                <td><?= htmlspecialchars($ls['name']) ?></td>
                                <td><code><?= htmlspecialchars($ls['sku'] ?? '—') ?></code></td>
                                <td><strong class="<?= (int) $ls['quantity'] === 0 ? 'text-danger' : 'text-warning' ?>"><?= (int) $ls['quantity'] ?></strong></td>
                                <td><?= (int) $ls['low_stock_threshold'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($lowStock)): ?>
                            <tr><td colspan="4" class="text-muted text-center">Sve zalihe su u redu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="analytics-card">
        <div class="analytics-card__header">
            <h3>Poslednje porudžbine</h3>
            <a href="/admin/orders" class="btn btn-sm btn-secondary">Sve porudžbine →</a>
        </div>
        <div class="analytics-card__body analytics-card__body--no-padding">
            <div class="table-responsive">
                <table class="admin-table analytics-table">
                    <thead>
                        <tr><th>Broj</th><th>Kupac</th><th>Ukupno</th><th>Status</th><th>Datum</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrd as $ro): ?>
                        <tr>
                            <td><a href="/admin/order?id=<?= (int) $ro['id'] ?>">#<?= htmlspecialchars($ro['order_number']) ?></a></td>
                            <td><?= htmlspecialchars($ro['customer_name']) ?></td>
                            <td><?= formatPrice((float) $ro['total_price']) ?></td>
                            <td><span class="analytics-status analytics-status--<?= $ro['status'] ?>"><?= $statusLabels[$ro['status']] ?? $ro['status'] ?></span></td>
                            <td><?= formatDate($ro['created_at'], 'd.m.Y H:i') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Global Stats Footer -->
    <div class="analytics-global-stats">
        <div class="analytics-global-stat">
            <span class="analytics-global-stat__value"><?= (int) ($stats['total_users'] ?? 0) ?></span>
            <span class="analytics-global-stat__label">Ukupno korisnika</span>
        </div>
        <div class="analytics-global-stat">
            <span class="analytics-global-stat__value"><?= (int) ($stats['total_subscribers'] ?? 0) ?></span>
            <span class="analytics-global-stat__label">Email pretplatnika</span>
        </div>
        <div class="analytics-global-stat">
            <span class="analytics-global-stat__value"><?= (int) ($stats['loyalty_users'] ?? 0) ?></span>
            <span class="analytics-global-stat__label">Loyalty korisnika</span>
        </div>
        <div class="analytics-global-stat">
            <span class="analytics-global-stat__value"><?= formatPrice((float) ($revAgg['max_order'] ?? 0)) ?></span>
            <span class="analytics-global-stat__label">Najveća porudžbina</span>
        </div>
    </div>

</div><!-- /.analytics-page -->

<!-- Inline chart data -->
<script>
window.__analyticsData = {
    revenueByDay: {
        labels: <?= json_encode(array_column($ordersByDayData, 'date'), JSON_THROW_ON_ERROR) ?>,
        revenue: <?= json_encode(array_map(fn($d) => (float) $d['revenue'], $ordersByDayData), JSON_THROW_ON_ERROR) ?>,
        orders: <?= json_encode(array_map(fn($d) => (int) $d['orders'], $ordersByDayData), JSON_THROW_ON_ERROR) ?>
    },
    orderStatus: {
        labels: <?= json_encode(array_map(fn($s) => $statusLabels[$s['status']] ?? $s['status'], $statusBreakdown), JSON_THROW_ON_ERROR) ?>,
        data: <?= json_encode(array_map(fn($s) => (int) $s['cnt'], $statusBreakdown), JSON_THROW_ON_ERROR) ?>,
        colors: <?= json_encode(array_map(fn($s) => $statusColors[$s['status']] ?? '#6B7280', $statusBreakdown), JSON_THROW_ON_ERROR) ?>
    },
    monthly: {
        labels: <?= json_encode(array_column($monthlyTrend, 'month'), JSON_THROW_ON_ERROR) ?>,
        revenue: <?= json_encode(array_map(fn($m) => (float) $m['revenue'], $monthlyTrend), JSON_THROW_ON_ERROR) ?>,
        orders: <?= json_encode(array_map(fn($m) => (int) $m['orders'], $monthlyTrend), JSON_THROW_ON_ERROR) ?>
    },
    guestVsReg: {
        guest: <?= (int) ($guestVsReg['guest'] ?? 0) ?>,
        registered: <?= (int) ($guestVsReg['registered'] ?? 0) ?>
    },
    payment: {
        labels: <?= json_encode(array_column($paymentMethods, 'payment_method'), JSON_THROW_ON_ERROR) ?>,
        data: <?= json_encode(array_map(fn($p) => (int) $p['cnt'], $paymentMethods), JSON_THROW_ON_ERROR) ?>,
        revenue: <?= json_encode(array_map(fn($p) => (float) $p['revenue'], $paymentMethods), JSON_THROW_ON_ERROR) ?>
    }
};
</script>
<script src="<?= asset('/js/admin-analytics.js') ?>"></script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
