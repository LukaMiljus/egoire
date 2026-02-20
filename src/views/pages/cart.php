<?php
/* ============================================================
   Egoire – Luxury Cart Page
   View:  src/views/pages/cart.php
   CSS:   public/css/cart.css  (ct- namespace)
   JS:    public/js/cart.js
   ============================================================ */
declare(strict_types=1);

$title = 'Korpa | Egoire';

/* --- Cart data --- */
$cartItems = fetchCartItems();
$totals    = calculateCartTotals($cartItems);

$subtotal           = (float) ($totals['subtotal'] ?? 0);
$shipping           = (float) ($totals['shipping'] ?? 0);
$shippingThreshold  = (float) ($totals['shipping_threshold'] ?? 6000);
$total              = (float) ($totals['total'] ?? 0);
$totalQty           = (int)   ($totals['quantity'] ?? 0);

$freeShippingRemaining = max(0, $shippingThreshold - $subtotal);
$freeShippingProgress  = $shippingThreshold > 0
    ? min(100, round(($subtotal / $shippingThreshold) * 100))
    : 100;
$hasFreeShipping = $subtotal >= $shippingThreshold;

/* Gift wrap cost (client-side toggle, but define the amount here) */
$giftWrapCost = 300;
$giftWrappingOptions = fetchGiftWrappingOptions(true);

/* Page-specific assets */
$pageStyles  = ['/css/cart.css'];
$pageScripts = ['/js/cart.js'];

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     CART PAGE
     ============================================================ -->

     
