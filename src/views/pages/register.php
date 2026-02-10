<?php
declare(strict_types=1);
if (isUserAuthenticated()) redirect('/account');
$title = 'Registracija | Egoire';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('user_register', 3, 600);
    $data = [
        'first_name' => inputString('first_name', '', $_POST),
        'last_name'  => inputString('last_name', '', $_POST),
        'email'      => sanitizeEmail($_POST['email'] ?? ''),
        'phone'      => inputString('phone', '', $_POST),
        'password'   => $_POST['password'] ?? '',
    ];
    $confirm = $_POST['password_confirm'] ?? '';

    $errors = [];
    if (!$data['first_name']) $errors[] = 'Ime je obavezno.';
    if (!$data['last_name']) $errors[] = 'Prezime je obavezno.';
    if (!isValidEmail($data['email'])) $errors[] = 'Neispravan email.';
    $pwdCheck = validatePassword($data['password']);
    if (!$pwdCheck['valid']) $errors = array_merge($errors, $pwdCheck['errors']);
    if ($data['password'] !== $confirm) $errors[] = 'Lozinke se ne poklapaju.';

    if (empty($errors)) {
        $result = registerUser($data);
        if ($result['success']) {
            rateLimitReset('user_register');
            flash('success', 'Registracija uspešna! Dobrodošli.');
            redirect('/account');
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
            <h1>Registracija</h1>
            <?= renderFlash() ?>
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ime *</label>
                        <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars(inputString('first_name', '', $_POST)) ?>">
                    </div>
                    <div class="form-group">
                        <label>Prezime *</label>
                        <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars(inputString('last_name', '', $_POST)) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars(inputString('email', '', $_POST)) ?>">
                </div>
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars(inputString('phone', '', $_POST)) ?>">
                </div>
                <div class="form-group">
                    <label>Lozinka *</label>
                    <input type="password" name="password" class="form-control" required>
                    <small class="text-muted">Min 8 karaktera, veliko slovo, malo slovo, cifra</small>
                </div>
                <div class="form-group">
                    <label>Potvrdi lozinku *</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Registruj se</button>
            </form>
            <div class="auth-links">
                <a href="/login">Već imate nalog? Prijavite se</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
