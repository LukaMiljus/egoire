<?php
declare(strict_types=1);
$token = inputString('token');
if ($token) {
    marketingUnsubscribeByToken($token);
    $title = 'Odjava | Egoire';
    require __DIR__ . '/../layout/header.php';
    echo '<section class="section"><div class="container"><div class="empty-state"><h1>Uspešno ste se odjavili</h1><p>Nećete više primati naše email poruke.</p><a href="/" class="btn btn-primary">Nazad na početnu</a></div></div></section>';
    require __DIR__ . '/../layout/footer.php';
} else {
    redirect('/');
}