<section class="ct-page">
    <div class="ct-container">

        <!-- Page Header -->
        <div class="ct-header">
            <span class="ct-header__label">Vaša korpa</span>
            <h1 class="ct-header__title">Korpa</h1>
            <?php if ($totalQty > 0): ?>
            <p class="ct-header__count"><?= $totalQty ?> <?= $totalQty === 1 ? 'artikal' : ($totalQty < 5 && $totalQty > 1 ? 'artikla' : 'artikala') ?></p>
            <?php endif; ?>
        </div>

        <?php if (empty($cartItems)): ?>
        <!-- ============================================================
             EMPTY STATE
             ============================================================ -->
        <div class="ct-empty">
            <div class="ct-empty__icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" opacity="0.3">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <h2 class="ct-empty__title">Vaša korpa je prazna</h2>
            <p class="ct-empty__text">Istražite našu kolekciju premium proizvoda za negu kose.</p>
            <a href="/products" class="ct-btn ct-btn--primary">Pogledaj proizvode</a>
        </div>

        <?php else: ?>
        <!-- ============================================================
             FREE SHIPPING PROGRESS BAR
             ============================================================ -->
        <div class="ct-shipping-bar" id="ctShippingBar"
             data-threshold="<?= $shippingThreshold ?>"
             data-subtotal="<?= $subtotal ?>">
            <div class="ct-shipping-bar__track">
                <div class="ct-shipping-bar__fill" id="ctShippingFill"
                     style="width: <?= $freeShippingProgress ?>%"></div>
            </div>
            <p class="ct-shipping-bar__msg" id="ctShippingMsg">
                <?php if ($hasFreeShipping): ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Čestitamo! Ostvarili ste <strong>besplatnu dostavu</strong>.
                <?php else: ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Još <strong id="ctShippingRemaining"><?= formatPrice($freeShippingRemaining) ?></strong> do besplatne dostave.
                <?php endif; ?>
            </p>
        </div>

        <!-- ============================================================
             TWO-COLUMN LAYOUT
             ============================================================ -->
        <div class="ct-layout">

            <!-- LEFT: Cart Items -->
            <div class="ct-items" id="ctItems">

                <!-- Column Headers (desktop only) -->
                <div class="ct-items__head">
                    <span class="ct-items__head-product">Proizvod</span>
                    <span class="ct-items__head-price">Cena</span>
                    <span class="ct-items__head-qty">Količina</span>
                    <span class="ct-items__head-total">Ukupno</span>
                    <span class="ct-items__head-remove"></span>
                </div>

                <?php foreach ($cartItems as $item):
                    $itemPrice = productDisplayPrice($item);
                    $itemTotal = $itemPrice * (int) $item['quantity'];
                    $imgs = fetchProductImages((int) $item['product_id']);
                ?>
                <article class="ct-item" data-cart-id="<?= $item['cart_id'] ?>"
                         data-product-id="<?= $item['product_id'] ?>"
                         data-unit-price="<?= $itemPrice ?>"
                         data-quantity="<?= $item['quantity'] ?>">

                    <!-- Image + Info -->
                    <div class="ct-item__product">
                        <a href="/product/<?= htmlspecialchars($item['slug'] ?? '') ?>" class="ct-item__image-link">
                            <?php if (!empty($imgs)): ?>
                            <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 class="ct-item__image"
                                 loading="lazy">
                            <?php else: ?>
                            <div class="ct-item__placeholder"><span>Egoire</span></div>
                            <?php endif; ?>
                        </a>
                        <div class="ct-item__details">
                            <?php if (!empty($item['brand_name'])): ?>
                            <span class="ct-item__brand"><?= htmlspecialchars($item['brand_name']) ?></span>
                            <?php endif; ?>
                            <a href="/product/<?= htmlspecialchars($item['slug'] ?? '') ?>" class="ct-item__name">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                            <?php if (!empty($item['variant_ml'])): ?>
                            <span class="ct-item__variant"><?= (int) $item['variant_ml'] ?> ml<?= !empty($item['variant_label']) ? ' – ' . htmlspecialchars($item['variant_label']) : '' ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['sku'])): ?>
                            <span class="ct-item__sku">SKU: <?= htmlspecialchars($item['sku']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Unit Price -->
                    <div class="ct-item__price">
                        <?php if (!empty($item['variant_id'])): ?>
                            <?php if ($item['variant_sale_price'] && (float) $item['variant_sale_price'] > 0): ?>
                            <span class="ct-item__price-old"><?= formatPrice((float) $item['variant_price']) ?></span>
                            <span class="ct-item__price-sale"><?= formatPrice((float) $item['variant_sale_price']) ?></span>
                            <?php else: ?>
                            <span class="ct-item__price-current"><?= formatPrice((float) $item['variant_price']) ?></span>
                            <?php endif; ?>
                        <?php elseif ($item['sale_price'] && (float) $item['sale_price'] > 0): ?>
                        <span class="ct-item__price-old"><?= formatPrice((float) $item['price']) ?></span>
                        <span class="ct-item__price-sale"><?= formatPrice((float) $item['sale_price']) ?></span>
                        <?php else: ?>
                        <span class="ct-item__price-current"><?= formatPrice((float) $item['price']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Quantity Stepper -->
                    <div class="ct-item__qty">
                        <div class="ct-stepper">
                            <button class="ct-stepper__btn" type="button" data-action="minus" aria-label="Smanji">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                            <input class="ct-stepper__input" type="number" value="<?= $item['quantity'] ?>" min="1" max="99" aria-label="Količina" readonly>
                            <button class="ct-stepper__btn" type="button" data-action="plus" aria-label="Povećaj">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Line Total -->
                    <div class="ct-item__total">
                        <span class="ct-item__total-value"><?= formatPrice($itemTotal) ?></span>
                    </div>

                    <!-- Remove -->
                    <button class="ct-item__remove" type="button" data-remove aria-label="Ukloni proizvod">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- RIGHT: Summary Sidebar -->
            <aside class="ct-summary" id="ctSummary">
                <div class="ct-summary__card">
                    <h3 class="ct-summary__title">Rezime narudžbine</h3>

                    <!-- Subtotal -->
                    <div class="ct-summary__row">
                        <span>Međuzbir</span>
                        <span id="ctSubtotal"><?= formatPrice($subtotal) ?></span>
                    </div>

                    <!-- Gift Wrapping Options -->
                    <?php if (!empty($giftWrappingOptions)): ?>
                    <div class="ct-summary__gift" id="ctGiftWrap">
                        <p style="font-size:0.85rem;font-weight:600;margin-bottom:8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                            Poklon pakovanje
                        </p>
                        <?php foreach ($giftWrappingOptions as $gwo): ?>
                        <label class="ct-gift-toggle" style="display:block;margin-bottom:6px;">
                            <input type="radio" name="gift_wrapping_id" value="<?= $gwo['id'] ?>" data-gift-cost="<?= $gwo['price'] ?>" class="ct-gift-radio">
                            <span class="ct-gift-toggle__text">
                                <span class="ct-gift-toggle__label">
                                    <?php if (!empty($gwo['image'])): ?>
                                    <img src="<?= htmlspecialchars($gwo['image']) ?>" alt="" style="width:24px;height:24px;object-fit:cover;border-radius:4px;vertical-align:middle;margin-right:4px;">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($gwo['name']) ?>
                                </span>
                                <span class="ct-gift-toggle__price">+<?= formatPrice((float)$gwo['price']) ?></span>
                            </span>
                        </label>
                        <?php endforeach; ?>
                        <label class="ct-gift-toggle" style="display:block;margin-bottom:6px;">
                            <input type="radio" name="gift_wrapping_id" value="0" data-gift-cost="0" class="ct-gift-radio" checked>
                            <span class="ct-gift-toggle__text">
                                <span class="ct-gift-toggle__label">Bez pakovanja</span>
                                <span class="ct-gift-toggle__price">Besplatno</span>
                            </span>
                        </label>
                    </div>
                    <?php else: ?>
                    <!-- Fallback: simple gift wrap toggle -->
                    <div class="ct-summary__gift" id="ctGiftWrap">
                        <label class="ct-gift-toggle">
                            <input type="checkbox" id="ctGiftCheck" data-gift-cost="<?= $giftWrapCost ?>">
                            <span class="ct-gift-toggle__mark">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="ct-gift-toggle__text">
                                <span class="ct-gift-toggle__label">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                                    Poklon pakovanje
                                </span>
                                <span class="ct-gift-toggle__price">+<?= formatPrice((float) $giftWrapCost) ?></span>
                            </span>
                        </label>
                    </div>
                    <?php endif; ?>

                    <!-- Gift Wrap Cost (hidden until checked) -->
                    <div class="ct-summary__row ct-summary__row--gift" id="ctGiftRow" style="display: none;">
                        <span>Poklon pakovanje</span>
                        <span id="ctGiftPrice">+<?= formatPrice((float) $giftWrapCost) ?></span>
                    </div>

                    <!-- Shipping -->
                    <div class="ct-summary__row">
                        <span>Dostava</span>
                        <span id="ctShipping"><?= $hasFreeShipping ? '<span class="ct-summary__free">Besplatna</span>' : formatPrice($shipping) ?></span>
                    </div>

                    <hr class="ct-summary__divider">

                    <!-- Total -->
                    <div class="ct-summary__row ct-summary__row--total">
                        <span>Ukupno</span>
                        <span id="ctTotal"><?= formatPrice($total) ?></span>
                    </div>

                    <!-- CTA -->
                    <a href="/checkout" class="ct-btn ct-btn--cart" id="ctCheckoutBtn">
                        Nastavi na plaćanje
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>

                    <a href="/products" class="ct-btn ct-btn--ghost">Nastavi kupovinu</a>

                    <!-- Trust Signals -->
                    <div class="ct-summary__trust">
                        <div class="ct-trust-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            <span>Sigurna kupovina</span>
                        </div>
                        <div class="ct-trust-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            <span>Brza isporuka</span>
                        </div>
                        <div class="ct-trust-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            <span>Premium kvalitet</span>
                        </div>
                    </div>
                </div>
            </aside>

        </div><!-- /.ct-layout -->
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
