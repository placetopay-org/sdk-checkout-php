<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Webhooks;

use PlaceToPay\Checkout\Models\Common\Status;

readonly class NotificationPayload
{
    public function __construct(
        public ?int $requestId = null,
        public ?string $reference = null,
        public ?string $signature = null,
        public ?Status $status = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            requestId: isset($data['requestId']) ? (int) $data['requestId'] : null,
            reference: $data['reference'] ?? null,
            signature: $data['signature'] ?? null,
            status: isset($data['status']) ? Status::fromArray($data['status']) : null,
        );
    }
}
