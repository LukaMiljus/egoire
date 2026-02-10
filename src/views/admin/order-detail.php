<?php
declare(strict_types=1);
$id = inputInt('id');
if (!$id) { redirect('/admin/orders'); }
$order = fetchOrderById($id);
if (!$order) { redirect('/admin/orders'); }

$items = fetchOrderItems($id);
$address = fetchOrderAddress($id);
$notes = fetchOrderNotes($id);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = inputString('action', '', $_POST);

    if ($action === 'change_status') {
        $newStatus = inputString('new_status', '', $_POST);
        if ($newStatus) {
            updateOrderStatus($id, $newStatus);
            flash('success', 'Status porudžbine ažuriran.');
            redirect('/admin/order?id=' . $id);
        }
    }

    if ($action === 'add_note') {
        $note = inputString('note', '', $_POST);
        if ($note) {
            $adminId = $_SESSION['admin_id'] ?? null;
            addOrderNote($id, $note, $adminId);
            flash('success', 'Beleška dodata.');
            redirect('/admin/order?id=' . $id);
        }
    }
}

$title = 'Porudžbina ' . $order['order_number'];
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <div>
        <a href="/admin/orders" class="btn btn-secondary btn-sm">&larr; Nazad</a>
        <h1>Porudžbina <?= htmlspecialchars($order['order_number']) ?></h1>
    </div>
    <span class="badge <?= orderStatusClass($order['status']) ?>"><?= orderStatusLabel($order['status']) ?></span>
</div>

<div class="order-detail-grid">
    <!-- Order Info -->
    <div class="card">
        <h3>Informacije</h3>
        <dl class="info-list">
            <dt>Datum</dt><dd><?= formatDateTime($order['created_at']) ?></dd>
            <dt>Plaćanje</dt><dd><?= $order['payment_method'] === 'cod' ? 'Pouzeće' : ucfirst($order['payment_method']) ?></dd>
            <?php if ($order['user_id']): ?>
            <dt>Korisnik</dt><dd><a href="/admin/user?id=<?= $order['user_id'] ?>">#<?= $order['user_id'] ?></a> (registrovan)</dd>
            <?php else: ?>
            <dt>Korisnik</dt><dd>Gost</dd>
            <?php endif; ?>
            <?php if ($order['gift_card_code']): ?>
            <dt>Poklon kartica</dt><dd><?= htmlspecialchars($order['gift_card_code']) ?> (−<?= formatPrice((float) $order['gift_card_amount']) ?>)</dd>
            <?php endif; ?>
            <?php if ((float) $order['loyalty_earned'] > 0): ?>
            <dt>Loyalty bodovi (zarađeno)</dt><dd>+<?= (int) $order['loyalty_earned'] ?></dd>
            <?php endif; ?>
            <?php if ((float) $order['loyalty_spent'] > 0): ?>
            <dt>Loyalty bodovi (potrošeno)</dt><dd>-<?= (int) $order['loyalty_spent'] ?></dd>
            <?php endif; ?>
        </dl>
    </div>

    <!-- Customer / Address -->
    <div class="card">
        <h3>Kupac i dostava</h3>
        <?php if ($address): ?>
        <dl class="info-list">
            <dt>Ime</dt><dd><?= htmlspecialchars($address['first_name'] . ' ' . $address['last_name']) ?></dd>
            <dt>Email</dt><dd><?= htmlspecialchars($order['email']) ?></dd>
            <dt>Telefon</dt><dd><?= htmlspecialchars($address['phone']) ?></dd>
            <dt>Adresa</dt><dd><?= htmlspecialchars($address['address']) ?></dd>
            <dt>Grad</dt><dd><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['postal_code']) ?></dd>
            <dt>Država</dt><dd><?= htmlspecialchars($address['country']) ?></dd>
            <?php if ($address['note']): ?>
            <dt>Napomena</dt><dd><?= htmlspecialchars($address['note']) ?></dd>
            <?php endif; ?>
        </dl>
        <?php endif; ?>
    </div>
</div>

<!-- Order Items -->
<div class="card mt-4">
    <h3>Stavke</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Proizvod</th>
                <th>Varijanta</th>
                <th>Cena</th>
                <th>Kol.</th>
                <th>Popust</th>
                <th>Ukupno</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $subtotal = 0;
            foreach ($items as $item):
                $lineTotal = (float)$item['price'] * (int)$item['quantity'];
                $discount = (float)($item['discount_amount'] ?? 0);
                $lineFinal = $lineTotal - $discount;
                $subtotal += $lineFinal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= htmlspecialchars($item['variant_label'] ?? '-') ?></td>
                <td><?= formatPrice((float) $item['price']) ?></td>
                <td><?= (int) $item['quantity'] ?></td>
                <td><?= $discount > 0 ? '-' . formatPrice($discount) : '-' ?></td>
                <td><?= formatPrice($lineFinal) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Međuzbir</strong></td>
                <td><?= formatPrice($subtotal) ?></td>
            </tr>
            <?php if ((float) $order['shipping_price'] > 0): ?>
            <tr>
                <td colspan="5" class="text-right">Dostava</td>
                <td><?= formatPrice((float) $order['shipping_price']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ((float) ($order['gift_card_amount'] ?? 0) > 0): ?>
            <tr>
                <td colspan="5" class="text-right">Poklon kartica</td>
                <td>-<?= formatPrice((float) $order['gift_card_amount']) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>Ukupno</strong></td>
                <td><strong><?= formatPrice((float) $order['total_price']) ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- Gift Bag Info -->
<?php
$giftBagInfo = null;
try {
    $db = db();
    $stmt = $db->prepare("SELECT og.*, gbr.name AS rule_name FROM order_gift_bag og
        LEFT JOIN gift_bag_rules gbr ON og.gift_bag_rule_id = gbr.id
        WHERE og.order_id = ?");
    $stmt->execute([$id]);
    $giftBagInfo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}
if ($giftBagInfo): ?>
<div class="card mt-4">
    <h3>Gift Bag</h3>
    <p>Pravilo: <strong><?= htmlspecialchars($giftBagInfo['rule_name'] ?? 'N/A') ?></strong></p>
    <p>Popust: <strong><?= formatPrice((float) $giftBagInfo['discount_amount']) ?></strong></p>
</div>
<?php endif; ?>

<div class="order-detail-grid mt-4">
    <!-- Status Change -->
    <div class="card">
        <h3>Promeni status</h3>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="change_status">
            <div class="form-group">
                <select name="new_status" class="form-control">
                    <?php foreach (['new','processing','shipped','delivered','canceled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= orderStatusLabel($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Sačuvaj</button>
        </form>
    </div>

    <!-- Notes -->
    <div class="card">
        <h3>Beleške</h3>
        <div class="notes-list">
            <?php foreach ($notes as $note): ?>
            <div class="note-item">
                <span class="note-date"><?= formatDateTime($note['created_at']) ?></span>
                <?php if (!empty($note['admin_name'])): ?>
                <span class="badge badge-info"><?= htmlspecialchars($note['admin_name']) ?></span>
                <?php endif; ?>
                <p><?= nl2br(htmlspecialchars($note['note'])) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <form method="POST" class="mt-3">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_note">
            <div class="form-group">
                <textarea name="note" rows="3" class="form-control" placeholder="Dodaj belešku..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Dodaj belešku</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
