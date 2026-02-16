/* ============================================================
   Egoire – Header Module
   Handles: Scroll compression, mobile panel, accordion, search
   File: public/js/header.js
   ============================================================ */

(function () {
    'use strict';

    var header      = document.getElementById('egHeader');
    var hamburger   = document.getElementById('egHamburger');
    var mobilePanel = document.getElementById('egMobilePanel');
    var overlay     = document.getElementById('egOverlay');
    var mobileClose = document.getElementById('egMobileClose');
    var searchPanel = document.getElementById('egSearch');
    var searchInput = document.getElementById('egSearchInput');
    var searchClose = document.getElementById('egSearchClose');
    var searchResults = document.getElementById('egSearchResults');

    if (!header) return;


    /* ==========================================================
       SCROLL COMPRESSION
       rAF-throttled listener, toggles .is-scrolled at threshold
       ========================================================== */

    var SCROLL_THRESHOLD = 40;
    var ticking = false;

    function handleScroll() {
        if (window.scrollY > SCROLL_THRESHOLD) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(handleScroll);
            ticking = true;
        }
    }, { passive: true });

    /* Run once on load in case page is already scrolled */
    handleScroll();


    /* ==========================================================
       MOBILE PANEL — open / close
       ========================================================== */

    function openPanel() {
        if (!mobilePanel || !overlay) return;
        closeSearch(); /* always close search first */
        mobilePanel.classList.add('is-open');
        overlay.classList.add('is-active');
        if (hamburger) {
            hamburger.classList.add('is-active');
            hamburger.setAttribute('aria-expanded', 'true');
        }
        document.body.style.overflow = 'hidden';
    }

    function closePanel() {
        if (!mobilePanel || !overlay) return;
        mobilePanel.classList.remove('is-open');
        overlay.classList.remove('is-active');
        if (hamburger) {
            hamburger.classList.remove('is-active');
            hamburger.setAttribute('aria-expanded', 'false');
        }
        document.body.style.overflow = '';
    }

    if (hamburger) {
        hamburger.addEventListener('click', function () {
            if (mobilePanel.classList.contains('is-open')) {
                closePanel();
            } else {
                openPanel();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closePanel);
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', closePanel);
    }


    /* ==========================================================
       SEARCH DROPDOWN
       Bound to ALL .eg-search-trigger elements (desktop + mobile)
       ========================================================== */

    function openSearch() {
        if (!searchPanel) return;
        /* Close mobile panel first if open */
        if (mobilePanel && mobilePanel.classList.contains('is-open')) {
            closePanel();
        }
        searchPanel.classList.add('is-open');
        if (searchInput) {
            setTimeout(function () { searchInput.focus(); }, 80);
        }
    }

    function closeSearch() {
        if (!searchPanel) return;
        searchPanel.classList.remove('is-open');
        if (searchInput) searchInput.value = '';
        if (searchResults) searchResults.innerHTML = '';
    }

    function isSearchOpen() {
        return searchPanel && searchPanel.classList.contains('is-open');
    }

    /* Bind ALL triggers (desktop button + mobile button) */
    var searchTriggers = document.querySelectorAll('.eg-search-trigger');
    searchTriggers.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (isSearchOpen()) {
                closeSearch();
            } else {
                openSearch();
            }
        });
    });

    /* Close button inside search panel */
    if (searchClose) {
        searchClose.addEventListener('click', closeSearch);
    }

    /* Close search when clicking outside */
    document.addEventListener('click', function (e) {
        if (!isSearchOpen()) return;
        /* Ignore clicks inside the search panel or on triggers */
        if (searchPanel.contains(e.target)) return;
        var isTrigger = false;
        searchTriggers.forEach(function (btn) {
            if (btn.contains(e.target)) isTrigger = true;
        });
        if (!isTrigger) closeSearch();
    });


    /* ---------- AJAX Search ---------- */

    var searchTimer;
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');

    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            var query = this.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            searchTimer = setTimeout(function () {
                fetch('/api/search?q=' + encodeURIComponent(query))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success && data.results && data.results.length > 0) {
                            var html = '';
                            data.results.forEach(function (item) {
                                html += '<a href="' + item.url + '" class="eg-search__result-item">';
                                html += '<img src="' + (item.image || '') + '" alt="" class="eg-search__result-img">';
                                html += '<div class="eg-search__result-info">';
                                html += '<p class="eg-search__result-name">' + item.name + '</p>';
                                html += '<p class="eg-search__result-price">' + item.formatted_price + '</p>';
                                html += '</div></a>';
                            });
                            searchResults.innerHTML = html;
                        } else {
                            searchResults.innerHTML = '<p class="eg-search__no-results">Nema rezultata</p>';
                        }
                    })
                    .catch(function () {
                        searchResults.innerHTML = '';
                    });
            }, 300);
        });

        /* Submit form on Enter */
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                var q = this.value.trim();
                if (q.length > 0) {
                    window.location.href = '/search?q=' + encodeURIComponent(q);
                }
                e.preventDefault();
            }
        });
    }


    /* ==========================================================
       ESCAPE KEY — closes whatever is open
       ========================================================== */

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (isSearchOpen()) {
            closeSearch();
        } else if (mobilePanel && mobilePanel.classList.contains('is-open')) {
            closePanel();
        }
    });


    /* ==========================================================
       ACCORDION — single-open, smooth height animation
       ========================================================== */

    var triggers = document.querySelectorAll('.eg-accordion__trigger');

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var item    = this.closest('.eg-accordion__item');
            var content = item.querySelector('.eg-accordion__content');
            var isOpen  = item.classList.contains('is-open');

            /* Close every open accordion item */
            document.querySelectorAll('.eg-accordion__item.is-open').forEach(function (openItem) {
                openItem.classList.remove('is-open');
                var openContent = openItem.querySelector('.eg-accordion__content');
                if (openContent) openContent.style.maxHeight = null;
            });

            /* Toggle the clicked item */
            if (!isOpen) {
                item.classList.add('is-open');
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });

})();
