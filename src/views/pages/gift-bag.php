<?php
/* ============================================================
   Egoire – Luxury Gift Bag Page
   View:  src/views/pages/gift-bag.php
   CSS:   public/css/gift-bag.css  (gb- namespace)
   ============================================================ */
declare(strict_types=1);

$title = 'Gift Bag | Egoire';

/* --- Page-specific assets --- */
$pageStyles = ['/css/gift-bag.css'];

/* --- Data --- */
$rules = fetchGiftBagRules(true);

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO — Full-width gift bag visual
     ============================================================ -->
<section class="gb-hero">
    <img src="<?= asset('/images/gift-bag/gift-bag.png') ?>"
         alt="Egoire Gift Bag"
         class="gb-hero__img"
         loading="eager"
         draggable="false">
    <div class="gb-hero__overlay"></div>
    <div class="gb-hero__content">
        <span class="gb-hero__label">Poklon iskustvo</span>
        <h1 class="gb-hero__title">Gift Bag</h1>
        <p class="gb-hero__text">Pretvorite svaku narudžbinu u nezaboravan poklon — elegantan, pažljivo upakovan i spreman za iznenađenje.</p>
    </div>
</section>

<!-- ============================================================
     HOW IT WORKS — Three elegant steps
     ============================================================ -->
<section class="gb-steps">
    <div class="gb-container">
        <div class="gb-steps__header">
            <span class="gb-steps__label">Jednostavno</span>
            <h2 class="gb-steps__title">Kako funkcioniše?</h2>
            <p class="gb-steps__subtitle">Tri koraka do savršenog poklona — brzo, elegantno i bez komplikacija.</p>
        </div>

        <div class="gb-steps__grid">
            <!-- Step 01 -->
            <article class="gb-step">
                <span class="gb-step__number">01</span>
                <div class="gb-step__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <h3 class="gb-step__name">Izaberite proizvode</h3>
                <p class="gb-step__desc">Pretražite naš asortiman i dodajte do 4 proizvoda u korpu — šampone, tretmane, ulja ili stajling.</p>
            </article>

            <!-- Step 02 -->
            <article class="gb-step">
                <span class="gb-step__number">02</span>
                <div class="gb-step__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                    </svg>
                </div>
                <h3 class="gb-step__name">Odaberite Gift Bag</h3>
                <p class="gb-step__desc">U korpi označite opciju za poklon pakovanje — vaši proizvodi biće pažljivo upakovani u premium Gift Bag.</p>
            </article>

            <!-- Step 03 -->
            <article class="gb-step">
                <span class="gb-step__number">03</span>
                <div class="gb-step__icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/>
                    </svg>
                </div>
                <h3 class="gb-step__name">Cena se dodaje automatski</h3>
                <p class="gb-step__desc">Na checkout stranici videćete tačan iznos. Bez skrivenih troškova, potpuno transparentno.</p>
            </article>
        </div>

        <p class="gb-steps__note">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            Gift Bag može sadržati maksimalno 4 proizvoda po porudžbini.
        </p>
    </div>
</section>

<!-- ============================================================
     ACTIVE OFFERS — Gift bag rules from database
     ============================================================ -->
<?php if ($rules): ?>
<section class="gb-offers">
    <div class="gb-container">
        <div class="gb-offers__header">
            <span class="gb-offers__label">Posebne ponude</span>
            <h2 class="gb-offers__title">Aktivne Gift Bag ponude</h2>
        </div>

        <div class="gb-offers__grid">
            <?php foreach ($rules as $r): ?>
            <article class="gb-offer">
                <div class="gb-offer__accent"></div>
                <h3 class="gb-offer__name"><?= htmlspecialchars($r['name']) ?></h3>

                <div class="gb-offer__details">
                    <?php if (!empty($r['min_order_value'])): ?>
                    <div class="gb-offer__detail">
                        <span class="gb-offer__detail-label">Minimalni iznos</span>
                        <span class="gb-offer__detail-value"><?= formatPrice((float) $r['min_order_value']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($r['min_products'])): ?>
                    <div class="gb-offer__detail">
                        <span class="gb-offer__detail-label">Minimalno proizvoda</span>
                        <span class="gb-offer__detail-value"><?= (int) $r['min_products'] ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <a href="/products" class="gb-offer__cta">
                    <span>Kupuj sada</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     EMOTIONAL EDITORIAL BLOCK
     ============================================================ -->
<section class="gb-editorial">
    <div class="gb-container gb-editorial__inner">
        <div class="gb-editorial__visual">
            <img src="<?= asset('/images/gift-bag/gift-bag2.png') ?>"
                 alt="Elegantno pakovanje"
                 class="gb-editorial__img"
                 loading="lazy"
                 draggable="false">
        </div>
        <div class="gb-editorial__content">
            <span class="gb-editorial__label">Više od poklona</span>
            <h2 class="gb-editorial__title">Poklonite eleganciju</h2>
            <p class="gb-editorial__text">Svaki Gift Bag je pažljivo dizajniran da ostavi utisak — od prvog dodira papira do poslednjeg odmotavanja. Premium materijali, diskretni brending i pažnja prema detaljima čine svaki poklon nezaboravnim iskustvom.</p>
            <p class="gb-editorial__text">Bilo da je u pitanju rođendan, godišnjica ili jednostavno želja da nekome ulepšate dan — naš Gift Bag je savršen izbor za one koji cene istinsku lepotu.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA BANNER — Contact for undecided customers
     ============================================================ -->
<section class="gb-cta">
    <div class="gb-container gb-cta__inner">
        <h2 class="gb-cta__title">Niste sigurni šta da odaberete?</h2>
        <p class="gb-cta__text">Naš tim je tu da vam pomogne da sastavite savršen poklon — potpuno besplatno.</p>
        <a href="/contact" class="gb-cta__btn">
            <span>Kontaktirajte nas</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
            </svg>
        </a>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
