<?php

declare(strict_types=1);

/**
 * Poštarina – isključeno po defaultu (besplatna dostava za sve porudžbine).
 * Uključiti u .env: SHIPPING_ENABLED=true
 */
return [
    'enabled' => filter_var(
        getenv('SHIPPING_ENABLED') !== false ? getenv('SHIPPING_ENABLED') : 'false',
        FILTER_VALIDATE_BOOLEAN
    ),

    // Besplatna dostava iznad ovog iznosa (RSD)
    'free_threshold' => (float) (getenv('SHIPPING_FREE_THRESHOLD') !== false
        ? getenv('SHIPPING_FREE_THRESHOLD')
        : '6000'),

    // Fiksna cena dostave ispod praga (RSD)
    'cost' => (float) (getenv('SHIPPING_COST') !== false
        ? getenv('SHIPPING_COST')
        : '500'),
];
