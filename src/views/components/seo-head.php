<?php

declare(strict_types=1);

$cfg = seoConfig();
$seoTitle = $title ?? ($cfg['site_name'] . ' – Luxury Hair Care');
$seoDescription = $metaDescription ?? $cfg['site_description'];
$seoCanonical = canonicalUrl();
$seoOgImage = !empty($ogImage) ? $ogImage : seoDefaultOgImage();
$seoNoIndex = !empty($seoNoIndex);
$seoRobots = seoRobotsMeta($seoNoIndex);

$jsonLdGraphs = [seoJsonLdOrganization(), seoJsonLdWebSite()];
if (!empty($jsonLdExtra) && is_array($jsonLdExtra)) {
    $jsonLdGraphs = array_merge($jsonLdGraphs, $jsonLdExtra);
}
?>
    <meta name="robots" content="<?= htmlspecialchars($seoRobots) ?>">
    <meta name="googlebot" content="<?= htmlspecialchars($seoRobots) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($seoCanonical) ?>">
    <link rel="alternate" hreflang="<?= htmlspecialchars($cfg['language']) ?>" href="<?= htmlspecialchars($seoCanonical) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($seoCanonical) ?>">

    <meta property="og:locale" content="<?= htmlspecialchars($cfg['locale']) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType ?? 'website') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seoCanonical) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($cfg['site_name']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seoOgImage) ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seoOgImage) ?>">
<?php if (!empty($cfg['twitter_handle'])): ?>
    <meta name="twitter:site" content="@<?= htmlspecialchars(ltrim($cfg['twitter_handle'], '@')) ?>">
<?php endif; ?>

<?php if (!empty($cfg['google_site_verification'])): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($cfg['google_site_verification']) ?>">
<?php endif; ?>
<?php if (!empty($cfg['bing_site_verification'])): ?>
    <meta name="msvalidate.01" content="<?= htmlspecialchars($cfg['bing_site_verification']) ?>">
<?php endif; ?>

<?= renderJsonLd($jsonLdGraphs) ?>
