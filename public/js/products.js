/* ============================================================
   Egoire – Luxury Products Listing JS
   File:  public/js/products.js
   ============================================================ */

(function () {
    'use strict';

    /* ==========================================================
       DOM REFERENCES
       ========================================================== */
    const filterTrigger  = document.getElementById('epFilterTrigger');
    const filterPanel    = document.getElementById('epFilterPanel');
    const filterOverlay  = document.getElementById('epFilterOverlay');
    const filterClose    = document.getElementById('epFilterClose');
    const sortSelect     = document.getElementById('epSort');

    /* ==========================================================
       1. FILTER PANEL — Open / Close
       ========================================================== */
    function openFilters() {
        filterPanel?.classList.add('is-open');
        filterOverlay?.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeFilters() {
        filterPanel?.classList.remove('is-open');
        filterOverlay?.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    filterTrigger?.addEventListener('click', openFilters);
    filterClose?.addEventListener('click', closeFilters);
    filterOverlay?.addEventListener('click', closeFilters);

    // ESC key closes filter panel
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && filterPanel?.classList.contains('is-open')) {
            closeFilters();
        }
    });


    /* ==========================================================
       2. ACCORDION — Toggle open/close
       ========================================================== */
    document.querySelectorAll('[data-accordion]').forEach(function (acc) {
        const trigger = acc.querySelector('.ep-accordion__trigger');
        if (!trigger) return;

        // Default: first two accordions open
        const allAccordions = Array.from(document.querySelectorAll('[data-accordion]'));
        if (allAccordions.indexOf(acc) < 2) {
            acc.classList.add('is-open');
        }

        trigger.addEventListener('click', function () {
            acc.classList.toggle('is-open');
        });
    });


    /* ==========================================================
       3. SORT — Navigate on change
       ========================================================== */
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            const params = new URLSearchParams(window.location.search);
            const val = this.value;

            if (val) {
                params.set('sort', val);
            } else {
                params.delete('sort');
            }
            // Reset to page 1 on sort change
            params.delete('page');

            const qs = params.toString();
            window.location.href = '/products' + (qs ? '?' + qs : '');
        });
    }


    /* ==========================================================
       4. PRODUCT CARDS — Click to navigate + stop propagation
       ========================================================== */
    document.querySelectorAll('.ep-card[data-href]').forEach(function (card) {
        // Card click → navigate
        card.addEventListener('click', function (e) {
            // Don't navigate if user is selecting text
            if (window.getSelection && window.getSelection().toString().length > 0) return;
            window.location.href = card.dataset.href;
        });

        // Stop propagation for interactive elements inside card
        card.querySelectorAll('[data-stop-propagation]').forEach(function (zone) {
            zone.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        });
    });


    /* ==========================================================
       5. QUANTITY STEPPER
       ========================================================== */
    document.querySelectorAll('.ep-stepper').forEach(function (stepper) {
        const input = stepper.querySelector('.ep-stepper__input');
        const minus = stepper.querySelector('[data-action="minus"]');
        const plus  = stepper.querySelector('[data-action="plus"]');
        if (!input) return;

        minus?.addEventListener('click', function (e) {
            e.preventDefault();
            let val = parseInt(input.value) || 1;
            if (val > 1) input.value = val - 1;
        });

        plus?.addEventListener('click', function (e) {
            e.preventDefault();
            let val = parseInt(input.value) || 1;
            if (val < 99) input.value = val + 1;
        });

        input.addEventListener('change', function () {
            let val = parseInt(this.value) || 1;
            this.value = Math.max(1, Math.min(99, val));
        });
    });


    /* ==========================================================
       6. ADD TO CART — AJAX
       ========================================================== */
    document.querySelectorAll('[data-add-to-cart]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const card = btn.closest('.ep-card');
            if (!card) return;

            const productId = card.dataset.productId;
            const qtyInput  = card.querySelector('.ep-stepper__input');
            const qty        = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';

            // Visual feedback
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>Dodajem…</span>';
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

                    // Update cart badge
                    const badge = document.getElementById('cartCount');
                    if (badge && data.cart_count !== undefined) {
                        badge.textContent = data.cart_count;
                    }

                    setTimeout(function () {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 2000);
                } else {
                    btn.innerHTML = '<span>Greška</span>';
                    setTimeout(function () {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 2000);
                }
            })
            .catch(function () {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    });

})();
