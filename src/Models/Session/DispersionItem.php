<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

use PlaceToPay\Checkout\Models\Common\AmountBase;

readonly class DispersionItem
{
    public function __construct(
        public ?string $agreement = null,
        public ?string $agreementType = null,
        public ?AmountBase $amount = null,
        public ?string $paymentMethod = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->agreement !== null) {
            $data['agreement'] = $this->agreement;
        }
        if ($this->agreementType !== null) {
            $data['agreementType'] = $this->agreementType;
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount->toArray();
        }
        if ($this->paymentMethod !== null) {
            $data['paymentMethod'] = $this->paymentMethod;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            agreement: $data['agreement'] ?? null,
            agreementType: $data['agreementType'] ?? null,
            amount: isset($data['amount']) ? AmountBase::fromArray($data['amount']) : null,
            paymentMethod: $data['paymentMethod'] ?? null,
        );
    }
}
