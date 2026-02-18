<?php
/* ============================================================
   Egoire – Luxury Contact Page
   View:  src/views/pages/contact.php
   CSS:   public/css/contact.css  (co- namespace)
   ============================================================ */
declare(strict_types=1);

$title = 'Kontakt | Egoire';

/* --- Page-specific assets --- */
$pageStyles = ['/css/contact.css'];

/* --- Form processing --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    requireRateLimit('contact', 3, 600);

    $name    = inputString('name', '', $_POST);
    $email   = sanitizeEmail($_POST['email'] ?? '');
    $phone   = inputString('phone', '', $_POST);
    $subject = inputString('subject', '', $_POST);
    $message = trim($_POST['message'] ?? '');

    $errors = [];
    if (!$name)               $errors[] = 'Ime je obavezno.';
    if (!isValidEmail($email)) $errors[] = 'Neispravan email.';
    if (!$message)            $errors[] = 'Poruka je obavezna.';

    if (empty($errors)) {
        createContactMessage($name, $email, $phone ?: null, $subject ?: null, $message);
        flash('success', 'Vaša poruka je poslata. Odgovorićemo u najkraćem roku.');
        redirect('/contact');
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="co-hero">
    <div class="co-hero__inner">
        <span class="co-hero__label">Podrška</span>
        <h1 class="co-hero__title">Kontaktirajte nas</h1>
        <p class="co-hero__text">Tu smo da odgovorimo na svako vaše pitanje — sa pažnjom, stručnošću i osećajem za detalje koji Egoire čine posebnim.</p>
    </div>
</section>

<!-- ============================================================
     REASONS — Why contact us
     ============================================================ -->
<section class="co-reasons">
    <div class="co-container">
        <div class="co-reasons__header">
            <span class="co-reasons__label">Kako vam možemo pomoći</span>
            <h2 class="co-reasons__title">Vaš razlog, naš prioritet</h2>
        </div>

        <div class="co-reasons__grid">
            <article class="co-reason">
                <div class="co-reason__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <h3 class="co-reason__name">Besplatne konsultacije</h3>
                <p class="co-reason__desc">Personalizovani saveti za negu kose prilagođeni vašem tipu i potrebama.</p>
            </article>

            <article class="co-reason">
                <div class="co-reason__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <h3 class="co-reason__name">Saveti o proizvodima</h3>
                <p class="co-reason__desc">Pomažemo vam da odaberete idealne proizvode iz naše premium kolekcije.</p>
            </article>

            <article class="co-reason">
                <div class="co-reason__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                    </svg>
                </div>
                <h3 class="co-reason__name">Pomoć oko poklona</h3>
                <p class="co-reason__desc">Sastavite savršen Gift Bag za voljenu osobu — mi biramo, vi iznenađujete.</p>
            </article>

            <article class="co-reason">
                <div class="co-reason__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13" rx="2"/><path d="m16 8 5 3-5 3z"/>
                    </svg>
                </div>
                <h3 class="co-reason__name">Pitanja o porudžbini</h3>
                <p class="co-reason__desc">Status isporuke, povraćaj, zamena — sve informacije na jednom mestu.</p>
            </article>

            <article class="co-reason">
                <div class="co-reason__icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="co-reason__name">Poslovna saradnja</h3>
                <p class="co-reason__desc">Zainteresovani ste za partnerstvo ili veleprodaju? Razgovarajmo.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============================================================
     CONTACT SECTION — Info + Form
     ============================================================ -->
<section class="co-main">
    <div class="co-container">
        <div class="co-layout">

            <!-- ── Left: Contact Information ── -->
            <div class="co-info">
                <span class="co-info__label">Kontakt informacije</span>
                <h2 class="co-info__title">Javite nam se</h2>
                <p class="co-info__text">Odgovaramo na sve upite u roku od 24 sata. Za hitna pitanja, pozovite nas telefonom.</p>

                <div class="co-info__items">
                    <div class="co-info__item">
                        <div class="co-info__item-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="m22 6-10 7L2 6"/>
                            </svg>
                        </div>
                        <div>
                            <span class="co-info__item-label">Email</span>
                            <a href="mailto:info@egoire.rs" class="co-info__item-value">info@egoire.rs</a>
                        </div>
                    </div>

                    <div class="co-info__item">
                        <div class="co-info__item-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="co-info__item-label">Telefon</span>
                            <a href="tel:+381641234567" class="co-info__item-value">+381 64 123 4567</a>
                        </div>
                    </div>

                    <div class="co-info__item">
                        <div class="co-info__item-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                            </svg>
                        </div>
                        <div>
                            <span class="co-info__item-label">Radno vreme</span>
                            <span class="co-info__item-value">Pon — Pet: 09:00 – 17:00</span>
                        </div>
                    </div>
                </div>

                <!-- Social links -->
                <div class="co-info__social">
                    <span class="co-info__social-label">Pratite nas</span>
                    <div class="co-info__social-links">
                        <a href="#" class="co-info__social-link" aria-label="Instagram">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="#" class="co-info__social-link" aria-label="Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="co-info__social-link" aria-label="TikTok">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ── Right: Contact Form ── -->
            <div class="co-form-wrapper">
                <form method="POST" class="co-form" novalidate>
                    <?= csrfField() ?>

                    <div class="co-form__row">
                        <div class="co-form__group">
                            <label for="co-name" class="co-form__label">Ime i prezime <span class="co-form__req">*</span></label>
                            <input type="text"
                                   id="co-name"
                                   name="name"
                                   class="co-form__input"
                                   required
                                   autocomplete="name"
                                   placeholder="Vaše ime"
                                   value="<?= htmlspecialchars(inputString('name', '', $_POST)) ?>">
                        </div>
                        <div class="co-form__group">
                            <label for="co-email" class="co-form__label">Email <span class="co-form__req">*</span></label>
                            <input type="email"
                                   id="co-email"
                                   name="email"
                                   class="co-form__input"
                                   required
                                   autocomplete="email"
                                   placeholder="vas@email.com"
                                   value="<?= htmlspecialchars(inputString('email', '', $_POST)) ?>">
                        </div>
                    </div>

                    <div class="co-form__row">
                        <div class="co-form__group">
                            <label for="co-phone" class="co-form__label">Telefon</label>
                            <input type="tel"
                                   id="co-phone"
                                   name="phone"
                                   class="co-form__input"
                                   autocomplete="tel"
                                   placeholder="+381 ..."
                                   value="<?= htmlspecialchars(inputString('phone', '', $_POST)) ?>">
                        </div>
                        <div class="co-form__group">
                            <label for="co-subject" class="co-form__label">Predmet</label>
                            <input type="text"
                                   id="co-subject"
                                   name="subject"
                                   class="co-form__input"
                                   placeholder="Tema vaše poruke"
                                   value="<?= htmlspecialchars(inputString('subject', '', $_POST)) ?>">
                        </div>
                    </div>

                    <div class="co-form__group co-form__group--full">
                        <label for="co-message" class="co-form__label">Poruka <span class="co-form__req">*</span></label>
                        <textarea id="co-message"
                                  name="message"
                                  class="co-form__textarea"
                                  rows="6"
                                  required
                                  placeholder="Kako vam možemo pomoći?"><?= htmlspecialchars(inputString('message', '', $_POST)) ?></textarea>
                    </div>

                    <button type="submit" class="co-form__btn">
                        <span>Pošaljite poruku</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2 11 13"/><path d="M22 2 15 22 11 13 2 9z"/>
                        </svg>
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
