<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrfMetaTag() ?>
    <title><?= htmlspecialchars($title ?? 'Egoire') ?> – Egoire</title>
    <link rel="stylesheet" href="<?= asset('/css/style.css') ?>">
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <span>Besplatna dostava za porudžbine iznad 5.000 RSD</span>
        </div>
    </div>

    <!-- Header -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="/" class="logo">
                <span class="logo-text">EGOIRE</span>
            </a>

            <nav class="main-nav" id="mainNav">
                <a href="/products" class="<?= isActivePath('/products') ? 'active' : '' ?>">Proizvodi</a>
                <a href="/categories" class="<?= isActivePath('/categories') ? 'active' : '' ?>">Kategorije</a>
                <a href="/brands" class="<?= isActivePath('/brands') ? 'active' : '' ?>">Brendovi</a>
                <a href="/gift-bag" class="<?= isActivePath('/gift-bag') ? 'active' : '' ?>">Gift Bag</a>
                <a href="/gift-card" class="<?= isActivePath('/gift-card') ? 'active' : '' ?>">Gift Card</a>
                <a href="/blog" class="<?= isActivePath('/blog') ? 'active' : '' ?>">Blog</a>
                <a href="/contact" class="<?= isActivePath('/contact') ? 'active' : '' ?>">Kontakt</a>
            </nav>

            <div class="header-actions">
                <button class="search-toggle" id="searchToggle" aria-label="Pretraga">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>

                <?php if (isUserAuthenticated()): ?>
                    <a href="/account" class="header-icon" title="Moj nalog">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </a>
                <?php else: ?>
                    <a href="/login" class="header-icon" title="Prijava">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </a>
                <?php endif; ?>

                <a href="/cart" class="header-icon cart-icon" title="Korpa">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span class="cart-count" id="cartCount"><?= cartItemCount() ?></span>
                </a>

                <button class="mobile-menu-toggle" id="menuToggle" aria-label="Meni">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <!-- Search Overlay -->
        <div class="search-overlay" id="searchOverlay" style="display:none;">
            <div class="container">
                <form action="/search" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Pretražite proizvode..." autocomplete="off" class="search-input" id="searchInput">
                    <button type="submit" class="search-submit">Pretraži</button>
                    <button type="button" class="search-close" id="searchClose">✕</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?= renderFlash() ?>

    <!-- Main Content -->
    <main class="main-content">
