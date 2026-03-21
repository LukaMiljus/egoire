<?php
/* ============================================================
   Egoire – Isporuka i povraćaj
   View:  src/views/pages/shipping-returns.php
   ============================================================ */
declare(strict_types=1);

$title = 'Isporuka i povraćaj | Egoire';
$pageStyles = ['/css/legal.css'];

require __DIR__ . '/../layout/header.php';
?>

<section class="legal-hero">
    <div class="legal-container">
        <span class="legal-hero__label">Informacije za kupce</span>
        <h1 class="legal-hero__title">Isporuka i povraćaj</h1>
        <p class="legal-hero__text">Poslednja izmena: <?= date('d.m.Y') ?></p>
    </div>
</section>

<section class="legal-content">
    <div class="legal-container legal-container--narrow">

        <div class="legal-intro">
            <p>Na ovoj stranici možete pronaći sve informacije o načinima isporuke, troškovima dostave, pravilima za povraćaj i zamenu proizvoda. Vaše zadovoljstvo nam je prioritet — ukoliko imate pitanja, slobodno nas kontaktirajte.</p>
        </div>

        <!-- ============ ISPORUKA ============ -->

        <article class="legal-section">
            <h2>1. Zona isporuke</h2>
            <p>Trenutno vršimo isporuku na celoj teritoriji Republike Srbije putem kurirske službe. Za eventualne isporuke van teritorije Srbije, kontaktirajte nas pre naručivanja radi provere mogućnosti i troškova.</p>
        </article>

        <article class="legal-section">
            <h2>2. Troškovi dostave</h2>
            <table class="legal-table">
                <thead>
                    <tr>
                        <th>Vrednost narudžbine</th>
                        <th>Trošak dostave</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Do 6.000 RSD</td>
                        <td>500 RSD</td>
                    </tr>
                    <tr>
                        <td>Preko 6.000 RSD</td>
                        <td><strong>Besplatna dostava</strong></td>
                    </tr>
                </tbody>
            </table>
            <p>Troškovi dostave se automatski obračunavaju u korpi i jasno su prikazani pre finalizacije narudžbine.</p>
        </article>

        <article class="legal-section">
            <h2>3. Rok isporuke</h2>
            <ul>
                <li><strong>Obrada narudžbine:</strong> 1–2 radna dana od potvrde narudžbine</li>
                <li><strong>Dostava kurirskom službom:</strong> 1–3 radna dana od predaje pošiljke kuriru</li>
                <li><strong>Ukupan očekivani rok:</strong> 2–5 radnih dana</li>
            </ul>
            <p>U periodima pojačane potražnje (praznici, promotivne akcije), rokovi isporuke mogu biti neznatno produženi. O eventualnim kašnjenjima ćemo vas blagovremeno obavestiti.</p>
        </article>

        <article class="legal-section">
            <h2>4. Preuzimanje pošiljke</h2>
            <ul>
                <li>Kurir će vas kontaktirati pre dostave radi dogovora oko tačnog vremena.</li>
                <li>Prilikom preuzimanja, pregledajte spoljašnje pakovanje. Ukoliko je oštećeno, imate pravo da odbijete prijem pošiljke.</li>
                <li>Potpisom na dostavnici potvrđujete prijem pošiljke u neoštećenom pakovanju.</li>
                <li>Ukoliko niste dostupni za preuzimanje, kurir će pokušati ponovnu dostavu. Nakon dva neuspešna pokušaja, pošiljka se vraća u naš magacin.</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>5. Plaćanje prilikom preuzimanja</h2>
            <p>Trenutno je dostupno plaćanje pouzećem — plaćanje kuriru gotovinom prilikom preuzimanja pošiljke. Tačan iznos biće naveden u email potvrdi narudžbine.</p>
        </article>

        <!-- ============ POVRAĆAJ I ZAMENA ============ -->

        <article class="legal-section">
            <h2>6. Pravo na odustanak od ugovora</h2>
            <p>U skladu sa Zakonom o zaštiti potrošača Republike Srbije, imate pravo da odustanete od ugovora u roku od <strong>14 dana</strong> od dana prijema pošiljke, bez navođenja razloga.</p>
            <p>Da biste ostvarili pravo na odustanak, potrebno je da nas obavestite pisanim putem (email na info@egoire.rs) o svojoj odluci da odustanete od ugovora. Možete koristiti sledeći obrazac, ali to nije obavezno:</p>
            <blockquote class="legal-blockquote">
                „Obaveštavam Vas da odustajem od ugovora o kupovini sledećih proizvoda: [navesti proizvode]. Naručeno dana [datum narudžbine], primljeno dana [datum prijema]. Ime i prezime: [vaše ime]. Adresa: [vaša adresa]. Potpis (ako se dostavlja u papirnoj formi). Datum."
            </blockquote>
        </article>

        <article class="legal-section">
            <h2>7. Uslovi za povraćaj</h2>
            <p>Da bi povraćaj bio prihvaćen, moraju biti ispunjeni sledeći uslovi:</p>
            <ul>
                <li>Proizvod mora biti <strong>neotpakovan i nekorišćen</strong>, u originalnom pakovanju</li>
                <li>Ne sme biti narušen zaštitni pečat ili folija na proizvodu</li>
                <li>Povraćaj mora biti prijavljen u roku od 14 dana od prijema pošiljke</li>
                <li>Proizvod mora biti vraćen u roku od 14 dana od slanja obaveštenja o odustanku</li>
            </ul>
            <p><strong>Napomena:</strong> Zbog prirode kozmetičkih proizvoda i higijenskih razloga, ne možemo prihvatiti povraćaj otvorenih ili korišćenih proizvoda, osim u slučaju reklamacije na kvalitet ili oštećenje.</p>
        </article>

        <article class="legal-section">
            <h2>8. Troškovi povraćaja</h2>
            <ul>
                <li>U slučaju odustanka od ugovora, troškove vraćanja pošiljke snosi kupac.</li>
                <li>Ukoliko je proizvod isporučen oštećen ili se razlikuje od naručenog, Egoire snosi troškove povraćaja.</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>9. Povraćaj novca (refundacija)</h2>
            <ul>
                <li>Po prijemu i proveri vraćenog proizvoda, refundacija se vrši u roku od <strong>14 dana</strong>.</li>
                <li>Novac se vraća istim putem kojim je izvršeno plaćanje, osim ako se drugačije ne dogovorimo.</li>
                <li>U slučaju plaćanja pouzećem, refundacija se vrši na tekući račun kupca.</li>
                <li>Refundaciji podležu cena proizvoda i inicijalni trošak standardne dostave (ne i eventualni dodatni troškovi za ubrzanu dostavu).</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>10. Reklamacije — oštećeni ili neispravni proizvodi</h2>
            <p>Ukoliko primite oštećen, neispravan ili pogrešan proizvod:</p>
            <ol>
                <li>Kontaktirajte nas u roku od 48 sati od prijema na <a href="mailto:info@egoire.rs">info@egoire.rs</a></li>
                <li>Pošaljite fotografiju oštećenja ili problema</li>
                <li>Sačuvajte proizvod i pakovanje do rešavanja reklamacije</li>
            </ol>
            <p>Egoire se obavezuje da reši reklamaciju u zakonskom roku od 8 dana od prijema. Rešenje može uključivati zamenu proizvoda, povraćaj novca ili popust na sledeću kupovinu, u dogovoru sa kupcem.</p>
        </article>

        <article class="legal-section">
            <h2>11. Zamena proizvoda</h2>
            <p>Zamena proizvoda je moguća pod istim uslovima kao i povraćaj (neotpakovan, nekorišćen proizvod, rok od 14 dana). Kontaktirajte nas na info@egoire.rs radi dogovora o zameni.</p>
        </article>

        <article class="legal-section">
            <h2>12. Kontakt za pitanja o isporuci i povraćaju</h2>
            <p>Za sva pitanja ili nedoumice u vezi sa isporukom, povraćajem i zamenom proizvoda:</p>
            <ul>
                <li>Email: <a href="mailto:info@egoire.rs">info@egoire.rs</a></li>
                <li>Telefon: +381 64 123 4567</li>
            </ul>
        </article>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
