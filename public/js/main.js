/* ==========================================================================
   Egoire – Main Frontend JavaScript
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function () {

    /* ---------- CSRF Meta Token ---------- */
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    /* ---------- Mobile Menu ---------- */
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    const navOverlay = document.querySelector('.nav-overlay');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function () {
            mainNav.classList.toggle('open');
            if (navOverlay) navOverlay.classList.toggle('active');
            document.body.style.overflow = mainNav.classList.contains('open') ? 'hidden' : '';
        });
        if (navOverlay) {
            navOverlay.addEventListener('click', function () {
                mainNav.classList.remove('open');
                navOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    }

    /* ---------- Search Overlay ---------- */
    const searchTrigger = document.querySelector('.search-trigger');
    const searchOverlay = document.querySelector('.search-overlay');
    const searchClose = document.querySelector('.search-overlay-close');
    const searchInput = document.querySelector('.search-overlay-input');
    const searchResults = document.querySelector('.search-overlay-results');
    let searchTimer;

    if (searchTrigger && searchOverlay) {
        searchTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            if (searchInput) searchInput.focus();
        });

        if (searchClose) {
            searchClose.addEventListener('click', function () {
                searchOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        searchOverlay.addEventListener('click', function (e) {
            if (e.target === searchOverlay) {
                searchOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                const query = this.value.trim();
                if (query.length < 2) {
                    searchResults.innerHTML = '';
                    return;
                }
                searchTimer = setTimeout(function () {
                    fetch('/api/search?q=' + encodeURIComponent(query))
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (data.success && data.results.length > 0) {
                                let html = '';
                                data.results.forEach(function (item) {
                                    html += '<a href="' + item.url + '" class="search-overlay-item">';
                                    html += '<img src="' + item.image + '" alt="">';
                                    html += '<div class="info">';
                                    html += '<div class="name">' + item.name + '</div>';
                                    html += '<div class="price">' + item.formatted_price + '</div>';
                                    html += '</div></a>';
                                });
                                searchResults.innerHTML = html;
                            } else {
                                searchResults.innerHTML = '<p style="padding:16px;color:#999;text-align:center;">Nema rezultata</p>';
                            }
                        })
                        .catch(function () {
                            searchResults.innerHTML = '';
                        });
                }, 300);
            });
        }
    }

    /* ---------- Add to Cart (PDP) ---------- */
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-add-cart');
            const feedback = document.querySelector('.add-cart-feedback');
            const formData = new FormData(this);
            formData.append('csrf_token', csrfToken);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Dodavanje...';

            fetch('/api/cart/add', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    // Update cart badge
                    const badge = document.getElementById('cartCount');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.style.display = 'flex';
                    }
                    if (feedback) {
                        feedback.className = 'add-cart-feedback show success';
                        feedback.textContent = 'Proizvod je dodat u korpu!';
                    }
                } else {
                    if (feedback) {
                        feedback.className = 'add-cart-feedback show error';
                        feedback.textContent = data.error || 'Greška pri dodavanju.';
                    }
                }
            })
            .catch(function () {
                if (feedback) {
                    feedback.className = 'add-cart-feedback show error';
                    feedback.textContent = 'Greška pri dodavanju.';
                }
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Dodaj u korpu';
                setTimeout(function () {
                    if (feedback) feedback.classList.remove('show');
                }, 4000);
            });
        });
    }

    /* ---------- Cart Page – Quantity Update ---------- */
    document.querySelectorAll('.cart-qty-update').forEach(function (input) {
        input.addEventListener('change', function () {
            const cartId = this.dataset.cartId;
            const qty = parseInt(this.value);
            if (qty < 1) return;

            fetch('/api/cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ cart_id: cartId, quantity: qty })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Greška');
                }
            });
        });
    });

    /* ---------- Cart Page – Remove Item ---------- */
    document.querySelectorAll('.cart-remove-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Ukloniti proizvod iz korpe?')) return;
            const cartId = this.dataset.cartId;

            fetch('/api/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ cart_id: cartId })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });

    /* ---------- Quantity +/- Buttons ---------- */
    document.querySelectorAll('.qty-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('.qty-input');
            if (!input) return;
            let val = parseInt(input.value) || 1;
            if (this.dataset.action === 'minus' && val > 1) val--;
            if (this.dataset.action === 'plus') val++;
            input.value = val;
            input.dispatchEvent(new Event('change'));
        });
    });

    /* ---------- PDP Thumbnail Gallery ---------- */
    document.querySelectorAll('.pdp-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            const mainImg = document.querySelector('.pdp-main-image img');
            if (!mainImg) return;
            mainImg.src = this.dataset.full || this.querySelector('img').src;
            document.querySelectorAll('.pdp-thumb').forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    /* ---------- PDP Variant Selection ---------- */
    document.querySelectorAll('.variant-option').forEach(function (opt) {
        opt.addEventListener('click', function () {
            if (this.classList.contains('out-of-stock')) return;
            document.querySelectorAll('.variant-option').forEach(function (o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    /* ---------- Gift Card Validation (Checkout) ---------- */
    const gcBtn = document.getElementById('validate-gift-card');
    if (gcBtn) {
        gcBtn.addEventListener('click', function () {
            const input = document.getElementById('gift_card_code');
            const result = document.querySelector('.gift-card-result');
            if (!input || !input.value.trim()) return;

            fetch('/api/gift-card/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ code: input.value.trim() })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (result) {
                    if (data.success) {
                        result.className = 'gift-card-result valid';
                        result.textContent = '✓ Kartica validna. Stanje: ' + data.formatted;
                    } else {
                        result.className = 'gift-card-result invalid';
                        result.textContent = '✗ ' + data.error;
                    }
                }
            });
        });
    }

    /* ---------- Newsletter Subscription ---------- */
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = this.querySelector('input[type="email"]');
            const feedback = document.querySelector('.newsletter-feedback');
            if (!input || !input.value.trim()) return;

            fetch('/api/newsletter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ email: input.value.trim() })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (feedback) {
                    if (data.success) {
                        feedback.className = 'newsletter-feedback show success';
                        feedback.textContent = data.message;
                        input.value = '';
                    } else {
                        feedback.className = 'newsletter-feedback show error';
                        feedback.textContent = data.error;
                    }
                    setTimeout(function () { feedback.classList.remove('show'); }, 5000);
                }
            });
        });
    }

    /* ---------- FAQ Accordion ---------- */
    document.querySelectorAll('.faq-question').forEach(function (q) {
        q.addEventListener('click', function () {
            const item = this.closest('.faq-item');
            const wasOpen = item.classList.contains('open');
            // Close all
            document.querySelectorAll('.faq-item').forEach(function (fi) { fi.classList.remove('open'); });
            // Toggle
            if (!wasOpen) item.classList.add('open');
        });
    });

    /* ---------- Gift Card Amount Selection ---------- */
    document.querySelectorAll('.amount-option').forEach(function (opt) {
        opt.addEventListener('click', function () {
            document.querySelectorAll('.amount-option').forEach(function (o) { o.classList.remove('selected'); });
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            // Update preview
            const preview = document.querySelector('.gc-amount');
            if (preview && radio) {
                preview.textContent = parseInt(radio.value).toLocaleString('sr-RS') + ' RSD';
            }
        });
    });

    /* ---------- Checkout – Saved Address Fill ---------- */
    const addressSelect = document.getElementById('saved-address');
    if (addressSelect) {
        addressSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt.value) return;
            const fields = ['first_name', 'last_name', 'phone', 'address', 'city', 'postal_code', 'country'];
            fields.forEach(function (f) {
                const el = document.getElementById(f);
                if (el && opt.dataset[f]) el.value = opt.dataset[f];
            });
        });
    }

    /* ---------- Smooth Scroll for Anchor Links ---------- */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* ---------- Lazy Load Images ---------- */
    if ('IntersectionObserver' in window) {
        const imgObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imgObserver.unobserve(img);
                }
            });
        }, { rootMargin: '100px' });

        document.querySelectorAll('img[data-src]').forEach(function (img) {
            imgObserver.observe(img);
        });
    }

    /* ---------- Confirm Delete ---------- */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Da li ste sigurni?')) {
                e.preventDefault();
            }
        });
    });

    /* ---------- Auto-hide flash messages ---------- */
    document.querySelectorAll('.flash').forEach(function (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(function () { flash.remove(); }, 500);
        }, 6000);
    });
});
