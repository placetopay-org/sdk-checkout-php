<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

use PlaceToPay\Checkout\Models\Common\Status;

readonly class CancelSessionResponse
{
    public function __construct(
        public Status $status,
        public ?SessionInformation $session = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = ['status' => $this->status->toArray()];
        if ($this->session !== null) {
            $data['session'] = $this->session->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            status: Status::fromArray($data['status']),
            session: isset($data['session']) ? SessionInformation::fromArray($data['session']) : null,
        );
    }
}
