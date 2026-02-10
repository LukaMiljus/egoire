<?php
declare(strict_types=1);
$title = 'Blog';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && inputString('action', '', $_POST) === 'delete') {
    requireCsrf();
    $postId = inputInt('post_id', 0, $_POST);
    if ($postId) {
        $db = db();
        $db->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$postId]);
        flash('success', 'Post obrisan.');
        redirect('/admin/blog');
    }
}

$posts = fetchBlogPosts(['limit' => 100]);
require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <h1>Blog</h1>
    <a href="/admin/blog/new" class="btn btn-primary">+ Novi post</a>
</div>

<div class="card">
    <table class="admin-table">
        <thead>
            <tr><th>Slika</th><th>Naslov</th><th>Autor</th><th>Status</th><th>Datum</th><th>Akcije</th></tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $p): ?>
            <tr>
                <td>
                    <?php if ($p['featured_image']): ?>
                    <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="" class="thumb-sm">
                    <?php else: ?>
                    <span class="no-image">—</span>
                    <?php endif; ?>
                </td>
                <td><a href="/admin/blog/edit?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></a></td>
                <td><?= htmlspecialchars($p['author'] ?? '-') ?></td>
                <td><span class="badge <?= $p['is_published'] ? 'badge-success' : 'badge-secondary' ?>"><?= $p['is_published'] ? 'Objavljen' : 'Draft' ?></span></td>
                <td><?= formatDate($p['created_at']) ?></td>
                <td class="actions-cell">
                    <a href="/admin/blog/edit?id=<?= $p['id'] ?>" class="btn btn-sm">Uredi</a>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Obriši post?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
            <tr><td colspan="6" class="text-muted text-center">Nema postova.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
