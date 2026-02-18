<?php
/* ============================================================
   Egoire – Luxury Categories Page
   View:  src/views/pages/categories.php
   CSS:   public/css/categories.css  (ct- namespace)
   ============================================================ */
declare(strict_types=1);

$title = 'Kategorije | Egoire';

/* --- Page-specific assets --- */
$pageStyles = ['/css/categories.css'];

/* --- Data --- */
$parentCategories = fetchCategories(['parent_id' => null, 'active_only' => true]);

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="ct-hero">
    <div class="ct-hero__inner">
        <span class="ct-hero__label">Asortiman</span>
        <h1 class="ct-hero__title">Kategorije</h1>
        <p class="ct-hero__text">Otkrijte naš pažljivo kurirani izbor premium proizvoda za negu kose — od šampona i tretmana do profesionalnog stajlinga.</p>
    </div>
</section>

<!-- ============================================================
     FEATURED CATEGORIES
     ============================================================ -->
<section class="ct-section">
    <div class="ct-container">

        <?php if ($parentCategories): ?>
        <?php foreach ($parentCategories as $index => $cat):
            $subcategories = fetchSubcategories((int) $cat['id']);
            $isReversed = ($index % 2 !== 0);
        ?>

        <!-- ── Main Category Feature Block ── -->
        <article class="ct-feature <?= $isReversed ? 'ct-feature--reverse' : '' ?>">
            <div class="ct-feature__visual">
                <?php if ($cat['image']): ?>
                <img src="<?= htmlspecialchars($cat['image']) ?>"
                     alt="<?= htmlspecialchars($cat['name']) ?>"
                     class="ct-feature__img"
                     loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                     draggable="false">
                <?php else: ?>
                <div class="ct-feature__placeholder">
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="ct-feature__content">
                <span class="ct-feature__index"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <h2 class="ct-feature__name"><?= htmlspecialchars($cat['name']) ?></h2>

                <?php if ($cat['description']): ?>
                <div class="ct-feature__desc">
                    <?= $cat['description'] ?>
                </div>
                <?php endif; ?>

                <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" class="ct-feature__cta">
                    <span>Pogledaj proizvode</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </article>

        <!-- ── Subcategories Grid ── -->
        <?php if ($subcategories): ?>
        <div class="ct-subs">
            <div class="ct-subs__grid">
                <?php foreach ($subcategories as $sub): ?>
                <a href="/category/<?= htmlspecialchars($sub['slug']) ?>" class="ct-sub">
                    <div class="ct-sub__visual">
                        <?php if ($sub['image']): ?>
                        <img src="<?= htmlspecialchars($sub['image']) ?>"
                             alt="<?= htmlspecialchars($sub['name']) ?>"
                             class="ct-sub__img"
                             loading="lazy"
                             draggable="false">
                        <?php else: ?>
                        <div class="ct-sub__placeholder">
                            <span><?= htmlspecialchars(mb_substr($sub['name'], 0, 1)) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="ct-sub__body">
                        <h3 class="ct-sub__name"><?= htmlspecialchars($sub['name']) ?></h3>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Divider between category groups (not after last) -->
        <?php if ($index < count($parentCategories) - 1): ?>
        <hr class="ct-divider">
        <?php endif; ?>

        <?php endforeach; ?>

        <?php else: ?>
        <div class="ct-empty">
            <p class="ct-empty__text">Trenutno nema dostupnih kategorija.</p>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
