<?php
declare(strict_types=1);
$slug = $routeParams['slug'] ?? '';
$page = fetchPage($slug);
if (!$page) { http_response_code(404); require __DIR__ . '/404.php'; return; }

$title = htmlspecialchars($page['title']) . ' | Egoire';
require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero"><div class="container"><h1><?= htmlspecialchars($page['title']) ?></h1></div></section>

<section class="section">
    <div class="container container-md">
        <div class="content-block">
            <?= $page['body'] ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
