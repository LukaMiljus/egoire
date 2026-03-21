<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Adrese | Egoire';
$pageStyles = ['/css/account.css'];
$addresses = fetchUserAddresses((int) $user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = inputString('action', '', $_POST);

    if ($action === 'save') {
        $data = [
            'first_name'  => inputString('first_name', '', $_POST),
            'last_name'   => inputString('last_name', '', $_POST),
            'phone'       => inputString('phone', '', $_POST),
            'address'     => inputString('address', '', $_POST),
            'city'        => inputString('city', '', $_POST),
            'postal_code' => inputString('postal_code', '', $_POST),
            'country'     => inputString('country', 'Srbija', $_POST),
            'is_default'  => isset($_POST['is_default']) ? 1 : 0,
        ];
        $addrId = inputInt('address_id', 0, $_POST) ?: null;
        saveUserAddress((int) $user['id'], $data, $addrId);
        flash('success', 'Adresa sačuvana.');
        redirect('/account/addresses');
    }

    if ($action === 'delete') {
        $addrId = inputInt('address_id', 0, $_POST);
        if ($addrId) deleteUserAddress($addrId, (int) $user['id']);
        flash('success', 'Adresa obrisana.');
        redirect('/account/addresses');
    }
}

require __DIR__ . '/../layout/header.php';
?>

<section class="ac-page">
    <div class="ac-container">
        <div class="ac-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="ac-content">
                <h1 class="ac-title">Moje adrese</h1>

                <div class="ac-addresses">
                    <?php foreach ($addresses as $addr): ?>
                    <div class="ac-address">
                        <div class="ac-address__name"><?= htmlspecialchars($addr['first_name'] . ' ' . $addr['last_name']) ?></div>
                        <div class="ac-address__line">
                            <?= htmlspecialchars($addr['address']) ?><br>
                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['postal_code']) ?><br>
                            <?= htmlspecialchars($addr['phone']) ?>
                        </div>
                        <?php if ($addr['is_default']): ?><span class="ac-address__default">Podrazumevana</span><?php endif; ?>
                        <div class="ac-address__actions">
                            <form method="POST" onsubmit="return confirm('Obriši?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                <button type="submit" class="ac-form-btn ac-form-btn--danger">Obriši</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="ac-card">
                    <h3 class="ac-card__title">Dodaj novu adresu</h3>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="save">
                        <div class="ac-form-row">
                            <div class="ac-form-group"><label class="ac-form-label">Ime</label><input type="text" name="first_name" class="ac-form-input" required></div>
                            <div class="ac-form-group"><label class="ac-form-label">Prezime</label><input type="text" name="last_name" class="ac-form-input" required></div>
                        </div>
                        <div class="ac-form-group"><label class="ac-form-label">Telefon</label><input type="tel" name="phone" class="ac-form-input" required></div>
                        <div class="ac-form-group"><label class="ac-form-label">Adresa</label><input type="text" name="address" class="ac-form-input" required></div>
                        <div class="ac-form-row">
                            <div class="ac-form-group"><label class="ac-form-label">Grad</label><input type="text" name="city" class="ac-form-input" required></div>
                            <div class="ac-form-group"><label class="ac-form-label">Poštanski broj</label><input type="text" name="postal_code" class="ac-form-input" required></div>
                        </div>
                        <div class="ac-form-group"><label class="ac-form-label">Država</label><input type="text" name="country" class="ac-form-input" value="Srbija"></div>
                        <div class="ac-form-group"><label class="ac-checkbox-label"><input type="checkbox" name="is_default" value="1"> Podrazumevana</label></div>
                        <button type="submit" class="ac-form-btn">Sačuvaj adresu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
