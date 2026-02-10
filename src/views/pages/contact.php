<?php
declare(strict_types=1);
$title = 'Kontakt | Egoire';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('contact', 3, 600);
    $name = inputString('name', '', $_POST);
    $email = sanitizeEmail($_POST['email'] ?? '');
    $subject = inputString('subject', '', $_POST);
    $message = trim($_POST['message'] ?? '');

    $errors = [];
    if (!$name) $errors[] = 'Ime je obavezno.';
    if (!isValidEmail($email)) $errors[] = 'Neispravan email.';
    if (!$message) $errors[] = 'Poruka je obavezna.';

    if (empty($errors)) {
        createContactMessage($name, $email, $subject, $message);
        flash('success', 'Vaša poruka je poslata. Odgovorićemo u najkraćem roku.');
        redirect('/contact');
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero"><div class="container"><h1>Kontakt</h1></div></section>

<section class="section">
    <div class="container">
        <div class="contact-layout">
            <div class="contact-info">
                <h2>Javite nam se</h2>
                <p>Imate pitanje? Tu smo za vas.</p>
                <div class="contact-details">
                    <div class="contact-item">
                        <strong>Email</strong>
                        <a href="mailto:info@egoire.rs">info@egoire.rs</a>
                    </div>
                    <div class="contact-item">
                        <strong>Telefon</strong>
                        <a href="tel:+381641234567">+381 64 123 4567</a>
                    </div>
                    <div class="contact-item">
                        <strong>Radno vreme</strong>
                        <span>Pon - Pet: 09:00 - 17:00</span>
                    </div>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label>Ime *</label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars(inputString('name', '', $_POST)) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars(inputString('email', '', $_POST)) ?>">
                    </div>
                    <div class="form-group">
                        <label>Predmet</label>
                        <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars(inputString('subject', '', $_POST)) ?>">
                    </div>
                    <div class="form-group">
                        <label>Poruka *</label>
                        <textarea name="message" rows="6" class="form-control" required><?= htmlspecialchars(inputString('message', '', $_POST)) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Pošalji poruku</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
