<?php

declare(strict_types=1);

if (!function_exists('giftWrappingConfig')) {
    function giftWrappingConfig(): array
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../../config/gift-wrapping.php';
        }
        return $config;
    }
}

if (!function_exists('isGiftWrappingEnabled')) {
    function isGiftWrappingEnabled(): bool
    {
        return !empty(giftWrappingConfig()['enabled']);
    }
}
