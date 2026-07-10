<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class Amount
{
    /**
     * @param TaxDetail[] $taxes
     * @param AmountDetail[] $details
     */
    public function __construct(
        public string $currency,
        public int|float $total,
        public array $taxes = [],
        public array $details = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'currency' => $this->currency,
            'total' => $this->total,
        ];

        if ($this->taxes !== []) {
            $data['taxes'] = array_map(fn (TaxDetail $t) => $t->toArray(), $this->taxes);
        }
        if ($this->details !== []) {
            $data['details'] = array_map(fn (AmountDetail $d) => $d->toArray(), $this->details);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $taxes = [];
        foreach ($data['taxes'] ?? [] as $tax) {
            $taxes[] = TaxDetail::fromArray($tax);
        }

        $details = [];
        foreach ($data['details'] ?? [] as $detail) {
            $details[] = AmountDetail::fromArray($detail);
        }

        return new self(
            currency: $data['currency'],
            total: $data['total'],
            taxes: $taxes,
            details: $details,
        );
    }
}
