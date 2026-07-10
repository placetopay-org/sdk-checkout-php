<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Transaction;

readonly class AutopayRequest
{
    /**
     * @param array<string, mixed>|null $subscription
     * @param array<string, mixed>|null $charges
     */
    public function __construct(
        public ?array $subscription = null,
        public ?array $charges = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->subscription !== null) {
            $data['subscription'] = $this->subscription;
        }
        if ($this->charges !== null) {
            $data['charges'] = $this->charges;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            subscription: $data['subscription'] ?? null,
            charges: $data['charges'] ?? null,
        );
    }
}
