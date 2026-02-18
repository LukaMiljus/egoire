<?php
/* ============================================================
   Egoire — Luxury Search Results Page
   Layout: Hero + product grid using reusable product-card
   CSS:    public/css/search-results.css
   ============================================================ */
declare(strict_types=1);

$query      = trim(inputString('q'));
$title      = $query ? 'Pretraga: ' . $query : 'Pretraga';
$pageStyles = ['/css/search-results.css', '/css/product-card.css'];
$pageScripts = ['/js/product-card.js'];
$products   = [];

if ($query && mb_strlen($query) >= 2) {
    $products = fetchProducts([
        'search' => $query,
        'active' => true,
        'limit'  => 24,
    ]);
}

$resultCount = count($products);

require __DIR__ . '/../layout/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     SEARCH RESULTS — Hero + Grid
═══════════════════════════════════════════════════════════ -->

<!-- Hero -->
<section class="lx-search-hero">
    <div class="lx-search-hero__container">
        <p class="lx-search-hero__eyebrow">Pretraga</p>
        <h1 class="lx-search-hero__title">Rezultati pretrage</h1>

        <?php if ($query): ?>
        <p class="lx-search-hero__meta">
            <span class="lx-search-hero__count"><?= $resultCount ?></span>
            <?= $resultCount === 1 ? 'rezultat' : ($resultCount >= 2 && $resultCount <= 4 ? 'rezultata' : 'rezultata') ?>
            za &ldquo;<span class="lx-search-hero__query"><?= htmlspecialchars($query) ?></span>&rdquo;
        </p>
        <?php endif; ?>

        <!-- Inline search form -->
        <form method="GET" action="/search" class="lx-search-hero__form" autocomplete="off">
            <div class="lx-search-hero__input-wrap">
                <svg class="lx-search-hero__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    name="q"
                    value="<?= htmlspecialchars($query) ?>"
                    placeholder="Pretražite proizvode, brendove, kategorije..."
                    class="lx-search-hero__input"
                    autofocus
                >
                <button type="submit" class="lx-search-hero__submit">
                    <span>Pretraži</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Results -->
<section class="lx-search-results">
    <div class="lx-search-results__container">

        <?php if ($query && $resultCount > 0): ?>

        <!-- Product Grid -->
        <div class="lx-search-grid">
            <?php foreach ($products as $p):
                $cardProduct = $p;
                $cardImages  = fetchProductImages((int) $p['id']);
                $cardFlags   = fetchProductFlags((int) $p['id']);
                $cardVariant = 'default';
                include __DIR__ . '/../components/product-card.php';
            endforeach; ?>
        </div>

        <?php elseif ($query): ?>

        <!-- Empty State -->
        <div class="lx-search-empty">
            <div class="lx-search-empty__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                    <path d="M8 11h6"/>
                </svg>
            </div>
            <h2 class="lx-search-empty__title">Nismo pronašli rezultate</h2>
            <p class="lx-search-empty__desc">
                Nismo pronašli proizvode za upit &ldquo;<?= htmlspecialchars($query) ?>&rdquo;.
                Pokušajte sa drugačijim pojmom ili pretražite naše kategorije.
            </p>
            <div class="lx-search-empty__actions">
                <a href="/products" class="lx-search-btn lx-search-btn--primary">
                    <span>Svi proizvodi</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="/categories" class="lx-search-btn lx-search-btn--outline">Kategorije</a>
                <a href="/brands" class="lx-search-btn lx-search-btn--outline">Brendovi</a>
            </div>
        </div>

        <?php else: ?>

        <!-- Initial state (no query) -->
        <div class="lx-search-empty">
            <div class="lx-search-empty__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
            </div>
            <h2 class="lx-search-empty__title">Pretražite Egoire kolekciju</h2>
            <p class="lx-search-empty__desc">
                Unesite naziv proizvoda, brenda ili kategorije u polje za pretragu iznad.
            </p>
        </div>

        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
