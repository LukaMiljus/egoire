<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/header/admin-header.css">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet">

</head>
<body>
<!-- PC ADMIN HEADER  -->

    <div class="header-holder">
        <div class="logo-holder">
            <img src="../../public/images/logos/egoire-logo.png" alt="egoire-logo">
        </div>
        <div class="header-links">
            <div class="main-links">
            <a href="">Početna</a>
            <a href="">Porudžbine</a>
            <a href="">Artikli</a>
            <a href="">Kategorije</a>
            <a href="">Brendovi</a>
            <a href="">Korisnici</a>
            <a href="">Loyalty</a>
            <a href="">Analitika</a>
            <a href="">Email Marketing</a>
            </div>
            
            <div class="user-links">
                <p class="active-user">Name </p>
                <a href="admin-logout.php">Izloguj se</a>
            </div>
        </div>
    </div>

<!-- PC ADMIN HEADER  -->




<!-- MOBILE ADMIN HEADER -->
<div class="mobile-header">
    <div class="mobile-header-inner">
        <div class="mobile-logo">
            <img src="../../public/images/logos/egoire-logo.png" alt="egoire-logo">
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
            <a href="">Početna</a>
            <a href="">Porudžbine</a>
            <a href="">Artikli</a>
            <a href="">Kategorije</a>
            <a href="">Brendovi</a>
            <a href="">Korisnici</a>
            <a href="">Loyalty</a>
            <a href="">Analitika</a>
            <a href="">Email Marketing</a>
        </nav>

        <div class="mobile-user-links">
            <p class="active-user">Name</p>
            <a href="admin-logout.php">Izloguj se</a>
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


</body>
</html>