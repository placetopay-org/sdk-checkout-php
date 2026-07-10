<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class AmountBase
{
    public function __construct(
        public string $currency,
        public int|float $total,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'total' => $this->total,
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'],
            total: $data['total'],
        );
    }
}
