<?php
/* ============================================================
   Egoire – Luxury Checkout Page
   View:  src/views/pages/checkout.php
   CSS:   public/css/checkout.css  (co- namespace)
   JS:    public/js/checkout.js
   ============================================================ */
declare(strict_types=1);

$title = 'Checkout | Egoire';

/* --- Cart data --- */
$cartItems = fetchCartItems();
$totals    = calculateCartTotals($cartItems);

if (empty($cartItems)) {
    redirect('/cart');
}

$subtotal = (float) ($totals['subtotal'] ?? 0);
$shipping = (float) ($totals['shipping'] ?? 0);
$total    = (float) ($totals['total'] ?? 0);
$hasFreeShipping = $shipping <= 0;

/* --- User data --- */
$user      = isUserAuthenticated() ? currentUser() : null;
$addresses = $user ? fetchUserAddresses((int) $user['id']) : [];
$loyaltyInfo     = $user ? fetchUserLoyalty((int) $user['id']) : null;
$loyaltySettings = fetchLoyaltySettings();

$showLoyalty = $loyaltyInfo
    && $loyaltySettings
    && !empty($loyaltySettings['is_active'])
    && (int) ($loyaltyInfo['points_balance'] ?? 0) >= (int) ($loyaltySettings['min_points_redeem'] ?? 100);

/* --- Page-specific assets --- */
$pageStyles  = ['/css/checkout.css'];
$pageScripts = ['/js/checkout.js'];

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     CHECKOUT PAGE
     ============================================================ -->
