<?php
declare(strict_types=1);

$id = inputInt('id');
$option = $id ? fetchGiftWrappingById($id) : null;
$isEdit = $option !== null;
$title = $isEdit ? 'Uredi poklon pakovanje' : 'Novo poklon pakovanje';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $data = [
        'name'        => inputString('name', '', $_POST),
        'description' => trim($_POST['description'] ?? ''),
        'price'       => inputFloat('price', 0, $_POST),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'  => inputInt('sort_order', 0, $_POST),
    ];

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $result = uploadImage($_FILES['image'], 'gift-wrapping');
        if ($result) {
            $data['image'] = $result;
        }
    }

    $errors = [];
    if (!$data['name']) $errors[] = 'Naziv je obavezan.';
    if ($data['price'] <= 0) $errors[] = 'Cena mora biti veća od 0.';

    if (empty($errors)) {
        saveGiftWrapping($data, $id ?: null);
        flash('success', $isEdit ? 'Pakovanje ažurirano.' : 'Pakovanje kreirano.');
        redirect('/admin/gift-wrapping');
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <a href="/admin/gift-wrapping" class="btn btn-secondary btn-sm">&larr; Nazad</a>
    <h1><?= $title ?></h1>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="form-grid-2">
        <div>
            <div class="card mb-4">
                <h3>Detalji pakovanja</h3>
                <div class="form-group">
                    <label>Naziv *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($option['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Opis</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($option['description'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cena (RSD) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="<?= $option['price'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Redosled prikaza</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $option['sort_order'] ?? 0 ?>" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= ($option['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Aktivno
                    </label>
                </div>
            </div>
        </div>
        <div>
            <div class="card mb-4">
                <h3>Slika</h3>
                <?php if (!empty($option['image'])): ?>
                <div class="current-image mb-3">
                    <img src="<?= htmlspecialchars($option['image']) ?>" alt="" style="max-width: 200px;">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Sačuvaj' : 'Kreiraj' ?></button>
        <a href="/admin/gift-wrapping" class="btn btn-secondary">Otkaži</a>
    </div>
</form>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
