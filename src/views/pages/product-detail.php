<?php
/* ============================================================
   Egoire – Luxury Product Detail Page
   View:  src/views/pages/product-detail.php
   CSS:   public/css/product-details.css  (pd- namespace)
   JS:    public/js/product-details.js
   ============================================================ */
declare(strict_types=1);

$slug = $routeParams['slug'] ?? '';
$product = fetchProductBySlug($slug);
if (!$product || !$product['is_active']) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$images     = fetchProductImages((int) $product['id']);
$categories = fetchProductCategories((int) $product['id']);
$flags      = fetchProductFlags((int) $product['id']);
$stock      = fetchProductStock((int) $product['id']);
$inStock    = !$stock || (int) ($stock['quantity'] ?? 0) > 0;

$title           = (($product['meta_title'] ?? '') ?: $product['name']) . ' | Egoire';
$metaDescription = $product['meta_description'] ?? $product['short_description'] ?? '';
$ogImage         = !empty($images) ? (baseUrl() . $images[0]['image_path']) : '';
$productVariants = fetchProductVariants((int) $product['id']);

$salePercent = ($product['sale_price'] && (float) $product['price'] > 0)
    ? round((1 - (float) $product['sale_price'] / (float) $product['price']) * 100)
    : 0;

/* Related products (same brand, exclude self) */
$related = fetchProducts([
    'brand_id'   => $product['brand_id'],
    'active'     => true,
    'limit'      => 4,
    'exclude_id' => $product['id'],
]);

/* Page-specific assets */
$pageStyles  = ['/css/product-card.css', '/css/product-details.css'];
$pageScripts = ['/js/product-card.js', '/js/product-details.js'];

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     BREADCRUMB
     ============================================================ -->
<nav class="pd-breadcrumb" aria-label="Breadcrumb">
    <div class="pd-container">
        <ol class="pd-breadcrumb__list">
            <li><a href="/">Početna</a></li>
            <li><a href="/products">Proizvodi</a></li>
            <?php if ($categories): ?>
            <li><a href="/category/<?= htmlspecialchars($categories[0]['slug']) ?>"><?= htmlspecialchars($categories[0]['name']) ?></a></li>
            <?php endif; ?>
            <li aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </div>
</nav>

<!-- ============================================================
     PRODUCT DETAIL — TWO-COLUMN LAYOUT
     ============================================================ -->
