<?php
declare(strict_types=1);
$title = 'Brendovi | Egoire';
$brands = fetchBrands();
require __DIR__ . '/../layout/header.php';
?>
<section class="page-hero"><div class="container"><h1>Brendovi</h1></div></section>
<section class="section">
    <div class="container">
        <div class="brand-grid large">
            <?php foreach ($brands as $b): ?>
            <a href="/brand/<?= htmlspecialchars($b['slug']) ?>" class="brand-card">
                <?php if ($b['logo']): ?>
                <img src="<?= htmlspecialchars($b['logo']) ?>" alt="<?= htmlspecialchars($b['name']) ?>">
                <?php else: ?>
                <span class="brand-name"><?= htmlspecialchars($b['name']) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
