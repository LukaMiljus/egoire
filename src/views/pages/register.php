<?php
/* ============================================================
   Egoire — Luxury Register Page
   Layout: Centered editorial card
   CSS:    public/css/register.css
   ============================================================ */
declare(strict_types=1);
if (isUserAuthenticated()) redirect('/account');

$title      = 'Registracija';
$pageStyles = ['/css/register.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('user_register', 3, 600);

    $data = [
        'first_name'     => inputString('first_name', '', $_POST),
        'last_name'      => inputString('last_name', '', $_POST),
        'email'          => sanitizeEmail($_POST['email'] ?? ''),
        'phone'          => inputString('phone', '', $_POST),
        'password'       => $_POST['password'] ?? '',
        'marketing_optin'=> !empty($_POST['marketing_optin']),
    ];
    $confirm = $_POST['password_confirm'] ?? '';

    $errors = [];
    if (!$data['first_name']) $errors[] = 'Ime je obavezno.';
    if (!$data['last_name'])  $errors[] = 'Prezime je obavezno.';
    if (!isValidEmail($data['email'])) $errors[] = 'Neispravan email.';
    $pwdCheck = validatePassword($data['password']);
    if (!$pwdCheck['valid']) $errors = array_merge($errors, $pwdCheck['errors']);
    if ($data['password'] !== $confirm) $errors[] = 'Lozinke se ne poklapaju.';

    if (empty($errors)) {
        $result = registerUser($data);
        if ($result['success']) {
            rateLimitReset('user_register');
            flash('success', 'Registracija uspešna! Dobrodošli u Egoire.');
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

<!-- ═══════════════════════════════════════════════════════════
     REGISTER — Luxury centered card
═══════════════════════════════════════════════════════════ -->
<section class="lx-register">
    <div class="lx-register__container">

        <!-- Hero text -->
        <div class="lx-register__hero">
            <p class="lx-register__eyebrow">Postanite deo Egoire sveta</p>
            <h1 class="lx-register__headline">Otključajte privilegije</h1>
            <p class="lx-register__subline">
                Kreirajte nalog i pristupite ekskluzivnim ponudama, loyalty programu
                i personalizovanim preporukama za negu kose.
            </p>
        </div>

        <!-- Form card -->
        <div class="lx-register__card">

            <!-- Elegant divider -->
            <div class="lx-register__divider-line" aria-hidden="true"></div>

            <?= renderFlash() ?>

            <form method="POST" class="lx-register__form" autocomplete="on">
                <?= csrfField() ?>

                <!-- Name row -->
                <div class="lx-form-row">
                    <div class="lx-form-group">
                        <label for="regFirstName" class="lx-form-label">Ime *</label>
                        <input
                            type="text"
                            id="regFirstName"
                            name="first_name"
                            class="lx-form-input"
                            required
                            autofocus
                            autocomplete="given-name"
                            placeholder="Vaše ime"
                            value="<?= htmlspecialchars(inputString('first_name', '', $_POST)) ?>"
                        >
                    </div>
                    <div class="lx-form-group">
                        <label for="regLastName" class="lx-form-label">Prezime *</label>
                        <input
                            type="text"
                            id="regLastName"
                            name="last_name"
                            class="lx-form-input"
                            required
                            autocomplete="family-name"
                            placeholder="Vaše prezime"
                            value="<?= htmlspecialchars(inputString('last_name', '', $_POST)) ?>"
                        >
                    </div>
                </div>

                <!-- Benefit microcopy -->
                <div class="lx-register__microcopy">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span>Loyalty poeni se aktiviraju odmah po registraciji</span>
                </div>

                <div class="lx-form-group">
                    <label for="regEmail" class="lx-form-label">Email adresa *</label>
                    <input
                        type="email"
                        id="regEmail"
                        name="email"
                        class="lx-form-input"
                        required
                        autocomplete="email"
                        placeholder="vase.ime@email.com"
                        value="<?= htmlspecialchars(inputString('email', '', $_POST)) ?>"
                    >
                </div>

                <div class="lx-form-group">
                    <label for="regPhone" class="lx-form-label">Telefon</label>
                    <input
                        type="tel"
                        id="regPhone"
                        name="phone"
                        class="lx-form-input"
                        autocomplete="tel"
                        placeholder="+381 6X XXX XXXX"
                        value="<?= htmlspecialchars(inputString('phone', '', $_POST)) ?>"
                    >
                </div>

                <div class="lx-form-row">
                    <div class="lx-form-group">
                        <label for="regPassword" class="lx-form-label">Lozinka *</label>
                        <input
                            type="password"
                            id="regPassword"
                            name="password"
                            class="lx-form-input"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        >
                        <p class="lx-form-hint">Min 8 karaktera, veliko slovo, malo slovo, cifra</p>
                    </div>
                    <div class="lx-form-group">
                        <label for="regPasswordConfirm" class="lx-form-label">Potvrdi lozinku *</label>
                        <input
                            type="password"
                            id="regPasswordConfirm"
                            name="password_confirm"
                            class="lx-form-input"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <!-- Marketing opt-in -->
                <div class="lx-form-checkbox-group">
                    <label class="lx-form-checkbox">
                        <input type="checkbox" name="marketing_optin" value="1">
                        <span class="lx-form-checkbox__mark"></span>
                        <span class="lx-form-checkbox__text">
                            Želim da primam ekskluzivne ponude, novosti o brendovima i beauty savete.
                        </span>
                    </label>
                </div>

                <button type="submit" class="lx-form-btn">
                    <span>Kreiraj nalog</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>

                <!-- Benefit microcopy bottom -->
                <div class="lx-register__microcopy lx-register__microcopy--center">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span>Vaši podaci su sigurni i zaštićeni SSL enkripcijom</span>
                </div>
            </form>

            <div class="lx-register__login-link">
                <span>Već imate nalog?</span>
                <a href="/login">Prijavite se</a>
            </div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
