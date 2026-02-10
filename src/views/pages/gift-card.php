<?php
declare(strict_types=1);
$title = 'Poklon kartica | Egoire';
$gcSettings = fetchGiftCardSettings();
$gcAmounts = fetchGiftCardAmounts();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $amount = inputFloat('amount', 0, $_POST);
    $recipientEmail = inputString('recipient_email', '', $_POST);
    $senderName = inputString('sender_name', '', $_POST);
    $message = inputString('gift_message', '', $_POST);

    if ($amount > 0 && $recipientEmail) {
        $code = createGiftCard($amount, isUserAuthenticated() ? (int) currentUserId() : null, $recipientEmail, $senderName, $message);
        // In production, redirect to payment. For now, show success.
        flash('success', 'Poklon kartica kreirana! Kod: ' . $code);
        redirect('/gift-card');
    } else {
        flash('error', 'Izaberite iznos i unesite email primaoca.');
    }
}

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Poklon kartica</h1>
        <p>Savršen poklon za dragu osobu</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="gift-card-layout">
            <div class="gift-card-visual">
                <div class="gift-card-preview">
                    <div class="gc-brand">Egoire</div>
                    <div class="gc-amount" id="gcPreviewAmount">1.000 RSD</div>
                    <div class="gc-text">Poklon kartica</div>
                </div>
            </div>

            <div class="gift-card-form">
                <form method="POST">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label>Izaberite iznos</label>
                        <div class="amount-options">
                            <?php foreach ($gcAmounts as $a): ?>
                            <label class="amount-option">
                                <input type="radio" name="amount" value="<?= (float) $a['amount'] ?>" onclick="document.getElementById('gcPreviewAmount').textContent='<?= formatPrice((float) $a['amount']) ?>'">
                                <span><?= formatPrice((float) $a['amount']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Vaše ime</label>
                        <input type="text" name="sender_name" class="form-control" placeholder="Vaše ime">
                    </div>
                    <div class="form-group">
                        <label>Email primaoca *</label>
                        <input type="email" name="recipient_email" class="form-control" required placeholder="email@primaoca.com">
                    </div>
                    <div class="form-group">
                        <label>Poruka</label>
                        <textarea name="gift_message" rows="3" class="form-control" placeholder="Opciona poruka za primaoca..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">Kupi poklon karticu</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
