<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Instrument;

readonly class Card
{
    public function __construct(
        public ?string $number = null,
        public ?string $expiration = null,
        public ?string $cvv = null,
        public ?string $installments = null,
        public ?string $pin = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->number !== null) {
            $data['number'] = $this->number;
        }
        if ($this->expiration !== null) {
            $data['expiration'] = $this->expiration;
        }
        if ($this->cvv !== null) {
            $data['cvv'] = $this->cvv;
        }
        if ($this->installments !== null) {
            $data['installments'] = $this->installments;
        }
        if ($this->pin !== null) {
            $data['pin'] = $this->pin;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'] ?? null,
            expiration: $data['expiration'] ?? null,
            cvv: $data['cvv'] ?? null,
            installments: $data['installments'] ?? null,
            pin: $data['pin'] ?? null,
        );
    }
}
