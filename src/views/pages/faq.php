<?php
/* ============================================================
   Egoire – FAQ Page
   View:  src/views/pages/faq.php
   CSS:   public/css/faq.css  (fq- namespace)
   ============================================================ */
declare(strict_types=1);

$title = 'Često postavljana pitanja | Egoire';
$pageStyles = ['/css/faq.css'];

$faqs = fetchFaqs();

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="fq-hero">
    <div class="fq-container">
        <span class="fq-hero__label">Pomoć & Podrška</span>
        <h1 class="fq-hero__title">Često postavljana pitanja</h1>
        <p class="fq-hero__text">Pronađite odgovore na najčešća pitanja o narudžbinama, isporuci, proizvodima i poklon pakovanjima.</p>
    </div>
</section>

<!-- ============================================================
     FAQ ACCORDION
     ============================================================ -->
<section class="fq-content">
    <div class="fq-container fq-container--narrow">

        <?php if (!empty($faqs)): ?>
        <div class="fq-list" role="list">
            <?php foreach ($faqs as $i => $f): ?>
            <div class="fq-item" role="listitem">
                <button class="fq-item__trigger"
                        aria-expanded="false"
                        aria-controls="fq-answer-<?= $i ?>">
                    <span class="fq-item__question"><?= htmlspecialchars($f['question']) ?></span>
                    <span class="fq-item__icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19" class="fq-item__icon-v"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </span>
                </button>
                <div class="fq-item__panel" id="fq-answer-<?= $i ?>">
                    <div class="fq-item__answer">
                        <?= nl2br(htmlspecialchars($f['answer'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="fq-empty">
            <p>Nema pitanja za prikaz.</p>
        </div>
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
    document.querySelectorAll('.fq-item__trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.fq-item');
            var isOpen = item.classList.contains('fq-item--open');

            /* Close all */
            document.querySelectorAll('.fq-item--open').forEach(function (openItem) {
                openItem.classList.remove('fq-item--open');
                openItem.querySelector('.fq-item__trigger').setAttribute('aria-expanded', 'false');
            });

            /* Toggle current */
            if (!isOpen) {
                item.classList.add('fq-item--open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
