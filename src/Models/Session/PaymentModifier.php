<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Session;

readonly class PaymentModifier
{
    /**
     * @param array<string, mixed>|null $additional
     */
    public function __construct(
        public ?string $type = null,
        public ?string $code = null,
        public ?array $additional = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [];
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->code !== null) {
            $data['code'] = $this->code;
        }
        if ($this->additional !== null) {
            $data['additional'] = $this->additional;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? null,
            code: $data['code'] ?? null,
            additional: $data['additional'] ?? null,
        );
    }
}
