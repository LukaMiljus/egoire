<?php
declare(strict_types=1);

$id = inputInt('id');
$category = $id ? fetchCategoryById($id) : null;
$isEdit = $category !== null;
$title = $isEdit ? 'Uredi kategoriju' : 'Nova kategorija';
$categories = fetchCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $data = [
        'name'             => inputString('name', '', $_POST),
        'slug'             => inputString('slug', '', $_POST) ?: generateSlug(inputString('name', '', $_POST)),
        'parent_id'        => inputInt('parent_id', 0, $_POST) ?: null,
        'description'      => trim($_POST['description'] ?? ''),
        'meta_title'       => inputString('meta_title', '', $_POST),
        'meta_description' => inputString('meta_description', '', $_POST),
        'sort_order'       => inputInt('sort_order', 0, $_POST),
        'is_active'        => isset($_POST['is_active']) ? 1 : 0,
    ];

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $result = uploadImage($_FILES['image'], 'uploads/categories');
        if ($result['success']) {
            $data['image'] = $result['path'];
        }
    }

    $errors = [];
    if (!$data['name']) $errors[] = 'Naziv je obavezan.';

    if (empty($errors)) {
        saveCategory($data, $id ?: null);
        flash('success', $isEdit ? 'Kategorija ažurirana.' : 'Kategorija kreirana.');
        redirect('/admin/categories');
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <a href="/admin/categories" class="btn btn-secondary btn-sm">&larr; Nazad</a>
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
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($category['slug'] ?? '') ?>" placeholder="Automatski">
                </div>
                <div class="form-group">
                    <label>Nadkategorija</label>
                    <select name="parent_id" class="form-control">
                        <option value="">-- Bez nadkategorije --</option>
                        <?php foreach ($categories as $c):
                            if ($isEdit && $c['id'] == $id) continue;
                        ?>
                        <option value="<?= $c['id'] ?>" <?= ($category['parent_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Opis</label>
                    <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Redosled</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Aktivna
                    </label>
                </div>
            </div>
        </div>

        <div>
            <div class="card mb-4">
                <h3>Slika</h3>
                <?php if (!empty($category['image'])): ?>
                <div class="current-image mb-3">
                    <img src="<?= htmlspecialchars($category['image']) ?>" alt="" style="max-width: 200px;">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>
            </div>

            <div class="card mb-4">
                <h3>SEO</h3>
                <div class="form-group">
                    <label>Meta naslov</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($category['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Meta opis</label>
                    <textarea name="meta_description" rows="3" class="form-control"><?= htmlspecialchars($category['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Sačuvaj' : 'Kreiraj' ?></button>
        <a href="/admin/categories" class="btn btn-secondary">Otkaži</a>
    </div>
</form>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
