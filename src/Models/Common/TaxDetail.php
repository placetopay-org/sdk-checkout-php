<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class TaxDetail
{
    public function __construct(
        public ?string $kind = null,
        public int|float|null $amount = null,
        public int|float|null $base = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->kind !== null) {
            $data['kind'] = $this->kind;
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount;
        }
        if ($this->base !== null) {
            $data['base'] = $this->base;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'] ?? null,
            amount: $data['amount'] ?? null,
            base: $data['base'] ?? null,
        );
    }
}
