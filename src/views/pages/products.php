<?php
/* ============================================================
   Egoire – Luxury Products Listing
   View:  src/views/pages/products.php
   ============================================================ */
declare(strict_types=1);

$title = 'Proizvodi | Egoire';

/* --- Page-specific assets --- */
$pageStyles  = ['/css/products.css'];
$pageScripts = ['/js/products.js'];

/* --- Collect filters from query string --- */
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

$onSale = inputString('on_sale');
if ($onSale === '1') $filters['on_sale'] = true;

$filters = array_filter($filters, fn($v) => $v !== null && $v !== '' && $v !== false);
$filters['active'] = true;

/* --- Data --- */
$total      = countProducts($filters);
$pagination = paginate($total, 12, $page);
$filters['limit']  = $pagination['per_page'];
$filters['offset'] = $pagination['offset'];
$products   = fetchProducts($filters);

$allCategories    = fetchCategories(['active_only' => true]);
$parentCategories = fetchCategories(['parent_id' => null, 'active_only' => true]);
$allBrands        = fetchBrands(['active_only' => true]);

/* --- Active filter labels (for UI display) --- */
$activeFilters = [];
if (inputInt('category_id')) {
    $activeCat = fetchCategoryById(inputInt('category_id'));
    if ($activeCat) $activeFilters[] = ['key' => 'category_id', 'label' => $activeCat['name']];
}
if (inputInt('brand_id')) {
    foreach ($allBrands as $b) {
        if ((int)$b['id'] === inputInt('brand_id')) {
            $activeFilters[] = ['key' => 'brand_id', 'label' => $b['name']];
            break;
        }
    }
}
if ($flagFilter) {
    $flagLabels = ['best_seller' => 'Bestseller', 'new' => 'Novo'];
    $activeFilters[] = ['key' => 'flag', 'label' => $flagLabels[$flagFilter] ?? ucfirst($flagFilter)];
}
if ($onSale === '1') {
    $activeFilters[] = ['key' => 'on_sale', 'label' => 'Na akciji'];
}
if (inputString('price_min') || inputString('price_max')) {
    $prLabel = '';
    if (inputString('price_min')) $prLabel .= 'od ' . inputString('price_min');
    if (inputString('price_max')) $prLabel .= ($prLabel ? ' ' : '') . 'do ' . inputString('price_max');
    $activeFilters[] = ['key' => 'price', 'label' => $prLabel . ' RSD'];
}
if (inputString('search')) {
    $activeFilters[] = ['key' => 'search', 'label' => '„' . inputString('search') . '"'];
}

/* Sort label map */
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
     FILTER OVERLAY + SLIDE-IN PANEL
     ============================================================ -->
<div class="ep-filter-overlay" id="epFilterOverlay"></div>

