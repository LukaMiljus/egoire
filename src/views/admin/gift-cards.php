<?php
declare(strict_types=1);
$title = 'Poklon kartice';

// Handle create gift card
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = inputString('action', '', $_POST);

    if ($action === 'create') {
        $amount = inputFloat('amount', 0, $_POST);
        $recipientEmail = inputString('recipient_email', '', $_POST);
        $senderName = inputString('sender_name', '', $_POST);
        $message = inputString('message', '', $_POST);
        if ($amount > 0) {
            $result = createGiftCard($amount, null, $recipientEmail ?: null, $senderName ?: null, $message ?: null);
            if ($result['success']) {
                flash('success', 'Poklon kartica kreirana: ' . $result['code']);
            } else {
                flash('error', $result['error'] ?? 'Greška pri kreiranju.');
            }
            redirect('/admin/gift-cards');
        } else {
            flash('error', 'Iznos mora biti veći od 0.');
        }
    }

    if ($action === 'update_settings') {
        $db = db();
        $isActive = isset($_POST['gc_active']) ? 1 : 0;
        $customMin = inputFloat('gc_min', 500, $_POST);
        $customMax = inputFloat('gc_max', 50000, $_POST);
        $expiryDays = inputInt('gc_validity', 365, $_POST);
        $db->prepare("UPDATE gift_card_settings SET is_active = ?, custom_min = ?, custom_max = ?, default_expiry_days = ?, updated_at = NOW() ORDER BY id DESC LIMIT 1")
           ->execute([$isActive, $customMin, $customMax, $expiryDays]);
        flash('success', 'Podešavanja sačuvana.');
        redirect('/admin/gift-cards');
    }
}

$giftCards = fetchGiftCards();
$gcSettings = fetchGiftCardSettings();
$gcAmounts = fetchGiftCardAmounts();

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Poklon kartice</h1>
</div>

<div class="form-grid-2">
    <!-- Settings -->
    <div class="card mb-4">
        <h3>Podešavanja</h3>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_settings">
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="gc_active" value="1" <?= ($gcSettings['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Aktivno
                </label>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Min iznos</label>
                    <input type="number" name="gc_min" class="form-control" value="<?= $gcSettings['custom_min'] ?? 500 ?>">
                </div>
                <div class="form-group">
                    <label>Max iznos</label>
                    <input type="number" name="gc_max" class="form-control" value="<?= $gcSettings['custom_max'] ?? 50000 ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Važnost (dana)</label>
                <input type="number" name="gc_validity" class="form-control" value="<?= $gcSettings['default_expiry_days'] ?? 365 ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Sačuvaj</button>
        </form>

        <h4 class="mt-4">Predefinisani iznosi</h4>
        <div class="tag-list">
            <?php foreach ($gcAmounts as $a): ?>
            <span class="badge"><?= formatPrice((float) $a['amount']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Create Gift Card -->
    <div class="card mb-4">
        <h3>Kreiraj poklon karticu</h3>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label>Iznos (RSD) *</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email primaoca</label>
                <input type="email" name="recipient_email" class="form-control">
            </div>
            <div class="form-group">
                <label>Ime pošiljaoca</label>
                <input type="text" name="sender_name" class="form-control">
            </div>
            <div class="form-group">
                <label>Poruka</label>
                <textarea name="message" rows="3" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kreiraj</button>
        </form>
    </div>
</div>

<!-- List -->
<div class="card">
    <h3>Sve poklon kartice</h3>
    <table class="admin-table">
        <thead>
            <tr><th>Kod</th><th>Iznos</th><th>Preostalo</th><th>Status</th><th>Primalac</th><th>Datum</th><th>Ističe</th></tr>
        </thead>
        <tbody>
            <?php foreach ($giftCards as $gc): ?>
            <tr>
                <td><code><?= htmlspecialchars($gc['code']) ?></code></td>
                <td><?= formatPrice((float) $gc['initial_amount']) ?></td>
                <td><?= formatPrice((float) $gc['balance']) ?></td>
                <td><span class="badge <?= $gc['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>"><?= $gc['status'] === 'active' ? 'Aktivna' : htmlspecialchars(ucfirst($gc['status'])) ?></span></td>
                <td><?= htmlspecialchars($gc['recipient_email'] ?? '-') ?></td>
                <td><?= formatDate($gc['created_at']) ?></td>
                <td><?= $gc['expires_at'] ? formatDate($gc['expires_at']) : '-' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($giftCards)): ?>
            <tr><td colspan="7" class="text-muted text-center">Nema poklon kartica.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
