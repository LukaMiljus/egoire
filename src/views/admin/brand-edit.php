<?php
declare(strict_types=1);

$id = inputInt('id');
$brand = $id ? fetchBrandById($id) : null;
$isEdit = $brand !== null;
$title = $isEdit ? 'Uredi brend' : 'Novi brend';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $data = [
        'name'             => inputString('name', '', $_POST),
        'slug'             => inputString('slug', '', $_POST) ?: generateSlug(inputString('name', '', $_POST)),
        'description'      => trim($_POST['description'] ?? ''),
        'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'       => inputInt('sort_order', 0, $_POST),
    ];

    if (!empty($_FILES['logo']['name'])) {
        $result = uploadImage($_FILES['logo'], 'brands');
        if ($result) {
            $data['logo'] = $result;
        }
    }

    $errors = [];
    if (!$data['name']) $errors[] = 'Naziv je obavezan.';

    if (empty($errors)) {
        saveBrand($data, $id ?: null);
        flash('success', $isEdit ? 'Brend ažuriran.' : 'Brend kreiran.');
        redirect('/admin/brands');
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <a href="/admin/brands" class="btn btn-secondary btn-sm">&larr; Nazad</a>
    <h1><?= $title ?></h1>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="form-grid-2">
        <div>
            <div class="card mb-4">
                <h3>Informacije</h3>
                <div class="form-group">
                    <label>Naziv *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($brand['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($brand['slug'] ?? '') ?>" placeholder="Automatski">
                </div>
                <div class="form-group">
                    <label>Opis</label>
                    <textarea name="description" rows="5" class="form-control"><?= htmlspecialchars($brand['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= ($brand['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Aktivan
                    </label>
                </div>
            </div>
        </div>
        <div>
            <div class="card mb-4">
                <h3>Logo</h3>
                <?php if (!empty($brand['logo'])): ?>
                <div class="current-image mb-3">
                    <img src="<?= htmlspecialchars($brand['logo']) ?>" alt="" style="max-width: 200px;">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <input type="file" name="logo" accept="image/*" class="form-control">
                </div>
            </div>
            <div class="card mb-4">
                <h3>SEO</h3>
                <div class="form-group">
                    <label>Meta naslov</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($brand['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Meta opis</label>
                    <textarea name="meta_description" rows="3" class="form-control"><?= htmlspecialchars($brand['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Sačuvaj' : 'Kreiraj' ?></button>
        <a href="/admin/brands" class="btn btn-secondary">Otkaži</a>
    </div>
</form>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
