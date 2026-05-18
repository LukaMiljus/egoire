<?php

declare(strict_types=1);

header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$entries = buildSitemapEntries();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($entries as $entry): ?>
    <url>
        <loc><?= htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= htmlspecialchars($entry['lastmod'], ENT_XML1) ?></lastmod>
        <changefreq><?= htmlspecialchars($entry['changefreq'], ENT_XML1) ?></changefreq>
        <priority><?= htmlspecialchars($entry['priority'], ENT_XML1) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
