<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Podešavanja | Egoire';

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

<section class="section">
    <div class="container">
        <div class="account-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="account-content">
                <h1>Podešavanja</h1>

                <div class="card mb-4">
                    <h3>Informacije o profilu</h3>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-row">
                            <div class="form-group"><label>Ime</label><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required></div>
                            <div class="form-group"><label>Prezime</label><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required></div>
                        </div>
                        <div class="form-group"><label>Email</label><input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled></div>
                        <div class="form-group"><label>Telefon</label><input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                        <button type="submit" class="btn btn-primary">Sačuvaj</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Promena lozinke</h3>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group"><label>Trenutna lozinka</label><input type="password" name="current_password" class="form-control" required></div>
                        <div class="form-group"><label>Nova lozinka</label><input type="password" name="new_password" class="form-control" required></div>
                        <div class="form-group"><label>Potvrdi novu lozinku</label><input type="password" name="confirm_password" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary">Promeni lozinku</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
