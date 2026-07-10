<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Instrument;

readonly class Bank
{
    public function __construct(
        public ?string $code = null,
        public ?string $name = null,
        public ?bool $preselected = null,
        public ?string $returnUrl = null,
        public ?string $pseCode = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->code !== null) {
            $data['code'] = $this->code;
        }
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->preselected !== null) {
            $data['preselected'] = $this->preselected;
        }
        if ($this->returnUrl !== null) {
            $data['returnUrl'] = $this->returnUrl;
        }
        if ($this->pseCode !== null) {
            $data['pseCode'] = $this->pseCode;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            preselected: $data['preselected'] ?? null,
            returnUrl: $data['returnUrl'] ?? null,
            pseCode: $data['pseCode'] ?? null,
        );
    }
}
