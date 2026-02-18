<?php
/* ============================================================
   Egoire – Reusable Luxury Product Card Component
   File:  src/views/components/product-card.php
   CSS:   public/css/product-card.css  (pc- namespace)
   JS:    public/js/product-card.js
   ============================================================
   Required variables (set before include):
     $cardProduct  — product associative array (name, slug, price, sale_price, brand_name, short_description, id …)
     $cardImages   — array from fetchProductImages()
     $cardFlags    — array from fetchProductFlags()
   Optional:
     $cardVariant  — 'default' | 'compact'  (default: 'default')
   ============================================================ */
declare(strict_types=1);

$_p     = $cardProduct;
$_imgs  = $cardImages  ?? [];
$_flags = $cardFlags   ?? [];
$_var   = $cardVariant  ?? 'default';

$_salePercent = ($_p['sale_price'] && (float) $_p['price'] > 0)
    ? (int) round((1 - (float) $_p['sale_price'] / (float) $_p['price']) * 100)
    : 0;
?>

<article class="pc-card<?= $_var === 'compact' ? ' pc-card--compact' : '' ?>"
         data-href="/product/<?= htmlspecialchars($_p['slug']) ?>"
         data-product-id="<?= (int) $_p['id'] ?>">

    <!-- Visual -->
    <div class="pc-card__visual">
        <?php if (!empty($_imgs)): ?>
        <img src="<?= htmlspecialchars($_imgs[0]['image_path']) ?>"
             alt="<?= htmlspecialchars($_p['name']) ?>"
             class="pc-card__img"
             loading="lazy"
             draggable="false">
        <?php else: ?>
        <div class="pc-card__placeholder">
            <span>Egoire</span>
        </div>
        <?php endif; ?>

        <?php if ($_flags || $_salePercent): ?>
        <div class="pc-card__badges">
            <?php if ($_salePercent): ?>
            <span class="pc-badge pc-badge--sale">-<?= $_salePercent ?>%</span>
            <?php endif; ?>
            <?php if ($_flags): foreach ($_flags as $_f): ?>
            <span class="pc-badge pc-badge--<?= htmlspecialchars($_f) ?>">
                <?= $_f === 'best_seller' ? 'Bestseller' : ($_f === 'new' ? 'Novo' : ucfirst(str_replace('_', ' ', $_f))) ?>
            </span>
            <?php endforeach; endif; ?>
        </div>
        <?php endif; ?>

        <!-- Hover overlay (desktop) -->
        <div class="pc-card__hover-overlay">
            <span>Pogledaj</span>
        </div>
    </div>

    <!-- Body -->
    <div class="pc-card__body">

        <?php if (!empty($_p['brand_name'])): ?>
        <span class="pc-card__brand"><?= htmlspecialchars($_p['brand_name']) ?></span>
        <?php endif; ?>

        <h3 class="pc-card__name"><?= htmlspecialchars($_p['name']) ?></h3>

        <?php if ($_var !== 'compact' && !empty($_p['short_description'])): ?>
        <p class="pc-card__desc"><?= htmlspecialchars(truncate($_p['short_description'], 75)) ?></p>
        <?php endif; ?>

        <div class="pc-card__price">
            <?php if ($_p['sale_price']): ?>
            <span class="pc-card__price-old"><?= formatPrice((float) $_p['price']) ?></span>
            <span class="pc-card__price-sale"><?= formatPrice((float) $_p['sale_price']) ?></span>
            <?php else: ?>
            <span class="pc-card__price-current"><?= formatPrice((float) $_p['price']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Actions (stop propagation zone) -->
        <div class="pc-card__actions" data-stop-propagation>
            <div class="pc-stepper">
                <button class="pc-stepper__btn" type="button" data-action="minus" aria-label="Smanji količinu">−</button>
                <input  class="pc-stepper__input" type="number" value="1" min="1" max="99" aria-label="Količina">
                <button class="pc-stepper__btn" type="button" data-action="plus" aria-label="Povećaj količinu">+</button>
            </div>
            <button class="pc-card__cart-btn" type="button" data-add-to-cart>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
                <span>Dodaj u korpu</span>
            </button>
        </div>

    </div>
</article>
