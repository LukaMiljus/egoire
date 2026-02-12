<?php
declare(strict_types=1);
$title = 'Checkout | Egoire';

$cartItems = fetchCartItems();
$totals = calculateCartTotals($cartItems);

if (empty($cartItems)) {
    redirect('/cart');
}

$user = isUserAuthenticated() ? currentUser() : null;
$addresses = $user ? fetchUserAddresses((int) $user['id']) : [];
$loyaltyInfo = $user ? fetchUserLoyalty((int) $user['id']) : null;
$loyaltySettings = fetchLoyaltySettings();

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Checkout</h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <form id="checkoutForm" method="POST" action="/api/checkout">
            <?= csrfField() ?>

            <div class="checkout-layout">
                <!-- Left: Details -->
                <div class="checkout-form">
                    <?php if (!isUserAuthenticated()): ?>
                    <div class="checkout-guest-notice">
                        <p>Već imate nalog? <a href="/login?redirect=/checkout">Prijavite se</a> za brži checkout i loyalty bodove.</p>
                    </div>
                    <?php endif; ?>

                    <!-- Shipping Address -->
                    <div class="card mb-4">
                        <h3>Adresa za dostavu</h3>

                        <?php if ($addresses): ?>
                        <div class="form-group">
                            <label>Sačuvane adrese</label>
                            <select id="savedAddress" class="form-control" onchange="fillAddress(this.value)">
                                <option value="">-- Nova adresa --</option>
                                <?php foreach ($addresses as $a): ?>
                                <option value="<?= $a['id'] ?>" data-addr='<?= htmlspecialchars(json_encode($a)) ?>'><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name'] . ' - ' . $a['address'] . ', ' . $a['city']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ime *</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Prezime *</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Telefon *</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Adresa *</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Grad *</label>
                                <input type="text" name="city" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Poštanski broj *</label>
                                <input type="text" name="postal_code" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Država</label>
                            <input type="text" name="country" class="form-control" value="Srbija">
                        </div>
                        <div class="form-group">
                            <label>Napomena</label>
                            <textarea name="note" rows="3" class="form-control" placeholder="Opciona napomena za dostavu..."></textarea>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="card mb-4">
                        <h3>Način plaćanja</h3>
                        <div class="payment-options">
                            <label class="payment-option active">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div class="payment-label">
                                    <strong>Plaćanje pouzećem</strong>
                                    <span>Platite kuriru pri preuzimanju</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Gift Card -->
                    <div class="card mb-4">
                        <h3>Poklon kartica</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="gift_card_code" id="giftCardCode" class="form-control" placeholder="XXXX-XXXX-XXXX-XXXX">
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-secondary" id="validateGiftCardBtn">Proveri</button>
                            </div>
                        </div>
                        <div id="giftCardResult"></div>
                    </div>

                    <!-- Loyalty -->
                    <?php if ($loyaltyInfo && (int) $loyaltyInfo['points_balance'] >= (int) ($loyaltySettings['min_points_redeem'] ?? 100)): ?>
                    <div class="card mb-4">
                        <h3>Loyalty bodovi</h3>
                        <p>Imate <strong><?= (int) $loyaltyInfo['points_balance'] ?></strong> bodova (<?= formatPrice((float) $loyaltyInfo['points_balance'] * (float) ($loyaltySettings['rsd_per_point'] ?? 1)) ?>)</p>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="use_loyalty" value="1">
                                Iskoristi loyalty bodove
                            </label>
                        </div>
                        <div class="form-group" id="loyaltyPointsField" style="display:none">
                            <label>Broj bodova (max <?= (int) $loyaltyInfo['points_balance'] ?>)</label>
                            <input type="number" name="loyalty_points" class="form-control" max="<?= (int) $loyaltyInfo['points_balance'] ?>" min="0">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Summary -->
                <div class="checkout-summary">
                    <div class="card sticky">
                        <h3>Vaša porudžbina</h3>
                        <div class="order-items-summary">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="order-summary-item">
                                <span class="item-name"><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                                <span class="item-price"><?= formatPrice(productDisplayPrice($item) * (int) $item['quantity']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <div class="summary-row">
                            <span>Međuzbir</span>
                            <span><?= formatPrice($totals['subtotal']) ?></span>
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
                            <span id="checkoutTotal"><?= formatPrice($totals['total']) ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block mt-3" id="placeOrderBtn">Poruči</button>
                        <p class="text-muted text-center mt-2" style="font-size: 0.8rem;">
                            Klikom na "Poruči" prihvatate naše <a href="/terms">uslove korišćenja</a>.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function fillAddress(id) {
    if (!id) return;
    const opt = document.querySelector('#savedAddress option[value="'+id+'"]');
    if (!opt) return;
    const a = JSON.parse(opt.dataset.addr);
    document.querySelector('[name="first_name"]').value = a.first_name || '';
    document.querySelector('[name="last_name"]').value = a.last_name || '';
    document.querySelector('[name="phone"]').value = a.phone || '';
    document.querySelector('[name="address"]').value = a.address || '';
    document.querySelector('[name="city"]').value = a.city || '';
    document.querySelector('[name="postal_code"]').value = a.postal_code || '';
    document.querySelector('[name="country"]').value = a.country || 'Srbija';
}

// Loyalty toggle
const loyaltyCheck = document.querySelector('[name="use_loyalty"]');
if (loyaltyCheck) {
    loyaltyCheck.addEventListener('change', function() {
        document.getElementById('loyaltyPointsField').style.display = this.checked ? 'block' : 'none';
    });
}

// Gift card validation
document.getElementById('validateGiftCardBtn')?.addEventListener('click', function() {
    const code = document.getElementById('giftCardCode').value.trim();
    if (!code) return;
    fetch('/api/gift-card/validate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ code: code })
    }).then(r => r.json()).then(data => {
        const el = document.getElementById('giftCardResult');
        if (data.valid) {
            el.innerHTML = '<p class="text-success">✓ Kartica validna. Preostalo: ' + data.remaining + '</p>';
        } else {
            el.innerHTML = '<p class="text-danger">✗ ' + (data.error || 'Nevažeća kartica') + '</p>';
        }
    });
});

// Submit
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.textContent = 'Obrađujem...';
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
