<?php
declare(strict_types=1);
$title = 'Nova kampanja';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $subject = inputString('subject', '', $_POST);
    $body = trim($_POST['body'] ?? '');
    if ($subject && $body) {
        createEmailCampaign($subject, $body);
        flash('success', 'Kampanja kreirana kao draft.');
        redirect('/admin/marketing');
    } else {
        flash('error', 'Sva polja su obavezna.');
    }
}

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <a href="/admin/marketing" class="btn btn-secondary btn-sm">&larr; Nazad</a>
    <h1>Nova email kampanja</h1>
</div>

<div class="card">
    <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Naslov emaila *</label>
            <input type="text" name="subject" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Telo emaila (HTML) *</label>
            <textarea name="body" rows="12" class="form-control" required></textarea>
            <small class="text-muted">Podržava HTML. Koristite {{unsubscribe_url}} za link za odjavu.</small>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Kreiraj draft</button>
            <a href="/admin/marketing" class="btn btn-secondary">Otkaži</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
