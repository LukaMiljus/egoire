<?php

declare(strict_types=1);

if (!function_exists('seoConfig')) {
    function seoConfig(): array
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../../config/seo.php';
        }
        return $config;
    }
}

if (!function_exists('siteUrl')) {
    /** Kanonski domen (produkcija) – postaviti SITE_URL u .env */
    function siteUrl(): string
    {
        static $url = null;
        if ($url !== null) {
            return $url;
        }

        $fromEnv = getenv('SITE_URL');
        if (is_string($fromEnv) && $fromEnv !== '' && filter_var($fromEnv, FILTER_VALIDATE_URL)) {
            $url = rtrim($fromEnv, '/');
            return $url;
        }

        $url = baseUrl();
        return $url;
    }
}

if (!function_exists('absoluteUrl')) {
    function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return siteUrl() . '/' . ltrim($path, '/');
    }
}

if (!function_exists('canonicalUrl')) {
    function canonicalUrl(?string $path = null): string
    {
        if ($path !== null) {
            return absoluteUrl($path);
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $pathOnly = parse_url($uri, PHP_URL_PATH) ?: '/';
        $pathOnly = $pathOnly === '' ? '/' : $pathOnly;

        return absoluteUrl($pathOnly);
    }
}

if (!function_exists('isSeoIndexable')) {
    /** Da li sajt treba da bude indeksiran (van maintenance režima). */
    function isSeoIndexable(): bool
    {
        return !isMaintenanceMode();
    }
}

if (!function_exists('isPrivateFrontendPath')) {
    function isPrivateFrontendPath(): bool
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        foreach (seoConfig()['disallow_paths'] as $disallow) {
            $prefix = rtrim($disallow, '/');
            if ($prefix === '') {
                continue;
            }
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('seoRobotsMeta')) {
    function seoRobotsMeta(bool $forceNoIndex = false): string
    {
        if ($forceNoIndex || !isSeoIndexable() || isPrivateFrontendPath()) {
            return 'noindex, nofollow';
        }

        return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    }
}

if (!function_exists('seoDefaultOgImage')) {
    function seoDefaultOgImage(): string
    {
        return absoluteUrl(seoConfig()['default_og_image'] ?? '/images/logos/egoire-logo.png');
    }
}

if (!function_exists('formatSitemapLastmod')) {
    function formatSitemapLastmod(?string $datetime): string
    {
        if (!$datetime) {
            return date('Y-m-d');
        }
        $ts = strtotime($datetime);
        return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
    }
}

if (!function_exists('buildSitemapEntries')) {
    /**
     * @return list<array{loc: string, lastmod: string, changefreq: string, priority: string}>
     */
    function buildSitemapEntries(): array
    {
        if (!isSeoIndexable()) {
            return [];
        }

        $entries = [];

        foreach (seoConfig()['static_paths'] as $page) {
            $entries[] = [
                'loc'        => absoluteUrl($page['path']),
                'lastmod'    => date('Y-m-d'),
                'changefreq' => $page['changefreq'],
                'priority'   => $page['priority'],
            ];
        }

        try {
            $products = db()->query(
                'SELECT slug, updated_at FROM products WHERE is_active = 1 ORDER BY updated_at DESC'
            )->fetchAll();

            foreach ($products as $row) {
                $entries[] = [
                    'loc'        => absoluteUrl('/product/' . $row['slug']),
                    'lastmod'    => formatSitemapLastmod($row['updated_at'] ?? null),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }

            $categories = db()->query(
                'SELECT slug, updated_at FROM categories WHERE is_active = 1 ORDER BY updated_at DESC'
            )->fetchAll();

            foreach ($categories as $row) {
                $entries[] = [
                    'loc'        => absoluteUrl('/category/' . $row['slug']),
                    'lastmod'    => formatSitemapLastmod($row['updated_at'] ?? null),
                    'changefreq' => 'weekly',
                    'priority'   => '0.7',
                ];
            }

            $brands = db()->query(
                'SELECT slug, updated_at FROM brands WHERE is_active = 1 ORDER BY updated_at DESC'
            )->fetchAll();

            foreach ($brands as $row) {
                $entries[] = [
                    'loc'        => absoluteUrl('/brand/' . $row['slug']),
                    'lastmod'    => formatSitemapLastmod($row['updated_at'] ?? null),
                    'changefreq' => 'monthly',
                    'priority'   => '0.7',
                ];
            }

            $posts = db()->query(
                "SELECT slug, updated_at, published_at FROM blog_posts
                 WHERE status = 'published' AND published_at <= NOW()
                 ORDER BY published_at DESC"
            )->fetchAll();

            foreach ($posts as $row) {
                $lastmod = $row['updated_at'] ?? $row['published_at'] ?? null;
                $entries[] = [
                    'loc'        => absoluteUrl('/blog/' . $row['slug']),
                    'lastmod'    => formatSitemapLastmod($lastmod),
                    'changefreq' => 'monthly',
                    'priority'   => '0.65',
                ];
            }

            $pages = db()->query(
                "SELECT slug, updated_at FROM pages WHERE status = 'active' AND slug NOT IN ('about')"
            )->fetchAll();

            foreach ($pages as $row) {
                $entries[] = [
                    'loc'        => absoluteUrl('/' . $row['slug']),
                    'lastmod'    => formatSitemapLastmod($row['updated_at'] ?? null),
                    'changefreq' => 'monthly',
                    'priority'   => '0.5',
                ];
            }
        } catch (\Throwable $e) {
            error_log('Sitemap build error: ' . $e->getMessage());
        }

        return $entries;
    }
}

if (!function_exists('buildRobotsTxt')) {
    function buildRobotsTxt(): string
    {
        $lines = ['User-agent: *', ''];

        if (!isSeoIndexable()) {
            $lines[] = 'Disallow: /';
            $lines[] = '';
            $lines[] = '# Sajt je u režimu pripreme (MAINTENANCE_MODE=true).';
            $lines[] = '# Postavite MAINTENANCE_MODE=false pre lansiranja za Google indeks.';
            return implode("\n", $lines);
        }

        $lines[] = 'Allow: /';

        foreach (seoConfig()['disallow_paths'] as $disallow) {
            $lines[] = 'Disallow: ' . $disallow;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . absoluteUrl('/sitemap.xml');
        $lines[] = '';

        return implode("\n", $lines);
    }
}

if (!function_exists('seoJsonLdOrganization')) {
    function seoJsonLdOrganization(): array
    {
        $cfg = seoConfig();

        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => $cfg['site_name'],
            'url'         => siteUrl(),
            'logo'        => absoluteUrl('/images/logos/egoire-logo.png'),
            'description' => $cfg['site_description'],
            'areaServed'  => [
                '@type' => 'Country',
                'name'  => 'Serbia',
            ],
        ];
    }
}

if (!function_exists('seoJsonLdWebSite')) {
    function seoJsonLdWebSite(): array
    {
        $cfg = seoConfig();

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => $cfg['site_name'],
            'url'             => siteUrl(),
            'description'     => $cfg['site_description'],
            'inLanguage'      => 'sr-RS',
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => siteUrl() . '/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }
}

if (!function_exists('seoJsonLdProduct')) {
    function seoJsonLdProduct(array $product, array $images = [], bool $inStock = true): array
    {
        $price = (float) ($product['sale_price'] ?: $product['price']);
        $image = '';

        if (!empty($images[0]['image_path'])) {
            $image = absoluteUrl($images[0]['image_path']);
        }

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['name'],
            'description' => $product['short_description'] ?? $product['meta_description'] ?? '',
            'sku'         => $product['sku'] ?? '',
            'url'         => absoluteUrl('/product/' . $product['slug']),
            'brand'       => [
                '@type' => 'Brand',
                'name'  => $product['brand_name'] ?? 'Egoire',
            ],
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => absoluteUrl('/product/' . $product['slug']),
                'priceCurrency' => 'RSD',
                'price'         => number_format($price, 2, '.', ''),
                'availability'  => $inStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ];

        if ($image !== '') {
            $schema['image'] = $image;
        }

        return $schema;
    }
}

if (!function_exists('seoJsonLdArticle')) {
    function seoJsonLdArticle(array $post): array
    {
        $schema = [
            '@context'      => 'https://schema.org',
            '@type'         => 'BlogPosting',
            'headline'      => $post['title'],
            'description'   => $post['excerpt'] ?? $post['meta_description'] ?? '',
            'url'           => absoluteUrl('/blog/' . $post['slug']),
            'datePublished' => date('c', strtotime($post['published_at'] ?? $post['created_at'])),
            'dateModified'  => date('c', strtotime($post['updated_at'] ?? $post['published_at'] ?? 'now')),
            'author'        => [
                '@type' => 'Organization',
                'name'  => seoConfig()['site_name'],
            ],
            'publisher'     => [
                '@type' => 'Organization',
                'name'  => seoConfig()['site_name'],
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => absoluteUrl('/images/logos/egoire-logo.png'),
                ],
            ],
            'inLanguage'    => 'sr-RS',
        ];

        if (!empty($post['featured_image'])) {
            $schema['image'] = absoluteUrl($post['featured_image']);
        }

        return $schema;
    }
}

if (!function_exists('renderJsonLd')) {
    /** @param array<int, array<string, mixed>> $graphs */
    function renderJsonLd(array $graphs): string
    {
        if ($graphs === []) {
            return '';
        }

        $payload = count($graphs) === 1 ? $graphs[0] : ['@context' => 'https://schema.org', '@graph' => $graphs];

        return '<script type="application/ld+json">'
            . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            . '</script>';
    }
}
