<?php
declare(strict_types=1);
requireUser();
$user = currentUser();
$title = 'Adrese | Egoire';
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

<section class="section">
    <div class="container">
        <div class="account-layout">
            <?php require __DIR__ . '/account-sidebar.php'; ?>

            <div class="account-content">
                <h1>Moje adrese</h1>

                <div class="address-grid">
                    <?php foreach ($addresses as $addr): ?>
                    <div class="address-card">
                        <p><strong><?= htmlspecialchars($addr['first_name'] . ' ' . $addr['last_name']) ?></strong></p>
                        <p><?= htmlspecialchars($addr['address']) ?></p>
                        <p><?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['postal_code']) ?></p>
                        <p><?= htmlspecialchars($addr['phone']) ?></p>
                        <?php if ($addr['is_default']): ?><span class="badge badge-info">Podrazumevana</span><?php endif; ?>
                        <div class="mt-2">
                            <form method="POST" class="inline-form" onsubmit="return confirm('Obriši?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Obriši</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card mt-4">
                    <h3>Dodaj novu adresu</h3>
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="save">
                        <div class="form-row">
                            <div class="form-group"><label>Ime</label><input type="text" name="first_name" class="form-control" required></div>
                            <div class="form-group"><label>Prezime</label><input type="text" name="last_name" class="form-control" required></div>
                        </div>
                        <div class="form-group"><label>Telefon</label><input type="tel" name="phone" class="form-control" required></div>
                        <div class="form-group"><label>Adresa</label><input type="text" name="address" class="form-control" required></div>
                        <div class="form-row">
                            <div class="form-group"><label>Grad</label><input type="text" name="city" class="form-control" required></div>
                            <div class="form-group"><label>Poštanski broj</label><input type="text" name="postal_code" class="form-control" required></div>
                        </div>
                        <div class="form-group"><label>Država</label><input type="text" name="country" class="form-control" value="Srbija"></div>
                        <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_default" value="1"> Podrazumevana</label></div>
                        <button type="submit" class="btn btn-primary">Sačuvaj adresu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
