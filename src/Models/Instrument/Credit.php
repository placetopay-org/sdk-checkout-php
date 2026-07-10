<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Instrument;

readonly class Credit
{
    public function __construct(
        public ?string $code = null,
        public ?string $type = null,
        public ?string $groupCode = null,
        public ?string $installment = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->code !== null) {
            $data['code'] = $this->code;
        }
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->groupCode !== null) {
            $data['groupCode'] = $this->groupCode;
        }
        if ($this->installment !== null) {
            $data['installment'] = $this->installment;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? null,
            type: $data['type'] ?? null,
            groupCode: $data['groupCode'] ?? null,
            installment: $data['installment'] ?? null,
        );
    }
}
