/* ============================================================
   Egoire – Luxury Checkout JS
   File:  public/js/checkout.js
   ============================================================ */
(function () {
    'use strict';

    /* --------------------------------------------------
       DOM REFS
       -------------------------------------------------- */
    const form       = document.getElementById('coForm');
    const submitBtn  = document.getElementById('coPlaceOrder');
    const savedAddr  = document.getElementById('coSavedAddr');
    const csrfMeta   = document.querySelector('meta[name="csrf-token"]');
    const csrfToken  = csrfMeta ? csrfMeta.content : '';

    /* --------------------------------------------------
       1. SAVED ADDRESS AUTO-FILL
       -------------------------------------------------- */
    if (savedAddr) {
        savedAddr.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt || !opt.dataset.addr) {
                clearAddressFields();
                return;
            }
            try {
                const a = JSON.parse(opt.dataset.addr);
                setVal('coFirstName', a.first_name);
                setVal('coLastName',  a.last_name);
                setVal('coPhone',     a.phone);
                setVal('coAddress',   a.address);
                setVal('coCity',      a.city);
                setVal('coPostal',    a.postal_code);
                setVal('coCountry',   a.country || 'Srbija');
            } catch (_) { /* ignore parse errors */ }
        });
    }

    function setVal(id, v) {
        var el = document.getElementById(id);
        if (el) el.value = v || '';
    }

    function clearAddressFields() {
        ['coFirstName','coLastName','coPhone','coAddress','coCity','coPostal'].forEach(function(id) {
            setVal(id, '');
        });
        setVal('coCountry', 'Srbija');
    }

    /* --------------------------------------------------
       2. COLLAPSIBLE PANELS (Gift Card & Promo)
       -------------------------------------------------- */
    document.querySelectorAll('[data-co-expand]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var key   = this.getAttribute('data-co-expand');
            var panel = document.querySelector('[data-co-panel="' + key + '"]');
            if (!panel) return;

            var isOpen = panel.classList.contains('is-open');

            if (isOpen) {
                panel.classList.remove('is-open');
                this.classList.remove('is-active');
            } else {
                panel.classList.add('is-open');
                this.classList.add('is-active');
                // Focus the first input inside
                var input = panel.querySelector('input');
                if (input) setTimeout(function () { input.focus(); }, 350);
            }
        });
    });

    /* --------------------------------------------------
       3. GIFT CARD VALIDATION
       -------------------------------------------------- */
    var giftBtn      = document.getElementById('coGiftValidate');
    var giftInput    = document.getElementById('coGiftCode');
    var giftFeedback = document.getElementById('coGiftFeedback');

    if (giftBtn && giftInput) {
        giftBtn.addEventListener('click', function () {
            var code = giftInput.value.trim();
            if (!code) {
                showFeedback(giftFeedback, 'Unesite kod poklon kartice.', 'error');
                return;
            }

            giftBtn.disabled = true;
            giftBtn.textContent = '...';

            fetch('/api/gift-card/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code: code })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.valid) {
                    showFeedback(giftFeedback, '✓ Kartica validna — preostalo: ' + data.remaining, 'success');
                } else {
                    showFeedback(giftFeedback, '✗ ' + (data.error || 'Nevažeća poklon kartica.'), 'error');
                }
            })
            .catch(function () {
                showFeedback(giftFeedback, 'Greška pri proveri. Pokušajte ponovo.', 'error');
            })
            .finally(function () {
                giftBtn.disabled = false;
                giftBtn.textContent = 'Proveri';
            });
        });
    }

    /* --------------------------------------------------
       4. PROMO CODE (placeholder — wire to API as needed)
       -------------------------------------------------- */
    var promoBtn      = document.getElementById('coPromoApply');
    var promoInput    = document.getElementById('coPromoCode');
    var promoFeedback = document.getElementById('coPromoFeedback');

    if (promoBtn && promoInput) {
        promoBtn.addEventListener('click', function () {
            var code = promoInput.value.trim();
            if (!code) {
                showFeedback(promoFeedback, 'Unesite promo kod.', 'error');
                return;
            }

            promoBtn.disabled = true;
            promoBtn.textContent = '...';

            fetch('/api/promo/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code: code })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.valid) {
                    showFeedback(promoFeedback, '✓ Promo kod primenjen! Popust: ' + (data.discount || ''), 'success');
                    /* Update summary total if server returns new total */
                    if (data.new_total) {
                        var totalEl = document.getElementById('coTotal');
                        if (totalEl) totalEl.textContent = data.new_total;
                    }
                    if (data.discount_display) {
                        var row = document.getElementById('coDiscountRow');
                        var val = document.getElementById('coDiscountValue');
                        if (row) row.style.display = 'flex';
                        if (val) val.textContent = '-' + data.discount_display;
                    }
                } else {
                    showFeedback(promoFeedback, '✗ ' + (data.error || 'Nevažeći promo kod.'), 'error');
                }
            })
            .catch(function () {
                showFeedback(promoFeedback, 'Greška pri proveri. Pokušajte ponovo.', 'error');
            })
            .finally(function () {
                promoBtn.disabled = false;
                promoBtn.textContent = 'Primeni';
            });
        });
    }

    /* --------------------------------------------------
       5. LOYALTY TOGGLE
       -------------------------------------------------- */
    var loyaltyCheck = document.getElementById('coUseLoyalty');
    var loyaltyField = document.getElementById('coLoyaltyField');

    if (loyaltyCheck && loyaltyField) {
        loyaltyCheck.addEventListener('change', function () {
            loyaltyField.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                var pts = document.getElementById('coLoyaltyPts');
                if (pts) pts.value = 0;
            }
        });
    }

    /* --------------------------------------------------
       6. FORM SUBMISSION
       -------------------------------------------------- */
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            var lbl = submitBtn.querySelector('span');
            if (lbl) lbl.textContent = 'Obrađujem…';
        });
    }

    /* --------------------------------------------------
       HELPERS
       -------------------------------------------------- */
    function showFeedback(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'co-feedback co-feedback--' + type;
    }

})();
