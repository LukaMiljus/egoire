/* ============================================================
   Egoire – Unified Product Card JS
   File:  public/js/product-card.js
   ============================================================
   Handles:
   1. Card click → navigate to product detail
   2. Stop propagation on interactive zones
   3. Quantity stepper (± buttons)
   4. AJAX Add to Cart
   5. Brand page sort select
   ============================================================ */
(function () {
    'use strict';

    /* ==========================================================
       1. CARD CLICK → NAVIGATE
       ========================================================== */
    document.querySelectorAll('.pc-card[data-href]').forEach(function (card) {
        card.addEventListener('click', function (e) {
            // Don't navigate if user is selecting text
            if (window.getSelection && window.getSelection().toString().length > 0) return;
            window.location.href = card.dataset.href;
        });

        // Stop propagation for interactive zones
        card.querySelectorAll('[data-stop-propagation]').forEach(function (zone) {
            zone.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        });
    });


    /* ==========================================================
       2. QUANTITY STEPPER
       ========================================================== */
    document.querySelectorAll('.pc-stepper').forEach(function (stepper) {
        var input = stepper.querySelector('.pc-stepper__input');
        var minus = stepper.querySelector('[data-action="minus"]');
        var plus  = stepper.querySelector('[data-action="plus"]');
        if (!input) return;

        var minVal = parseInt(input.min, 10) || 1;
        var maxVal = parseInt(input.max, 10) || 99;

        function clamp(v) {
            return Math.max(minVal, Math.min(maxVal, v));
        }

        if (minus) {
            minus.addEventListener('click', function (e) {
                e.preventDefault();
                input.value = clamp(parseInt(input.value, 10) - 1);
            });
        }

        if (plus) {
            plus.addEventListener('click', function (e) {
                e.preventDefault();
                input.value = clamp(parseInt(input.value, 10) + 1);
            });
        }

        input.addEventListener('change', function () {
            this.value = clamp(parseInt(this.value, 10) || 1);
        });
    });


    /* ==========================================================
       3. ADD TO CART — AJAX
       ========================================================== */
    document.querySelectorAll('.pc-card [data-add-to-cart]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var card = btn.closest('.pc-card');
            if (!card) return;

            var productId = card.dataset.productId;
            var qtyInput  = card.querySelector('.pc-stepper__input');
            var qty       = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;

            var csrfMeta  = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.content : '';

            // Save original content
            var originalHTML = btn.innerHTML;
            btn.innerHTML = '<span>Dodajem\u2026</span>';
            btn.disabled = true;

            fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: 'product_id=' + encodeURIComponent(productId)
                    + '&quantity=' + encodeURIComponent(qty),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>Dodato!</span>';

                    // Update header cart badge
                    var badge = document.getElementById('cartCount');
                    if (badge && data.cart_count !== undefined) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'flex';
                    }

                    setTimeout(function () {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }, 2000);
                } else {
                    btn.innerHTML = '<span>Gre\u0161ka</span>';
                    setTimeout(function () {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }, 2000);
                }
            })
            .catch(function () {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
        });
    });


    /* ==========================================================
       4. BRAND PAGE SORT
       ========================================================== */
    var brandSort = document.getElementById('brandSort');
    if (brandSort) {
        brandSort.addEventListener('change', function () {
            var params = new URLSearchParams(window.location.search);
            var val = this.value;

            if (val) {
                params.set('sort', val);
            } else {
                params.delete('sort');
            }
            params.delete('page');

            var qs = params.toString();
            window.location.href = window.location.pathname + (qs ? '?' + qs : '');
        });
    }

})();