<section class="co-page">
    <div class="co-container">

        <!-- Page Header -->
        <div class="co-header">
            <span class="co-header__label">Sigurna kupovina</span>
            <h1 class="co-header__title">Checkout</h1>
        </div>

        <form id="coForm" method="POST" action="/api/checkout" autocomplete="on">
            <?= csrfField() ?>

            <div class="co-layout">

                <!-- ========================================
                     LEFT COLUMN — FORM SECTIONS
                     ======================================== -->
                <div class="co-form">

                    <!-- Guest Notice -->
                    <?php if (!isUserAuthenticated()): ?>
                    <div class="co-notice">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <p>Već imate nalog? <a href="/login?redirect=/checkout">Prijavite se</a> za brži checkout i loyalty bodove.</p>
                    </div>
                    <?php endif; ?>

                    <!-- ============================
                         1. SHIPPING ADDRESS
                         ============================ -->
                    <section class="co-section">
                        <div class="co-section__head">
                            <span class="co-section__number">1</span>
                            <h2 class="co-section__title">Adresa za dostavu</h2>
                        </div>

                        <?php if ($addresses): ?>
                        <div class="co-field">
                            <label class="co-label" for="coSavedAddr">Sačuvane adrese</label>
                            <div class="co-select-wrap">
                                <select class="co-input co-input--select" id="coSavedAddr" name="saved_address">
                                    <option value="">Nova adresa</option>
                                    <?php foreach ($addresses as $a): ?>
                                    <option value="<?= $a['id'] ?>"
                                            data-addr='<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>'>
                                        <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name'] . ' — ' . $a['address'] . ', ' . $a['city']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="co-select-wrap__icon" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="co-row">
                            <div class="co-field">
                                <label class="co-label" for="coFirstName">Ime <span class="co-req">*</span></label>
                                <input class="co-input" type="text" id="coFirstName" name="first_name"
                                       value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                                       autocomplete="given-name" required>
                            </div>
                            <div class="co-field">
                                <label class="co-label" for="coLastName">Prezime <span class="co-req">*</span></label>
                                <input class="co-input" type="text" id="coLastName" name="last_name"
                                       value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                                       autocomplete="family-name" required>
                            </div>
                        </div>

                        <div class="co-row">
                            <div class="co-field">
                                <label class="co-label" for="coEmail">Email <span class="co-req">*</span></label>
                                <input class="co-input" type="email" id="coEmail" name="email"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       autocomplete="email" required>
                            </div>
                            <div class="co-field">
                                <label class="co-label" for="coPhone">Telefon <span class="co-req">*</span></label>
                                <input class="co-input" type="tel" id="coPhone" name="phone"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       autocomplete="tel" required>
                            </div>
                        </div>

                        <div class="co-field">
                            <label class="co-label" for="coAddress">Adresa <span class="co-req">*</span></label>
                            <input class="co-input" type="text" id="coAddress" name="address"
                                   autocomplete="street-address" required>
                        </div>

                        <div class="co-row">
                            <div class="co-field">
                                <label class="co-label" for="coCity">Grad <span class="co-req">*</span></label>
                                <input class="co-input" type="text" id="coCity" name="city"
                                       autocomplete="address-level2" required>
                            </div>
                            <div class="co-field">
                                <label class="co-label" for="coPostal">Poštanski broj <span class="co-req">*</span></label>
                                <input class="co-input" type="text" id="coPostal" name="postal_code"
                                       autocomplete="postal-code" required>
                            </div>
                        </div>

                        <div class="co-field">
                            <label class="co-label" for="coCountry">Država</label>
                            <input class="co-input" type="text" id="coCountry" name="country"
                                   value="Srbija" autocomplete="country-name">
                        </div>

                        <div class="co-field">
                            <label class="co-label" for="coNote">Napomena za dostavu</label>
                            <textarea class="co-input co-input--textarea" id="coNote" name="note"
                                      rows="3" placeholder="Opciono — posebne instrukcije za kurira..."></textarea>
                        </div>
                    </section>

                    <!-- ============================
                         2. PAYMENT METHOD
                         ============================ -->
                    <section class="co-section">
                        <div class="co-section__head">
                            <span class="co-section__number">2</span>
                            <h2 class="co-section__title">Način plaćanja</h2>
                        </div>

                        <div class="co-payment-options">
                            <label class="co-payment">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <span class="co-payment__box">
                                    <span class="co-payment__radio"></span>
                                    <span class="co-payment__info">
                                        <span class="co-payment__name">Plaćanje pouzećem</span>
                                        <span class="co-payment__desc">Platite kuriru pri preuzimanju pošiljke.</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </section>

                    <!-- ============================
                         3. GIFT CARD (collapsible)
                         ============================ -->
                    <section class="co-section co-section--compact">
                        <button class="co-expand-trigger" type="button" data-co-expand="giftCard">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                            <span>Imate poklon karticu?</span>
                            <svg class="co-expand-trigger__chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
                        </button>
                        <div class="co-expand-panel" data-co-panel="giftCard">
                            <div class="co-expand-panel__inner">
                                <div class="co-row co-row--action">
                                    <div class="co-field co-field--grow">
                                        <input class="co-input" type="text" name="gift_card_code" id="coGiftCode"
                                               placeholder="XXXX-XXXX-XXXX-XXXX">
                                    </div>
                                    <button type="button" class="co-btn co-btn--outline co-btn--sm" id="coGiftValidate">
                                        Proveri
                                    </button>
                                </div>
                                <div class="co-feedback" id="coGiftFeedback"></div>
                            </div>
                        </div>
                    </section>

                    <!-- ============================
                         4. PROMO CODE (collapsible)
                         ============================ -->
                    <section class="co-section co-section--compact">
                        <button class="co-expand-trigger" type="button" data-co-expand="promo">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            <span>Imate promo kod?</span>
                            <svg class="co-expand-trigger__chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
                        </button>
                        <div class="co-expand-panel" data-co-panel="promo">
                            <div class="co-expand-panel__inner">
                                <div class="co-row co-row--action">
                                    <div class="co-field co-field--grow">
                                        <input class="co-input" type="text" name="promo_code" id="coPromoCode"
                                               placeholder="Unesite promo kod">
                                    </div>
                                    <button type="button" class="co-btn co-btn--outline co-btn--sm" id="coPromoApply">
                                        Primeni
                                    </button>
                                </div>
                                <div class="co-feedback" id="coPromoFeedback"></div>
                            </div>
                        </div>
                    </section>

                    <!-- ============================
                         5. LOYALTY (conditional)
                         ============================ -->
                    <?php if ($showLoyalty): ?>
                    <section class="co-section">
                        <div class="co-section__head">
                            <span class="co-section__number">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </span>
                            <h2 class="co-section__title">Loyalty bodovi</h2>
                        </div>
                        <div class="co-loyalty">
                            <p class="co-loyalty__balance">
                                Imate <strong><?= (int) $loyaltyInfo['points_balance'] ?></strong> bodova
                                <span class="co-loyalty__value">(<?= formatPrice((float) $loyaltyInfo['points_balance'] * (float) ($loyaltySettings['rsd_per_point'] ?? 1)) ?>)</span>
                            </p>
                            <label class="co-checkbox">
                                <input type="checkbox" name="use_loyalty" value="1" id="coUseLoyalty">
                                <span class="co-checkbox__mark">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                <span class="co-checkbox__label">Iskoristi loyalty bodove</span>
                            </label>
                            <div class="co-field co-loyalty__points-field" id="coLoyaltyField" style="display: none;">
                                <label class="co-label" for="coLoyaltyPts">Broj bodova (maks. <?= (int) $loyaltyInfo['points_balance'] ?>)</label>
                                <input class="co-input" type="number" id="coLoyaltyPts" name="loyalty_points"
                                       max="<?= (int) $loyaltyInfo['points_balance'] ?>" min="0" value="0">
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- ============================
                         6. MARKETING OPT-IN
                         ============================ -->
                    <div class="co-marketing">
                        <label class="co-checkbox">
                            <input type="checkbox" name="marketing_optin" value="1">
                            <span class="co-checkbox__mark">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span class="co-checkbox__label">Želim da primam ekskluzivne ponude i novosti putem emaila.</span>
                        </label>
                    </div>

                </div><!-- /.co-form -->

                <!-- ========================================
                     RIGHT COLUMN — ORDER SUMMARY
                     ======================================== -->
                <aside class="co-summary">
                    <div class="co-summary__card">
                        <h3 class="co-summary__title">Vaša porudžbina</h3>

                        <!-- Mini Product List -->
                        <div class="co-summary__items">
                            <?php foreach ($cartItems as $item):
                                $itemPrice = productDisplayPrice($item);
                                $lineTotal = $itemPrice * (int) $item['quantity'];
                                $imgs = fetchProductImages((int) $item['product_id']);
                            ?>
                            <div class="co-summary__item">
                                <div class="co-summary__item-img">
                                    <?php if (!empty($imgs)): ?>
                                    <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>"
                                         alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                                    <?php else: ?>
                                    <span class="co-summary__item-ph">E</span>
                                    <?php endif; ?>
                                    <span class="co-summary__item-qty"><?= $item['quantity'] ?></span>
                                </div>
                                <div class="co-summary__item-info">
                                    <span class="co-summary__item-name"><?= htmlspecialchars($item['name']) ?></span>
                                    <?php if (!empty($item['brand_name'])): ?>
                                    <span class="co-summary__item-brand"><?= htmlspecialchars($item['brand_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="co-summary__item-total"><?= formatPrice($lineTotal) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="co-summary__divider">

                        <!-- Totals -->
                        <div class="co-summary__row">
                            <span>Međuzbir</span>
                            <span><?= formatPrice($subtotal) ?></span>
                        </div>

                        <div class="co-summary__row">
                            <span>Dostava</span>
                            <span><?= $hasFreeShipping ? '<span class="co-summary__free">Besplatna</span>' : formatPrice($shipping) ?></span>
                        </div>

                        <div class="co-summary__row co-summary__row--discount" id="coDiscountRow" style="display: none;">
                            <span>Popust</span>
                            <span id="coDiscountValue">-0 RSD</span>
                        </div>

                        <hr class="co-summary__divider">

                        <div class="co-summary__row co-summary__row--total">
                            <span>Ukupno</span>
                            <span id="coTotal"><?= formatPrice($total) ?></span>
                        </div>

                        <!-- Primary CTA -->
                        <button type="submit" class="co-btn co-btn--cart" id="coPlaceOrder">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            <span>Poruči</span>
                        </button>

                        <p class="co-summary__legal">
                            Klikom na „Poruči" prihvatate naše <a href="/terms">uslove korišćenja</a> i <a href="/privacy">politiku privatnosti</a>.
                        </p>

                        <!-- Trust -->
                        <div class="co-summary__trust">
                            <div class="co-trust">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                <span>Sigurna kupovina</span>
                            </div>
                            <div class="co-trust">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <span>Zaštita podataka</span>
                            </div>
                        </div>
                    </div>
                </aside>

            </div><!-- /.co-layout -->
        </form>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
