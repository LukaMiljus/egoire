<?php
declare(strict_types=1);
$title = 'Zaboravljena lozinka | Egoire';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('forgot_pass', 3, 600);
    $email = sanitizeEmail($_POST['email'] ?? '');
    if (isValidEmail($email)) {
        requestPasswordReset($email);
    }
    flash('success', 'Ako nalog postoji, poslaćemo vam link za reset lozinke.');
    redirect('/forgot-password');
}

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container container-sm">
        <div class="auth-card">
            <h1>Zaboravljena lozinka</h1>
            <?= renderFlash() ?>
            <p>Unesite email adresu i poslaćemo vam link za reset lozinke.</p>
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Pošalji link</button>
            </form>
            <div class="auth-links">
                <a href="/login">Nazad na prijavu</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
