<?php
$currentPath = currentPath();
?>
<aside class="account-sidebar">
    <nav class="account-nav">
        <a href="/account" class="<?= $currentPath === '/account' ? 'active' : '' ?>">Dashboard</a>
        <a href="/account/orders" class="<?= isActivePath('/account/orders') ? 'active' : '' ?>">Porudžbine</a>
        <a href="/account/addresses" class="<?= isActivePath('/account/addresses') ? 'active' : '' ?>">Adrese</a>
        <a href="/account/loyalty" class="<?= isActivePath('/account/loyalty') ? 'active' : '' ?>">Loyalty</a>
        <a href="/account/settings" class="<?= isActivePath('/account/settings') ? 'active' : '' ?>">Podešavanja</a>
        <a href="/logout" class="logout-link">Odjavi se</a>
    </nav>
</aside>
