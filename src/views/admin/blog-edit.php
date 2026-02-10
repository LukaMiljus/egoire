<?php
declare(strict_types=1);

$id = inputInt('id');
$post = $id ? fetchBlogPostById($id) : null;
$isEdit = $post !== null;
$title = $isEdit ? 'Uredi post' : 'Novi post';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $data = [
        'title'            => inputString('title', '', $_POST),
        'slug'             => inputString('slug', '', $_POST) ?: generateSlug(inputString('title', '', $_POST)),
        'content'          => trim($_POST['content'] ?? ''),
        'excerpt'          => inputString('excerpt', '', $_POST),
        'author'           => inputString('author', '', $_POST),
        'meta_title'       => inputString('meta_title', '', $_POST),
        'meta_description' => inputString('meta_description', '', $_POST),
        'is_published'     => isset($_POST['is_published']) ? 1 : 0,
    ];

    if (!empty($_FILES['featured_image']['name'])) {
        $result = uploadImage($_FILES['featured_image'], 'uploads/blog');
        if ($result['success']) {
            $data['featured_image'] = $result['path'];
        }
    }

    $errors = [];
    if (!$data['title']) $errors[] = 'Naslov je obavezan.';

    if (empty($errors)) {
        saveBlogPost($data, $id ?: null);
        flash('success', $isEdit ? 'Post ažuriran.' : 'Post kreiran.');
        redirect('/admin/blog');
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <a href="/admin/blog" class="btn btn-secondary btn-sm">&larr; Nazad</a>
    <h1><?= $title ?></h1>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="form-grid-2">
        <div>
            <div class="card mb-4">
                <h3>Sadržaj</h3>
                <div class="form-group">
                    <label>Naslov *</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($post['slug'] ?? '') ?>" placeholder="Automatski">
                </div>
                <div class="form-group">
                    <label>Izvod</label>
                    <textarea name="excerpt" rows="3" class="form-control"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Sadržaj (HTML)</label>
                    <textarea name="content" rows="15" class="form-control"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div>
            <div class="card mb-4">
                <h3>Detalji</h3>
                <div class="form-group">
                    <label>Autor</label>
                    <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($post['author'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_published" value="1" <?= ($post['is_published'] ?? 0) ? 'checked' : '' ?>>
                        Objavljen
                    </label>
                </div>
            </div>

            <div class="card mb-4">
                <h3>Slika</h3>
                <?php if (!empty($post['featured_image'])): ?>
                <div class="current-image mb-3">
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="" style="max-width: 200px;">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <input type="file" name="featured_image" accept="image/*" class="form-control">
                </div>
            </div>

            <div class="card mb-4">
                <h3>SEO</h3>
                <div class="form-group">
                    <label>Meta naslov</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($post['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Meta opis</label>
                    <textarea name="meta_description" rows="3" class="form-control"><?= htmlspecialchars($post['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Sačuvaj' : 'Kreiraj' ?></button>
        <a href="/admin/blog" class="btn btn-secondary">Otkaži</a>
    </div>
</form>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
