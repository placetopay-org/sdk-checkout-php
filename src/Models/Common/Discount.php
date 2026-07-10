<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class Discount
{
    public function __construct(
        public ?string $code = null,
        public ?string $type = null,
        public int|float|null $amount = null,
        public int|float|null $base = null,
        public int|float|null $percent = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->code !== null) {
            $data['code'] = $this->code;
        }
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount;
        }
        if ($this->base !== null) {
            $data['base'] = $this->base;
        }
        if ($this->percent !== null) {
            $data['percent'] = $this->percent;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? null,
            type: $data['type'] ?? null,
            amount: $data['amount'] ?? null,
            base: $data['base'] ?? null,
            percent: $data['percent'] ?? null,
        );
    }
}
