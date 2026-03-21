<?php
/* ============================================================
   Egoire – Blog Listing Page
   View:  src/views/pages/blog.php
   CSS:   public/css/blog.css  (bl- namespace)
   ============================================================ */
declare(strict_types=1);

$title = 'Blog | Egoire';
$pageStyles = ['/css/blog.css'];

$page  = inputInt('page', 1);
$limit = 12;
$posts = fetchBlogPosts([
    'published_only' => true,
    'limit'          => $limit,
    'offset'         => ($page - 1) * $limit,
]);

/* Count total for pagination */
$allPosts   = fetchBlogPosts(['published_only' => true]);
$totalPosts = count($allPosts);
$totalPages = (int) ceil($totalPosts / $limit);

/* Featured post = first on page 1 */
$featured    = ($page === 1 && !empty($posts)) ? array_shift($posts) : null;

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="bl-hero">
    <div class="bl-container">
        <span class="bl-hero__label">Egoire Journal</span>
        <h1 class="bl-hero__title">Blog</h1>
        <p class="bl-hero__text">Saveti za negu kose, trendovi, vodiči za proizvode i priče iz sveta luksuzne kozmetike.</p>
    </div>
</section>

<!-- ============================================================
     FEATURED POST (page 1 only)
     ============================================================ -->
<?php if ($featured): ?>
<section class="bl-featured">
    <div class="bl-container">
        <a href="/blog/<?= htmlspecialchars($featured['slug']) ?>" class="bl-featured__card">
            <div class="bl-featured__image-wrap">
                <?php if ($featured['featured_image']): ?>
                <img src="<?= htmlspecialchars($featured['featured_image']) ?>"
                     alt="<?= htmlspecialchars($featured['title']) ?>"
                     class="bl-featured__image"
                     loading="eager">
                <?php else: ?>
                <div class="bl-featured__image bl-featured__image--placeholder"></div>
                <?php endif; ?>
            </div>
            <div class="bl-featured__body">
                <span class="bl-featured__label">Istaknuti članak</span>
                <h2 class="bl-featured__title"><?= htmlspecialchars($featured['title']) ?></h2>
                <p class="bl-featured__excerpt">
                    <?= htmlspecialchars($featured['excerpt'] ?? truncate(strip_tags($featured['body']), 200)) ?>
                </p>
                <div class="bl-featured__meta">
                    <time datetime="<?= htmlspecialchars($featured['published_at'] ?? $featured['created_at']) ?>">
                        <?= formatDate($featured['published_at'] ?? $featured['created_at']) ?>
                    </time>
                </div>
                <span class="bl-featured__btn">
                    Pročitaj više
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
            </div>
        </a>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     POSTS GRID
     ============================================================ -->
<section class="bl-grid-section">
    <div class="bl-container">

        <?php if (!empty($posts)): ?>
        <div class="bl-grid">
            <?php foreach ($posts as $bp): ?>
            <a href="/blog/<?= htmlspecialchars($bp['slug']) ?>" class="bl-card">
                <div class="bl-card__image-wrap">
                    <?php if ($bp['featured_image']): ?>
                    <img src="<?= htmlspecialchars($bp['featured_image']) ?>"
                         alt="<?= htmlspecialchars($bp['title']) ?>"
                         class="bl-card__image"
                         loading="lazy">
                    <?php else: ?>
                    <div class="bl-card__image bl-card__image--placeholder"></div>
                    <?php endif; ?>
                </div>
                <div class="bl-card__body">
                    <time class="bl-card__date" datetime="<?= htmlspecialchars($bp['published_at'] ?? $bp['created_at']) ?>">
                        <?= formatDate($bp['published_at'] ?? $bp['created_at']) ?>
                    </time>
                    <h3 class="bl-card__title"><?= htmlspecialchars($bp['title']) ?></h3>
                    <p class="bl-card__excerpt">
                        <?= htmlspecialchars($bp['excerpt'] ?? truncate(strip_tags($bp['body']), 120)) ?>
                    </p>
                    <span class="bl-card__link">
                        Pročitaj više
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="bl-pagination" aria-label="Paginacija bloga">
            <?php if ($page > 1): ?>
            <a href="/blog?page=<?= $page - 1 ?>" class="bl-pagination__btn bl-pagination__btn--prev">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                Prethodna
            </a>
            <?php endif; ?>

            <div class="bl-pagination__pages">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/blog?page=<?= $i ?>"
                   class="bl-pagination__page <?= $i === $page ? 'bl-pagination__page--active' : '' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
            <a href="/blog?page=<?= $page + 1 ?>" class="bl-pagination__btn bl-pagination__btn--next">
                Sledeća
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <?php elseif (!$featured): ?>
        <div class="bl-empty">
            <div class="bl-empty__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <p class="bl-empty__text">Uskoro objavljujemo nove članke. Pratite nas!</p>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
