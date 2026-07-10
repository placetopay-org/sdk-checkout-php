<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

use PlaceToPay\Checkout\Models\Common\Status;

readonly class CreateSessionResponse
{
    public function __construct(
        public Status $status,
        public ?int $requestId = null,
        public ?string $processUrl = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = ['status' => $this->status->toArray()];
        if ($this->requestId !== null) {
            $data['requestId'] = $this->requestId;
        }
        if ($this->processUrl !== null) {
            $data['processUrl'] = $this->processUrl;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            status: Status::fromArray($data['status']),
            requestId: isset($data['requestId']) ? (int) $data['requestId'] : null,
            processUrl: $data['processUrl'] ?? null,
        );
    }
}
