<?php
/* ============================================================
   Egoire – Premium Glassmorphism Header
   Layout: src/views/layout/header.php
   ============================================================ */
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrfMetaTag() ?>
    <title><?= htmlspecialchars($title ?? 'Egoire') ?> – Egoire</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('/css/header/header.css') ?>">
    <?php if (!empty($pageStyles)): ?>
    <?php foreach ($pageStyles as $__ps): ?>
    <link rel="stylesheet" href="<?= asset($__ps) ?>">
    <?php endforeach; ?>
    <?php endif; ?>
    <link rel="manifest" href="/manifest.json">
</head>
<body>

<!-- ============================================================
     Announcement Bar
     ============================================================ -->
<div class="eg-announce">
    <p>Besplatna dostava za porudžbine iznad 5.000 RSD</p>
</div>

<!-- ============================================================
     Header — Three-layer glassmorphism
     ============================================================ -->
<header class="eg-hdr" id="egHeader">
    <div class="eg-hdr__inner">

        <!-- Logo -->
        <a href="/" class="eg-logo">
            <img src="/images/logos/egoire-logo.png" alt="Egoire" class="eg-logo__img">
        </a>

        <!-- Desktop Navigation -->
        <nav class="eg-nav" role="navigation" aria-label="Glavna navigacija">
            <ul class="eg-nav__list">

                <!-- 1. Početna -->
                <li class="eg-nav__item">
                    <a href="/" class="eg-nav__link <?= currentPath() === '/' ? 'is-active' : '' ?>">Početna</a>
                </li>

                <!-- 1b. Proizvodi -->
                <li class="eg-nav__item">
                    <a href="/products" class="eg-nav__link <?= isActivePath('/products') ? 'is-active' : '' ?>">Proizvodi</a>
                </li>

                <!-- 2. Brendovi — logo grid dropdown -->
                <li class="eg-nav__item eg-nav__item--has-dd">
                    <a href="/brands" class="eg-nav__link <?= isActivePath('/brands') ? 'is-active' : '' ?>">
                        Brendovi
                        <svg class="eg-nav__chevron" width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.5 3.5 5 6 7.5 3.5"/></svg>
                    </a>
                    <div class="eg-dropdown">
                        <div class="eg-dropdown__panel">
                            <div class="eg-dropdown__inner">
                                <h3 class="eg-dropdown__title">Naši brendovi</h3>
                                <?php $headerBrands = fetchBrands(['active_only' => true]); ?>
                                <div class="eg-dropdown__brand-grid">
                                    <?php foreach ($headerBrands as $hb): ?>
                                    <a href="/brand/<?= htmlspecialchars($hb['slug']) ?>" class="eg-brand-tile">
                                        <?php if (!empty($hb['logo'])): ?>
                                        <img src="<?= htmlspecialchars($hb['logo']) ?>" alt="<?= htmlspecialchars($hb['name']) ?>" loading="lazy">
                                        <?php else: ?>
                                        <span class="eg-brand-tile__name"><?= htmlspecialchars($hb['name']) ?></span>
                                        <?php endif; ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="eg-dropdown__footer">
                                    <a href="/brands" class="eg-dropdown__view-all">Pogledaj sve brendove</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 3. Kategorije — multi-column dropdown -->
                <li class="eg-nav__item eg-nav__item--has-dd">
                    <a href="/categories" class="eg-nav__link <?= isActivePath('/categories') ? 'is-active' : '' ?>">
                        Kategorije
                        <svg class="eg-nav__chevron" width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.5 3.5 5 6 7.5 3.5"/></svg>
                    </a>
                    <div class="eg-dropdown">
                        <div class="eg-dropdown__panel">
                            <div class="eg-dropdown__inner">
                                <?php $headerParentCats = fetchCategories(['parent_id' => null, 'active_only' => true]); ?>
                                <div class="eg-dropdown__columns">
                                    <?php foreach ($headerParentCats as $hpc):
                                        $hpcSubs = fetchSubcategories((int) $hpc['id']);
                                    ?>
                                    <div class="eg-dropdown__col">
                                        <h4 class="eg-dropdown__col-title">
                                            <a href="/category/<?= htmlspecialchars($hpc['slug']) ?>"><?= htmlspecialchars($hpc['name']) ?></a>
                                        </h4>
                                        <?php if ($hpcSubs): ?>
                                        <ul class="eg-dropdown__links">
                                            <?php foreach ($hpcSubs as $sub): ?>
                                            <li><a href="/category/<?= htmlspecialchars($sub['slug']) ?>"><?= htmlspecialchars($sub['name']) ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 4. Poklon vrećice — text + image split -->
                <li class="eg-nav__item eg-nav__item--has-dd">
                    <a href="/gift-bag" class="eg-nav__link <?= isActivePath('/gift-bag') ? 'is-active' : '' ?>">
                        Poklon vrećice
                        <svg class="eg-nav__chevron" width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.5 3.5 5 6 7.5 3.5"/></svg>
                    </a>
                    <div class="eg-dropdown">
                        <div class="eg-dropdown__panel">
                            <div class="eg-dropdown__inner">
                                <div class="eg-dropdown__split">
                                    <div class="eg-dropdown__split-text">
                                        <h3 class="eg-dropdown__title">Poklon vrećice</h3>
                                        <p class="eg-dropdown__desc">Izaberite savršen poklon za voljenu osobu. Naše elegantne poklon vrećice sadrže pažljivo odabrane premium proizvode za negu kose.</p>
                                        <a href="/gift-bag" class="eg-dropdown__cta">Saznaj više</a>
                                    </div>
                                    <div class="eg-dropdown__split-media">
                                        <div class="img-placeholder img-placeholder--lg"></div>
                                        <img src="/images/gift-bag/gift-bag.png" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 5. Gift kartice — 3 card horizontal -->
                <li class="eg-nav__item eg-nav__item--has-dd">
                    <a href="/gift-card" class="eg-nav__link <?= isActivePath('/gift-card') ? 'is-active' : '' ?>">
                        Gift kartice
                        <svg class="eg-nav__chevron" width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.5 3.5 5 6 7.5 3.5"/></svg>
                    </a>
                    <div class="eg-dropdown">
                        <div class="eg-dropdown__panel">
                            <div class="eg-dropdown__inner">
                                <h3 class="eg-dropdown__title">Gift kartice</h3>
                                <div class="eg-dropdown__gift-grid">
                                    <div class="eg-gift-tile">
                                        <div class="img-placeholder img-placeholder--card"></div>
                                        <!-- image placeholder -->
                                        <p class="eg-gift-tile__label">2.000 RSD</p>
                                        <p class="eg-gift-tile__desc">Idealan poklon za prijatelje</p>
                                    </div>
                                    <div class="eg-gift-tile">
                                        <div class="img-placeholder img-placeholder--card"></div>
                                        <!-- image placeholder -->
                                        <p class="eg-gift-tile__label">5.000 RSD</p>
                                        <p class="eg-gift-tile__desc">Za posebne prilike</p>
                                    </div>
                                    <div class="eg-gift-tile">
                                        <div class="img-placeholder img-placeholder--card"></div>
                                        <!-- image placeholder -->
                                        <p class="eg-gift-tile__label">10.000 RSD</p>
                                        <p class="eg-gift-tile__desc">Premium iskustvo nege</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 6. Loyalty — text + CTA gold button -->
                <li class="eg-nav__item eg-nav__item--has-dd">
                    <a href="/account/loyalty" class="eg-nav__link <?= isActivePath('/loyalty') || isActivePath('/account/loyalty') ? 'is-active' : '' ?>">
                        Loyalty
                        <svg class="eg-nav__chevron" width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.5 3.5 5 6 7.5 3.5"/></svg>
                    </a>
                    <div class="eg-dropdown">
                        <div class="eg-dropdown__panel">
                            <div class="eg-dropdown__inner eg-dropdown__inner--narrow">
                                <h3 class="eg-dropdown__title">Loyalty Program</h3>
                                <p class="eg-dropdown__desc">Skupljajte poene sa svakom kupovinom i uživajte u ekskluzivnim pogodnostima. Pridružite se našoj zajednici ljubitelja premium nege kose.</p>
                                <a href="/register" class="eg-btn-gold">Registruj se</a>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- 7. Konsultacije — direct link -->
                <li class="eg-nav__item">
                    <a href="/contact" class="eg-nav__link <?= isActivePath('/contact') ? 'is-active' : '' ?>">Konsultacije</a>
                </li>

            </ul>
        </nav>

        <!-- Header Actions — Search, Profile, Cart -->
        <div class="eg-actions">

            <!-- Search trigger -->
            <button class="eg-actions__btn eg-search-trigger" aria-label="Pretraga">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
            </button>

            <!-- Profile / Login -->
            <?php if (isUserAuthenticated()): ?>
                <a href="/account" class="eg-actions__btn" aria-label="Moj nalog">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
            <?php else: ?>
                <a href="/login" class="eg-actions__btn" aria-label="Prijava">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
            <?php endif; ?>

            <!-- Cart with gold badge -->
            <a href="/cart" class="eg-actions__btn eg-actions__cart" aria-label="Korpa">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                    <path d="M3 6h18"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span class="eg-actions__badge" id="cartCount"><?= cartItemCount() ?></span>
            </a>

            <!-- Hamburger (visible ≤ 1024px) -->
            <button class="eg-hamburger" id="egHamburger" aria-label="Otvori meni" aria-expanded="false">
                <span class="eg-hamburger__line"></span>
                <span class="eg-hamburger__line"></span>
                <span class="eg-hamburger__line"></span>
            </button>

        </div>
    </div>

    <!-- Search Dropdown — glassmorphism panel -->
    <div class="eg-search" id="egSearch">
        <div class="eg-search__panel">
            <div class="eg-search__inner">
                <form class="eg-search__form" action="/search" method="GET" autocomplete="off">
                    <svg class="eg-search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" name="q" class="eg-search__input" id="egSearchInput" placeholder="Pretražite proizvode...">
                    <button type="button" class="eg-search__close" id="egSearchClose" aria-label="Zatvori pretragu">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                    </button>
                </form>
                <div class="eg-search__results" id="egSearchResults"></div>
            </div>
        </div>
    </div>
