<?php
declare(strict_types=1);
$slug = $routeParams['slug'] ?? '';
$category = fetchCategoryBySlug($slug);
if (!$category) { http_response_code(404); require __DIR__ . '/404.php'; return; }

$title = ($category['meta_title'] ?: $category['name']) . ' | Egoire';
$page = inputInt('page', 1);
$filters = ['category_id' => $category['id'], 'active' => true, 'sort' => inputString('sort')];
$total = countProducts($filters);
$pagination = paginate($total, 12, $page);
$filters['limit'] = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$products = fetchProducts($filters);
$subcats = fetchSubcategories((int) $category['id']);

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero" <?php if ($category['image']): ?>style="background-image: url('<?= htmlspecialchars($category['image']) ?>')"<?php endif; ?>>
    <div class="container">
        <h1><?= htmlspecialchars($category['name']) ?></h1>
        <?php if ($category['description']): ?>
        <p><?= htmlspecialchars(truncate(strip_tags($category['description']), 160)) ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($subcats): ?>
        <div class="subcategory-chips mb-4">
            <?php foreach ($subcats as $sc): ?>
            <a href="/category/<?= htmlspecialchars($sc['slug']) ?>" class="chip"><?= htmlspecialchars($sc['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="shop-toolbar">
            <span><?= $total ?> proizvoda</span>
            <select onchange="location=this.value">
                <option value="/category/<?= htmlspecialchars($slug) ?>">Podrazumevano</option>
                <option value="/category/<?= htmlspecialchars($slug) ?>?sort=price_asc" <?= inputString('sort') === 'price_asc' ? 'selected' : '' ?>>Cena ↑</option>
                <option value="/category/<?= htmlspecialchars($slug) ?>?sort=price_desc" <?= inputString('sort') === 'price_desc' ? 'selected' : '' ?>>Cena ↓</option>
                <option value="/category/<?= htmlspecialchars($slug) ?>?sort=newest" <?= inputString('sort') === 'newest' ? 'selected' : '' ?>>Najnovije</option>
            </select>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $p):
                $imgs = fetchProductImages((int) $p['id']);
            ?>
            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="product-card">
                <div class="product-image">
                    <?php if (!empty($imgs)): ?>
                    <img src="<?= htmlspecialchars($imgs[0]['image_url']) ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <div class="no-image-placeholder">Egoire</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
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
        <div class="empty-state"><p>Nema proizvoda u ovoj kategoriji.</p></div>
        <?php endif; ?>

        <?= renderPagination($pagination, '/category/' . $slug) ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
