<?php
declare(strict_types=1);
$slug = $routeParams['slug'] ?? '';
$brand = fetchBrandBySlug($slug);
if (!$brand) { http_response_code(404); require __DIR__ . '/404.php'; return; }

$title = ($brand['meta_title'] ?: $brand['name']) . ' | Egoire';
$page = inputInt('page', 1);
$filters = ['brand_id' => $brand['id'], 'active' => true, 'sort' => inputString('sort')];
$total = countProducts($filters);
$pagination = paginate($total, 12, $page);
$filters['limit'] = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$products = fetchProducts($filters);

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero">
    <div class="container">
        <?php if ($brand['logo']): ?>
        <img src="<?= htmlspecialchars($brand['logo']) ?>" alt="<?= htmlspecialchars($brand['name']) ?>" class="brand-hero-logo">
        <?php endif; ?>
        <h1><?= htmlspecialchars($brand['name']) ?></h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($brand['description']): ?>
        <div class="content-block mb-4">
            <?= $brand['description'] ?>
        </div>
        <?php endif; ?>

        <div class="shop-toolbar">
            <span><?= $total ?> proizvoda</span>
        </div>

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
                    <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="product-price">
                        <?php if ($p['sale_price']): ?>
                        <span class="price-old"><?= formatPrice((float) $p['price']) ?></span>
                        <span class="price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                        <?php else: ?>
                        <span class="price-current"><?= formatPrice((float) $p['price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
        <div class="empty-state"><p>Nema proizvoda za ovaj brend.</p></div>
        <?php endif; ?>

        <?= renderPagination($pagination, '/brand/' . $slug) ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
