<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= htmlspecialchars($title ?? 'Admin') ?> – Egoire Admin</title>
    <link rel="stylesheet" href="<?= asset('/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= asset('/css/admin-responsive.css') ?>">
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<div class="admin-wrapper">

    <!-- Sidebar Overlay (mobile) -->
    <div class="admin-sidebar-overlay"></div>

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <a href="/admin/dashboard" class="admin-logo">
                <img src="/images/logos/egoire-logo.png" alt="Egoire" style="max-height:32px;filter:brightness(0) invert(1);">
            </a>
            <div class="admin-logo-sub">Admin Panel</div>
        </div>

        <nav class="admin-nav">
            <div class="nav-label">Glavno</div>
            <a href="/admin/dashboard">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Početna
            </a>
            <a href="/admin/orders">
                <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Porudžbine
            </a>
            <a href="/admin/analytics">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Analitika
            </a>

            <div class="nav-divider"></div>
            <div class="nav-label">Katalog</div>
            <a href="/admin/products">
                <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                Artikli
            </a>
            <a href="/admin/inventory">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                Stanje
            </a>
            <a href="/admin/categories">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Kategorije
            </a>
            <a href="/admin/brands">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                Brendovi
            </a>

            <div class="nav-divider"></div>
            <div class="nav-label">Pokloni</div>
            <a href="/admin/gift-bag">
                <svg viewBox="0 0 24 24"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
                Gift Bag
            </a>
            <a href="/admin/gift-wrapping">
                <svg viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="14" rx="2"/><path d="M12 8v14"/><path d="M3 15h18"/><path d="M7.5 8a2.5 2.5 0 010-5L12 8"/><path d="M16.5 8a2.5 2.5 0 000-5L12 8"/></svg>
                Poklon pakovanja
            </a>
            <a href="/admin/loyalty">
                <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
                Loyalty
            </a>

            <div class="nav-divider"></div>
            <div class="nav-label">Korisnici</div>
            <a href="/admin/users">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                Korisnici
            </a>
            <a href="/admin/contacts">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Poruke
            </a>

            <div class="nav-divider"></div>
            <div class="nav-label">Sadržaj</div>
            <a href="/admin/blog">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Blog
            </a>
            <!-- <a href="/admin/pages">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Stranice
            </a> -->
            <a href="/admin/marketing">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Email Marketing
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="/admin/logout">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Izloguj se
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">

        <!-- Top Bar -->
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="admin-sidebar-toggle" aria-label="Toggle menu">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1><?= htmlspecialchars($title ?? 'Admin') ?></h1>
            </div>
            <div class="admin-topbar-actions">
                <a href="/" target="_blank" class="btn btn-sm btn-secondary">Pogledaj sajt</a>
            </div>
        </div>

        <?= renderFlash() ?>

        <div class="admin-content">