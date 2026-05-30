<?php
/* ============================================================
   Egoire – FAQ Page
   View:  src/views/pages/faq.php
   CSS:   public/css/faq.css  (fq- namespace)
   ============================================================ */
declare(strict_types=1);

$title = 'Često postavljana pitanja | Egoire';
$pageStyles = ['/css/faq.css'];

$shopFaqs = fetchFaqs();

$hairCareFaqs = [
    [
        'id'    => 'suva-kosa',
        'title' => 'Suva i oštećena kosa',
        'items' => [
            [
                'q' => 'Zašto mi je kosa suva iako koristim masku?',
                'a' => 'Suva kosa često nije samo problem hidratacije, već i oštećenja strukture dlake. Bitni su kvalitetni proizvodi, pravilno pranje i zaštita od toplote.',
            ],
            [
                'q' => 'Kako da znam da li mi je kosa oštećena?',
                'a' => 'Ako se mrsi, puca, nema sjaj, deluje grubo i krajevi se listaju — kosa traži ozbiljniju negu i tretmane obnove.',
            ],
            [
                'q' => 'Da li ulja mogu da oporave kosu?',
                'a' => 'Ulja daju sjaj i mekoću, ali ne mogu sama da poprave ozbiljno oštećenje. Kombinacija profesionalnih tretmana i kućne nege daje pravi rezultat.',
            ],
            [
                'q' => 'Zašto mi se kosa lomi?',
                'a' => 'Najčešći razlozi su:',
                'list' => [
                    'toplota bez zaštite',
                    'često blanširanje',
                    'agresivno češljanje',
                    'loša kućna nega',
                    'vezivanje mokre kose',
                ],
            ],
            [
                'q' => 'Koliko često treba raditi tretmane za obnovu?',
                'a' => 'Zavisi od stanja kose, ali uglavnom na 2–4 nedelje za vidljive i dugoročne rezultate.',
            ],
        ],
    ],
    [
        'id'    => 'plava-kosa',
        'title' => 'Nega plave kose',
        'items' => [
            [
                'q' => 'Kako da plava kosa ne požuti?',
                'a' => 'Redovno toniranje, silver šampon po preporuci i kvalitetna nega su ključ hladne i luksuzne plave boje.',
            ],
            [
                'q' => 'Zašto plava kosa deluje suvo?',
                'a' => 'Posvetljivanje otvara dlaku i kosa gubi vlagu, zato plava kosa traži mnogo više hidratacije i zaštite.',
            ],
            [
                'q' => 'Da li silver šampon sme često da se koristi?',
                'a' => 'Ne preterano. Previše silver šampona može isušiti kosu i dati sivkast ton. Balans je najvažniji.',
            ],
            [
                'q' => 'Kako da plava kosa izgleda skupo i negovano?',
                'a' => 'Sjaj, ton, zdravi krajevi i dobra nega čine razliku između obične i luksuzne plave kose.',
            ],
            [
                'q' => 'Mogu li da budem plava bez velike štete?',
                'a' => 'Može, ali uz postepen rad, profesionalne proizvode i redovno održavanje.',
            ],
        ],
    ],
    [
        'id'    => 'perut',
        'title' => 'Perut i teme glave',
        'items' => [
            [
                'q' => 'Zašto imam perut iako perem kosu redovno?',
                'a' => 'Perut nije uvek znak prljave kose. Može biti posledica suvog ili masnog temena, stresa, hormona ili pogrešnih proizvoda.',
            ],
            [
                'q' => 'Kako da razlikujem suvu kožu od peruti?',
                'a' => 'Suva koža obično daje sitne bele ljuspice i zatezanje, dok je perut često masnija i praćena svrabom.',
            ],
            [
                'q' => 'Da li ulja pomažu kod peruti?',
                'a' => 'Nekada mogu pomoći, ali kod određenih tipova peruti mogu pogoršati stanje. Zato je važna pravilna analiza temena.',
            ],
            [
                'q' => 'Koliko često treba prati kosu ako imam perut?',
                'a' => 'Ne previše retko. Redovno i pravilno pranje odgovarajućim preparatima pomaže smirivanju temena.',
            ],
        ],
    ],
    [
        'id'    => 'tretmani',
        'title' => 'Tretmani',
        'items' => [
            [
                'q' => 'Koji tretman je najbolji za moju kosu?',
                'a' => 'Najbolji tretman je onaj koji odgovara trenutnom stanju tvoje kose — zato je analiza kose prvi korak.',
            ],
            [
                'q' => 'Koliko traje efekat tretmana?',
                'a' => 'Uz pravilnu kućnu negu, efekti mogu trajati nedeljama. Salon + kućna nega daju najbolje rezultate.',
            ],
            [
                'q' => 'Da li jedan tretman može potpuno da oporavi kosu?',
                'a' => 'Jedan tretman može napraviti veliku razliku, ali ozbiljno oštećena kosa traži kontinuitet.',
            ],
            [
                'q' => 'Da li tretmani otežavaju kosu?',
                'a' => 'Kvalitetni tretmani ne bi trebalo da otežaju kosu, već da je učine mekom, sjajnom i zdravijom.',
            ],
        ],
    ],
    [
        'id'    => 'kucna-nega',
        'title' => 'Saveti za kućnu negu',
        'items' => [
            [
                'q' => 'Koja je najveća greška u kućnoj nezi?',
                'a' => 'Korišćenje pogrešnih proizvoda i toplote bez zaštite.',
            ],
            [
                'q' => 'Da li je skupa nega uvek bolja?',
                'a' => 'Nije poenta samo u ceni, već u kvalitetu i tome šta odgovara tvojoj kosi.',
            ],
            [
                'q' => 'Kako pravilno prati kosu?',
                'a' => 'Pravilno pranje uključuje:',
                'list' => [
                    'dva šamponiranja',
                    'masku samo na dužinu',
                    'mlaku vodu',
                    'nežno sušenje',
                    'bez grubog trljanja peškirom',
                ],
            ],
            [
                'q' => 'Koliko često koristiti masku?',
                'a' => '1–2 puta nedeljno je sasvim dovoljno za većinu tipova kose.',
            ],
            [
                'q' => 'Zašto kosa izgleda lepo iz salona, a kod kuće ne?',
                'a' => 'Tehnika feniranja, proizvodi i pravilna nega prave ogromnu razliku.',
            ],
            [
                'q' => 'Da li termička zaštita stvarno znači?',
                'a' => 'Apsolutno. Bez nje toplota postepeno uništava kvalitet kose.',
            ],
        ],
    ],
];

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="fq-hero">
    <div class="fq-container">
        <span class="fq-hero__label">Pomoć & Podrška</span>
        <h1 class="fq-hero__title">Često postavljana pitanja</h1>
        <p class="fq-hero__text">Odgovori o nezi kose, tretmanima i proizvodima — plus informacije o narudžbinama i isporuci.</p>
    </div>
