/* ============================================================
   Egoire – Luxury Product Detail JS
   File:  public/js/product-details.js
   ============================================================
   Features:
   1. Image gallery — fade transition, arrows, thumbnail click
   2. Mobile swipe (touch events)
   3. Dot indicators sync
   4. Quantity stepper
   5. Accordion toggle
   6. AJAX Add-to-Cart
   ============================================================ */
(function () {
    'use strict';

    /* ==========================================================
       1. IMAGE GALLERY
       ========================================================== */
    var gallery = document.querySelector('.pd-gallery');
    if (gallery) {
        var images      = gallery.querySelectorAll('.pd-gallery__image');
        var thumbs      = gallery.querySelectorAll('[data-gallery-thumb]');
        var dots        = gallery.querySelectorAll('[data-gallery-dot]');
        var prevBtn     = gallery.querySelector('[data-gallery-prev]');
        var nextBtn     = gallery.querySelector('[data-gallery-next]');
        var counterEl   = gallery.querySelector('[data-gallery-current]');
        var total       = images.length;
        var current     = 0;

        function goTo(index) {
            if (index < 0) index = total - 1;
            if (index >= total) index = 0;
            if (index === current) return;

            // Fade images
            images[current].classList.remove('is-active');
            images[index].classList.add('is-active');

            // Update thumbnails
            if (thumbs.length) {
                thumbs[current].classList.remove('is-active');
                thumbs[index].classList.add('is-active');
                // Scroll thumbnail into view
                thumbs[index].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }

            // Update dots
            if (dots.length) {
                dots[current].classList.remove('is-active');
                dots[index].classList.add('is-active');
            }

            // Update counter
            if (counterEl) {
                counterEl.textContent = index + 1;
            }

            current = index;
        }

        // Arrow clicks
        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); });

        // Thumbnail clicks
        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                goTo(parseInt(this.dataset.galleryThumb, 10));
            });
        });

        // Dot clicks
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(this.dataset.galleryDot, 10));
            });
        });

        // Keyboard navigation (when gallery is in viewport)
        document.addEventListener('keydown', function (e) {
            if (total <= 1) return;
            var rect = gallery.getBoundingClientRect();
            var inView = rect.top < window.innerHeight && rect.bottom > 0;
            if (!inView) return;
            if (e.key === 'ArrowLeft') { e.preventDefault(); goTo(current - 1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); goTo(current + 1); }
        });

        /* --- Mobile Swipe --- */
        var stage = gallery.querySelector('.pd-gallery__stage');
        if (stage && total > 1) {
            var touchStartX = 0;
            var touchStartY = 0;
            var touchDiffX  = 0;
            var isSwiping   = false;

            stage.addEventListener('touchstart', function (e) {
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                isSwiping = false;
            }, { passive: true });

            stage.addEventListener('touchmove', function (e) {
                var diffX = e.touches[0].clientX - touchStartX;
                var diffY = e.touches[0].clientY - touchStartY;
                touchDiffX = diffX;

                // Only horizontal swipe — prevent vertical scroll hijack
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    isSwiping = true;
                    e.preventDefault();
                }
            }, { passive: false });

            stage.addEventListener('touchend', function () {
                if (!isSwiping) return;
                var threshold = 50;
                if (touchDiffX < -threshold) {
                    goTo(current + 1);
                } else if (touchDiffX > threshold) {
                    goTo(current - 1);
                }
                touchDiffX = 0;
                isSwiping = false;
            }, { passive: true });
        }
    }


    /* ==========================================================
       2. QUANTITY STEPPER
       ========================================================== */
    document.querySelectorAll('.pd-stepper').forEach(function (stepper) {
        var input    = stepper.querySelector('.pd-stepper__input');
        var minusBtn = stepper.querySelector('[data-action="minus"]');
        var plusBtn  = stepper.querySelector('[data-action="plus"]');
        if (!input) return;

        var minVal = parseInt(input.min, 10) || 1;
        var maxVal = parseInt(input.max, 10) || 99;

        function clamp(v) {
            return Math.max(minVal, Math.min(maxVal, v));
        }

        if (minusBtn) {
            minusBtn.addEventListener('click', function () {
                input.value = clamp(parseInt(input.value, 10) - 1);
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener('click', function () {
                input.value = clamp(parseInt(input.value, 10) + 1);
            });
        }

        input.addEventListener('change', function () {
            this.value = clamp(parseInt(this.value, 10) || 1);
        });
    });


    /* ==========================================================
       3. ACCORDION
       ========================================================== */
    document.querySelectorAll('[data-pd-accordion]').forEach(function (acc) {
        var trigger = acc.querySelector('.pd-accordion__trigger');
        var panel   = acc.querySelector('.pd-accordion__panel');
        if (!trigger || !panel) return;

        // Initialize open panels
        if (trigger.getAttribute('aria-expanded') === 'true') {
            panel.classList.add('is-open');
            panel.style.maxHeight = panel.scrollHeight + 'px';
        }

        trigger.addEventListener('click', function () {
            var isOpen = panel.classList.contains('is-open');

            if (isOpen) {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                // Force reflow
                panel.offsetHeight;
                panel.style.maxHeight = '0';
                panel.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            } else {
                panel.classList.add('is-open');
                panel.style.maxHeight = panel.scrollHeight + 'px';
                trigger.setAttribute('aria-expanded', 'true');

                // After transition, remove inline max-height
                // so that dynamic content works
                panel.addEventListener('transitionend', function handler() {
                    if (panel.classList.contains('is-open')) {
                        panel.style.maxHeight = 'none';
                    }
                    panel.removeEventListener('transitionend', handler);
                });
            }
        });
    });


    /* ==========================================================
       4. ADD TO CART — AJAX
       ========================================================== */
    var cartForm = document.getElementById('pdCartForm');
    if (cartForm) {
        cartForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn      = document.getElementById('pdCartBtn');
            var feedback = document.getElementById('pdCartFeedback');
            var formData = new FormData(cartForm);

            var csrfMeta  = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfMeta ? csrfMeta.content : '';

            // Save original button content
            var originalHTML = btn.innerHTML;

            // Visual loading state
            btn.disabled = true;
            btn.innerHTML = '<span>Dodajem…</span>';

            fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    // Success state
                    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>Dodato u korpu!</span>';

                    // Update header cart badge
                    var badge = document.getElementById('cartCount');
                    if (badge && data.cart_count !== undefined) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'flex';
                    }

                    // Show feedback
                    if (feedback) {
                        feedback.textContent = 'Proizvod je uspešno dodat u korpu.';
                        feedback.className = 'pd-info__feedback is-visible is-success';
                    }

                    // Reset after 2.5s
                    setTimeout(function () {
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                        if (feedback) {
                            feedback.className = 'pd-info__feedback';
                        }
                    }, 2500);
                } else {
                    // Error from server
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                    if (feedback) {
                        feedback.textContent = data.error || 'Greška pri dodavanju.';
                        feedback.className = 'pd-info__feedback is-visible is-error';
                        setTimeout(function () {
                            feedback.className = 'pd-info__feedback';
                        }, 4000);
                    }
                }
            })
            .catch(function () {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                if (feedback) {
                    feedback.textContent = 'Greška pri dodavanju u korpu.';
                    feedback.className = 'pd-info__feedback is-visible is-error';
                    setTimeout(function () {
                        feedback.className = 'pd-info__feedback';
                    }, 4000);
                }
            });
        });
    }

    /* ==========================================================
       7. VARIANT SELECTOR — toggle active class & sync hidden input
       ========================================================== */
    var variantInputs = document.querySelectorAll('.pd-variant input[type="radio"]');
    var hiddenVariantId = document.getElementById('pdVariantId');
    if (variantInputs.length) {
        variantInputs.forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.pd-variant').forEach(function (el) {
                    el.classList.remove('pd-variant--active');
                });
                radio.closest('.pd-variant').classList.add('pd-variant--active');

                // Update hidden form field
                if (hiddenVariantId) {
                    hiddenVariantId.value = radio.value;
                }
            });
        });
    }

})();
