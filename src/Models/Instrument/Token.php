<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Instrument;

use PlaceToPay\Checkout\Models\Common\Status;

readonly class Token
{
    public function __construct(
        public ?string $token = null,
        public ?string $subtoken = null,
        public ?string $franchise = null,
        public ?string $franchiseName = null,
        public ?string $issuerName = null,
        public ?string $lastDigits = null,
        public ?string $validDate = null,
        public ?string $installments = null,
        public ?Status $status = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->token !== null) {
            $data['token'] = $this->token;
        }
        if ($this->subtoken !== null) {
            $data['subtoken'] = $this->subtoken;
        }
        if ($this->franchise !== null) {
            $data['franchise'] = $this->franchise;
        }
        if ($this->franchiseName !== null) {
            $data['franchiseName'] = $this->franchiseName;
        }
        if ($this->issuerName !== null) {
            $data['issuerName'] = $this->issuerName;
        }
        if ($this->lastDigits !== null) {
            $data['lastDigits'] = $this->lastDigits;
        }
        if ($this->validDate !== null) {
            $data['validDate'] = $this->validDate;
        }
        if ($this->installments !== null) {
            $data['installments'] = $this->installments;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'] ?? null,
            subtoken: $data['subtoken'] ?? null,
            franchise: $data['franchise'] ?? null,
            franchiseName: $data['franchiseName'] ?? null,
            issuerName: $data['issuerName'] ?? null,
            lastDigits: $data['lastDigits'] ?? null,
            validDate: $data['validDate'] ?? null,
            installments: $data['installments'] ?? null,
            status: isset($data['status']) ? Status::fromArray($data['status']) : null,
        );
    }
}
