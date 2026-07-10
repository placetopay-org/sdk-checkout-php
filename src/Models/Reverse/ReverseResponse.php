<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Reverse;

use PlaceToPay\Checkout\Models\Common\Status;
use PlaceToPay\Checkout\Models\Transaction\Transaction;

readonly class ReverseResponse
{
    public function __construct(
        public Status $status,
        public ?Transaction $payment = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = ['status' => $this->status->toArray()];
        if ($this->payment !== null) {
            $data['payment'] = $this->payment->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            status: Status::fromArray($data['status']),
            payment: isset($data['payment']) ? Transaction::fromArray($data['payment']) : null,
        );
    }
}
