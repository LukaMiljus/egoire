<?php
/* ============================================================
   Egoire – Politika privatnosti
   View:  src/views/pages/privacy.php
   ============================================================ */
declare(strict_types=1);

$title = 'Politika privatnosti | Egoire';
$pageStyles = ['/css/legal.css'];

require __DIR__ . '/../layout/header.php';
?>

<section class="legal-hero">
    <div class="legal-container">
        <span class="legal-hero__label">Pravne informacije</span>
        <h1 class="legal-hero__title">Politika privatnosti</h1>
        <p class="legal-hero__text">Poslednja izmena: <?= date('d.m.Y') ?></p>
    </div>
</section>

<section class="legal-content">
    <div class="legal-container legal-container--narrow">

        <div class="legal-intro">
            <p>Egoire d.o.o. (u daljem tekstu: „mi", „nas", „Egoire") posvećen je zaštiti vaših ličnih podataka. Ova Politika privatnosti objašnjava kako prikupljamo, koristimo, čuvamo i štitimo vaše lične podatke u skladu sa Zakonom o zaštiti podataka o ličnosti Republike Srbije („Sl. glasnik RS", br. 87/2018) i drugim važećim propisima.</p>
        </div>

        <article class="legal-section">
            <h2>1. Ko je rukovalac podataka?</h2>
            <p>Rukovalac vaših ličnih podataka je:</p>
            <ul>
                <li><strong>Egoire d.o.o.</strong></li>
                <li>Adresa: Beograd, Republika Srbija</li>
                <li>Email: <a href="mailto:info@egoire.rs">info@egoire.rs</a></li>
                <li>Telefon: +381 64 123 4567</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>2. Koje podatke prikupljamo?</h2>
            <p>U zavisnosti od načina interakcije sa našim sajtom, možemo prikupljati sledeće kategorije ličnih podataka:</p>

            <h3>2.1. Podaci koje nam direktno dostavljate</h3>
            <ul>
                <li>Ime i prezime</li>
                <li>Email adresa</li>
                <li>Broj telefona</li>
                <li>Adresa za dostavu (ulica, grad, poštanski broj)</li>
                <li>Podaci o plaćanju (ne čuvamo podatke o platnoj kartici — plaćanje se obrađuje putem pouzdanih platnih procesora ili pouzećem)</li>
            </ul>

            <h3>2.2. Podaci koji se automatski prikupljaju</h3>
            <ul>
                <li>IP adresa</li>
                <li>Tip pretraživača i operativnog sistema</li>
                <li>Kolačići (cookies) i slični identifikatori</li>
                <li>Podaci o pregledu stranica i interakciji sa sajtom</li>
                <li>Informacije o sesiji (za funkcionisanje korpe i korisničkog naloga)</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>3. Svrha i pravni osnov obrade</h2>
            <p>Vaše podatke obrađujemo u sledeće svrhe:</p>
            <table class="legal-table">
                <thead>
                    <tr>
                        <th>Svrha</th>
                        <th>Pravni osnov</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Obrada i isporuka narudžbina</td>
                        <td>Izvršenje ugovora</td>
                    </tr>
                    <tr>
                        <td>Kreiranje i upravljanje korisničkim nalogom</td>
                        <td>Izvršenje ugovora / Pristanak</td>
                    </tr>
                    <tr>
                        <td>Komunikacija u vezi sa narudžbinom</td>
                        <td>Izvršenje ugovora</td>
                    </tr>
                    <tr>
                        <td>Slanje promotivnih poruka i newsletter-a</td>
                        <td>Pristanak korisnika</td>
                    </tr>
                    <tr>
                        <td>Analitika i unapređenje sajta</td>
                        <td>Legitimni interes</td>
                    </tr>
                    <tr>
                        <td>Prevencija prevara i zloupotreba</td>
                        <td>Legitimni interes</td>
                    </tr>
                    <tr>
                        <td>Ispunjenje zakonskih obaveza</td>
                        <td>Zakonska obaveza</td>
                    </tr>
                </tbody>
            </table>
        </article>

        <article class="legal-section">
            <h2>4. Kolačići (Cookies)</h2>
            <p>Naš sajt koristi kolačiće kako bi obezbedio pravilno funkcionisanje, zapamtio vaše preference i poboljšao korisničko iskustvo.</p>
            <h3>4.1. Neophodni kolačići</h3>
            <p>Ovi kolačići su neophodni za rad sajta (sesija, korpa, CSRF zaštita). Ne zahtevaju pristanak jer su tehnički neophodni.</p>
            <h3>4.2. Analitički kolačići</h3>
            <p>Koriste se za merenje posećenosti i analizu ponašanja korisnika. Aktiviraju se samo uz vaš pristanak.</p>
            <p>Možete upravljati kolačićima putem podešavanja vašeg pretraživača. Napominjemo da onemogućavanje kolačića može uticati na funkcionalnost sajta.</p>
        </article>

        <article class="legal-section">
            <h2>5. Deljenje podataka sa trećim licima</h2>
            <p>Vaše podatke delimo isključivo sa:</p>
            <ul>
                <li><strong>Kurirskim službama</strong> — radi isporuke narudžbina</li>
                <li><strong>Platnim procesorima</strong> — radi obrade plaćanja</li>
                <li><strong>Pružaocima IT usluga</strong> — hosting i tehničko održavanje sajta</li>
                <li><strong>Državnim organima</strong> — kada to zahteva zakon</li>
            </ul>
            <p>Ne prodajemo, ne iznajmljujemo i ne delimo vaše lične podatke sa trećim licima u marketinške svrhe bez vaše izričite saglasnosti.</p>
        </article>

        <article class="legal-section">
            <h2>6. Rok čuvanja podataka</h2>
            <p>Vaše podatke čuvamo onoliko dugo koliko je potrebno za ispunjenje svrhe za koju su prikupljeni:</p>
            <ul>
                <li><strong>Podaci o narudžbinama:</strong> 5 godina od datuma narudžbine (u skladu sa računovodstvenim propisima)</li>
                <li><strong>Korisnički nalog:</strong> dok nalog nije obrisan ili deaktiviran</li>
                <li><strong>Newsletter pretplata:</strong> do odjave korisnika</li>
                <li><strong>Kontakt poruke:</strong> do 2 godine od prijema</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>7. Vaša prava</h2>
            <p>U skladu sa Zakonom o zaštiti podataka o ličnosti, imate pravo da:</p>
            <ul>
                <li>Zatražite pristup vašim ličnim podacima</li>
                <li>Zahtevate ispravku netačnih podataka</li>
                <li>Zahtevate brisanje podataka („pravo na zaborav")</li>
                <li>Ograničite obradu vaših podataka</li>
                <li>Povučete saglasnost u bilo kom trenutku</li>
                <li>Podnesete prigovor Povereniku za informacije od javnog značaja i zaštitu podataka o ličnosti</li>
            </ul>
            <p>Za ostvarivanje bilo kog prava, kontaktirajte nas na <a href="mailto:info@egoire.rs">info@egoire.rs</a>.</p>
        </article>

        <article class="legal-section">
            <h2>8. Bezbednost podataka</h2>
            <p>Primenjujemo odgovarajuće tehničke i organizacione mere zaštite podataka, uključujući:</p>
            <ul>
                <li>SSL/TLS enkripciju komunikacije</li>
                <li>Šifrovanje lozinki (bcrypt)</li>
                <li>Ograničen pristup ličnim podacima</li>
                <li>Redovno ažuriranje softvera i bezbednosne zakrpe</li>
                <li>CSRF zaštitu formulara</li>
            </ul>
            <p>Uprkos svim preduzetim merama, nijedan sistem prenosa podataka niti elektronskog skladištenja nije 100% siguran. Preduzimamo sve razumne korake da zaštitimo vaše podatke.</p>
        </article>

        <article class="legal-section">
            <h2>9. Maloletni korisnici</h2>
            <p>Naš sajt i usluge namenjeni su osobama starijim od 18 godina. Svesno ne prikupljamo podatke maloletnih lica. Ukoliko saznamo da smo prikupili podatke maloletnog lica, odmah ćemo ih obrisati.</p>
        </article>

        <article class="legal-section">
            <h2>10. Izmene Politike privatnosti</h2>
            <p>Zadržavamo pravo da ovu Politiku privatnosti izmenimo u bilo kom trenutku. O svim značajnim izmenama bićete obavešteni putem sajta ili email-a. Nastavak korišćenja sajta nakon objave izmena smatra se prihvatanjem novih uslova.</p>
        </article>

        <article class="legal-section">
            <h2>11. Kontakt</h2>
            <p>Za sva pitanja u vezi sa zaštitom ličnih podataka, možete nas kontaktirati:</p>
            <ul>
                <li>Email: <a href="mailto:info@egoire.rs">info@egoire.rs</a></li>
                <li>Telefon: +381 64 123 4567</li>
                <li>Poštom: Egoire d.o.o., Beograd, Republika Srbija</li>
            </ul>
        </article>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
