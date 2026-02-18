<?php
/* ============================================================
   Egoire — Luxury Login Page
   Layout: Split-screen editorial + form
   CSS:    public/css/login.css
   ============================================================ */
declare(strict_types=1);
if (isUserAuthenticated()) redirect('/account');

$title       = 'Prijava';
$pageStyles  = ['/css/login.css'];
$redirectTo  = inputString('redirect') ?: '/account';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('user_login', 5, 300);

    $email    = inputString('email', '', $_POST);
    $password = $_POST['password'] ?? '';
    $result   = attemptUserLogin($email, $password);

    if ($result['success']) {
        rateLimitReset('user_login');
        redirect($redirectTo);
    } else {
        flash('error', $result['error']);
    }
}

require __DIR__ . '/../layout/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     LOGIN — Split-screen luxury layout
═══════════════════════════════════════════════════════════ -->
<section class="lx-login">
    <div class="lx-login__wrap">

        <!-- ── Left: Editorial Panel ── -->
        <div class="lx-login__editorial">
            <div class="lx-login__editorial-inner">
                <p class="lx-login__eyebrow">Vaš nalog</p>
                <h1 class="lx-login__headline">Dobrodošli<br>nazad.</h1>
                <p class="lx-login__subline">Prijavite se i uživajte u ekskluzivnim pogodnostima.</p>

                <div class="lx-login__benefits">
                    <div class="lx-login__benefit">
                        <span class="lx-login__benefit-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </span>
                        <div>
                            <strong>Loyalty program</strong>
                            <p>Sakupljajte poene i ostvarite ekskluzivne popuste.</p>
                        </div>
                    </div>
                    <div class="lx-login__benefit">
                        <span class="lx-login__benefit-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 12V8H6a2 2 0 01-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 000 4h4v-4h-4z"/></svg>
                        </span>
                        <div>
                            <strong>Ekskluzivne ponude</strong>
                            <p>Pristup posebnim cenama i limitiranim izdanjima.</p>
                        </div>
                    </div>
                    <div class="lx-login__benefit">
                        <span class="lx-login__benefit-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </span>
                        <div>
                            <strong>Brža kupovina</strong>
                            <p>Sačuvane adrese i istorija narudžbina za jednostavnije plaćanje.</p>
                        </div>
                    </div>
                    <div class="lx-login__benefit">
                        <span class="lx-login__benefit-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </span>
                        <div>
                            <strong>Rani pristup</strong>
                            <p>Budite prvi koji saznaju o novim brendovima i kolekcijama.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right: Login Form ── -->
        <div class="lx-login__form-side">
            <div class="lx-login__form-card">

                <h2 class="lx-login__form-title">Prijava</h2>
                <p class="lx-login__form-subtitle">Unesite vaše podatke za pristup nalogu.</p>

                <?= renderFlash() ?>

                <form method="POST" class="lx-login__form" autocomplete="on">
                    <?= csrfField() ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>">

                    <div class="lx-form-group">
                        <label for="loginEmail" class="lx-form-label">Email adresa</label>
                        <input
                            type="email"
                            id="loginEmail"
                            name="email"
                            class="lx-form-input"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="vase.ime@email.com"
                            value="<?= htmlspecialchars(inputString('email', '', $_POST)) ?>"
                        >
                    </div>

                    <div class="lx-form-group">
                        <div class="lx-form-label-row">
                            <label for="loginPassword" class="lx-form-label">Lozinka</label>
                            <a href="/forgot-password" class="lx-form-link">Zaboravljena?</a>
                        </div>
                        <input
                            type="password"
                            id="loginPassword"
                            name="password"
                            class="lx-form-input"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        >
                    </div>

                    <button type="submit" class="lx-form-btn">
                        <span>Prijavi se</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                </form>

                <div class="lx-login__divider">
                    <span>ili</span>
                </div>

                <div class="lx-login__register-cta">
                    <p>Nemate nalog?</p>
                    <a href="/register" class="lx-form-btn lx-form-btn--outline">Kreirajte nalog</a>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
