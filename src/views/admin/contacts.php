<?php
declare(strict_types=1);
$title = 'Kontakt poruke';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $msgId = inputInt('message_id', 0, $_POST);
    $status = inputString('status', '', $_POST);
    if ($msgId && $status) {
        updateContactMessageStatus($msgId, $status);
        flash('success', 'Status ažuriran.');
        redirect('/admin/contacts');
    }
}

$messages = fetchContactMessages(['limit' => 50]);
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Kontakt poruke</h1>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr><th>Datum</th><th>Ime</th><th>Email</th><th>Predmet</th><th>Poruka</th><th>Status</th><th>Akcije</th></tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $m): ?>
            <tr>
                <td><?= formatDateTime($m['created_at']) ?></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></td>
                <td><?= htmlspecialchars($m['subject'] ?? '-') ?></td>
                <td class="message-cell"><?= htmlspecialchars(truncate($m['message'], 100)) ?></td>
                <td>
                    <span class="badge <?= match($m['status']) { 'new' => 'badge-warning', 'read' => 'badge-info', 'replied' => 'badge-success', default => '' } ?>">
                        <?= match($m['status']) { 'new' => 'Nova', 'read' => 'Pročitana', 'replied' => 'Odgovoreno', default => $m['status'] } ?>
                    </span>
                </td>
                <td class="actions-cell">
                    <form method="POST" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                        <select name="status" onchange="this.form.submit()" class="form-control-sm">
                            <option value="new" <?= $m['status'] === 'new' ? 'selected' : '' ?>>Nova</option>
                            <option value="read" <?= $m['status'] === 'read' ? 'selected' : '' ?>>Pročitana</option>
                            <option value="replied" <?= $m['status'] === 'replied' ? 'selected' : '' ?>>Odgovoreno</option>
                        </select>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($messages)): ?>
            <tr><td colspan="7" class="text-muted text-center">Nema poruka.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
