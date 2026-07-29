/* ============================================================
   Egoire – Luxury Cart JS
   File:  public/js/cart.js
   ============================================================
   Features:
   1. Quantity stepper (AJAX update, no reload)
   2. Remove item (AJAX, animated removal)
   3. Gift wrap toggle
   4. Dynamic subtotal / total / shipping recalculation
   5. Formatters
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

    var CART_PAGE      = document.getElementById('ctPage');
    var SHIPPING_ON    = CART_PAGE ? CART_PAGE.dataset.shippingEnabled === '1' : false;
    var SHIPPING_COST  = CART_PAGE ? parseFloat(CART_PAGE.dataset.shippingCost) || 600 : 600;

    var EL_SUBTOTAL  = document.getElementById('ctSubtotal');
    var EL_SHIPPING  = document.getElementById('ctShipping');
    var EL_TOTAL     = document.getElementById('ctTotal');
    var EL_GIFT_ROW  = document.getElementById('ctGiftRow');
    var EL_GIFT_CHECK = document.getElementById('ctGiftCheck');
    var EL_GIFT_PRICE = document.getElementById('ctGiftPrice');
    var EL_SUMMARY   = document.getElementById('ctSummary');
    var GIFT_ON      = EL_SUMMARY ? EL_SUMMARY.dataset.giftEnabled === '1' : false;

    // Gift wrapping: support both radio buttons (multiple options) and checkbox (fallback)
    var GIFT_RADIOS = document.querySelectorAll('.ct-gift-radio');
    var GIFT_COST = EL_GIFT_CHECK ? parseFloat(EL_GIFT_CHECK.dataset.giftCost) || 300 : 0;


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

        // Gift wrap cost: check radio buttons first, then checkbox fallback
        var giftWrap = 0;
        if (GIFT_ON) {
            if (GIFT_RADIOS.length > 0) {
                GIFT_RADIOS.forEach(function (radio) {
                    if (radio.checked) {
                        giftWrap = parseFloat(radio.dataset.giftCost) || 0;
                    }
                });
            } else if (EL_GIFT_CHECK && EL_GIFT_CHECK.checked) {
                giftWrap = GIFT_COST;
            }
        }

        var shipping = SHIPPING_ON ? SHIPPING_COST : 0;
        var total    = subtotal + giftWrap + shipping;

        // Update subtotal
        if (EL_SUBTOTAL) EL_SUBTOTAL.textContent = formatPrice(subtotal);

        // Update shipping display
        if (EL_SHIPPING) {
            EL_SHIPPING.textContent = formatPrice(shipping);
        }

        // Update gift row visibility
        if (GIFT_ON && EL_GIFT_ROW) {
            EL_GIFT_ROW.style.display = giftWrap > 0 ? 'flex' : 'none';
            if (EL_GIFT_PRICE && giftWrap > 0) {
                EL_GIFT_PRICE.textContent = '+' + formatPrice(giftWrap);
            }
        }

        // Update total
        if (EL_TOTAL) EL_TOTAL.textContent = formatPrice(total);

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

    // Radio button gift wrapping options — enhanced card selection
    if (GIFT_ON && GIFT_RADIOS.length > 0) {
        GIFT_RADIOS.forEach(function (radio) {
            radio.addEventListener('change', function () {
                // Remove selected class from all cards
                document.querySelectorAll('.ct-gift-card').forEach(function (card) {
                    card.classList.remove('ct-gift-card--selected');
                });
                // Add selected class to the checked card
                var parentCard = radio.closest('.ct-gift-card');
                if (parentCard) {
                    parentCard.classList.add('ct-gift-card--selected');
                }
                recalcAll();

                // Persist selection to session via AJAX
                var selectedId = parseInt(radio.value, 10) || 0;
                fetchJSON('/api/cart/gift-wrapping', { gift_wrapping_id: selectedId });
            });
        });
    }

})();
