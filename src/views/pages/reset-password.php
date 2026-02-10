<?php
declare(strict_types=1);
$title = 'Reset lozinke | Egoire';
$token = inputString('token');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $token = inputString('token', '', $_POST);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    $errors = [];
    $pwdCheck = validatePassword($password);
    if (!$pwdCheck['valid']) $errors = array_merge($errors, $pwdCheck['errors']);
    if ($password !== $confirm) $errors[] = 'Lozinke se ne poklapaju.';

    if (empty($errors)) {
        $result = resetPassword($token, $password);
        if ($result['success']) {
            flash('success', 'Lozinka uspešno promenjena. Prijavite se.');
            redirect('/login');
        } else {
            flash('error', $result['error']);
        }
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/header.php';
?>

<section class="section">
    <div class="container container-sm">
        <div class="auth-card">
            <h1>Reset lozinke</h1>
            <?= renderFlash() ?>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label>Nova lozinka</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Potvrdi lozinku</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sačuvaj lozinku</button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
