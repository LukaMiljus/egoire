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
        $config    = shippingConfig();
        $enabled   = !empty($config['enabled']);
        $threshold = (float) ($config['free_threshold'] ?? 6000);
        $flatCost  = (float) ($config['cost'] ?? 500);

        if (!$enabled) {
            return [
                'shipping'           => 0.0,
                'shipping_threshold' => $threshold,
                'shipping_cost'      => $flatCost,
                'shipping_enabled'   => false,
                'has_free_shipping'  => true,
                'remaining'          => 0.0,
                'progress'           => 100.0,
            ];
        }

        $hasFree  = $subtotal >= $threshold;
        $shipping = $hasFree ? 0.0 : $flatCost;
        $remaining = max(0.0, $threshold - $subtotal);
        $progress  = $threshold > 0
            ? min(100.0, round(($subtotal / $threshold) * 100, 2))
            : 100.0;

        return [
            'shipping'           => round($shipping, 2),
            'shipping_threshold' => $threshold,
            'shipping_cost'      => $flatCost,
            'shipping_enabled'   => true,
            'has_free_shipping'  => $hasFree,
            'remaining'          => round($remaining, 2),
            'progress'           => $progress,
        ];
    }
}

if (!function_exists('shippingAnnouncementText')) {
    function shippingAnnouncementText(): string
    {
        if (!isShippingEnabled()) {
            return 'Besplatna dostava za sve porudžbine!';
        }

        $threshold = (float) (shippingConfig()['free_threshold'] ?? 6000);
        return 'Besplatna dostava za narudžbine preko ' . formatPrice($threshold) . '!';
    }
}
