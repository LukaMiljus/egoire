<?php
/* ============================================================
   Egoire – Individual Blog Post Page
   View:  src/views/pages/blog-post.php
   CSS:   public/css/blog.css  (bp- namespace)
   ============================================================ */
declare(strict_types=1);

$slug = $routeParams['slug'] ?? '';
$post = fetchBlogPostBySlug($slug);
if (!$post || $post['status'] !== 'published') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$title           = (($post['meta_title'] ?? '') ?: $post['title']) . ' | Blog | Egoire';
$metaDescription = $post['meta_description'] ?? $post['excerpt'] ?? '';
$ogImage         = !empty($post['featured_image']) ? (baseUrl() . $post['featured_image']) : '';
$pageStyles      = ['/css/blog.css'];

/* Fetch related posts (latest 3 excluding current) */
$relatedPosts = fetchBlogPosts([
    'published_only' => true,
    'limit'          => 4,
]);
$relatedPosts = array_filter($relatedPosts, fn($p) => $p['id'] !== $post['id']);
$relatedPosts = array_slice($relatedPosts, 0, 3);

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO — Breadcrumb, Title, Featured Image
     ============================================================ -->
<section class="bp-hero">
    <div class="bp-container">
        <nav class="bp-breadcrumb" aria-label="Navigacija">
            <a href="/">Početna</a>
            <span class="bp-breadcrumb__sep">/</span>
            <a href="/blog">Blog</a>
            <span class="bp-breadcrumb__sep">/</span>
            <span><?= htmlspecialchars($post['title']) ?></span>
        </nav>

        <header class="bp-hero__header">
            <time class="bp-hero__date" datetime="<?= htmlspecialchars($post['published_at'] ?? $post['created_at']) ?>">
                <?= formatDate($post['published_at'] ?? $post['created_at']) ?>
            </time>
            <h1 class="bp-hero__title"><?= htmlspecialchars($post['title']) ?></h1>
        </header>

        <?php if ($post['featured_image']): ?>
        <div class="bp-hero__image-wrap">
            <img src="<?= htmlspecialchars($post['featured_image']) ?>"
                 alt="<?= htmlspecialchars($post['title']) ?>"
                 class="bp-hero__image"
                 loading="eager">
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     ARTICLE BODY
     ============================================================ -->
<section class="bp-content">
    <div class="bp-container bp-container--narrow">
        <div class="bp-body">
            <?= $post['body'] ?>
        </div>

        <div class="bp-back">
            <a href="/blog" class="bp-back__link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                Svi članci
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     RELATED POSTS
     ============================================================ -->
<?php if (!empty($relatedPosts)): ?>
<section class="bp-related">
    <div class="bl-container">
        <div class="bp-related__header">
            <span class="bp-related__label">Nastavite čitanje</span>
            <h2 class="bp-related__title">Povezani članci</h2>
        </div>

        <div class="bp-related__grid">
            <?php foreach ($relatedPosts as $rp): ?>
            <a href="/blog/<?= htmlspecialchars($rp['slug']) ?>" class="bl-card">
                <div class="bl-card__image-wrap">
                    <?php if ($rp['featured_image']): ?>
                    <img src="<?= htmlspecialchars($rp['featured_image']) ?>"
                         alt="<?= htmlspecialchars($rp['title']) ?>"
                         class="bl-card__image"
                         loading="lazy">
                    <?php else: ?>
                    <div class="bl-card__image bl-card__image--placeholder"></div>
                    <?php endif; ?>
                </div>
                <div class="bl-card__body">
                    <time class="bl-card__date" datetime="<?= htmlspecialchars($rp['published_at'] ?? $rp['created_at']) ?>">
                        <?= formatDate($rp['published_at'] ?? $rp['created_at']) ?>
                    </time>
                    <h3 class="bl-card__title"><?= htmlspecialchars($rp['title']) ?></h3>
                    <p class="bl-card__excerpt">
                        <?= htmlspecialchars($rp['excerpt'] ?? truncate(strip_tags($rp['body']), 120)) ?>
                    </p>
                    <span class="bl-card__link">
                        Pročitaj više
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
