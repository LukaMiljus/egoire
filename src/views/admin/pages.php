<?php
declare(strict_types=1);
$title = 'Stranice i FAQ';

// Handle page edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = inputString('action', '', $_POST);

    if ($action === 'save_page') {
        $slug = inputString('page_slug', '', $_POST);
        $pageTitle = inputString('page_title', '', $_POST);
        $content = trim($_POST['page_content'] ?? '');
        if ($slug && $pageTitle) {
            $db = db();
            $db->prepare("UPDATE pages SET title = ?, body = ?, updated_at = NOW() WHERE slug = ?")
               ->execute([$pageTitle, $content, $slug]);
            flash('success', 'Stranica ažurirana.');
            redirect('/admin/pages');
        }
    }

    if ($action === 'save_faq') {
        $faqId = inputInt('faq_id', 0, $_POST);
        $question = inputString('question', '', $_POST);
        $answer = trim($_POST['answer'] ?? '');
        $sortOrder = inputInt('sort_order', 0, $_POST);
        $isActive = isset($_POST['faq_active']) ? 1 : 0;

        if ($question && $answer) {
            $db = db();
            if ($faqId) {
                $db->prepare("UPDATE faq SET question = ?, answer = ?, sort_order = ?, is_active = ? WHERE id = ?")
                   ->execute([$question, $answer, $sortOrder, $isActive, $faqId]);
            } else {
                $db->prepare("INSERT INTO faq (question, answer, sort_order, is_active) VALUES (?, ?, ?, ?)")
                   ->execute([$question, $answer, $sortOrder, $isActive]);
            }
            flash('success', 'FAQ sačuvano.');
            redirect('/admin/pages');
        }
    }

    if ($action === 'delete_faq') {
        $faqId = inputInt('faq_id', 0, $_POST);
        if ($faqId) {
            $db = db();
            $db->prepare("DELETE FROM faq WHERE id = ?")->execute([$faqId]);
            flash('success', 'FAQ obrisano.');
            redirect('/admin/pages');
        }
    }
}

$db = db();
$pages = $db->query("SELECT * FROM pages ORDER BY slug")->fetchAll(PDO::FETCH_ASSOC);
$faqs = fetchFaqs();

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Stranice i FAQ</h1>
</div>

<!-- Static Pages -->
<div class="card mb-4">
    <h3>Statičke stranice</h3>
    <?php foreach ($pages as $p): ?>
    <form method="POST" class="page-edit-form mb-4">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save_page">
        <input type="hidden" name="page_slug" value="<?= htmlspecialchars($p['slug']) ?>">
        <div class="form-group">
            <label><strong><?= htmlspecialchars($p['slug']) ?></strong> - Naslov</label>
            <input type="text" name="page_title" class="form-control" value="<?= htmlspecialchars($p['title']) ?>">
        </div>
        <div class="form-group">
            <label>Sadržaj (HTML)</label>
            <textarea name="page_content" rows="6" class="form-control"><?= htmlspecialchars($p['body'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Sačuvaj</button>
    </form>
    <hr>
    <?php endforeach; ?>
</div>

<!-- FAQ -->
<div class="card">
    <h3>FAQ</h3>

    <?php foreach ($faqs as $f): ?>
    <form method="POST" class="faq-edit-form mb-3">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save_faq">
        <input type="hidden" name="faq_id" value="<?= $f['id'] ?>">
        <div class="form-row">
            <div class="form-group" style="flex:2">
                <input type="text" name="question" class="form-control" value="<?= htmlspecialchars($f['question']) ?>">
            </div>
            <div class="form-group" style="flex:0.5">
                <input type="number" name="sort_order" class="form-control" value="<?= (int) $f['sort_order'] ?>" title="Redosled">
            </div>
            <div class="form-group" style="flex:0.3">
                <label class="checkbox-label"><input type="checkbox" name="faq_active" value="1" <?= $f['is_active'] ? 'checked' : '' ?>> Aktivan</label>
            </div>
        </div>
        <div class="form-group">
            <textarea name="answer" rows="3" class="form-control"><?= htmlspecialchars($f['answer']) ?></textarea>
        </div>
        <div class="actions-cell">
            <button type="submit" class="btn btn-sm btn-primary">Sačuvaj</button>
            <button type="submit" name="action" value="delete_faq" class="btn btn-sm btn-danger" onclick="return confirm('Obriši?')">Obriši</button>
        </div>
    </form>
    <?php endforeach; ?>

    <hr>
    <h4>Dodaj novo pitanje</h4>
    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save_faq">
        <input type="hidden" name="faq_id" value="0">
        <div class="form-group">
            <label>Pitanje</label>
            <input type="text" name="question" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Odgovor</label>
            <textarea name="answer" rows="3" class="form-control" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Redosled</label>
                <input type="number" name="sort_order" class="form-control" value="0">
            </div>
            <div class="form-group">
                <label class="checkbox-label"><input type="checkbox" name="faq_active" value="1" checked> Aktivan</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Dodaj</button>
    </form>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
