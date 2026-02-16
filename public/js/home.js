/* ============================================================
   Egoire – Luxury Homepage JavaScript
   File:  public/js/home.js
   ============================================================

   Features:
   1.  Scroll-triggered reveal animations (IntersectionObserver)
   2.  Product slider (scroll-snap + arrows + dots)
   3.  Testimonial slider (transform-based + autoplay)
   4.  Quantity stepper (+/−)
   5.  Add-to-bag AJAX
   6.  Contact form AJAX
   ============================================================ */

(function () {
    'use strict';

    /* ----------------------------------------------------------
       CSRF Token
       ---------------------------------------------------------- */
    var csrfMeta  = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';


    /* ==========================================================
       1. REVEAL ANIMATIONS
       ========================================================== */

    function initReveal() {
        var elements = document.querySelectorAll('[data-reveal]');
        if (!elements.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el    = entry.target;
                    var delay = parseInt(el.getAttribute('data-delay') || '0', 10);

                    setTimeout(function () {
                        el.classList.add('is-revealed');
                    }, delay);

                    observer.unobserve(el);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        });

        elements.forEach(function (el) {
            observer.observe(el);
        });
    }


    /* ==========================================================
       2. PRODUCT SLIDERS
       ========================================================== */

    function initSliders() {
        var sliders = document.querySelectorAll('[data-slider]');

        sliders.forEach(function (slider) {
            var track    = slider.querySelector('.eh-slider__track');
            var slides   = slider.querySelectorAll('.eh-slider__slide');
            var prevBtn  = slider.querySelector('.eh-slider__arrow--prev');
            var nextBtn  = slider.querySelector('.eh-slider__arrow--next');
            var dotsWrap = slider.querySelector('.eh-slider__dots');

            if (!track || slides.length < 1) return;

            /* --- Build dots --- */
            var dotCount = calculateDotCount(track, slides);
            buildDots(dotsWrap, dotCount);
            var dots = dotsWrap ? dotsWrap.querySelectorAll('.eh-slider__dot') : [];

            /* --- Arrow clicks --- */
            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    var slideWidth = slides[0].offsetWidth +
                        parseInt(getComputedStyle(track).gap || '0', 10);
                    track.scrollBy({ left: -slideWidth, behavior: 'smooth' });
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    var slideWidth = slides[0].offsetWidth +
                        parseInt(getComputedStyle(track).gap || '0', 10);
                    track.scrollBy({ left: slideWidth, behavior: 'smooth' });
                });
            }

            /* --- Dot click --- */
            dots.forEach(function (dot, i) {
                dot.addEventListener('click', function () {
                    var slideWidth = slides[0].offsetWidth +
                        parseInt(getComputedStyle(track).gap || '0', 10);
                    track.scrollTo({ left: slideWidth * i, behavior: 'smooth' });
                });
            });

            /* --- Sync dots on scroll --- */
            var scrollTimer;
            track.addEventListener('scroll', function () {
                clearTimeout(scrollTimer);
                scrollTimer = setTimeout(function () {
                    syncDots(track, slides, dots);
                }, 80);
            });

            /* --- Recalculate dots on resize --- */
            var resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    var newDotCount = calculateDotCount(track, slides);
                    buildDots(dotsWrap, newDotCount);
                    dots = dotsWrap ? dotsWrap.querySelectorAll('.eh-slider__dot') : [];

                    /* Re-bind dot clicks */
                    dots.forEach(function (dot, i) {
                        dot.addEventListener('click', function () {
                            var slideWidth = slides[0].offsetWidth +
                                parseInt(getComputedStyle(track).gap || '0', 10);
                            track.scrollTo({ left: slideWidth * i, behavior: 'smooth' });
                        });
                    });

                    syncDots(track, slides, dots);
                }, 250);
            });

            /* Initial sync */
            syncDots(track, slides, dots);
        });
    }

    function calculateDotCount(track, slides) {
        if (!slides.length) return 0;
        var slideWidth = slides[0].offsetWidth +
            parseInt(getComputedStyle(track).gap || '0', 10);
        var visible = Math.round(track.offsetWidth / slideWidth);
        return Math.max(1, slides.length - visible + 1);
    }

    function buildDots(container, count) {
        if (!container) return;
        container.innerHTML = '';
        for (var i = 0; i < count; i++) {
            var dot = document.createElement('button');
            dot.className = 'eh-slider__dot' + (i === 0 ? ' is-active' : '');
            dot.setAttribute('aria-label', 'Stranica ' + (i + 1));
            container.appendChild(dot);
        }
    }

    function syncDots(track, slides, dots) {
        if (!dots.length || !slides.length) return;
        var slideWidth = slides[0].offsetWidth +
            parseInt(getComputedStyle(track).gap || '0', 10);
        var index = Math.round(track.scrollLeft / slideWidth);
        index = Math.min(index, dots.length - 1);

        dots.forEach(function (d, i) {
            d.classList.toggle('is-active', i === index);
        });
    }


    /* ==========================================================
       3. TESTIMONIAL SLIDER
       ========================================================== */

    function initTestimonials() {
        var wrapper  = document.querySelector('[data-testimonials]');
        if (!wrapper) return;

        var track    = wrapper.querySelector('.eh-testimonials__track');
        var slides   = wrapper.querySelectorAll('.eh-testimonials__slide');
        var dotsWrap = wrapper.querySelector('.eh-testimonials__dots');

        if (!track || slides.length < 2) return;

        var current   = 0;
        var total     = slides.length;
        var autoTimer = null;
        var INTERVAL  = 6000;

        /* Build dots */
        for (var i = 0; i < total; i++) {
            var dot = document.createElement('button');
            dot.className = 'eh-testimonials__dot' + (i === 0 ? ' is-active' : '');
            dot.setAttribute('aria-label', 'Recenzija ' + (i + 1));
            dot.dataset.index = i;
            dotsWrap.appendChild(dot);
        }

        var dots = dotsWrap.querySelectorAll('.eh-testimonials__dot');

        function goTo(index) {
            current = ((index % total) + total) % total;
            track.style.transform = 'translateX(-' + (current * 100) + '%)';
            dots.forEach(function (d, j) {
                d.classList.toggle('is-active', j === current);
            });
        }

        /* Dot clicks */
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(this.dataset.index, 10));
                resetAuto();
            });
        });

        /* Autoplay */
        function startAuto() {
            autoTimer = setInterval(function () {
                goTo(current + 1);
            }, INTERVAL);
        }

        function resetAuto() {
            clearInterval(autoTimer);
            startAuto();
        }

        startAuto();

        /* Pause on hover */
        wrapper.addEventListener('mouseenter', function () {
            clearInterval(autoTimer);
        });

        wrapper.addEventListener('mouseleave', function () {
            startAuto();
        });

        /* Touch swipe for testimonials */
        var touchStartX = 0;
        var touchDiff   = 0;

        wrapper.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
            clearInterval(autoTimer);
        }, { passive: true });

        wrapper.addEventListener('touchmove', function (e) {
            touchDiff = e.touches[0].clientX - touchStartX;
        }, { passive: true });

        wrapper.addEventListener('touchend', function () {
            if (Math.abs(touchDiff) > 50) {
                goTo(touchDiff > 0 ? current - 1 : current + 1);
            }
            touchDiff = 0;
            startAuto();
        });
    }


    /* ==========================================================
       4. QUANTITY STEPPER
       ========================================================== */

    function initSteppers() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.eh-stepper__btn');
            if (!btn) return;

            var stepper = btn.closest('.eh-stepper');
            var input   = stepper ? stepper.querySelector('.eh-stepper__input') : null;
            if (!input) return;

            var val = parseInt(input.value, 10) || 1;
            var action = btn.getAttribute('data-action');

            if (action === 'minus' && val > 1) val--;
            if (action === 'plus' && val < 99) val++;

            input.value = val;
        });
    }


    /* ==========================================================
       5. ADD TO BAG (AJAX)
       ========================================================== */

    function initAddToBag() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.eh-card__add-btn');
            if (!btn) return;

            var card = btn.closest('.eh-card');
            if (!card) return;

            var productId = card.getAttribute('data-product-id');
            var input     = card.querySelector('.eh-stepper__input');
            var quantity  = input ? parseInt(input.value, 10) || 1 : 1;
            var label     = btn.querySelector('span');
            var origText  = label ? label.textContent : '';

            /* Prevent double-click */
            if (btn.disabled) return;
            btn.disabled = true;
            btn.classList.add('is-loading');
            if (label) label.textContent = 'Dodavanje…';

            var formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('/api/cart/add', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.classList.remove('is-loading');

                if (data.success) {
                    btn.classList.add('is-success');
                    if (label) label.textContent = 'Dodato ✓';

                    /* Update header cart badge */
                    var badge = document.getElementById('cartCount');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'flex';
                    }
                } else {
                    btn.classList.add('is-error');
                    if (label) label.textContent = data.error || 'Greška';
                }

                setTimeout(function () {
                    btn.classList.remove('is-success', 'is-error');
                    btn.disabled = false;
                    if (label) label.textContent = origText;
                }, 2200);
            })
            .catch(function () {
                btn.classList.remove('is-loading');
                btn.classList.add('is-error');
                if (label) label.textContent = 'Greška';

                setTimeout(function () {
                    btn.classList.remove('is-error');
                    btn.disabled = false;
                    if (label) label.textContent = origText;
                }, 2200);
            });
        });
    }


    /* ==========================================================
       6. CONTACT FORM (AJAX)
       ========================================================== */

    function initContactForm() {
        var form     = document.getElementById('ehContactForm');
        var feedback = document.getElementById('ehContactFeedback');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var submitBtn = form.querySelector('button[type="submit"]');
            var origText  = submitBtn ? submitBtn.textContent : '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Slanje…';
            }

            var formData = new FormData(form);

            fetch('/api/contact', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (feedback) {
                    feedback.hidden = false;
                    if (data.success) {
                        feedback.className = 'eh-form-feedback is-success';
                        feedback.textContent = data.message || 'Poruka je uspešno poslata!';
                        form.reset();
                    } else {
                        feedback.className = 'eh-form-feedback is-error';
                        feedback.textContent = data.error || 'Došlo je do greške.';
                    }

                    setTimeout(function () {
                        feedback.hidden = true;
                    }, 6000);
                }
            })
            .catch(function () {
                if (feedback) {
                    feedback.hidden = false;
                    feedback.className = 'eh-form-feedback is-error';
                    feedback.textContent = 'Greška pri slanju poruke.';
                }
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = origText;
                }
            });
        });
    }


    /* ==========================================================
       INIT — DOM Ready
       ========================================================== */

    document.addEventListener('DOMContentLoaded', function () {
        initReveal();
        initSliders();
        initTestimonials();
        initSteppers();
        initAddToBag();
        initContactForm();
    });

})();
