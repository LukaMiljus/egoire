<?php

declare(strict_types=1);

/**
 * Poklon pakovanje – isključeno po defaultu.
 * Uključiti u .env: GIFT_WRAPPING_ENABLED=true
 */
return [
    'enabled' => filter_var(
        getenv('GIFT_WRAPPING_ENABLED') !== false ? getenv('GIFT_WRAPPING_ENABLED') : 'false',
        FILTER_VALIDATE_BOOLEAN
    ),
];
