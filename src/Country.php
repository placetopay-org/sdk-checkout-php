<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout;

enum Country: string
{
    case COLOMBIA = 'CO';
    case ECUADOR = 'EC';

    public function defaultCurrency(): string
    {
        return match ($this) {
            self::COLOMBIA => 'COP',
            self::ECUADOR => 'USD',
        };
    }

    public function defaultLocale(): string
    {
        return match ($this) {
            self::COLOMBIA => 'es_CO',
            self::ECUADOR => 'es_EC',
        };
    }

    public function baseUrlOverride(Environment $environment): ?string
    {
        return match ($this) {
            self::COLOMBIA => null,
            self::ECUADOR => match ($environment) {
                Environment::PRODUCTION => 'https://checkout.placetopay.ec',
                Environment::SANDBOX => 'https://checkout-test.placetopay.ec',
            },
        };
    }
}
