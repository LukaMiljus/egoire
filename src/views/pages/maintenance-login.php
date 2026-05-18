<?php

declare(strict_types=1);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('maintenance_login', 5, 300);

    $username = inputString('username', '', $_POST);
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        rateLimitRecord('maintenance_login');

        if (attemptAdminLogin($username, $password)) {
            rateLimitReset('maintenance_login');
            redirect('/');
        }

        $error = 'Pogrešno korisničko ime ili lozinka.';
    } else {
        $error = 'Unesite korisničko ime i lozinku.';
    }
}

if (isAdminAuthenticated()) {
    redirect('/');
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Egoire – Prijava tima</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/maintenance-login.css?v=<?= time() ?>">
</head>
<body class="ml-page">
    <div class="ml-bg" aria-hidden="true"></div>
    <div class="ml-overlay" aria-hidden="true"></div>

    <main class="ml-card">
        <a href="/coming-soon" class="ml-back" aria-label="Nazad na Coming Soon">&larr; Nazad</a>

        <img
            class="ml-logo"
            src="/images/logos/egoire-logo.png"
            alt="Egoire"
            width="160"
            height="48"
        >

        <h1 class="ml-title">Prijava tima</h1>
        <p class="ml-subtitle">Pristup sajtu tokom pripreme. Samo za administratore.</p>

        <?php if ($error): ?>
            <div class="ml-alert" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/maintenance-login" class="ml-form" autocomplete="on">
            <?= csrfField() ?>
            <div class="ml-field">
                <label for="ml-username">Korisničko ime</label>
                <input
                    type="text"
                    id="ml-username"
                    name="username"
                    required
                    autocomplete="username"
                    value="<?= htmlspecialchars($username ?? '') ?>"
                >
            </div>
            <div class="ml-field">
                <label for="ml-password">Lozinka</label>
                <input
                    type="password"
                    id="ml-password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </div>
            <button type="submit" class="ml-submit">Prijavi se</button>
        </form>
    </main>
</body>
</html>
