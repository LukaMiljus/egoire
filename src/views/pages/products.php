<?php
declare(strict_types=1);
$title = 'Proizvodi | Egoire';

$page = inputInt('page', 1);
$filters = [
    'active'      => true,
    'search'      => inputString('search'),
    'brand_id'    => inputInt('brand_id') ?: null,
    'category_id' => inputInt('category_id') ?: null,
    'price_min'   => inputFloat('price_min') ?: null,
    'price_max'   => inputFloat('price_max') ?: null,
    'sort'        => inputString('sort'),
];
$flagFilter = inputString('flag');
if ($flagFilter) $filters['flags'] = [$flagFilter];

$filters = array_filter($filters, fn($v) => $v !== null && $v !== '' && $v !== false);
$filters['active'] = true;

$total = countProducts($filters);
$pagination = paginate($total, 12, $page);
$filters['limit'] = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$products = fetchProducts($filters);

$categories = fetchCategories();
$brands = fetchBrands();

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Proizvodi</h1>
        <p><?= $total ?> proizvoda</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar">
                <form method="GET" action="/products" id="filterForm">
                    <?php if (inputString('search')): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars(inputString('search')) ?>">
                    <?php endif; ?>

                    <div class="filter-block">
                        <h4>Kategorije</h4>
                        <ul class="filter-list">
                            <?php foreach ($categories as $c): ?>
                            <li>
                                <label>
                                    <input type="radio" name="category_id" value="<?= $c['id'] ?>" <?= inputInt('category_id') === (int) $c['id'] ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </label>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-block">
                        <h4>Brendovi</h4>
                        <ul class="filter-list">
                            <?php foreach ($brands as $b): ?>
                            <li>
                                <label>
                                    <input type="radio" name="brand_id" value="<?= $b['id'] ?>" <?= inputInt('brand_id') === (int) $b['id'] ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </label>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-block">
                        <h4>Cena (RSD)</h4>
                        <div class="price-range">
                            <input type="number" name="price_min" placeholder="Od" value="<?= inputString('price_min') ?>" class="form-control">
                            <span>—</span>
                            <input type="number" name="price_max" placeholder="Do" value="<?= inputString('price_max') ?>" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Filtriraj</button>
                    <a href="/products" class="btn btn-secondary btn-block mt-2">Resetuj</a>
                </form>
            </aside>

            <!-- Products -->
            <div class="shop-main">
                <div class="shop-toolbar">
                    <div class="sort-select">
                        <select name="sort" onchange="window.location=this.value">
                            <option value="/products?<?= http_build_query(array_merge($_GET, ['sort' => ''])) ?>">Podrazumevano</option>
                            <option value="/products?<?= http_build_query(array_merge($_GET, ['sort' => 'price_asc'])) ?>" <?= inputString('sort') === 'price_asc' ? 'selected' : '' ?>>Cena: niska → visoka</option>
                            <option value="/products?<?= http_build_query(array_merge($_GET, ['sort' => 'price_desc'])) ?>" <?= inputString('sort') === 'price_desc' ? 'selected' : '' ?>>Cena: visoka → niska</option>
                            <option value="/products?<?= http_build_query(array_merge($_GET, ['sort' => 'newest'])) ?>" <?= inputString('sort') === 'newest' ? 'selected' : '' ?>>Najnovije</option>
                            <option value="/products?<?= http_build_query(array_merge($_GET, ['sort' => 'name_asc'])) ?>" <?= inputString('sort') === 'name_asc' ? 'selected' : '' ?>>Naziv A-Z</option>
                        </select>
                    </div>
                </div>

                <div class="product-grid">
                    <?php foreach ($products as $p):
                        $imgs = fetchProductImages((int) $p['id']);
                        $flags = fetchProductFlags((int) $p['id']);
                    ?>
                    <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="product-card">
                        <div class="product-image">
                            <?php if (!empty($imgs)): ?>
                            <img src="<?= htmlspecialchars($imgs[0]['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="no-image-placeholder">Egoire</div>
                            <?php endif; ?>
                            <?php if ($flags): ?>
                            <div class="product-badges">
                                <?php foreach ($flags as $f): ?>
                                <span class="product-badge badge-<?= $f['flag'] ?>"><?= ucfirst($f['flag']) ?></span>
                                <?php endforeach; ?>
                            </div>
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
                <div class="empty-state">
                    <p>Nema proizvoda za zadate filtere.</p>
                    <a href="/products" class="btn btn-outline">Pogledaj sve</a>
                </div>
                <?php endif; ?>

                <?= renderPagination($pagination, '/products') ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
