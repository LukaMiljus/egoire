<?php
declare(strict_types=1);
$title = 'Gift Bag | Egoire';
$rules = fetchGiftBagRules();
require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero gift-bag-hero">
    <div class="container">
        <h1>Gift Bag</h1>
        <p>Specijalne ponude i popusti za naše kupce</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="gift-bag-info">
            <h2>Kako funkcioniše?</h2>
            <div class="steps-grid">
                <div class="step">
                    <span class="step-number">1</span>
                    <h3>Dodajte proizvode</h3>
                    <p>Izaberite proizvode koje želite i dodajte ih u korpu</p>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <h3>Ispunite uslov</h3>
                    <p>Kada vaša korpa dostigne minimalni iznos, popust se automatski primenjuje</p>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <h3>Uživajte u popustu</h3>
                    <p>Popust će biti obračunat na checkout stranici</p>
                </div>
            </div>
        </div>

        <?php if ($rules): ?>
        <h2 class="section-title mt-5">Aktivne ponude</h2>
        <div class="gift-bag-rules-grid">
            <?php foreach ($rules as $r): ?>
            <div class="gift-bag-card">
                <h3><?= htmlspecialchars($r['name']) ?></h3>
                <div class="gift-bag-details">
                    <?php if ($r['min_order_value']): ?>
                    <p>Minimalni iznos: <strong><?= formatPrice((float) $r['min_order_value']) ?></strong></p>
                    <?php endif; ?>
                    <?php if ($r['min_products']): ?>
                    <p>Minimalni broj proizvoda: <strong><?= (int) $r['min_products'] ?></strong></p>
                    <?php endif; ?>
                </div>
                <a href="/products" class="btn btn-primary">Kupuj</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state mt-4">
            <p>Trenutno nema aktivnih Gift Bag ponuda.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
