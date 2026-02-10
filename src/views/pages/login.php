<?php
declare(strict_types=1);
if (isUserAuthenticated()) redirect('/account');
$title = 'Prijava | Egoire';
$redirectTo = inputString('redirect') ?: '/account';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('user_login', 5, 300);
    $email = inputString('email', '', $_POST);
    $password = $_POST['password'] ?? '';
    $result = attemptUserLogin($email, $password);
    if ($result['success']) {
        rateLimitReset('user_login');
        redirect($redirectTo);
    } else {
        flash('error', $result['error']);
    }
}

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container container-sm">
        <div class="auth-card">
            <h1>Prijava</h1>
            <?= renderFlash() ?>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required autofocus value="<?= htmlspecialchars(inputString('email', '', $_POST)) ?>">
                </div>
                <div class="form-group">
                    <label>Lozinka</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Prijavi se</button>
            </form>
            <div class="auth-links">
                <a href="/forgot-password">Zaboravljena lozinka?</a>
                <a href="/register">Nemate nalog? Registrujte se</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
