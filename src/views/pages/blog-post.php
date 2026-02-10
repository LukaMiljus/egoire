<?php
declare(strict_types=1);
$slug = $routeParams['slug'] ?? '';
$post = fetchBlogPostBySlug($slug);
if (!$post || !$post['is_published']) { http_response_code(404); require __DIR__ . '/404.php'; return; }

$title = ($post['meta_title'] ?: $post['title']) . ' | Blog | Egoire';
require __DIR__ . '/../layout/header.php';
?>

<article class="section">
    <div class="container container-md">
        <nav class="breadcrumb">
            <a href="/">Početna</a><span>/</span>
            <a href="/blog">Blog</a><span>/</span>
            <span><?= htmlspecialchars($post['title']) ?></span>
        </nav>

        <?php if ($post['featured_image']): ?>
        <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="" class="blog-hero-image">
        <?php endif; ?>

        <header class="blog-header">
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="blog-meta">
                <span><?= formatDate($post['created_at']) ?></span>
                <?php if ($post['author']): ?>
                <span>• <?= htmlspecialchars($post['author']) ?></span>
                <?php endif; ?>
            </div>
        </header>

        <div class="content-block blog-content">
            <?= $post['content'] ?>
        </div>

        <div class="blog-share mt-4">
            <a href="/blog" class="btn btn-outline">&larr; Svi postovi</a>
        </div>
    </div>
</article>

<?php require __DIR__ . '/../layout/footer.php'; ?>
