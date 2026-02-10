<?php
declare(strict_types=1);
$title = 'Korpa | Egoire';

$cartItems = fetchCartItems();
$totals = calculateCartTotals();

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Korpa</h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($cartItems)): ?>
        <div class="empty-state">
            <svg width="80" height="80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <h2>Vaša korpa je prazna</h2>
            <p>Dodajte proizvode da biste nastavili</p>
            <a href="/products" class="btn btn-primary">Pogledaj proizvode</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-cart-id="<?= $item['id'] ?>">
                    <div class="cart-item-image">
                        <?php $imgs = fetchProductImages((int) $item['product_id']); ?>
                        <?php if (!empty($imgs)): ?>
                        <img src="<?= htmlspecialchars($imgs[0]['image_url']) ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <div class="cart-item-info">
                        <a href="/product/<?= htmlspecialchars($item['product_slug'] ?? '') ?>" class="cart-item-name"><?= htmlspecialchars($item['product_name']) ?></a>
                        <?php if ($item['variant_label']): ?>
                        <span class="cart-item-variant"><?= htmlspecialchars($item['variant_label']) ?></span>
                        <?php endif; ?>
                        <span class="cart-item-price"><?= formatPrice((float) $item['price']) ?></span>
                    </div>
                    <div class="cart-item-quantity">
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">−</button>
                            <input type="number" value="<?= $item['quantity'] ?>" min="1" max="10" readonly>
                            <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                        </div>
                    </div>
                    <div class="cart-item-total">
                        <?= formatPrice((float) $item['price'] * (int) $item['quantity']) ?>
                    </div>
                    <button type="button" class="cart-item-remove" onclick="removeCartItem(<?= $item['id'] ?>)" title="Ukloni">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="card">
                    <h3>Rezime</h3>
                    <div class="summary-row">
                        <span>Međuzbir</span>
                        <span id="cartSubtotal"><?= formatPrice($totals['subtotal']) ?></span>
                    </div>
                    <?php if ($totals['gift_bag_discount'] > 0): ?>
                    <div class="summary-row discount">
                        <span>Gift Bag popust</span>
                        <span>-<?= formatPrice($totals['gift_bag_discount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Dostava</span>
                        <span><?= $totals['shipping'] > 0 ? formatPrice($totals['shipping']) : 'Besplatna' ?></span>
                    </div>
                    <hr>
                    <div class="summary-row total">
                        <span>Ukupno</span>
                        <span id="cartTotal"><?= formatPrice($totals['total']) ?></span>
                    </div>

                    <a href="/checkout" class="btn btn-primary btn-lg btn-block mt-3">Nastavi na plaćanje</a>
                    <a href="/products" class="btn btn-outline btn-block mt-2">Nastavi kupovinu</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function updateCartQty(cartId, qty) {
    if (qty < 1) { removeCartItem(cartId); return; }
    fetch('/api/cart/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ cart_id: cartId, quantity: qty })
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert(data.error || 'Greška'); });
}
function removeCartItem(cartId) {
    fetch('/api/cart/remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ cart_id: cartId })
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
