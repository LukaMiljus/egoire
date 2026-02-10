<?php

declare(strict_types=1);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('admin_login', 5, 300);

    $username = inputString('username', '', $_POST);
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        rateLimitRecord('admin_login');

        if (attemptAdminLogin($username, $password)) {
            rateLimitReset('admin_login');
            redirect('/admin/dashboard');
        } else {
            $error = 'Pogrešno korisničko ime ili lozinka.';
        }
    } else {
        $error = 'Unesite korisničko ime i lozinku.';
    }
}

if (isAdminAuthenticated()) {
    redirect('/admin/dashboard');
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Prijava – Egoire</title>
    <link rel="stylesheet" href="<?= asset('/css/admin.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-logo">EGOIRE</h1>
            <p class="login-subtitle">Admin Panel</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/admin/login" class="login-form">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="username">Korisničko ime</label>
                    <input type="text" id="username" name="username" required autocomplete="username"
                           value="<?= htmlspecialchars($username ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Lozinka</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-full">Prijavi se</button>
            </form>
        </div>
    </div>
</body>
</html>
