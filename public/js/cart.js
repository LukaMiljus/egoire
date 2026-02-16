/* ============================================================
   Egoire – Luxury Cart JS
   File:  public/js/cart.js
   ============================================================
   Features:
   1. Quantity stepper (AJAX update, no reload)
   2. Remove item (AJAX, animated removal)
   3. Gift wrap toggle
   4. Dynamic subtotal / total / shipping recalculation
   5. Free shipping progress bar animation
   6. Formatters
   ============================================================ */
(function () {
    'use strict';

    /* ==========================================================
       0. CONFIG & STATE
       ========================================================== */
    var CSRF_TOKEN = (function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    })();

    var SHIPPING_BAR   = document.getElementById('ctShippingBar');
    var SHIPPING_FILL  = document.getElementById('ctShippingFill');
    var SHIPPING_MSG   = document.getElementById('ctShippingMsg');
    var THRESHOLD      = SHIPPING_BAR ? parseFloat(SHIPPING_BAR.dataset.threshold) || 6000 : 6000;
    var SHIPPING_COST  = 500; // Flat rate when below threshold

    var EL_SUBTOTAL  = document.getElementById('ctSubtotal');
    var EL_SHIPPING  = document.getElementById('ctShipping');
    var EL_TOTAL     = document.getElementById('ctTotal');
    var EL_GIFT_ROW  = document.getElementById('ctGiftRow');
    var EL_GIFT_CHECK = document.getElementById('ctGiftCheck');

    var GIFT_COST = EL_GIFT_CHECK ? parseFloat(EL_GIFT_CHECK.dataset.giftCost) || 300 : 300;


    /* ==========================================================
       1. HELPERS
       ========================================================== */

    /** Format number as RSD price string */
    function formatPrice(amount) {
        var n = parseFloat(amount) || 0;
        // number_format(n, 2, ',', '.')
        var parts = n.toFixed(2).split('.');
        var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return intPart + ',' + parts[1] + ' RSD';
    }

    /** Collect all item data from DOM */
    function collectItems() {
        var items = [];
        document.querySelectorAll('.ct-item').forEach(function (el) {
            items.push({
                el: el,
                cartId:    parseInt(el.dataset.cartId, 10),
                unitPrice: parseFloat(el.dataset.unitPrice) || 0,
                quantity:  parseInt(el.dataset.quantity, 10) || 0
            });
        });
        return items;
    }

    /** Calculate subtotal from DOM data */
    function calcSubtotal() {
        var total = 0;
        collectItems().forEach(function (item) {
            total += item.unitPrice * item.quantity;
        });
        return Math.round(total * 100) / 100;
    }

    /** AJAX helper */
    function fetchJSON(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        }).then(function (r) { return r.json(); });
    }


    /* ==========================================================
       2. RECALCULATE ALL TOTALS
       ========================================================== */
    function recalcAll() {
        var subtotal = calcSubtotal();
        var giftWrap = (EL_GIFT_CHECK && EL_GIFT_CHECK.checked) ? GIFT_COST : 0;

        // Shipping uses subtotal WITHOUT gift wrap for threshold check
        var hasFree  = subtotal >= THRESHOLD;
        var shipping = hasFree ? 0 : SHIPPING_COST;
        var total    = subtotal + giftWrap + shipping;

        // Update subtotal
        if (EL_SUBTOTAL) EL_SUBTOTAL.textContent = formatPrice(subtotal);

        // Update shipping display
        if (EL_SHIPPING) {
            EL_SHIPPING.innerHTML = hasFree
                ? '<span class="ct-summary__free">Besplatna</span>'
                : formatPrice(shipping);
        }

        // Update gift row visibility
        if (EL_GIFT_ROW) {
            EL_GIFT_ROW.style.display = giftWrap > 0 ? 'flex' : 'none';
        }

        // Update total
        if (EL_TOTAL) EL_TOTAL.textContent = formatPrice(total);

        // Update progress bar
        updateShippingBar(subtotal);

        // Update header cart badge
        var totalQty = 0;
        collectItems().forEach(function (item) { totalQty += item.quantity; });
        var badge = document.getElementById('cartCount');
        if (badge) {
            badge.textContent = totalQty;
            badge.style.display = totalQty > 0 ? 'flex' : 'none';
        }
    }


    /* ==========================================================
       3. SHIPPING PROGRESS BAR
       ========================================================== */
    function updateShippingBar(subtotal) {
        if (!SHIPPING_BAR || !SHIPPING_FILL || !SHIPPING_MSG) return;

        var progress = THRESHOLD > 0 ? Math.min(100, (subtotal / THRESHOLD) * 100) : 100;
        var remaining = Math.max(0, THRESHOLD - subtotal);
        var achieved = subtotal >= THRESHOLD;

        // Animate fill width
        SHIPPING_FILL.style.width = progress + '%';

        // Toggle achieved class
        if (achieved) {
            SHIPPING_BAR.classList.add('ct-shipping-bar--achieved');
            SHIPPING_MSG.innerHTML =
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> ' +
                'Čestitamo! Ostvarili ste <strong>besplatnu dostavu</strong>.';
        } else {
            SHIPPING_BAR.classList.remove('ct-shipping-bar--achieved');
            SHIPPING_MSG.innerHTML =
                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> ' +
                'Još <strong>' + formatPrice(remaining) + '</strong> do besplatne dostave.';
        }
    }


    /* ==========================================================
       4. QUANTITY STEPPER
       ========================================================== */
    document.querySelectorAll('.ct-item').forEach(function (itemEl) {
        var cartId  = parseInt(itemEl.dataset.cartId, 10);
        var stepper = itemEl.querySelector('.ct-stepper');
        var input   = itemEl.querySelector('.ct-stepper__input');
        var minus   = itemEl.querySelector('[data-action="minus"]');
        var plus    = itemEl.querySelector('[data-action="plus"]');
        if (!stepper || !input) return;

        function updateQty(newQty) {
            if (newQty < 1) {
                removeItem(itemEl, cartId);
                return;
            }

            // Optimistic update
            var oldQty = parseInt(itemEl.dataset.quantity, 10);
            itemEl.dataset.quantity = newQty;
            input.value = newQty;

            // Update line total
            var unitPrice = parseFloat(itemEl.dataset.unitPrice) || 0;
            var totalEl = itemEl.querySelector('.ct-item__total-value');
            if (totalEl) totalEl.textContent = formatPrice(unitPrice * newQty);

            stepper.classList.add('is-loading');
            recalcAll();

            fetchJSON('/api/cart/update', { cart_id: cartId, quantity: newQty })
                .then(function (data) {
                    stepper.classList.remove('is-loading');
                    if (!data.success) {
                        // Rollback
                        itemEl.dataset.quantity = oldQty;
                        input.value = oldQty;
                        if (totalEl) totalEl.textContent = formatPrice(unitPrice * oldQty);
                        recalcAll();
                    }
                })
                .catch(function () {
                    stepper.classList.remove('is-loading');
                    itemEl.dataset.quantity = oldQty;
                    input.value = oldQty;
                    recalcAll();
                });
        }

        if (minus) {
            minus.addEventListener('click', function () {
                updateQty(parseInt(input.value, 10) - 1);
            });
        }
        if (plus) {
            plus.addEventListener('click', function () {
                updateQty(parseInt(input.value, 10) + 1);
            });
        }
    });


    /* ==========================================================
       5. REMOVE ITEM
       ========================================================== */
    function removeItem(itemEl, cartId) {
        itemEl.classList.add('is-removing');

        fetchJSON('/api/cart/remove', { cart_id: cartId })
            .then(function (data) {
                if (data.success) {
                    setTimeout(function () {
                        itemEl.remove();
                        recalcAll();

                        // If no items left, reload to show empty state
                        if (document.querySelectorAll('.ct-item').length === 0) {
                            location.reload();
                        }
                    }, 400);
                } else {
                    itemEl.classList.remove('is-removing');
                }
            })
            .catch(function () {
                itemEl.classList.remove('is-removing');
            });
    }

    document.querySelectorAll('[data-remove]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var itemEl = btn.closest('.ct-item');
            if (!itemEl) return;
            var cartId = parseInt(itemEl.dataset.cartId, 10);
            removeItem(itemEl, cartId);
        });
    });


    /* ==========================================================
       6. GIFT WRAP TOGGLE
       ========================================================== */
    if (EL_GIFT_CHECK) {
        EL_GIFT_CHECK.addEventListener('change', function () {
            recalcAll();
        });
    }

})();
