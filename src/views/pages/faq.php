<?php
declare(strict_types=1);
$title = 'FAQ | Egoire';
$faqs = fetchFaqs();
require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero"><div class="container"><h1>Često postavljana pitanja</h1></div></section>

<section class="section">
    <div class="container container-md">
        <div class="faq-list">
            <?php foreach ($faqs as $f): ?>
            <div class="faq-item">
                <button class="faq-question" onclick="this.parentElement.classList.toggle('open')">
                    <span><?= htmlspecialchars($f['question']) ?></span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        <?= nl2br(htmlspecialchars($f['answer'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($faqs)): ?>
        <div class="empty-state"><p>Nema pitanja.</p></div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <p>Niste pronašli odgovor?</p>
            <a href="/contact" class="btn btn-primary">Kontaktirajte nas</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
