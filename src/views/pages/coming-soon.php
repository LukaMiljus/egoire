<?php
/* ============================================================
   Egoire – Coming Soon / Uskoro
   View:  src/views/pages/coming-soon.php
   
   Standalone page – no header/footer layout.
   ============================================================ */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Egoire – Uskoro</title>
    <meta name="description" content="Egoire – Uskoro sve što vam treba za vašu kosu. Premium luxury hair care dolazi uskoro.">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="Egoire – Uskoro">
    <meta property="og:description" content="Uskoro sve što vam treba za vašu kosu.">
    <meta property="og:type" content="website">

    <!-- Preconnect fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="/css/coming-soon.css">

    <!-- Preload video poster for mobile fallback -->
    <link rel="preload" as="image" href="/images/coming-soon-poster.jpg">
</head>
<body>

    <!-- ========== Loading Overlay ========== -->
    <div class="cs-loader" id="cs-loader">
        <div class="cs-loader__ring"></div>
    </div>

    <!-- ========== Background Video ========== -->
    <!-- ★ CHANGE VIDEO: Replace the src below with your video path ★ -->
    <div class="cs-video-wrap">
        <video
            autoplay
            muted
            loop
            playsinline
            preload="auto"
            poster="/images/coming-soon-poster.jpg"
        >
            <source src="/videos/cs-video.mp4" type="video/mp4">
        </video>
        <!-- Fallback image shown if <video> is unsupported -->
        <img
            class="cs-fallback-img"
            src="/images/coming-soon-poster.jpg"
            alt="Egoire Coming Soon Background"
        >
    </div>

    <!-- ========== Dark Overlay ========== -->
    <div class="cs-overlay"></div>

    <!-- ========== Main Content ========== -->
    <main class="cs-content">

        <!-- ★ CHANGE LOGO: Replace the src below with your logo path ★ -->
        <img
            class="cs-logo"
            src="/images/logos/egoire-logo.png"
            alt="Egoire Logo"
            
        >

        <h1 class="cs-heading" id="cs-heading">
            Uskoro  <br /> Sve što vaša kosa zaista  <span class="cs-accent">zaslužuje.</span>
        </h1>

        <!-- Countdown -->
        <div class="cs-countdown" id="cs-countdown">
            <div class="cs-countdown__block">
                <span class="cs-countdown__number" id="cs-days">00</span>
                <span class="cs-countdown__label">Dana</span>
            </div>
            <div class="cs-countdown__block">
                <span class="cs-countdown__number" id="cs-hours">00</span>
                <span class="cs-countdown__label">Sati</span>
            </div>
            <div class="cs-countdown__block">
                <span class="cs-countdown__number" id="cs-minutes">00</span>
                <span class="cs-countdown__label">Minuta</span>
            </div>
            <div class="cs-countdown__block">
                <span class="cs-countdown__number" id="cs-seconds">00</span>
                <span class="cs-countdown__label">Sekundi</span>
            </div>
        </div>

        <hr class="cs-divider" id="cs-divider">

        <p class="cs-subtext" id="cs-subtext">Luxury hair care &middot; Ekskluzivni brendovi</p>

        <!-- Shown when countdown finishes -->
        <div class="cs-open-message" id="cs-open-message">
            <span class="cs-open-message__title">Otvoreni smo!</span>
            <a href="/" class="cs-open-message__cta">Posetite Shop</a>
        </div>

    </main>

    <!-- Script -->
    <script src="/js/coming-soon.js"></script>
</body>
</html>
