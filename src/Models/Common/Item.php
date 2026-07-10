<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class Item
{
    public function __construct(
        public ?string $sku = null,
        public ?string $name = null,
        public ?string $category = null,
        public int|float|null $qty = null,
        public int|float|null $price = null,
        public int|float|null $tax = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->sku !== null) {
            $data['sku'] = $this->sku;
        }
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->category !== null) {
            $data['category'] = $this->category;
        }
        if ($this->qty !== null) {
            $data['qty'] = $this->qty;
        }
        if ($this->price !== null) {
            $data['price'] = $this->price;
        }
        if ($this->tax !== null) {
            $data['tax'] = $this->tax;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            sku: $data['sku'] ?? null,
            name: $data['name'] ?? null,
            category: $data['category'] ?? null,
            qty: $data['qty'] ?? null,
            price: $data['price'] ?? null,
            tax: $data['tax'] ?? null,
        );
    }
}
