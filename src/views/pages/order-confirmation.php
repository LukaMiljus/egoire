<?php
declare(strict_types=1);
$orderNumber = $routeParams['number'] ?? inputString('order');
if (!$orderNumber) redirect('/');
$order = fetchOrderByNumber($orderNumber);
if (!$order) redirect('/');

$items = fetchOrderItems((int) $order['id']);
$address = fetchOrderAddress((int) $order['id']);
$title = 'Potvrda porudžbine | Egoire';

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container">
        <div class="order-confirmation">
            <div class="confirmation-icon">✓</div>
            <h1>Hvala na porudžbini!</h1>
            <p>Vaša porudžbina <strong><?= htmlspecialchars($order['order_number']) ?></strong> je uspešno primljena.</p>
            <p>Potvrda je poslata na <strong><?= htmlspecialchars($order['email']) ?></strong>.</p>

            <div class="card mt-4">
                <h3>Detalji porudžbine</h3>
                <table class="admin-table">
                    <thead>
                        <tr><th>Proizvod</th><th>Varijanta</th><th>Kol.</th><th>Cena</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= htmlspecialchars($item['variant_label'] ?? '-') ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td><?= formatPrice((float) $item['price'] * (int) $item['quantity']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Ukupno</strong></td>
                            <td><strong><?= formatPrice((float) $order['total_price']) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <?php if ($address): ?>
                <h4 class="mt-3">Dostava na adresu:</h4>
                <p>
                    <?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?><br>
                    <?= htmlspecialchars($address['address']) ?><br>
                    <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['postal_code']) ?><br>
                    <?= htmlspecialchars($address['phone']) ?>
                </p>
                <?php endif; ?>
            </div>

            <div class="mt-4">
                <a href="/products" class="btn btn-primary">Nastavi kupovinu</a>
                <?php if (isUserAuthenticated()): ?>
                <a href="/account/orders" class="btn btn-outline">Moje porudžbine</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