</header>

<!-- ============================================================
     Mobile Overlay + Slide-in Panel
     ============================================================ -->
<div class="eg-overlay" id="egOverlay"></div>

<aside class="eg-mobile" id="egMobilePanel" aria-label="Mobilni meni">

    <!-- Close -->
    <div class="eg-mobile__head">
        <button class="eg-mobile__close" id="egMobileClose" aria-label="Zatvori meni">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <path d="M18 6 6 18"/>
                <path d="M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Top row: search, profile, cart -->
    <div class="eg-mobile__actions-row">
        <button class="eg-mobile__action-btn eg-search-trigger" aria-label="Pretraga">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <span>Pretraga</span>
        </button>
        <?php if (isUserAuthenticated()): ?>
            <a href="/account" class="eg-mobile__action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Nalog</span>
            </a>
        <?php else: ?>
            <a href="/login" class="eg-mobile__action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Prijava</span>
            </a>
        <?php endif; ?>
        <a href="/cart" class="eg-mobile__action-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                <path d="M3 6h18"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <span>Korpa</span>
        </a>
    </div>

    <!-- Mobile accordion navigation -->
    <nav class="eg-mobile__nav">

        <a href="/" class="eg-mobile__link">Početna</a>
        <a href="/products" class="eg-mobile__link">Proizvodi</a>

        <!-- Brendovi -->
        <div class="eg-accordion__item">
            <button class="eg-accordion__trigger">
                <span>Brendovi</span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
            </button>
            <div class="eg-accordion__content">
                <div class="eg-accordion__pad">
                    <?php $mobileBrands = $headerBrands ?? fetchBrands(['active_only' => true]); ?>
                    <?php foreach ($mobileBrands as $mb): ?>
                    <a href="/brand/<?= htmlspecialchars($mb['slug']) ?>" class="eg-mobile__sublink"><?= htmlspecialchars($mb['name']) ?></a>
                    <?php endforeach; ?>
                    <a href="/brands" class="eg-mobile__sublink eg-mobile__sublink--all">Svi brendovi</a>
                </div>
            </div>
        </div>

        <!-- Kategorije -->
        <div class="eg-accordion__item">
            <button class="eg-accordion__trigger">
                <span>Kategorije</span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
            </button>
            <div class="eg-accordion__content">
                <div class="eg-accordion__pad">
                    <?php $mobileCats = $headerParentCats ?? fetchCategories(['parent_id' => null, 'active_only' => true]); ?>
                    <?php foreach ($mobileCats as $mc):
                        $mcSubs = fetchSubcategories((int) $mc['id']);
                    ?>
                    <span class="eg-mobile__sublink-title"><?= htmlspecialchars($mc['name']) ?></span>
                    <?php foreach ($mcSubs as $ms): ?>
                    <a href="/category/<?= htmlspecialchars($ms['slug']) ?>" class="eg-mobile__sublink"><?= htmlspecialchars($ms['name']) ?></a>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                    <a href="/categories" class="eg-mobile__sublink eg-mobile__sublink--all">Sve kategorije</a>
                </div>
            </div>
        </div>

        <!-- Poklon vrećice -->
        <div class="eg-accordion__item">
            <button class="eg-accordion__trigger">
                <span>Poklon vrećice</span>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
            </button>
            <div class="eg-accordion__content">
                <div class="eg-accordion__pad">
                    <p class="eg-mobile__desc">Elegantne poklon vrećice sa premium proizvodima za negu kose.</p>
                    <a href="/gift-bag" class="eg-mobile__sublink eg-mobile__sublink--all">Pogledaj ponudu</a>
                </div>
            </div>
        </div>

        <a href="/gift-card" class="eg-mobile__link">Gift kartice</a>
        <a href="/account/loyalty" class="eg-mobile__link">Loyalty program</a>
        <a href="/contact" class="eg-mobile__link">Konsultacije</a>

    </nav>
</aside>

<!-- Flash Messages -->
<?= renderFlash() ?>

<!-- Main Content -->
<main class="main-content">
