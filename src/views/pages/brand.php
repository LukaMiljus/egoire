<?php
/* ============================================================
   Egoire – Brand Product Listing
   View:  src/views/pages/brand.php
   CSS:   public/css/brand.css + public/css/product-card.css
   JS:    public/js/product-card.js
   ============================================================ */
declare(strict_types=1);

$slug  = $routeParams['slug'] ?? '';
$brand = fetchBrandBySlug($slug);
if (!$brand) { http_response_code(404); require __DIR__ . '/404.php'; return; }

$title = ($brand['meta_title'] ?: $brand['name']) . ' | Egoire';

/* --- Page-specific assets --- */
$pageStyles  = ['/css/product-card.css', '/css/brand.css'];
$pageScripts = ['/js/product-card.js'];

/* --- Data --- */
$page    = inputInt('page', 1);
$filters = [
    'brand_id' => $brand['id'],
    'active'   => true,
    'sort'     => inputString('sort'),
];
$total      = countProducts($filters);
$pagination = paginate($total, 12, $page);
$filters['limit']  = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$products   = fetchProducts($filters);

/* Sort labels */
$sortLabels = [
    ''           => 'Preporučeno',
    'price_asc'  => 'Cena: niska → visoka',
    'price_desc' => 'Cena: visoka → niska',
    'newest'     => 'Najnovije',
    'name_asc'   => 'Naziv A – Ž',
];
$currentSort = inputString('sort');

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     BRAND HERO
     ============================================================ -->
<section class="brand-hero">
    <div class="brand-hero__inner">
        <?php if ($brand['logo']): ?>
        <img src="<?= htmlspecialchars($brand['logo']) ?>"
             alt="<?= htmlspecialchars($brand['name']) ?>"
             class="brand-hero__logo">
        <?php endif; ?>

        <h1 class="brand-hero__title"><?= htmlspecialchars($brand['name']) ?></h1>

        <?php if ($brand['description']): ?>
        <div class="brand-hero__desc">
            <?= $brand['description'] ?>
        </div>
        <?php endif; ?>

        <span class="brand-hero__count">
            <?= $total ?> <?= $total === 1 ? 'proizvod' : 'proizvoda' ?>
        </span>
    </div>
</section>

<!-- ============================================================
     BRAND PRODUCTS GRID
     ============================================================ -->
<section class="brand-products">
    <div class="brand-products__container">

        <!-- Toolbar -->
        <div class="brand-toolbar">
            <div class="brand-toolbar__sort">
                <label class="brand-toolbar__label" for="brandSort">Sortiraj:</label>
                <select class="brand-toolbar__select" id="brandSort" name="sort">
                    <?php foreach ($sortLabels as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val) ?>" <?= $currentSort === $val ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($products): ?>
        <div class="pc-grid">
            <?php foreach ($products as $p):
                $cardProduct = $p;
                $cardImages  = fetchProductImages((int) $p['id']);
                $cardFlags   = fetchProductFlags((int) $p['id']);
                $cardVariant = 'default';
                include __DIR__ . '/../components/product-card.php';
            endforeach; ?>
        </div>

        <!-- Pagination -->
        <?= renderPagination($pagination, '/brand/' . $slug) ?>

        <?php else: ?>
        <div class="brand-empty">
            <h3 class="brand-empty__title">Nema proizvoda</h3>
            <p class="brand-empty__text">Trenutno nema proizvoda za ovaj brend.</p>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
