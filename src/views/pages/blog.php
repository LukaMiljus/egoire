<?php
declare(strict_types=1);
$title = 'Blog | Egoire';
$page = inputInt('page', 1);
$posts = fetchBlogPosts(['published_only' => true, 'limit' => 12, 'offset' => ($page - 1) * 12]);

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero"><div class="container"><h1>Blog</h1></div></section>

<section class="section">
    <div class="container">
        <div class="blog-grid">
            <?php foreach ($posts as $bp): ?>
            <a href="/blog/<?= htmlspecialchars($bp['slug']) ?>" class="blog-card">
                <?php if ($bp['featured_image']): ?>
                <img src="<?= htmlspecialchars($bp['featured_image']) ?>" alt="" loading="lazy">
                <?php endif; ?>
                <div class="blog-card-body">
                    <span class="blog-date"><?= formatDate($bp['created_at']) ?></span>
                    <h3><?= htmlspecialchars($bp['title']) ?></h3>
                    <p><?= htmlspecialchars($bp['excerpt'] ?? truncate(strip_tags($bp['content']), 120)) ?></p>
                    <span class="read-more">Pročitaj više →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($posts)): ?>
        <div class="empty-state"><p>Nema objavljenih postova.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
