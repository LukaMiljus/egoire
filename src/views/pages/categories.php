<?php
declare(strict_types=1);
$title = 'Kategorije | Egoire';
$categories = fetchCategories();
require __DIR__ . '/../layout/header.php';
?>
<section class="page-hero"><div class="container"><h1>Kategorije</h1></div></section>
<section class="section">
    <div class="container">
        <div class="category-grid large">
            <?php foreach ($categories as $c): ?>
            <a href="/category/<?= htmlspecialchars($c['slug']) ?>" class="category-card">
                <?php if ($c['image']): ?>
                <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>" loading="lazy">
                <?php endif; ?>
                <h3><?= htmlspecialchars($c['name']) ?></h3>
                <?php if ($c['description']): ?>
                <p><?= htmlspecialchars(truncate(strip_tags($c['description']), 80)) ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
