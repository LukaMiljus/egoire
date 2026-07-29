<?php

declare(strict_types=1);

if (!function_exists('shippingConfig')) {
    function shippingConfig(): array
    {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../../config/shipping.php';
        }
        return $config;
    }
}

if (!function_exists('isShippingEnabled')) {
    function isShippingEnabled(): bool
    {
        return !empty(shippingConfig()['enabled']);
    }
}

if (!function_exists('calculateShipping')) {
    /**
     * @return array{
     *   shipping: float,
     *   shipping_threshold: float,
     *   shipping_cost: float,
     *   shipping_enabled: bool,
     *   has_free_shipping: bool,
     *   remaining: float,
     *   progress: float
     * }
     */
    function calculateShipping(float $subtotal): array
    {
        $flatCost = (float) (shippingConfig()['cost'] ?? 600);

        return [
            'shipping'           => round($flatCost, 2),
            'shipping_threshold' => 0.0,
            'shipping_cost'      => $flatCost,
            'shipping_enabled'   => true,
            'has_free_shipping'  => false,
            'remaining'          => 0.0,
            'progress'           => 0.0,
        ];
    }
}
