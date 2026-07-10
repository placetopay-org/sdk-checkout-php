<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class AmountConversion
{
    public function __construct(
        public AmountBase $from,
        public AmountBase $to,
        public int|float|null $factor = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'from' => $this->from->toArray(),
            'to' => $this->to->toArray(),
        ];
        if ($this->factor !== null) {
            $data['factor'] = $this->factor;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: AmountBase::fromArray($data['from']),
            to: AmountBase::fromArray($data['to']),
            factor: $data['factor'] ?? null,
        );
    }
}
