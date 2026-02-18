<?php
/* ============================================================
   Egoire – Luxury Brands Directory
   View:  src/views/pages/brands.php
   CSS:   public/css/brands.css
   ============================================================ */
declare(strict_types=1);

$title = 'Naši brendovi | Egoire';

/* --- Page-specific assets --- */
$pageStyles = ['/css/brands.css'];

/* --- Data --- */
$brands = fetchBrands(['active_only' => true]);

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="br-hero">
    <div class="br-hero__inner">
        <span class="br-hero__label">Kolekcija</span>
        <h1 class="br-hero__title">Naši brendovi</h1>
        <p class="br-hero__text">Pažljivo birani svetski brendovi profesionalne nege kose — svaki sa jedinstvenom filozofijom lepote, kvaliteta i inovacije.</p>
    </div>
</section>

<!-- ============================================================
     BRANDS GRID
     ============================================================ -->
<section class="br-section">
    <div class="br-container">

        <?php if ($brands): ?>
        <div class="br-grid">
            <?php foreach ($brands as $b): ?>
            <a href="/brand/<?= htmlspecialchars($b['slug']) ?>" class="br-card">
                <div class="br-card__visual">
                    <?php if ($b['logo']): ?>
                    <img src="<?= htmlspecialchars($b['logo']) ?>"
                         alt="<?= htmlspecialchars($b['name']) ?>"
                         class="br-card__logo"
                         loading="lazy"
                         draggable="false">
                    <?php else: ?>
                    <span class="br-card__fallback"><?= htmlspecialchars($b['name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="br-card__body">
                    <h2 class="br-card__name"><?= htmlspecialchars($b['name']) ?></h2>
                    <?php if (!empty($b['description'])): ?>
                    <p class="br-card__desc"><?= htmlspecialchars(strip_tags($b['description'])) ?></p>
                    <?php endif; ?>
                    <span class="br-card__link">
                        Istraži kolekciju
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="br-empty">
            <p class="br-empty__text">Trenutno nema dostupnih brendova.</p>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
