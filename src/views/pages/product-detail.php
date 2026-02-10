<?php
declare(strict_types=1);

$slug = $routeParams['slug'] ?? '';
$product = fetchProductBySlug($slug);
if (!$product || !$product['is_active']) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$images = fetchProductImages((int) $product['id']);
$categories = fetchProductCategories((int) $product['id']);
$flags = fetchProductFlags((int) $product['id']);
$stock = fetchProductStock((int) $product['id']);
$displayPrice = productDisplayPrice($product);

$title = ($product['meta_title'] ?: $product['name']) . ' | Egoire';
$metaDescription = $product['meta_description'] ?? $product['short_description'] ?? '';

// Related products from same brand
$related = fetchProducts([
    'brand_id' => $product['brand_id'],
    'active' => true,
    'limit' => 4,
    'exclude_id' => $product['id'],
]);

require __DIR__ . '/../layout/header.php';
?>

<section class="section product-detail-section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Početna</a>
            <span>/</span>
            <a href="/products">Proizvodi</a>
            <?php if ($categories): ?>
            <span>/</span>
            <a href="/category/<?= htmlspecialchars($categories[0]['slug']) ?>"><?= htmlspecialchars($categories[0]['name']) ?></a>
            <?php endif; ?>
            <span>/</span>
            <span><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="product-detail">
            <!-- Images -->
            <div class="product-gallery">
                <div class="main-image">
                    <?php if (!empty($images)): ?>
                    <img src="<?= htmlspecialchars($images[0]['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" id="mainProductImage">
                    <?php else: ?>
                    <div class="no-image-placeholder large">Egoire</div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                <div class="thumbnail-row">
                    <?php foreach ($images as $i => $img): ?>
                    <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="" class="thumbnail <?= $i === 0 ? 'active' : '' ?>" onclick="document.getElementById('mainProductImage').src=this.src; document.querySelectorAll('.thumbnail').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-detail-info">
                <?php if ($product['brand_name']): ?>
                <a href="/brand/<?= htmlspecialchars($product['brand_slug'] ?? '') ?>" class="product-brand-link"><?= htmlspecialchars($product['brand_name']) ?></a>
                <?php endif; ?>

                <h1><?= htmlspecialchars($product['name']) ?></h1>

                <?php if ($flags): ?>
                <div class="product-badges">
                    <?php foreach ($flags as $f): ?>
                    <span class="product-badge badge-<?= $f['flag'] ?>"><?= ucfirst($f['flag']) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="product-price-large">
                    <?php if ($product['sale_price']): ?>
                    <span class="price-old"><?= formatPrice((float) $product['price']) ?></span>
                    <span class="price-sale"><?= formatPrice((float) $product['sale_price']) ?></span>
                    <?php
                        $discount = round((1 - (float)$product['sale_price'] / (float)$product['price']) * 100);
                    ?>
                    <span class="discount-badge">-<?= $discount ?>%</span>
                    <?php else: ?>
                    <span class="price-current"><?= formatPrice((float) $product['price']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($product['short_description']): ?>
                <p class="product-short-desc"><?= htmlspecialchars($product['short_description']) ?></p>
                <?php endif; ?>

                <!-- Add to Cart Form -->
                <form class="add-to-cart-form" id="addToCartForm">
                    <?= csrfMetaTag() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <?php if ($stock): ?>
                    <div class="form-group">
                        <label>Varijanta</label>
                        <div class="variant-options">
                            <?php foreach ($stock as $i => $sv): ?>
                            <label class="variant-option <?= $i === 0 ? 'active' : '' ?>" data-qty="<?= (int) $sv['quantity'] ?>">
                                <input type="radio" name="variant_label" value="<?= htmlspecialchars($sv['variant_label']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($sv['variant_label']) ?></span>
                                <?php if ((int) $sv['quantity'] < 1): ?>
                                <small class="out-of-stock">Nema na stanju</small>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Količina</label>
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                            <input type="number" name="quantity" value="1" min="1" max="10" id="qtyInput">
                            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block add-to-cart-btn" id="addToCartBtn">
                        Dodaj u korpu
                    </button>
                </form>

                <!-- Categories -->
                <?php if ($categories): ?>
                <div class="product-meta">
                    <span>Kategorije:</span>
                    <?php foreach ($categories as $c): ?>
                    <a href="/category/<?= htmlspecialchars($c['slug']) ?>"><?= htmlspecialchars($c['name']) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description -->
        <?php if ($product['description']): ?>
        <div class="product-description mt-5">
            <h2>Opis proizvoda</h2>
            <div class="content-block">
                <?= $product['description'] ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Related Products -->
<?php if ($related): ?>
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title">Slični proizvodi</h2>
        <div class="product-grid">
            <?php foreach ($related as $rp):
                $rImgs = fetchProductImages((int) $rp['id']);
            ?>
            <a href="/product/<?= htmlspecialchars($rp['slug']) ?>" class="product-card">
                <div class="product-image">
                    <?php if (!empty($rImgs)): ?>
                    <img src="<?= htmlspecialchars($rImgs[0]['image_url']) ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <div class="no-image-placeholder">Egoire</div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-brand"><?= htmlspecialchars($rp['brand_name'] ?? '') ?></span>
                    <h3 class="product-name"><?= htmlspecialchars($rp['name']) ?></h3>
                    <div class="product-price">
                        <?php if ($rp['sale_price']): ?>
                        <span class="price-old"><?= formatPrice((float) $rp['price']) ?></span>
                        <span class="price-sale"><?= formatPrice((float) $rp['sale_price']) ?></span>
                        <?php else: ?>
                        <span class="price-current"><?= formatPrice((float) $rp['price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function changeQty(d) {
    const inp = document.getElementById('qtyInput');
    let v = parseInt(inp.value) + d;
    if (v < 1) v = 1;
    if (v > 10) v = 10;
    inp.value = v;
}

document.querySelectorAll('.variant-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.variant-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
    });
});

document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = document.getElementById('addToCartBtn');
    btn.disabled = true;
    btn.textContent = 'Dodajem...';

    fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✓ Dodato!';
            const badge = document.getElementById('cartBadge');
            if (badge) badge.textContent = data.cart_count;
            setTimeout(() => { btn.textContent = 'Dodaj u korpu'; btn.disabled = false; }, 2000);
        } else {
            alert(data.error || 'Greška');
            btn.textContent = 'Dodaj u korpu';
            btn.disabled = false;
        }
    })
    .catch(() => {
        alert('Greška pri dodavanju');
        btn.textContent = 'Dodaj u korpu';
        btn.disabled = false;
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
