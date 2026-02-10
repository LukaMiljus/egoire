<?php
$title = '404 - Stranica nije pronađena | Egoire';
if (!headers_sent()) http_response_code(404);
require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container">
        <div class="empty-state">
            <h1 style="font-size: 4rem; color: var(--gold);">404</h1>
            <h2>Stranica nije pronađena</h2>
            <p>Stranica koju tražite ne postoji ili je premeštena.</p>
            <a href="/" class="btn btn-primary">Nazad na početnu</a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
