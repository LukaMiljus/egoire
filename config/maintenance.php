<?php

declare(strict_types=1);

/**
 * Maintenance mode – javni sajt prikazuje samo Coming Soon.
 * Admini se prijavljuju na /maintenance-login da vide pun sajt.
 */
return [
    // Uključeno po defaultu; isključiti u .env: MAINTENANCE_MODE=false
    'enabled' => filter_var(
        getenv('MAINTENANCE_MODE') !== false ? getenv('MAINTENANCE_MODE') : 'true',
        FILTER_VALIDATE_BOOLEAN
    ),

    // Putanje dostupne bez admin prijave (bez vodećeg slasha)
    'public_paths' => [
        '',
        'coming-soon',
        'maintenance-login',
        'maintenance-logout',
        'admin/login',
    ],
];
