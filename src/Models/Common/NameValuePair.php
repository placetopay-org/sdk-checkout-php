<?php

declare(strict_types=1);

namespace PlaceToPay\Checkout\Models\Common;

readonly class NameValuePair
{
    public function __construct(
        public string $keyword,
        public mixed $value = null,
        public ?string $displayOn = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = ['keyword' => $this->keyword];
        if ($this->value !== null) {
            $data['value'] = $this->value;
        }
        if ($this->displayOn !== null) {
            $data['displayOn'] = $this->displayOn;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            keyword: $data['keyword'],
            value: $data['value'] ?? null,
            displayOn: $data['displayOn'] ?? null,
        );
    }
}
