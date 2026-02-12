<?php
declare(strict_types=1);
$title = 'Pretraga | Egoire';
$query = inputString('q');
$products = [];
if ($query && strlen($query) >= 2) {
    $products = fetchProducts(['search' => $query, 'active' => true, 'limit' => 24]);
}
require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero"><div class="container"><h1>Pretraga</h1></div></section>

<section class="section">
    <div class="container">
        <form method="GET" action="/search" class="search-form-large">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Pretražite proizvode..." class="form-control" autofocus>
            <button type="submit" class="btn btn-primary">Traži</button>
        </form>

        <?php if ($query): ?>
        <p class="search-results-count"><?= count($products) ?> rezultata za "<?= htmlspecialchars($query) ?>"</p>

        <div class="product-grid">
            <?php foreach ($products as $p):
                $imgs = fetchProductImages((int) $p['id']);
            ?>
            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="product-card">
                <div class="product-image">
                    <?php if (!empty($imgs)): ?>
                    <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <div class="no-image-placeholder">Egoire</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                    <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="product-price">
                        <span class="price-current"><?= formatPrice((float) ($p['sale_price'] ?: $p['price'])) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
        <div class="empty-state"><p>Nema rezultata za vašu pretragu.</p><a href="/products" class="btn btn-outline">Pogledaj sve proizvode</a></div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
