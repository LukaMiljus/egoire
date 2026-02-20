<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> – Egoire Admin</title>
    <link rel="stylesheet" href="<?= asset('/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('/css/header/admin-header.css') ?>">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
</head>
<body>
<!-- PC ADMIN HEADER  -->

    <div class="header-holder">
        <div class="logo-holder">
            <img src="/images/logos/egoire-logo.png" alt="egoire-logo">
        </div>
        <div class="header-links">
            <div class="main-links">
            <a href="/admin/dashboard">Početna</a>
            <a href="/admin/orders">Porudžbine</a>
            <a href="/admin/products">Artikli</a>
            <a href="/admin/inventory">Stanje</a>
            <a href="/admin/categories">Kategorije</a>
            <a href="/admin/brands">Brendovi</a>
            <a href="/admin/users">Korisnici</a>
            <a href="/admin/blog">Blog</a>
            <a href="/admin/gift-bag">Gift Bag</a>
            <a href="/admin/gift-wrapping">Poklon pakovanja</a>
            <a href="/admin/gift-cards">Gift Kartice</a>
            <a href="/admin/loyalty">Loyalty</a>
            <a href="/admin/contacts">Poruke</a>
            <a href="/admin/analytics">Analitika</a>
            <a href="/admin/marketing">Email Marketing</a>
            <a href="/admin/pages">Stranice</a>
            </div>
            
            <div class="user-links">
                <p class="active-user">Name </p>
                <a href="/admin/logout">Izloguj se</a>
            </div>
        </div>
    </div>

<!-- PC ADMIN HEADER  -->




<!-- MOBILE ADMIN HEADER -->
<div class="mobile-header">
    <div class="mobile-header-inner">
        <div class="mobile-logo">
            <img src="/images/logos/egoire-logo.png" alt="egoire-logo">
        </div>

        <button class="hamburger" aria-label="Open menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</div>

<div class="mobile-menu-overlay">
    <div class="mobile-menu-content">
        <nav class="mobile-nav-links">
            <a href="/admin/dashboard">Početna</a>
            <a href="/admin/orders">Porudžbine</a>
            <a href="/admin/products">Artikli</a>
            <a href="/admin/inventory">Stanje</a>
            <a href="/admin/categories">Kategorije</a>
            <a href="/admin/brands">Brendovi</a>
            <a href="/admin/users">Korisnici</a>
            <a href="/admin/blog">Blog</a>
            <a href="/admin/gift-bag">Gift Bag</a>
            <a href="/admin/gift-wrapping">Poklon pakovanja</a>
            <a href="/admin/gift-cards">Gift Kartice</a>
            <a href="/admin/loyalty">Loyalty</a>
            <a href="/admin/contacts">Poruke</a>
            <a href="/admin/analytics">Analitika</a>
            <a href="/admin/marketing">Email Marketing</a>
            <a href="/admin/pages">Stranice</a>
        </nav>

        <div class="mobile-user-links">
            <p class="active-user">Name</p>
            <a href="/admin/logout">Izloguj se</a>
        </div>
    </div>
</div>
<!-- MOBILE ADMIN HEADER -->
<script>
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.querySelector('.mobile-menu-overlay');

    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        mobileMenu.classList.toggle('active');
    });
</script>

<?= renderFlash() ?>

<div class="admin-content">