<aside class="ep-filter-panel" id="epFilterPanel" aria-label="Filteri">
    <div class="ep-filter-panel__head">
        <h3 class="ep-filter-panel__title">Filteri</h3>
        <button class="ep-filter-panel__close" id="epFilterClose" aria-label="Zatvori filtere">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
        </button>
    </div>

    <form class="ep-filter-panel__body" method="GET" action="/products" id="epFilterForm">
        <?php if (inputString('search')): ?>
        <input type="hidden" name="search" value="<?= htmlspecialchars(inputString('search')) ?>">
        <?php endif; ?>

        <!-- Kategorije -->
        <div class="ep-accordion" data-accordion>
            <button class="ep-accordion__trigger" type="button">
                <span>Kategorije</span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
            </button>
            <div class="ep-accordion__content">
                <?php foreach ($parentCategories as $pc): ?>
                <div class="ep-filter-group">
                    <span class="ep-filter-group__title"><?= htmlspecialchars($pc['name']) ?></span>
                    <?php $subs = fetchSubcategories((int) $pc['id']); ?>
                    <?php foreach ($subs as $sub): ?>
                    <label class="ep-checkbox">
                        <input type="radio" name="category_id" value="<?= (int) $sub['id'] ?>"
                            <?= inputInt('category_id') === (int) $sub['id'] ? 'checked' : '' ?>>
                        <span class="ep-checkbox__mark"></span>
                        <span class="ep-checkbox__label"><?= htmlspecialchars($sub['name']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Brendovi -->
        <div class="ep-accordion" data-accordion>
            <button class="ep-accordion__trigger" type="button">
                <span>Brendovi</span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
            </button>
            <div class="ep-accordion__content">
                <?php foreach ($allBrands as $b): ?>
                <label class="ep-checkbox">
                    <input type="radio" name="brand_id" value="<?= (int) $b['id'] ?>"
                        <?= inputInt('brand_id') === (int) $b['id'] ? 'checked' : '' ?>>
                    <span class="ep-checkbox__mark"></span>
                    <span class="ep-checkbox__label"><?= htmlspecialchars($b['name']) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cena -->
        <div class="ep-accordion" data-accordion>
            <button class="ep-accordion__trigger" type="button">
                <span>Cena</span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
            </button>
            <div class="ep-accordion__content">
                <div class="ep-price-range">
                    <div class="ep-price-range__field">
                        <label class="ep-price-range__label">Od</label>
                        <input type="number" name="price_min" class="ep-price-range__input"
                               placeholder="0" value="<?= htmlspecialchars(inputString('price_min')) ?>">
                    </div>
                    <span class="ep-price-range__sep">—</span>
                    <div class="ep-price-range__field">
                        <label class="ep-price-range__label">Do</label>
                        <input type="number" name="price_max" class="ep-price-range__input"
                               placeholder="∞" value="<?= htmlspecialchars(inputString('price_max')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Oznake -->
        <div class="ep-accordion" data-accordion>
            <button class="ep-accordion__trigger" type="button">
                <span>Oznake</span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
            </button>
            <div class="ep-accordion__content">
                <label class="ep-checkbox">
                    <input type="checkbox" name="on_sale" value="1"
                        <?= $onSale === '1' ? 'checked' : '' ?>>
                    <span class="ep-checkbox__mark"></span>
                    <span class="ep-checkbox__label">Na akciji</span>
                </label>
                <label class="ep-checkbox">
                    <input type="radio" name="flag" value="best_seller"
                        <?= $flagFilter === 'best_seller' ? 'checked' : '' ?>>
                    <span class="ep-checkbox__mark"></span>
                    <span class="ep-checkbox__label">Bestseller</span>
                </label>
                <label class="ep-checkbox">
                    <input type="radio" name="flag" value="new"
                        <?= $flagFilter === 'new' ? 'checked' : '' ?>>
                    <span class="ep-checkbox__mark"></span>
                    <span class="ep-checkbox__label">Novo</span>
                </label>
            </div>
        </div>

        <div class="ep-filter-panel__actions">
            <button type="submit" class="ep-btn ep-btn--primary ep-btn--full">Primeni filtere</button>
            <a href="/products" class="ep-btn ep-btn--ghost ep-btn--full">Resetuj sve</a>
        </div>
    </form>
</aside>

<!-- ============================================================
     STICKY FILTER TRIGGER
     ============================================================ -->
<button class="ep-filter-trigger" id="epFilterTrigger" aria-label="Otvori filtere">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
        <line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="16" y2="12"/><line x1="4" y1="18" x2="12" y2="18"/>
    </svg>
    <span class="ep-filter-trigger__text">Filteri</span>
    <?php if ($activeFilters): ?>
    <span class="ep-filter-trigger__count"><?= count($activeFilters) ?></span>
    <?php endif; ?>
</button>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<section class="ep-hero" data-reveal>
    <div class="ep-container">
        <div class="ep-hero__content">
            <h1 class="ep-hero__title">Proizvodi</h1>
            <p>Pažljivo odabrani premium proizvodi za negu i transformaciju vaše kose.</p>
            <p class="ep-hero__count"><?= $total ?> <?= $total === 1 ? 'proizvod' : ($total < 5 && $total > 1 ? 'proizvoda' : 'proizvoda') ?></p>
        </div>

        <div class="ep-toolbar">
            <!-- Active filters -->
            <?php if ($activeFilters): ?>
            <div class="ep-active-filters">
                <?php foreach ($activeFilters as $af): ?>
                <span class="ep-active-filters__tag">
                    <?= htmlspecialchars($af['label']) ?>
                    <a href="<?= removeQueryParam($af['key']) ?>" class="ep-active-filters__remove" aria-label="Ukloni filter">×</a>
                </span>
                <?php endforeach; ?>
                <a href="/products" class="ep-active-filters__clear">Obriši sve</a>
            </div>
            <?php endif; ?>

            <!-- Sort -->
            <div class="ep-sort">
                <label class="ep-sort__label" for="epSort">Sortiraj:</label>
                <div class="ep-sort__select-wrap">
                    <select class="ep-sort__select" id="epSort" name="sort">
                        <?php foreach ($sortLabels as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $currentSort === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <svg class="ep-sort__chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     PRODUCT GRID
     ============================================================ -->
<section class="ep-section">
    <div class="ep-container">

        <?php if ($products): ?>
        <div class="ep-grid">
            <?php foreach ($products as $index => $p):
                $imgs  = fetchProductImages((int) $p['id']);
                $flags = fetchProductFlags((int) $p['id']);
                $salePercent = ($p['sale_price'] && $p['price'] > 0)
                    ? round((1 - (float)$p['sale_price'] / (float)$p['price']) * 100)
                    : 0;
            ?>
            <article class="ep-card" data-href="/product/<?= htmlspecialchars($p['slug']) ?>" data-product-id="<?= (int) $p['id'] ?>">
                <!-- Visual -->
                <div class="ep-card__visual">
                    <?php if (!empty($imgs)): ?>
                    <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>"
                         class="ep-card__img"
                         loading="lazy">
                    <?php else: ?>
                    <div class="ep-card__placeholder">
                        <span>Egoire</span>
                    </div>
                    <?php endif; ?>

                    <?php if ($flags || $salePercent): ?>
                    <div class="ep-card__badges">
                        <?php if ($salePercent): ?>
                        <span class="ep-badge ep-badge--sale">-<?= $salePercent ?>%</span>
                        <?php endif; ?>
                        <?php if ($flags): foreach ($flags as $f): ?>
                        <span class="ep-badge ep-badge--<?= $f ?>"><?= ucfirst(str_replace('_', ' ', $f)) ?></span>
                        <?php endforeach; endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Quick view hover overlay (desktop) -->
                    <div class="ep-card__hover-action">
                        <span>Pogledaj</span>
                    </div>
                </div>

                <!-- Body -->
                <div class="ep-card__body">
                    <span class="ep-card__brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                    <h3 class="ep-card__name"><?= htmlspecialchars($p['name']) ?></h3>

                    <?php if (!empty($p['short_description'])): ?>
                    <p class="ep-card__desc"><?= htmlspecialchars(truncate($p['short_description'], 75)) ?></p>
                    <?php endif; ?>

                    <div class="ep-card__price">
                        <?php if ($p['sale_price']): ?>
                        <span class="ep-card__price-old"><?= formatPrice((float) $p['price']) ?></span>
                        <span class="ep-card__price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                        <?php else: ?>
                        <span class="ep-card__price-current"><?= formatPrice((float) $p['price']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Actions (stop propagation zone) -->
                    <div class="ep-card__actions" data-stop-propagation>
                        <div class="ep-stepper">
                            <button class="ep-stepper__btn" type="button" data-action="minus" aria-label="Smanji količinu">−</button>
                            <input  class="ep-stepper__input" type="number" value="1" min="1" max="99" aria-label="Količina">
                            <button class="ep-stepper__btn" type="button" data-action="plus" aria-label="Povećaj količinu">+</button>
                        </div>
                        <button class="ep-card__add-btn" type="button" data-add-to-cart>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            <span>Dodaj u korpu</span>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?= renderPagination($pagination, '/products') ?>

        <?php else: ?>
        <div class="ep-empty">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <h3 class="ep-empty__title">Nema rezultata</h3>
            <p class="ep-empty__text">Nismo pronašli proizvode za zadate filtere.</p>
            <a href="/products" class="ep-btn ep-btn--outline">Pogledaj sve proizvode</a>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
