<?php
declare(strict_types=1);
$title = 'Egoire | Luxury Hair Care';

$featuredProducts = fetchProducts(['flags' => ['best_seller'], 'active' => true, 'limit' => 8]);
$newProducts = fetchProducts(['flags' => ['new'], 'active' => true, 'limit' => 4]);
$categories = fetchCategories();
$brands = fetchBrands();
$blogPosts = fetchBlogPosts(['limit' => 3, 'published_only' => true]);

require __DIR__ . '/../layout/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Luxury Hair Care</h1>
        <p>Ekskluzivni proizvodi za negu kose vrhunskog kvaliteta</p>
        <a href="/products" class="btn btn-hero">Pogledaj kolekciju</a>
    </div>
</section>

<!-- Categories -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Kategorije</h2>
        <div class="category-grid">
            <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
            <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" class="category-card">
                <?php if ($cat['image']): ?>
                <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
                <?php endif; ?>
                <h3><?= htmlspecialchars($cat['name']) ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Bestsellers -->
<?php if ($featuredProducts): ?>
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">Najprodavaniji</h2>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $p):
                $imgs = fetchProductImages((int) $p['id']);
                $displayPrice = productDisplayPrice($p);
            ?>
            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="product-card">
                <div class="product-image">
                    <?php if (!empty($imgs)): ?>
                    <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="no-image-placeholder">Egoire</div>
                    <?php endif; ?>
                    <?php $flags = fetchProductFlags((int) $p['id']); ?>
                    <?php if ($flags): ?>
                    <div class="product-badges">
                        <?php foreach ($flags as $f): ?>
                        <span class="product-badge badge-<?= $f ?>"><?= ucfirst($f) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                    <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="product-price">
                        <?php if ($p['sale_price']): ?>
                        <span class="price-old"><?= formatPrice((float) $p['price']) ?></span>
                        <span class="price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                        <?php else: ?>
                        <span class="price-current"><?= formatPrice((float) $p['price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/products" class="btn btn-outline">Pogledaj sve proizvode</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- New Arrivals -->
<?php if ($newProducts): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Novo u ponudi</h2>
        <div class="product-grid">
            <?php foreach ($newProducts as $p):
                $imgs = fetchProductImages((int) $p['id']);
            ?>
            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="product-card">
                <div class="product-image">
                    <?php if (!empty($imgs)): ?>
                    <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="no-image-placeholder">Egoire</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                    <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
                    <div class="product-price">
                        <?php if ($p['sale_price']): ?>
                        <span class="price-old"><?= formatPrice((float) $p['price']) ?></span>
                        <span class="price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                        <?php else: ?>
                        <span class="price-current"><?= formatPrice((float) $p['price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Brands -->
<?php if ($brands): ?>
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">Brendovi</h2>
        <div class="brand-grid">
            <?php foreach ($brands as $b): ?>
            <a href="/brand/<?= htmlspecialchars($b['slug']) ?>" class="brand-card">
                <?php if ($b['logo']): ?>
                <img src="<?= htmlspecialchars($b['logo']) ?>" alt="<?= htmlspecialchars($b['name']) ?>">
                <?php else: ?>
                <span><?= htmlspecialchars($b['name']) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Gift Bag Banner -->
<section class="section gift-bag-banner">
    <div class="container">
        <div class="banner-content">
            <h2>Gift Bag</h2>
            <p>Otkrijte naše specijalne ponude i dobijte popust na odabrane proizvode</p>
            <a href="/gift-bag" class="btn btn-hero">Saznaj više</a>
        </div>
    </div>
</section>

<!-- Blog -->
<?php if ($blogPosts): ?>
<section class="section">
    <div class="container">
        <h2 class="section-title">Blog</h2>
        <div class="blog-grid">
            <?php foreach ($blogPosts as $bp): ?>
            <a href="/blog/<?= htmlspecialchars($bp['slug']) ?>" class="blog-card">
                <?php if ($bp['featured_image']): ?>
                <img src="<?= htmlspecialchars($bp['featured_image']) ?>" alt="" loading="lazy">
                <?php endif; ?>
                <div class="blog-card-body">
                    <span class="blog-date"><?= formatDate($bp['created_at']) ?></span>
                    <h3><?= htmlspecialchars($bp['title']) ?></h3>
                    <p><?= htmlspecialchars($bp['excerpt'] ?? truncate(strip_tags($bp['body']), 120)) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/blog" class="btn btn-outline">Svi postovi</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
