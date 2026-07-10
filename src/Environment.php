<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout;

enum Environment: string
{
    case SANDBOX = 'sandbox';
    case PRODUCTION = 'production';

    public function defaultBaseUrl(): string
    {
        return match ($this) {
            self::SANDBOX => 'https://checkout-test.placetopay.com',
            self::PRODUCTION => 'https://checkout.placetopay.com',
        };
    }
}
