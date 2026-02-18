<?php
/* ============================================================
   Egoire – Luxury Category Detail Page
   View:  src/views/pages/category.php
   CSS:   public/css/category-detail.css  (cd- namespace)
   ============================================================ */
declare(strict_types=1);

$slug     = $routeParams['slug'] ?? '';
$category = fetchCategoryBySlug($slug);
if (!$category) { http_response_code(404); require __DIR__ . '/404.php'; return; }

$title = htmlspecialchars($category['name']) . ' | Egoire';

/* --- Page-specific assets --- */
$pageStyles  = ['/css/product-card.css', '/css/category-detail.css'];
$pageScripts = ['/js/product-card.js'];

/* --- Data --- */
$page    = inputInt('page', 1);
$filters = [
    'category_id' => $category['id'],
    'active'      => true,
    'sort'        => inputString('sort'),
];
$total      = countProducts($filters);
$pagination = paginate($total, 12, $page);
$filters['limit']  = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$products   = fetchProducts($filters);
$subcats    = fetchSubcategories((int) $category['id']);

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
     HERO — Full-width category image with overlay
     ============================================================ -->
<section class="cd-hero<?= $category['image'] ? '' : ' cd-hero--no-image' ?>"
    <?php if ($category['image']): ?>
         style="background-image: url('<?= htmlspecialchars($category['image']) ?>')"
    <?php endif; ?>>
    <div class="cd-hero__overlay"></div>
    <div class="cd-hero__content">
        <h1 class="cd-hero__title"><?= htmlspecialchars($category['name']) ?></h1>
        <?php if ($category['description']): ?>
        <p class="cd-hero__desc"><?= htmlspecialchars(strip_tags($category['description'])) ?></p>
        <?php endif; ?>
        <span class="cd-hero__count"><?= $total ?> <?= $total === 1 ? 'proizvod' : 'proizvoda' ?></span>
    </div>
</section>

<!-- ============================================================
     PRODUCTS SECTION
     ============================================================ -->
<section class="cd-section">
    <div class="cd-container">

        <!-- Subcategory chips -->
        <?php if ($subcats): ?>
        <div class="cd-chips">
            <a href="/category/<?= htmlspecialchars($slug) ?>" class="cd-chip cd-chip--active">Sve</a>
            <?php foreach ($subcats as $sc): ?>
            <a href="/category/<?= htmlspecialchars($sc['slug']) ?>" class="cd-chip"><?= htmlspecialchars($sc['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Toolbar -->
        <div class="cd-toolbar">
            <div class="cd-toolbar__sort">
                <label class="cd-toolbar__label" for="cdSort">Sortiraj:</label>
                <select class="cd-toolbar__select" id="cdSort" name="sort">
                    <?php foreach ($sortLabels as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val) ?>" <?= $currentSort === $val ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Product grid -->
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
        <?= renderPagination($pagination, '/category/' . $slug) ?>

        <?php else: ?>
        <div class="cd-empty">
            <svg class="cd-empty__icon" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.25">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <h3 class="cd-empty__title">Nema proizvoda</h3>
            <p class="cd-empty__text">Trenutno nema proizvoda u ovoj kategoriji.</p>
            <a href="/products" class="cd-empty__link">Pogledaj sve proizvode</a>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- Sort JS (inline — lightweight) -->
<script>
(function(){
    var s = document.getElementById('cdSort');
    if (!s) return;
    s.addEventListener('change', function(){
        var p = new URLSearchParams(window.location.search);
        this.value ? p.set('sort', this.value) : p.delete('sort');
        p.delete('page');
        var q = p.toString();
        window.location.href = window.location.pathname + (q ? '?' + q : '');
    });
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