<section class="pd-product">
    <div class="pd-container">
        <div class="pd-product__grid">

            <!-- LEFT COLUMN — IMAGE GALLERY -->
            <div class="pd-gallery" data-image-count="<?= count($images) ?>">

                <!-- Main Stage (4:5 aspect) -->
                <div class="pd-gallery__stage">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $i => $img): ?>
                        <img src="<?= htmlspecialchars($img['image_path']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?> – <?= $i + 1 ?>"
                             class="pd-gallery__image <?= $i === 0 ? 'is-active' : '' ?>"
                             data-index="<?= $i ?>"
                             loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                             draggable="false">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pd-gallery__placeholder">
                            <span>Egoire</span>
                        </div>
                    <?php endif; ?>

                    <?php if (count($images) > 1): ?>
                    <!-- Navigation Arrows -->
                    <button class="pd-gallery__arrow pd-gallery__arrow--prev" data-gallery-prev aria-label="Prethodna slika">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>
                    <button class="pd-gallery__arrow pd-gallery__arrow--next" data-gallery-next aria-label="Sledeća slika">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 6 15 12 9 18"/>
                        </svg>
                    </button>

                    <!-- Counter -->
                    <span class="pd-gallery__counter">
                        <span data-gallery-current>1</span> / <?= count($images) ?>
                    </span>
                    <?php endif; ?>

                    <!-- Badges -->
                    <?php if ($flags || $salePercent): ?>
                    <div class="pd-gallery__badges">
                        <?php if ($salePercent): ?>
                        <span class="pd-badge pd-badge--sale">-<?= $salePercent ?>%</span>
                        <?php endif; ?>
                        <?php foreach ($flags as $f): ?>
                        <span class="pd-badge pd-badge--<?= $f ?>"><?= $f === 'best_seller' ? 'Bestseller' : ($f === 'new' ? 'Novo' : ucfirst($f)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Thumbnails -->
                <?php if (count($images) > 1): ?>
                <div class="pd-gallery__thumbs">
                    <?php foreach ($images as $i => $img): ?>
                    <button class="pd-gallery__thumb <?= $i === 0 ? 'is-active' : '' ?>"
                            data-gallery-thumb="<?= $i ?>"
                            aria-label="Slika <?= $i + 1 ?>">
                        <img src="<?= htmlspecialchars($img['image_path']) ?>"
                             alt=""
                             loading="lazy"
                             draggable="false">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Mobile Dot Indicators -->
                <?php if (count($images) > 1): ?>
                <div class="pd-gallery__dots">
                    <?php foreach ($images as $i => $img): ?>
                    <button class="pd-gallery__dot <?= $i === 0 ? 'is-active' : '' ?>"
                            data-gallery-dot="<?= $i ?>"
                            aria-label="Slika <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- RIGHT COLUMN — PRODUCT INFO -->
            <div class="pd-info">

                <!-- Brand -->
                <?php if ($product['brand_name']): ?>
                <a href="/brand/<?= htmlspecialchars($product['brand_slug'] ?? '') ?>" class="pd-info__brand">
                    <?= htmlspecialchars($product['brand_name']) ?>
                </a>
                <?php endif; ?>

                <!-- Title -->
                <h1 class="pd-info__title"><?= htmlspecialchars($product['name']) ?></h1>

                <!-- Categories -->
                <?php if ($categories): ?>
                <div class="pd-info__categories">
                    <?php foreach ($categories as $ci => $c): ?>
                        <?php if ($ci > 0): ?><span class="pd-info__cat-sep">·</span><?php endif; ?>
                        <a href="/category/<?= htmlspecialchars($c['slug']) ?>" class="pd-info__cat-link"><?= htmlspecialchars($c['name']) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Price -->
                <div class="pd-info__price">
                    <?php if ($product['sale_price']): ?>
                    <span class="pd-info__price-old"><?= formatPrice((float) $product['price']) ?></span>
                    <span class="pd-info__price-sale"><?= formatPrice((float) $product['sale_price']) ?></span>
                    <span class="pd-info__price-badge">-<?= $salePercent ?>%</span>
                    <?php else: ?>
                    <span class="pd-info__price-current"><?= formatPrice((float) $product['price']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Short Description -->
                <?php if ($product['short_description']): ?>
                <p class="pd-info__excerpt"><?= htmlspecialchars($product['short_description']) ?></p>
                <?php endif; ?>

                <!-- Divider -->
                <hr class="pd-info__divider">

                <!-- Stock Notice -->
                <?php if (!$inStock): ?>
                <div class="pd-info__stock pd-info__stock--out">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Trenutno nema na stanju
                </div>
                <?php else: ?>
                <div class="pd-info__stock pd-info__stock--in">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Na stanju
                </div>
                <?php endif; ?>

                <!-- Variant Selector -->
                <?php if ($productVariants): ?>
                <div class="pd-info__variants">
                    <label class="pd-info__variants-label">Izaberite varijantu</label>
                    <div class="pd-info__variants-list">
                        <?php foreach ($productVariants as $vi => $variant): ?>
                        <label class="pd-variant <?= $vi === 0 ? 'pd-variant--active' : '' ?>">
                            <input type="radio" name="variant_id" value="<?= $variant['id'] ?>"
                                   data-price="<?= (float) $variant['price'] ?>"
                                   data-sale-price="<?= (float) ($variant['sale_price'] ?? 0) ?>"
                                   <?= $vi === 0 ? 'checked' : '' ?>>
                            <span class="pd-variant__info">
                                <span class="pd-variant__ml"><?= (int) $variant['volume_ml'] ?> ml</span>
                                <?php if ($variant['label']): ?>
                                <span class="pd-variant__label"><?= htmlspecialchars($variant['label']) ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="pd-variant__price">
                                <?php if ($variant['sale_price']): ?>
                                <span class="pd-variant__price-old"><?= formatPrice((float) $variant['price']) ?></span>
                                <span class="pd-variant__price-sale"><?= formatPrice((float) $variant['sale_price']) ?></span>
                                <?php else: ?>
                                <?= formatPrice((float) $variant['price']) ?>
                                <?php endif; ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quantity + Add to Cart -->
                <form class="pd-info__cart" id="pdCartForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <?php if ($productVariants): ?>
                    <input type="hidden" name="variant_id" id="pdVariantId" value="<?= $productVariants[0]['id'] ?>">
                    <?php endif; ?>

                    <div class="pd-info__qty-row">
                        <label class="pd-info__qty-label">Količina</label>
                        <div class="pd-stepper">
                            <button class="pd-stepper__btn" type="button" data-action="minus" <?= !$inStock ? 'disabled' : '' ?> aria-label="Smanji količinu">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                            <input class="pd-stepper__input" type="number" name="quantity" value="1" min="1" max="99" <?= !$inStock ? 'disabled' : '' ?> aria-label="Količina">
                            <button class="pd-stepper__btn" type="button" data-action="plus" <?= !$inStock ? 'disabled' : '' ?> aria-label="Povećaj količinu">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="pd-btn pd-btn--cart" id="pdCartBtn" <?= !$inStock ? 'disabled' : '' ?>>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                        <span><?= $inStock ? 'Dodaj u korpu' : 'Nema na stanju' ?></span>
                    </button>

                    <!-- Feedback message -->
                    <div class="pd-info__feedback" id="pdCartFeedback"></div>
                </form>

                <!-- Divider -->
                <hr class="pd-info__divider">

                <!-- Expandable Description -->
                <?php if ($product['description']): ?>
                <div class="pd-accordion" data-pd-accordion>
                    <button class="pd-accordion__trigger" type="button" aria-expanded="true">
                        <span>Opis proizvoda</span>
                        <svg class="pd-accordion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <div class="pd-accordion__panel is-open">
                        <div class="pd-accordion__content pd-prose">
                            <?= $product['description'] ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- How to Use -->
                <?php if (!empty($product['how_to_use'])): ?>
                <div class="pd-accordion" data-pd-accordion>
                    <button class="pd-accordion__trigger" type="button" aria-expanded="false">
                        <span>Način upotrebe</span>
                        <svg class="pd-accordion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <div class="pd-accordion__panel">
                        <div class="pd-accordion__content pd-prose">
                            <?= $product['how_to_use'] ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Composition / Ingredients -->
                <?php if (!empty($product['ingredients'])): ?>
                <div class="pd-accordion" data-pd-accordion>
                    <button class="pd-accordion__trigger" type="button" aria-expanded="false">
                        <span>Sastav</span>
                        <svg class="pd-accordion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <div class="pd-accordion__panel">
                        <div class="pd-accordion__content pd-prose">
                            <?= nl2br(htmlspecialchars($product['ingredients'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Fragrance Notes -->
                <?php if (!empty($product['fragrance_notes'])): ?>
                <div class="pd-accordion" data-pd-accordion>
                    <button class="pd-accordion__trigger" type="button" aria-expanded="false">
                        <span>Mirisne note</span>
                        <svg class="pd-accordion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <div class="pd-accordion__panel">
                        <div class="pd-accordion__content pd-prose">
                            <?= nl2br(htmlspecialchars($product['fragrance_notes'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- SKU / Meta -->
                <?php if (!empty($product['sku'])): ?>
                <div class="pd-info__meta">
                    <span class="pd-info__meta-label">SKU</span>
                    <span class="pd-info__meta-value"><?= htmlspecialchars($product['sku']) ?></span>
                </div>
                <?php endif; ?>

            </div><!-- /.pd-info -->
        </div><!-- /.pd-product__grid -->
    </div>
</section>

<!-- ============================================================
     RECOMMENDED PRODUCTS
     ============================================================ -->
<?php if ($related): ?>
<section class="pd-related">
    <div class="pd-container">
        <div class="pd-related__header">
            <span class="pd-related__label">Pogledajte i</span>
            <h2 class="pd-related__title">Preporučeni proizvodi</h2>
        </div>

        <div class="pc-grid">
            <?php foreach ($related as $rp):
                $cardProduct = $rp;
                $cardImages  = fetchProductImages((int) $rp['id']);
                $cardFlags   = fetchProductFlags((int) $rp['id']);
                $cardVariant = 'compact';
                include __DIR__ . '/../components/product-card.php';
            endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
