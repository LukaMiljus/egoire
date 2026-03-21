<?php
/* ============================================================
   Egoire – Uslovi korišćenja
   View:  src/views/pages/terms.php
   ============================================================ */
declare(strict_types=1);

$title = 'Uslovi korišćenja | Egoire';
$pageStyles = ['/css/legal.css'];

require __DIR__ . '/../layout/header.php';
?>

<section class="legal-hero">
    <div class="legal-container">
        <span class="legal-hero__label">Pravne informacije</span>
        <h1 class="legal-hero__title">Uslovi korišćenja</h1>
        <p class="legal-hero__text">Poslednja izmena: <?= date('d.m.Y') ?></p>
    </div>
</section>

<section class="legal-content">
    <div class="legal-container legal-container--narrow">

        <div class="legal-intro">
            <p>Molimo vas da pažljivo pročitate ove Uslove korišćenja pre nego što pristupite ili koristite internet prodavnicu Egoire (www.egoire.rs). Korišćenjem sajta, potvrđujete da ste pročitali, razumeli i prihvatili ove uslove u celosti. Ukoliko se ne slažete sa bilo kojim delom ovih uslova, molimo vas da ne koristite naš sajt.</p>
        </div>

        <article class="legal-section">
            <h2>1. Opšte odredbe</h2>
            <p>Ovi Uslovi korišćenja regulišu odnos između Egoire d.o.o. (u daljem tekstu: „Prodavac", „mi", „Egoire") i korisnika internet prodavnice (u daljem tekstu: „Kupac", „Korisnik", „vi").</p>
            <p>Egoire zadržava pravo da u bilo kom trenutku izmeni ove Uslove korišćenja bez prethodne najave. Izmene stupaju na snagu danom objave na sajtu. Korisnici su dužni da redovno proveravaju aktuelne uslove.</p>
        </article>

        <article class="legal-section">
            <h2>2. Registracija i korisnički nalog</h2>
            <ul>
                <li>Registracija nije obavezna za kupovinu, ali omogućava pristup dodatnim funkcijama (praćenje narudžbina, loyalty program, čuvanje adresa).</li>
                <li>Korisnik je odgovoran za tačnost podataka unetih prilikom registracije.</li>
                <li>Korisnik je dužan da čuva poverljivost pristupnih podataka (lozinka).</li>
                <li>Egoire zadržava pravo da suspenduje ili obriše korisnički nalog u slučaju kršenja ovih uslova ili sumnje na zloupotrebu.</li>
                <li>Korišćenje sajta dozvoljeno je isključivo osobama starijim od 18 godina.</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>3. Proizvodi i cene</h2>
            <ul>
                <li>Svi proizvodi na sajtu su kozmetički proizvodi namenjeni spoljašnjoj upotrebi.</li>
                <li>Cene su izražene u srpskim dinarima (RSD) i uključuju PDV.</li>
                <li>Egoire zadržava pravo promene cena bez prethodne najave. Cena koja važi u trenutku narudžbine je konačna za tu narudžbinu.</li>
                <li>Fotografije proizvoda su ilustrativnog karaktera. Stvarni izgled pakovanja može se neznatno razlikovati.</li>
                <li>Ukoliko je cena pogrešno prikazana usled tehničke greške, Egoire zadržava pravo da poništi narudžbinu i o tome obavesti kupca.</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>4. Narudžbina i ugovor o kupoprodaji</h2>
            <p>Narudžbina se smatra prihvaćenom tek nakon što Egoire pošalje potvrdu putem email-a. Do momenta slanja potvrde, Egoire zadržava pravo da odbije narudžbinu iz bilo kog razloga, uključujući ali ne ograničavajući se na:</p>
            <ul>
                <li>Nedostupnost proizvoda</li>
                <li>Greške u ceni ili opisu proizvoda</li>
                <li>Nemogućnost verifikacije podataka kupca</li>
                <li>Sumnja na prevaru ili zloupotreba</li>
            </ul>
            <p>Ugovor o kupoprodaji smatra se zaključenim u trenutku slanja potvrde narudžbine.</p>
        </article>

        <article class="legal-section">
            <h2>5. Plaćanje</h2>
            <p>Dostupni načini plaćanja:</p>
            <ul>
                <li><strong>Plaćanje pouzećem</strong> — plaćanje kuriru prilikom preuzimanja pošiljke</li>
                <li>Drugi načini plaćanja mogu biti ponuđeni i biće naznačeni na stranici za plaćanje</li>
            </ul>
            <p>Egoire ne čuva podatke o platnim karticama kupaca. Svi platni podaci se obrađuju putem sigurnih platnih procesora.</p>
        </article>

        <article class="legal-section">
            <h2>6. Poklon pakovanje (Gift Bag)</h2>
            <ul>
                <li>Kupci mogu izabrati opciju poklon pakovanja prilikom narudžbine.</li>
                <li>Jedan Gift Bag može sadržati maksimalno 4 proizvoda.</li>
                <li>Cena poklon pakovanja se dodaje na ukupan iznos narudžbine.</li>
                <li>Gift Bag pakovanje je dekorativnog karaktera i ne utiče na kvalitet ili zaštitu proizvoda tokom transporta.</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>7. Gift Card (poklon kartica)</h2>
            <ul>
                <li>Gift Card se može koristiti za plaćanje narudžbina na sajtu.</li>
                <li>Gift Card se ne može zameniti za gotovinu.</li>
                <li>Gift Card ima ograničen rok važenja (naznačen na kartici).</li>
                <li>Egoire ne snosi odgovornost za izgubljen ili ukraden Gift Card kod.</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>8. Odgovornost za korišćenje proizvoda</h2>
            <p><strong>Korisnik je u potpunosti odgovoran za korišćenje kupljenih proizvoda.</strong></p>
            <ul>
                <li>Pre prve upotrebe kozmetičkog proizvoda, preporučujemo da izvršite alergijski test na manjem delu kože.</li>
                <li>Egoire ne snosi odgovornost za alergijske reakcije, iritacije ili bilo kakve neželjene efekte prouzrokovane korišćenjem proizvoda.</li>
                <li>Proizvode koristite u skladu sa uputstvima navedenim na ambalaži.</li>
                <li>U slučaju iritacije ili neželjene reakcije, odmah prekinite korišćenje i obratite se lekaru.</li>
                <li>Egoire ne garantuje specifične rezultate primene proizvoda jer oni zavise od individualnih karakteristika korisnika.</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>9. Ograničenje odgovornosti</h2>
            <p>Egoire ne snosi odgovornost u sledećim slučajevima:</p>
            <ul>
                <li>Nepravilno korišćenje proizvoda suprotno uputstvima proizvođača</li>
                <li>Alergijske reakcije ili preosetljivost na pojedine sastojke</li>
                <li>Šteta nastala usled više sile (prirodne katastrofe, pandemiji, ratni sukobi)</li>
                <li>Kvarovi i prekidi u radu sajta izazvani tehničkim problemima</li>
                <li>Neautorizovani pristup korisničkom nalogu usled nepažnje korisnika</li>
                <li>Kašnjenja u isporuci izazvana od strane kurirske službe</li>
            </ul>
            <p>U svakom slučaju, ukupna odgovornost Egoire-a ne može premašiti iznos koji je korisnik platio za konkretnu narudžbinu.</p>
        </article>

        <article class="legal-section">
            <h2>10. Intelektualna svojina</h2>
            <p>Sav sadržaj na sajtu (tekstovi, fotografije, logotipi, grafički elementi, softver) je vlasništvo Egoire-a ili trećih lica čija je upotreba odobrena. Zabranjeno je:</p>
            <ul>
                <li>Kopiranje, reprodukovanje ili distribucija sadržaja bez pisane dozvole</li>
                <li>Korišćenje sadržaja u komercijalne svrhe</li>
                <li>Modifikacija ili izrada derivata sadržaja sajta</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>11. Zabranjena ponašanja</h2>
            <p>Korisnicima je zabranjeno:</p>
            <ul>
                <li>Korišćenje sajta u nezakonite svrhe</li>
                <li>Pokušaji neovlašćenog pristupa sistemu</li>
                <li>Unošenje lažnih podataka prilikom narudžbine</li>
                <li>Zloupotreba promotivnih kodova ili Gift Card sistema</li>
                <li>Automatsko preuzimanje podataka (scraping)</li>
            </ul>
        </article>

        <article class="legal-section">
            <h2>12. Merodavno pravo i rešavanje sporova</h2>
            <p>Na ove Uslove korišćenja primenjuje se pravo Republike Srbije. U slučaju spora, strane će pokušati da ga reše sporazumno. Ukoliko to nije moguće, nadležan je sud u Beogradu, Republika Srbija.</p>
        </article>

        <article class="legal-section">
            <h2>13. Kontakt</h2>
            <p>Za sva pitanja u vezi sa ovim Uslovima korišćenja, kontaktirajte nas:</p>
            <ul>
                <li>Email: <a href="mailto:info@egoire.rs">info@egoire.rs</a></li>
                <li>Telefon: +381 64 123 4567</li>
            </ul>
        </article>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