</section>

<!-- ============================================================
     FAQ ACCORDION
     ============================================================ -->
<section class="fq-content">
    <div class="fq-container fq-container--narrow">

        <!-- Category quick nav -->
        <nav class="fq-nav" aria-label="Kategorije pitanja">
            <?php foreach ($hairCareFaqs as $cat): ?>
            <a href="#fq-<?= htmlspecialchars($cat['id']) ?>" class="fq-nav__pill">
                <?= htmlspecialchars($cat['title']) ?>
            </a>
            <?php endforeach; ?>
            <?php if (!empty($shopFaqs)): ?>
            <a href="#fq-narudzbine" class="fq-nav__pill">Narudžbine</a>
            <?php endif; ?>
        </nav>

        <!-- Hair care categories -->
        <?php
        $itemIndex = 0;
        foreach ($hairCareFaqs as $category):
        ?>
        <section class="fq-category" id="fq-<?= htmlspecialchars($category['id']) ?>">
            <h2 class="fq-category__title"><?= htmlspecialchars($category['title']) ?></h2>
            <div class="fq-list" role="list">
                <?php foreach ($category['items'] as $item): ?>
                <div class="fq-item" role="listitem">
                    <button class="fq-item__trigger"
                            type="button"
                            aria-expanded="false"
                            aria-controls="fq-answer-<?= $itemIndex ?>">
                        <span class="fq-item__question"><?= htmlspecialchars($item['q']) ?></span>
                        <span class="fq-item__icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19" class="fq-item__icon-v"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </span>
                    </button>
                    <div class="fq-item__panel" id="fq-answer-<?= $itemIndex ?>">
                        <div class="fq-item__answer">
                            <p><?= htmlspecialchars($item['a']) ?></p>
                            <?php if (!empty($item['list'])): ?>
                            <ul class="fq-answer__list">
                                <?php foreach ($item['list'] as $listItem): ?>
                                <li><?= htmlspecialchars($listItem) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
                $itemIndex++;
                endforeach;
                ?>
            </div>
        </section>
        <?php endforeach; ?>

        <!-- Shop / admin FAQs from database -->
        <?php if (!empty($shopFaqs)): ?>
        <section class="fq-category" id="fq-narudzbine">
            <h2 class="fq-category__title">Narudžbine i kupovina</h2>
            <div class="fq-list" role="list">
                <?php foreach ($shopFaqs as $f): ?>
                <div class="fq-item" role="listitem">
                    <button class="fq-item__trigger"
                            type="button"
                            aria-expanded="false"
                            aria-controls="fq-answer-<?= $itemIndex ?>">
                        <span class="fq-item__question"><?= htmlspecialchars($f['question']) ?></span>
                        <span class="fq-item__icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19" class="fq-item__icon-v"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </span>
                    </button>
                    <div class="fq-item__panel" id="fq-answer-<?= $itemIndex ?>">
                        <div class="fq-item__answer">
                            <p><?= nl2br(htmlspecialchars($f['answer'])) ?></p>
                        </div>
                    </div>
                </div>
                <?php
                $itemIndex++;
                endforeach;
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- CTA -->
        <div class="fq-cta">
            <div class="fq-cta__inner">
                <h2 class="fq-cta__title">Niste pronašli odgovor?</h2>
                <p class="fq-cta__text">Naš tim je tu da vam pomogne. Pišite nam ili nas pozovite — odgovaramo u roku od 24 sata.</p>
                <a href="/contact" class="fq-cta__btn">
                    Kontaktirajte nas
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </div>

    </div>
</section>

<script>
(function () {
    'use strict';

    function closeItem(item) {
        var panel = item.querySelector('.fq-item__panel');
        var btn = item.querySelector('.fq-item__trigger');
        item.classList.remove('fq-item--open');
        btn.setAttribute('aria-expanded', 'false');
        if (panel) panel.style.maxHeight = '0';
    }

    function openItem(item) {
        var panel = item.querySelector('.fq-item__panel');
        var btn = item.querySelector('.fq-item__trigger');
        item.classList.add('fq-item--open');
        btn.setAttribute('aria-expanded', 'true');
        if (panel) panel.style.maxHeight = panel.scrollHeight + 'px';
    }

    document.querySelectorAll('.fq-item__trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.fq-item');
            var isOpen = item.classList.contains('fq-item--open');

            document.querySelectorAll('.fq-item--open').forEach(closeItem);

            if (!isOpen) {
                openItem(item);
            }
        });
    });

    window.addEventListener('resize', function () {
        document.querySelectorAll('.fq-item--open .fq-item__panel').forEach(function (panel) {
            panel.style.maxHeight = panel.scrollHeight + 'px';
        });
    });
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
