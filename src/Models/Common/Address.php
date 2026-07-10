<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class Address
{
    public function __construct(
        public ?string $street = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $phone = null,
        public ?string $postalCode = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->street !== null) {
            $data['street'] = $this->street;
        }
        if ($this->city !== null) {
            $data['city'] = $this->city;
        }
        if ($this->state !== null) {
            $data['state'] = $this->state;
        }
        if ($this->country !== null) {
            $data['country'] = $this->country;
        }
        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }
        if ($this->postalCode !== null) {
            $data['postalCode'] = $this->postalCode;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            phone: $data['phone'] ?? null,
            postalCode: $data['postalCode'] ?? null,
        );
    }
}
