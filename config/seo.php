<?php

declare(strict_types=1);

return [
    'site_name'        => (function_exists('envVar') ? envVar('SITE_NAME') : ($_ENV['SITE_NAME'] ?? getenv('SITE_NAME'))) ?: 'Egoire',
    'site_description' => (function_exists('envVar') ? envVar('SITE_DESCRIPTION') : ($_ENV['SITE_DESCRIPTION'] ?? getenv('SITE_DESCRIPTION'))) ?: 'Egoire – premium proizvodi za negu kose. Ekskluzivni brendovi i luxury hair care u Srbiji.',
    'locale'           => 'sr_RS',
    'language'         => 'sr-RS',
    'country'          => 'RS',
    'default_og_image' => '/images/logos/egoire-logo.png',
    'twitter_handle'   => (function_exists('envVar') ? envVar('TWITTER_HANDLE') : ($_ENV['TWITTER_HANDLE'] ?? getenv('TWITTER_HANDLE'))) ?: '',

    // Google Search Console – meta tag vrednost (samo kod, bez "meta name=")
    'google_site_verification' => (function_exists('envVar') ? envVar('GOOGLE_SITE_VERIFICATION') : ($_ENV['GOOGLE_SITE_VERIFICATION'] ?? getenv('GOOGLE_SITE_VERIFICATION'))) ?: '',

    // Bing Webmaster Tools (opciono)
    'bing_site_verification' => (function_exists('envVar') ? envVar('BING_SITE_VERIFICATION') : ($_ENV['BING_SITE_VERIFICATION'] ?? getenv('BING_SITE_VERIFICATION'))) ?: '',

    /** Statične javne stranice za sitemap */
    'static_paths' => [
        ['path' => '/',              'priority' => '1.0',  'changefreq' => 'daily'],
        ['path' => '/products',      'priority' => '0.9',  'changefreq' => 'daily'],
        ['path' => '/categories',   'priority' => '0.85', 'changefreq' => 'weekly'],
        ['path' => '/brands',       'priority' => '0.85', 'changefreq' => 'weekly'],
        ['path' => '/blog',         'priority' => '0.8',  'changefreq' => 'weekly'],
        ['path' => '/contact',      'priority' => '0.6',  'changefreq' => 'monthly'],
        ['path' => '/faq',          'priority' => '0.6',  'changefreq' => 'monthly'],
        ['path' => '/about',        'priority' => '0.5',  'changefreq' => 'monthly'],
        ['path' => '/gift-bag',     'priority' => '0.55', 'changefreq' => 'monthly'],
        ['path' => '/gift-card',    'priority' => '0.55', 'changefreq' => 'monthly'],
        ['path' => '/terms',        'priority' => '0.3',  'changefreq' => 'yearly'],
        ['path' => '/privacy',      'priority' => '0.3',  'changefreq' => 'yearly'],
        ['path' => '/shipping',    'priority' => '0.4',  'changefreq' => 'yearly'],
    ],

    /** Putanje koje se ne indeksiraju */
    'disallow_paths' => [
        '/admin/',
        '/api/',
        '/account/',
        '/cart',
        '/checkout',
        '/login',
        '/register',
        '/logout',
        '/forgot-password',
        '/reset-password',
        '/order-confirmation',
        '/maintenance-login',
        '/maintenance-logout',
        '/coming-soon',
        '/search',
        '/unsubscribe',
    ],
];
