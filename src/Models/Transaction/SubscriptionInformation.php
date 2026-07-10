<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Transaction;

use PlaceToPay\Checkout\Models\Common\Status;

readonly class SubscriptionInformation
{
    /**
     * @param array<string, mixed> $instrument
     */
    public function __construct(
        public ?string $type = null,
        public ?Status $status = null,
        public array $instrument = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status->toArray();
        }
        if ($this->instrument !== []) {
            $data['instrument'] = $this->instrument;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            status: isset($data['status']) ? Status::fromArray($data['status']) : null,
            instrument: $data['instrument'] ?? [],
        );
    }
}
