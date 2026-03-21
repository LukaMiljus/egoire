<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Podešavanja | Egoire';
$pageStyles = ['/css/account.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = inputString('action', '', $_POST);

    if ($action === 'update_profile') {
        $db = db();
        $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW() WHERE id = ?")
           ->execute([
               inputString('first_name', '', $_POST),
               inputString('last_name', '', $_POST),
               inputString('phone', '', $_POST),
               $user['id']
           ]);
        flash('success', 'Profil ažuriran.');
        redirect('/account/settings');
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            flash('error', 'Trenutna lozinka nije tačna.');
        } elseif ($newPass !== $confirm) {
            flash('error', 'Nove lozinke se ne poklapaju.');
        } else {
            $pwdCheck = validatePassword($newPass);
            if (!$pwdCheck['valid']) {
                flash('error', implode(' ', $pwdCheck['errors']));
            } else {
                $db = db();
                $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?")
                   ->execute([password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]), $user['id']]);
                flash('success', 'Lozinka promenjena.');
            }
        }
        redirect('/account/settings');
    }
}

require __DIR__ . '/../layout/header.php';
?>

<section class="ac-page">
    <div class="ac-container">
        <div class="ac-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="ac-content">
                <h1 class="ac-title">Podešavanja</h1>

                <div class="ac-card">
                    <h3 class="ac-card__title">Informacije o profilu</h3>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="update_profile">
                        <div class="ac-form-row">
                            <div class="ac-form-group"><label class="ac-form-label">Ime</label><input type="text" name="first_name" class="ac-form-input" value="<?= htmlspecialchars($user['first_name']) ?>" required></div>
                            <div class="ac-form-group"><label class="ac-form-label">Prezime</label><input type="text" name="last_name" class="ac-form-input" value="<?= htmlspecialchars($user['last_name']) ?>" required></div>
                        </div>
                        <div class="ac-form-group"><label class="ac-form-label">Email</label><input type="email" class="ac-form-input" value="<?= htmlspecialchars($user['email']) ?>" disabled></div>
                        <div class="ac-form-group"><label class="ac-form-label">Telefon</label><input type="tel" name="phone" class="ac-form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                        <button type="submit" class="ac-form-btn">Sačuvaj</button>
                    </form>
                </div>

                <div class="ac-card">
                    <h3 class="ac-card__title">Promena lozinke</h3>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="change_password">
                        <div class="ac-form-group"><label class="ac-form-label">Trenutna lozinka</label><input type="password" name="current_password" class="ac-form-input" required></div>
                        <div class="ac-form-group"><label class="ac-form-label">Nova lozinka</label><input type="password" name="new_password" class="ac-form-input" required></div>
                        <div class="ac-form-group"><label class="ac-form-label">Potvrdi novu lozinku</label><input type="password" name="confirm_password" class="ac-form-input" required></div>
                        <button type="submit" class="ac-form-btn">Promeni lozinku</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
