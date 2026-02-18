    </main>

    <!-- ═══════════════════════════════════════════════════════════════
         FOOTER — Luxury Editorial Design
         Three-tier: Brand → Navigation → Legal
    ═══════════════════════════════════════════════════════════════ -->
    <footer class="lx-footer" role="contentinfo">

        <!-- ── Top: Brand Identity ── -->
        <div class="lx-footer__top">
            <div class="lx-footer__container">
                <div class="lx-footer__brand">
                    <a href="/" class="lx-footer__logo" aria-label="Egoire — Početna">EGOIRE</a>
                    <p class="lx-footer__tagline">
                        Luksuzna nega kose. Premium brendovi, pažljivo birani za one koji
                        ne pristaju na kompromis.
                    </p>
                </div>
                <div class="lx-footer__social">
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="lx-footer__social-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5"/>
                            <circle cx="12" cy="12" r="5"/>
                            <circle cx="17.5" cy="6.5" r="1.2"/>
                        </svg>
                    </a>
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="lx-footer__social-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                        </svg>
                    </a>
                    <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" aria-label="TikTok" class="lx-footer__social-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12a4 4 0 104 4V4a5 5 0 005 5"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Middle: Navigation Columns ── -->
        <div class="lx-footer__middle">
            <div class="lx-footer__container">
                <nav class="lx-footer__nav" aria-label="Footer navigacija">

                    <!-- Column 1 — Navigacija -->
                    <div class="lx-footer__col">
                        <h4 class="lx-footer__heading">Navigacija</h4>
                        <ul class="lx-footer__list">
                            <li><a href="/products">Svi proizvodi</a></li>
                            <li><a href="/categories">Kategorije</a></li>
                            <li><a href="/brands">Brendovi</a></li>
                            <li><a href="/gift-bag">Gift Bag</a></li>
                            <li><a href="/gift-card">Gift Card</a></li>
                        </ul>
                    </div>

                    <!-- Column 2 — Informacije -->
                    <div class="lx-footer__col">
                        <h4 class="lx-footer__heading">Informacije</h4>
                        <ul class="lx-footer__list">
                            <li><a href="/about">O nama</a></li>
                            <li><a href="/blog">Blog</a></li>
                            <li><a href="/faq">Česta pitanja</a></li>
                            <li><a href="/contact">Kontakt</a></li>
                            <li><a href="/shipping">Isporuka i povraćaj</a></li>
                        </ul>
                    </div>

                    <!-- Column 3 — Podrška -->
                    <div class="lx-footer__col">
                        <h4 class="lx-footer__heading">Podrška</h4>
                        <ul class="lx-footer__list">
                            <li><a href="/contact">Konsultacije</a></li>
                            <li><a href="/privacy">Politika privatnosti</a></li>
                            <li><a href="/terms">Uslovi korišćenja</a></li>
                        </ul>
                    </div>

                    <!-- Column 4 — Newsletter -->
                    <div class="lx-footer__col lx-footer__col--newsletter">
                        <h4 class="lx-footer__heading">Newsletter</h4>
                        <p class="lx-footer__newsletter-desc">
                            Pridružite se našem svetu lepote. Ekskluzivne ponude, novi
                            brendovi i beauty saveti — direktno u vaš inbox.
                        </p>
                        <form class="lx-footer__newsletter-form" id="newsletterForm" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <div class="lx-footer__input-wrap">
                                <input
                                    type="email"
                                    name="email"
                                    class="lx-footer__input"
                                    placeholder="Vaša email adresa"
                                    required
                                    aria-label="Email adresa za newsletter"
                                >
                                <button type="submit" class="lx-footer__submit" aria-label="Prijavite se">
                                    <span>Prijavi se</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                        <div id="newsletterMsg" class="lx-footer__form-msg"></div>
                    </div>

                </nav>
            </div>
        </div>

        <!-- ── Bottom: Legal & Payments ── -->
        <div class="lx-footer__bottom">
            <div class="lx-footer__container">
                <p class="lx-footer__copyright">
                    &copy; <?= date('Y') ?> Egoire. Sva prava zadržana.
                </p>

                <!-- Payment icons (minimal monochrome) -->
                <div class="lx-footer__payments" aria-label="Načini plaćanja">
                    <!-- Visa -->
                    <svg class="lx-footer__payment-icon" viewBox="0 0 48 32" aria-label="Visa">
                        <rect width="48" height="32" rx="4" fill="none" stroke="currentColor" stroke-width="1"/>
                        <text x="24" y="20" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" font-weight="600" fill="currentColor">VISA</text>
                    </svg>
                    <!-- Mastercard -->
                    <svg class="lx-footer__payment-icon" viewBox="0 0 48 32" aria-label="Mastercard">
                        <rect width="48" height="32" rx="4" fill="none" stroke="currentColor" stroke-width="1"/>
                        <circle cx="19" cy="16" r="7" fill="none" stroke="currentColor" stroke-width="1"/>
                        <circle cx="29" cy="16" r="7" fill="none" stroke="currentColor" stroke-width="1"/>
                    </svg>
                    <!-- Maestro -->
                    <svg class="lx-footer__payment-icon" viewBox="0 0 48 32" aria-label="Maestro">
                        <rect width="48" height="32" rx="4" fill="none" stroke="currentColor" stroke-width="1"/>
                        <circle cx="19" cy="16" r="7" fill="none" stroke="currentColor" stroke-width="1" opacity=".6"/>
                        <circle cx="29" cy="16" r="7" fill="none" stroke="currentColor" stroke-width="1" opacity=".6"/>
                        <text x="24" y="27" text-anchor="middle" font-family="Inter,sans-serif" font-size="4.5" fill="currentColor" opacity=".7">MAESTRO</text>
                    </svg>
                </div>

                <div class="lx-footer__legal">
                    <a href="/terms">Uslovi korišćenja</a>
                    <span class="lx-footer__legal-dot" aria-hidden="true"></span>
                    <a href="/privacy">Politika privatnosti</a>
                </div>
            </div>
        </div>

    </footer>

    <script src="<?= asset('/js/header.js') ?>"></script>
    <script src="<?= asset('/js/main.js') ?>"></script>
    <?php if (!empty($pageScripts)): ?>
    <?php foreach ($pageScripts as $__pjs): ?>
    <script src="<?= asset($__pjs) ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
    <script src="/pwa.js"></script>
</body>
</html>
